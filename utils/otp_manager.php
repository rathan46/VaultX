<?php
require_once __DIR__ . '/../config/email.php';
require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class OTPManager {
    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Generate a random OTP
     */
    public function generateOTP($length = OTP_LENGTH) {
        return str_pad(random_int(0, pow(10, $length) - 1), $length, '0', STR_PAD_LEFT);
    }

    /**
     * Create and store OTP
     */
    public function createOTP($email, $type = 'registration') {
        try {
            // Delete previous OTPs for this email and type
            $stmt = $this->pdo->prepare(
                "DELETE FROM otp_verification WHERE email = ? AND otp_type = ? AND expires_at > NOW()"
            );
            $stmt->execute([$email, $type]);

            $otp = $this->generateOTP();
            $expiryTime = date('Y-m-d H:i:s', time() + (OTP_EXPIRY_MINUTES * 60));

            $stmt = $this->pdo->prepare(
                "INSERT INTO otp_verification (email, otp, otp_type, expires_at) 
                 VALUES (?, ?, ?, ?)"
            );
            $stmt->execute([$email, $otp, $type, $expiryTime]);

            return $otp;
        } catch (Exception $e) {
            error_log("Error creating OTP: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send OTP via email
     */
    public function sendOTPEmail($email, $otp, $type = 'registration') {
        try {
            $mail = new PHPMailer(true);

            // Server settings
            $mail->isSMTP();
            $mail->Host = SMTP_HOST;
            $mail->SMTPAuth = true;
            $mail->Username = SMTP_USER;
            $mail->Password = SMTP_PASS;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = SMTP_PORT;

            // Recipients
            $mail->setFrom(FROM_EMAIL, FROM_NAME);
            $mail->addAddress($email);

            // Content
            $mail->isHTML(true);
            $mail->Subject = $type === 'registration' 
                ? 'VaultX - Verify Your Email' 
                : 'VaultX - Password Reset OTP';

            // Email body
            $body = $this->getEmailTemplate($otp, $type);
            $mail->Body = $body;
            $mail->AltBody = strip_tags($body);

            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Mailer Error: " . $mail->ErrorInfo);
            return false;
        }
    }

    /**
     * Verify OTP
     */
    public function verifyOTP($email, $otp, $type = 'registration') {
        try {
            $stmt = $this->pdo->prepare(
                "SELECT id FROM otp_verification 
                 WHERE email = ? AND otp = ? AND otp_type = ? 
                 AND is_verified = FALSE AND expires_at > NOW()"
            );
            $stmt->execute([$email, $otp, $type]);

            if ($stmt->fetch()) {
                // Mark as verified
                $stmt = $this->pdo->prepare(
                    "UPDATE otp_verification SET is_verified = TRUE 
                     WHERE email = ? AND otp = ? AND otp_type = ?"
                );
                $stmt->execute([$email, $otp, $type]);
                return true;
            }

            return false;
        } catch (Exception $e) {
            error_log("Error verifying OTP: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if OTP is valid and not expired
     */
    public function isOTPValid($email, $type = 'registration') {
        try {
            $stmt = $this->pdo->prepare(
                "SELECT otp FROM otp_verification 
                 WHERE email = ? AND otp_type = ? 
                 AND is_verified = FALSE AND expires_at > NOW()
                 ORDER BY created_at DESC LIMIT 1"
            );
            $stmt->execute([$email, $type]);
            return $stmt->fetch() ? true : false;
        } catch (Exception $e) {
            error_log("Error checking OTP validity: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get remaining time for OTP
     */
    public function getOTPExpiryTime($email, $type = 'registration') {
        try {
            $stmt = $this->pdo->prepare(
                "SELECT TIMESTAMPDIFF(SECOND, NOW(), expires_at) as seconds_left 
                 FROM otp_verification 
                 WHERE email = ? AND otp_type = ? 
                 AND is_verified = FALSE AND expires_at > NOW()
                 ORDER BY created_at DESC LIMIT 1"
            );
            $stmt->execute([$email, $type]);
            $result = $stmt->fetch();
            return $result ? $result['seconds_left'] : 0;
        } catch (Exception $e) {
            error_log("Error getting OTP expiry: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get email template
     */
    private function getEmailTemplate($otp, $type) {
        $title = $type === 'registration' ? 'Verify Your Email' : 'Reset Your Password';
        $message = $type === 'registration' 
            ? 'Thank you for signing up! Please verify your email with the OTP below.' 
            : 'You requested to reset your password. Please use the OTP below to proceed.';

        return "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; background-color: #f4f4f4; }
                .container { max-width: 600px; margin: 0 auto; background-color: #ffffff; padding: 20px; border-radius: 8px; }
                .header { text-align: center; color: #333; }
                .otp-box { background-color: #f9f9f9; padding: 20px; border-radius: 5px; text-align: center; margin: 20px 0; }
                .otp { font-size: 32px; font-weight: bold; color: #007bff; letter-spacing: 5px; }
                .footer { text-align: center; color: #666; font-size: 12px; margin-top: 20px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>VaultX</h1>
                    <h2>$title</h2>
                </div>
                <p>$message</p>
                <div class='otp-box'>
                    <p>Your One-Time Password:</p>
                    <div class='otp'>$otp</div>
                </div>
                <p style='color: #666;'>This OTP will expire in " . OTP_EXPIRY_MINUTES . " minutes.</p>
                <p style='color: #999;'>If you didn't request this, please ignore this email.</p>
                <div class='footer'>
                    <p>&copy; 2024 VaultX. All rights reserved.</p>
                </div>
            </div>
        </body>
        </html>
        ";
    }

    /**
     * Clean expired OTPs
     */
    public function cleanExpiredOTPs() {
        try {
            $stmt = $this->pdo->prepare(
                "DELETE FROM otp_verification WHERE expires_at < NOW()"
            );
            $stmt->execute();
        } catch (Exception $e) {
            error_log("Error cleaning expired OTPs: " . $e->getMessage());
        }
    }
}
