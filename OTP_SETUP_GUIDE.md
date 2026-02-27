# VaultX OTP Authentication & Password Reset Setup Guide

This guide explains how to set up and use the OTP-based user registration and password reset system with PHP Mailer.

## Features Implemented

✅ **OTP-based User Registration**
- Users receive 6-digit OTP via email during registration
- 10-minute expiry time for OTP
- Resend OTP functionality with 1-minute cooldown
- Real-time timer showing remaining time

✅ **Password Reset with OTP**
- Secure password reset via email OTP
- Multi-step verification process
- Email verification before password change
- Activity logging for security

## Installation Steps

### 1. Database Setup

First, run the OTP migration script to create the necessary tables:

```bash
mysql -u root secure_vault < database-otp-migration.sql
```

This creates:
- `otp_verification` table for storing OTP records
- Adds `email` column to `users` table

### 2. Install PHP Mailer via Composer

```bash
composer require phpmailer/phpmailer
```

If you don't have composer.json, create one:

```bash
composer init
composer require phpmailer/phpmailer
```

This creates a `vendor` directory with PHP Mailer.

### 3. Configure Email Settings

Edit `/config/email.php` and update your email configuration:

```php
define('SMTP_HOST', 'smtp.gmail.com');        // Your SMTP host
define('SMTP_PORT', 587);                      // Usually 587 for TLS
define('SMTP_USER', 'your-email@gmail.com');  // Your email
define('SMTP_PASS', 'your-app-password');     // App password (not regular password)
define('FROM_EMAIL', 'noreply@vaultx.com');   // Sender email
define('FROM_NAME', 'VaultX Security');       // Sender name
```

### 4. Gmail Configuration (Recommended)

If using Gmail:

1. **Enable 2-Factor Authentication** in your Google Account
2. **Generate App Password:**
   - Go to: https://myaccount.google.com/apppasswords
   - Select "Mail" and "Windows Computer" (or your device)
   - Generate and copy the 16-character password
3. **Update email.php:**
   ```php
   define('SMTP_USER', 'your-email@gmail.com');
   define('SMTP_PASS', 'xxxx xxxx xxxx xxxx'); // The generated app password
   ```

### 5. Alternative Email Providers

**For SendGrid:**
```php
define('SMTP_HOST', 'smtp.sendgrid.net');
define('SMTP_PORT', 587);
define('SMTP_USER', 'apikey');
define('SMTP_PASS', 'SG.your-api-key-here');
```

**For Mailgun:**
```php
define('SMTP_HOST', 'smtp.mailgun.org');
define('SMTP_PORT', 587);
define('SMTP_USER', 'postmaster@your-domain.com');
define('SMTP_PASS', 'your-mailgun-password');
```

## User Registration Flow

### Step 1: Enter Registration Details
- Username
- Email address
- Password (8+ characters)
- Confirm password

### Step 2: Receive OTP
- User submits form
- 6-digit OTP is generated and sent to email
- User can resend OTP with 1-minute cooldown

### Step 3: Verify OTP
- User enters 6-digit OTP
- OTP is validated against expiry time (10 minutes)
- Account is created upon successful verification

### Step 4: Login
- User is redirected to login page
- Can now log in with username/password

## Password Reset Flow

### Step 1: Request Reset
- User enters email on forgot password page
- OTP is sent to registered email

### Step 2: Verify OTP
- User enters 6-digit OTP sent to email
- OTP is validated

### Step 3: Set New Password
- User enters new password (8+ characters)
- Password is updated in database

### Step 4: Login with New Password
- User can now log in with new password

## File Structure

```
├── config/
│   ├── config.php          (existing - session configuration)
│   ├── database.php        (existing - database connection)
│   └── email.php           (NEW - email configuration)
│
├── auth/
│   ├── register.php        (MODIFIED - OTP registration flow)
│   ├── login.php           (existing - login logic)
│   ├── logout.php          (existing - logout logic)
│   └── forgot_password.php (NEW - password reset flow)
│
├── utils/
│   ├── helpers.php         (existing)
│   └── otp_manager.php     (NEW - OTP management class)
│
├── public/
│   ├── index.php           (MODIFIED - added forgot password link)
│   ├── register.php        (MODIFIED - new registration UI)
│   ├── forgot_password.php (NEW - password reset UI)
│   └── dashboard.php       (existing)
│
└── database-otp-migration.sql (NEW - database schema)
```

## OTP Manager Class Methods

The `OTPManager` class handles all OTP operations:

