<?php
require_once '../config/database.php';
require_once '../config/email.php';
require_once '../utils/otp_manager.php';

$response = ['success' => false, 'message' => '', 'step' => 'initial'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'request_reset') {
        // Step 1: User submits email
        $email = trim($_POST['email'] ?? '');

        if (empty($email)) {
            $response['message'] = 'Email is required';
        } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $response['message'] = 'Invalid email format';
        } else {
            try {
                // Check if user exists
                $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
                $stmt->execute([$email]);

                if (!$stmt->fetch()) {
                    // For security, don't reveal if email exists
                    $response['success'] = true;
                    $response['message'] = 'If an account exists with this email, you will receive reset instructions.';
                    $response['step'] = 'verify_otp';
                } else {
                    // Create and send OTP
                    $otpManager = new OTPManager($pdo);
                    $otp = $otpManager->createOTP($email, 'password_reset');

                    if ($otp && $otpManager->sendOTPEmail($email, $otp, 'password_reset')) {
                        // Store email in session
                        $_SESSION['reset_email'] = $email;
                        $_SESSION['reset_timestamp'] = time();

                        $response['success'] = true;
                        $response['message'] = 'OTP sent to your email';
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

        if (empty($_SESSION['reset_email'])) {
            $response['message'] = 'Session expired. Please request password reset again.';
        } else if (empty($otp) || strlen($otp) !== 6 || !ctype_digit($otp)) {
            $response['message'] = 'Please enter a valid 6-digit OTP';
        } else {
            try {
                $email = $_SESSION['reset_email'];
                $otpManager = new OTPManager($pdo);

                if ($otpManager->verifyOTP($email, $otp, 'password_reset')) {
                    $_SESSION['otp_verified'] = true;
                    $response['success'] = true;
                    $response['message'] = 'OTP verified. You can now reset your password.';
                    $response['step'] = 'reset_password';
                } else {
                    $response['message'] = 'Invalid or expired OTP. Please try again.';
                }
            } catch (Exception $e) {
                $response['message'] = 'An error occurred. Please try again.';
                error_log($e->getMessage());
            }
        }

    } else if ($action === 'reset_password') {
        // Step 3: User submits new password
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        if (empty($_SESSION['reset_email']) || !$_SESSION['otp_verified']) {
            $response['message'] = 'Invalid session. Please request password reset again.';
        } else if (empty($password) || empty($confirm_password)) {
            $response['message'] = 'All fields are required';
        } else if (strlen($password) < 8) {
            $response['message'] = 'Password must be at least 8 characters';
        } else if ($password !== $confirm_password) {
            $response['message'] = 'Passwords do not match';
        } else {
            try {
                $email = $_SESSION['reset_email'];
                $password_hash = password_hash($password, PASSWORD_DEFAULT);

                // Update password
                $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE email = ?");
                $stmt->execute([$password_hash, $email]);

                // Log activity
                $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
                $stmt->execute([$email]);
                $user = $stmt->fetch();

                if ($user) {
                    $stmt = $pdo->prepare(
                        "INSERT INTO activity_log (user_id, action) VALUES (?, 'Password reset')"
                    );
                    $stmt->execute([$user['id']]);
                }

                // Clear session
                unset($_SESSION['reset_email']);
                unset($_SESSION['reset_timestamp']);
                unset($_SESSION['otp_verified']);

                $response['success'] = true;
                $response['message'] = 'Password reset successful! Redirecting to login...';
                $response['step'] = 'success';
                $response['redirect'] = '../auth/login.php';
            } catch (Exception $e) {
                $response['message'] = 'An error occurred. Please try again.';
                error_log($e->getMessage());
            }
        }

    } else if ($action === 'resend_otp') {
        // Resend OTP
        if (empty($_SESSION['reset_email'])) {
            $response['message'] = 'Session expired. Please request password reset again.';
        } else {
            try {
                $email = $_SESSION['reset_email'];
                $otpManager = new OTPManager($pdo);
                $otp = $otpManager->createOTP($email, 'password_reset');

                if ($otp && $otpManager->sendOTPEmail($email, $otp, 'password_reset')) {
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
?>
