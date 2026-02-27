<?php
session_start();
require_once '../config/database.php';
require_once '../config/email.php';
require_once '../utils/helpers.php';
require_once '../utils/otp_manager.php';

$response = ['success' => false, 'message' => '', 'step' => 'initial'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'request_otp') {
        // Step 1: User submits email and password, OTP is sent
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        // Validation
        if (empty($username) || empty($email) || empty($password)) {
            $response['message'] = 'All fields are required';
        } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $response['message'] = 'Invalid email format';
        } else if (strlen($password) < 8) {
            $response['message'] = 'Password must be at least 8 characters';
        } else if ($password !== $confirm_password) {
            $response['message'] = 'Passwords do not match';
        } else {
            try {
                // Check if username or email already exists
                $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
                $stmt->execute([$username, $email]);

                if ($stmt->fetch()) {
                    $response['message'] = 'Username or email already exists';
                } else {
                    // Create OTP
                    $otpManager = new OTPManager($pdo);
                    $otp = $otpManager->createOTP($email, 'registration');

                    if ($otp && $otpManager->sendOTPEmail($email, $otp, 'registration')) {
                        // Store temporary registration data in session
                        $_SESSION['temp_register'] = [
                            'username' => $username,
                            'email' => $email,
                            'password' => password_hash($password, PASSWORD_DEFAULT),
                            'timestamp' => time()
                        ];

                        $response['success'] = true;
                        $response['message'] = 'OTP sent to your email. Please verify to complete registration.';
                        $response['step'] = 'verify_otp';
                    } else {
                        $response['message'] = 'Failed to send OTP. Please try again.';
                    }
                }
            } catch (Exception $e) {
                $response['message'] = 'An error occurred. Please try again.';
                error_log($e->getMessage());
            }
        }

    } else if ($action === 'verify_otp') {
        // Step 2: User submits OTP
        $otp = trim($_POST['otp'] ?? '');

        if (empty($_SESSION['temp_register'])) {
            $response['message'] = 'Session expired. Please register again.';
        } else if (empty($otp) || strlen($otp) !== 6 || !ctype_digit($otp)) {
            $response['message'] = 'Please enter a valid 6-digit OTP';
        } else {
            try {
                $email = $_SESSION['temp_register']['email'];
                $otpManager = new OTPManager($pdo);

                if ($otpManager->verifyOTP($email, $otp, 'registration')) {
                    // OTP verified, create user account
                    $userUID = generateUserUID($pdo);
                    $stmt = $pdo->prepare(
                        "INSERT INTO users (user_uid, username, email, password_hash)
                         VALUES (?, ?, ?, ?)"
                    );
                    $stmt->execute([
                        $userUID,
                        $_SESSION['temp_register']['username'],
                        $_SESSION['temp_register']['email'],
                        $_SESSION['temp_register']['password']
                    ]);

                    // Log activity
                    $userId = $pdo->lastInsertId();
                    $stmt = $pdo->prepare(
                        "INSERT INTO activity_log (user_id, action) VALUES (?, 'User registered')"
                    );
                    $stmt->execute([$userId]);

                    // Clear session
                    unset($_SESSION['temp_register']);

                    $response['success'] = true;
                    $response['message'] = 'Registration successful! Redirecting to login...';
                    $response['step'] = 'success';
                    $response['redirect'] = '../auth/login.php';
                } else {
                    $response['message'] = 'Invalid or expired OTP. Please try again.';
                }
            } catch (Exception $e) {
                $response['message'] = 'An error occurred. Please try again.';
                error_log($e->getMessage());
            }
        }

    } else if ($action === 'resend_otp') {
        // Resend OTP
        if (empty($_SESSION['temp_register'])) {
            $response['message'] = 'Session expired. Please register again.';
        } else {
            try {
                $email = $_SESSION['temp_register']['email'];
                $otpManager = new OTPManager($pdo);
                $otp = $otpManager->createOTP($email, 'registration');

                if ($otp && $otpManager->sendOTPEmail($email, $otp, 'registration')) {
                    $response['success'] = true;
                    $response['message'] = 'OTP resent to your email';
                    $response['step'] = 'verify_otp';
                } else {
                    $response['message'] = 'Failed to resend OTP. Please try again.';
                }
            } catch (Exception $e) {
                $response['message'] = 'An error occurred. Please try again.';
                error_log($e->getMessage());
            }
        }
    }

    // Return JSON response
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

// If already logged in, redirect to dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: ../public/dashboard.php");
    exit;
}
?>