### Creating OTP
```php
$otpManager = new OTPManager($pdo);
$otp = $otpManager->createOTP('user@email.com', 'registration');
// Returns: 6-digit OTP string
```

### Sending OTP Email
```php
$success = $otpManager->sendOTPEmail(
    'user@email.com', 
    $otp, 
    'registration' // or 'password_reset'
);
// Returns: true on success, false on failure
```

### Verifying OTP
```php
$isValid = $otpManager->verifyOTP(
    'user@email.com', 
    '123456',           // User entered OTP
    'registration'      // Type: 'registration' or 'password_reset'
);
// Returns: true if valid and not expired, false otherwise
```

### Checking OTP Validity
```php
$isValid = $otpManager->isOTPValid('user@email.com', 'registration');
// Returns: true if unexpired OTP exists
```

### Getting OTP Expiry Time
```php
$secondsLeft = $otpManager->getOTPExpiryTime('user@email.com', 'registration');
// Returns: seconds remaining before expiry
```

## Security Features

✅ **Password Hashing**: Uses bcrypt (PASSWORD_DEFAULT)
✅ **OTP Encryption**: Stored as plain text (better to encrypt in production)
✅ **Session Security**: HTTP-only cookies, SameSite strict
✅ **Rate Limiting**: 1-minute cooldown for resending OTP
✅ **Email Validation**: Validates email format before sending OTP
✅ **Password Requirements**: Minimum 8 characters
✅ **Expired OTP Cleanup**: Automatic cleanup of expired OTPs

## Testing

### Test Registration:
1. Go to `/public/register.php`
2. Fill in details with a valid email
3. Check email for OTP
4. Enter OTP to complete registration

### Test Password Reset:
1. Go to `/public/forgot_password.php`
2. Enter registered email
3. Check email for OTP
4. Enter OTP and new password
5. Login with new password

### Test Resend OTP:
1. During registration/reset, click "Resend OTP"
2. Wait for button to be enabled (1-minute cooldown)
3. Verify new OTP in email

## Troubleshooting

### "Failed to send OTP" Error

**Check email configuration:**
```php
// Test SMTP connection
$mail = new PHPMailer(true);
$mail->isSMTP();
$mail->Host = SMTP_HOST;
$mail->SMTPAuth = true;
$mail->Username = SMTP_USER;
$mail->Password = SMTP_PASS;
$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
$mail->Port = SMTP_PORT;

try {
    $mail->smtpConnect();
    echo "SMTP connected successfully";
} catch (Exception $e) {
    echo "SMTP Error: " . $e->getMessage();
}
```

### OTP Not Received

1. Check SPAM folder
2. Verify email configuration in `/config/email.php`
3. Check email logs: `error_log()` in PHP
4. Ensure `vendor/autoload.php` is loaded

### "Session expired" Error

- Session timeout may be too short
- Increase session timeout in `/config/config.php`
- Clear browser cookies and try again

### Database Errors

1. Verify tables exist:
   ```sql
   SHOW TABLES LIKE 'otp_verification';
   SHOW COLUMNS FROM users;
   ```

2. Run migration if tables missing:
   ```bash
   mysql -u root secure_vault < database-otp-migration.sql
   ```

## Production Considerations

1. **Encrypt OTP in Database**: Store encrypted OTP instead of plain text
2. **Rate Limiting**: Implement IP-based rate limiting for registration
3. **Email Verification**: Consider double opt-in for email verification
4. **OTP Length**: Consider increasing OTP to 8 digits for production
5. **HTTPS Only**: Always use HTTPS in production
6. **Environment Variables**: Move email credentials to .env file
7. **Logging**: Log all authentication attempts
8. **GDPR Compliance**: Implement data retention policies

## API Endpoints

### Registration
- **URL**: `/auth/register.php`
- **Method**: POST
- **Actions**: 
  - `request_otp`: Send OTP to email
  - `verify_otp`: Verify OTP and create account
  - `resend_otp`: Resend OTP

### Password Reset
- **URL**: `/auth/forgot_password.php`
- **Method**: POST
- **Actions**:
  - `request_reset`: Send reset OTP
  - `verify_otp`: Verify OTP
  - `reset_password`: Update password
  - `resend_otp`: Resend OTP

## Support

For issues or questions:
1. Check error logs: `error_log()`
2. Verify all files are in correct directories
3. Test SMTP connection manually
4. Check PHP version compatibility (PHP 7.0+)

## License

This implementation is part of the VaultX project.
