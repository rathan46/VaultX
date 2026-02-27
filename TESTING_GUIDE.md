# VaultX OTP Testing Guide

This guide explains how to test the OTP authentication system during development.

## Quick Test Setup (3 Options)

### Option 1: Using Gmail (Recommended for Testing)

**Pros**: Free, reliable, no setup needed
**Cons**: Requires Google Account with 2FA

#### Steps:

1. **Enable 2-Factor Authentication on Google Account**
   - Go to: https://myaccount.google.com/
   - Enable 2-Step Verification

2. **Generate App Password**
   - Go to: https://myaccount.google.com/apppasswords
   - Select "Mail" and "Windows Computer"
   - Google generates a 16-character password (e.g., `xxxx xxxx xxxx xxxx`)

3. **Update `/config/email.php`**
   ```php
   define('SMTP_HOST', 'smtp.gmail.com');
   define('SMTP_PORT', 587);
   define('SMTP_USER', 'your-email@gmail.com');
   define('SMTP_PASS', 'xxxx xxxx xxxx xxxx');  // App password from Google
   define('FROM_EMAIL', 'your-email@gmail.com');
   define('FROM_NAME', 'VaultX Security');
   ```

4. **Test It**
   ```bash
   php test_otp_setup.php
   ```

### Option 2: Using Mailtrap (Best for Development)

**Pros**: Free, sandbox environment, catches all emails
**Cons**: Requires registration

#### Steps:

1. **Create Account**
   - Go to: https://mailtrap.io
   - Sign up for free account

2. **Get SMTP Credentials**
   - Go to Dashboard > Inbox > SMTP Settings
   - Copy SMTP host, port, username, password

3. **Update `/config/email.php`**
   ```php
   define('SMTP_HOST', 'smtp.mailtrap.io');
   define('SMTP_PORT', 465);  // or 2525 or 587
   define('SMTP_USER', 'your-mailtrap-username');
   define('SMTP_PASS', 'your-mailtrap-password');
   define('FROM_EMAIL', 'noreply@vaultx.com');
   define('FROM_NAME', 'VaultX Security');
   ```

4. **Test It**
   - All emails will appear in Mailtrap inbox
   - OTP code will be visible in email HTML

### Option 3: Using MailHog (Local Development)

**Pros**: Free, local only, no internet needed
**Cons**: Requires installation

#### Installation:

1. **Download MailHog**
   - Go to: https://github.com/mailhog/MailHog/releases
   - Download for your OS (Windows/Mac/Linux)

2. **Run MailHog**
   ```bash
   # On Windows
   MailHog.exe

   # On Mac/Linux
   ./mailhog
   ```

3. **Access Web UI**
   - Open: http://localhost:1025 (SMTP)
   - Open: http://localhost:8025 (Web UI)

4. **Update `/config/email.php`**
   ```php
   define('SMTP_HOST', 'localhost');
   define('SMTP_PORT', 1025);
   define('SMTP_USER', '');  // Empty for MailHog
   define('SMTP_PASS', '');  // Empty for MailHog
   define('FROM_EMAIL', 'noreply@vaultx.com');
   define('FROM_NAME', 'VaultX Security');
   ```

## Test Cases

### Test Case 1: User Registration with OTP

**Objective**: Verify complete registration flow

**Steps**:
1. Open http://localhost/public/register.php
2. Enter:
   - Username: `testuser`
   - Email: `test@example.com`
   - Password: `TestPassword123`
   - Confirm Password: `TestPassword123`
3. Click "Send OTP to Email"
4. Check email inbox for OTP
5. Copy OTP from email
6. Enter OTP in form
7. Click "Verify OTP"
8. Should see "Registration successful!"

**Expected Results**:
- ✅ User account created in database
- ✅ Email received with valid OTP
- ✅ OTP expires after 10 minutes
- ✅ Can login with new account

### Test Case 2: Resend OTP During Registration

**Objective**: Verify resend OTP functionality

**Steps**:
1. Start registration process
2. After receiving first OTP, click "Resend OTP"
3. Wait for new OTP in email
4. Use new OTP to verify
5. Should complete registration

**Expected Results**:
- ✅ Resend button disabled for 1 minute
- ✅ New OTP received
- ✅ Old OTP no longer works
- ✅ New OTP accepted for verification

### Test Case 3: OTP Expiry

**Objective**: Verify OTP expires after 10 minutes

**Steps**:
1. Get OTP from registration email
2. Wait 10+ minutes
3. Try to enter expired OTP
4. Should get "Invalid or expired OTP" error

**Expected Results**:
- ✅ Expired OTP rejected
- ✅ Timer shows 0:00 after expiry
- ✅ User must request new OTP

### Test Case 4: Password Reset Flow

**Objective**: Verify complete password reset

**Steps**:
1. Open http://localhost/public/forgot_password.php
2. Enter email of existing user
3. Check email for OTP
4. Enter OTP
5. Enter new password: `NewPassword456`
6. Confirm password: `NewPassword456`
7. Should see "Password reset successful!"
8. Login with new password

**Expected Results**:
- ✅ OTP sent to registered email
- ✅ Password updated in database
- ✅ Old password no longer works
- ✅ New password works for login

### Test Case 5: Invalid Email Format

**Objective**: Verify email validation

**Steps**:
1. Try to register with email: `invalid-email`
2. Should get "Invalid email format" error
3. Try with email: `test@`
4. Should get error

**Expected Results**:
- ✅ Invalid emails rejected
- ✅ Clear error message displayed

### Test Case 6: Password Too Short

**Objective**: Verify password validation

**Steps**:
1. Try to register with password: `short`
2. Should get "Password must be at least 8 characters" error

**Expected Results**:
- ✅ Short passwords rejected
- ✅ Clear error message displayed

### Test Case 7: Duplicate Username/Email

**Objective**: Verify uniqueness validation

**Steps**:
1. Register user: `testuser` with `test@example.com`
2. Try to register again with same username
3. Should get "Username or email already exists" error

**Expected Results**:
- ✅ Duplicate accounts prevented
- ✅ Clear error message

### Test Case 8: OTP Format Validation

**Objective**: Verify OTP format validation

**Steps**:
1. During OTP entry, try:
   - Letters: `abcdef` → Should be rejected
   - Too short: `123` → Should be rejected
   - Too long: `1234567` → Should only accept 6 digits
   - Valid: `123456` → Should work

**Expected Results**:
- ✅ Non-numeric rejected
- ✅ Wrong length rejected
- ✅ Exactly 6 digits required

### Test Case 9: Session Timeout

**Objective**: Verify session handling

**Steps**:
1. Start registration
2. Close browser or clear cookies
3. Try to submit OTP
4. Should get "Session expired" error

**Expected Results**:
- ✅ Session validation works
- ✅ Clear error message

### Test Case 10: Email Notification Check

**Objective**: Verify email content

**Steps**:
1. Register new user
2. Check email received
3. Verify email contains:
   - Clear subject line
   - OTP code displayed
   - Expiry time mentioned
   - VaultX branding

**Expected Results**:
- ✅ Email HTML formatted correctly
- ✅ OTP clearly visible
- ✅ Professional appearance

## Database Verification

### Check Registered Users
```sql
SELECT id, username, email, created_at FROM users;
```

### Check OTP Records
```sql
SELECT email, otp, otp_type, is_verified, expires_at FROM otp_verification;
```

### Check Activity Log
```sql
SELECT user_id, action, created_at FROM activity_log;
```

## Debugging

### Enable Debug Mode

Add to top of files:
```php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/debug.log');
```

### Check Error Logs
```bash
tail -f /var/log/php-fpm.log
tail -f /path/to/debug.log
```

### Check Email Logs
In OTP Manager:
```php
error_log("OTP created: " . $otp . " for " . $email);
error_log("Mailer Error: " . $mail->ErrorInfo);
```

### Database Debugging
```sql
-- Check if OTP table exists
SHOW TABLES LIKE 'otp_verification';

-- Check OTP table structure
DESCRIBE otp_verification;

-- Check pending OTPs
SELECT * FROM otp_verification 
WHERE is_verified = FALSE 
AND expires_at > NOW();
```

## Browser Developer Tools

### Network Tab
- Watch API calls to `/auth/register.php`
- Check response JSON for errors
- Verify headers and timing

### Console Tab
- Check for JavaScript errors
- View fetch errors
- Debug timer functions

### Application Tab
- Check session cookies
- Verify HTTP-only flag
- Check SameSite setting

## Performance Testing

### Test Load Times
- Registration page load: Should be < 1s
- OTP sending: Should be < 3s
- OTP verification: Should be < 1s
- Password reset: Should be < 2s

### Test Concurrent Users
- Multiple registrations simultaneously
- Multiple OTP requests
- Rate limiting (if configured)

## Security Testing

### Test SQL Injection
- Username: `'; DROP TABLE users; --`
- Email: `test' OR '1'='1'@example.com`
- OTP: `' OR '1'='1`

Expected: All rejected safely

### Test XSS
- Username: `<script>alert('xss')</script>`
- Email: `test<img src=x onerror=alert('xss')>@example.com`

Expected: All escaped safely

### Test CSRF
- Form validation should include CSRF tokens
- Cross-origin requests should be blocked

## Troubleshooting

### "Failed to send OTP"
1. Check SMTP credentials in `/config/email.php`
2. Test SMTP connection: `php test_otp_setup.php`
3. Check firewall/ports (587 for Gmail)
4. Check server logs for Mailer errors

### Email Not Received
1. Check SPAM folder
2. Check email logs in `/config/email.php` configuration
3. Verify email address is correct
4. Check Mailtrap/MailHog inbox

### OTP Not Working
1. Verify OTP format (6 digits, numeric only)
2. Check expiry time
3. Verify database has OTP record
4. Check timezone settings

### Registration Fails
1. Check email format
2. Check password meets requirements
3. Check for duplicate username
4. Check database permissions

### Browser Console Errors
1. Check network tab for 404s
2. Verify all CSS/JS files exist
3. Check for CSP violations
4. Check CORS settings

## Quick Reference

| Component | File | Purpose |
|-----------|------|---------|
| OTP Manager | `utils/otp_manager.php` | Generates/verifies OTPs |
| Email Config | `config/email.php` | SMTP settings |
| Registration | `auth/register.php` | Backend logic |
| Reg UI | `public/register.php` | Frontend form |
| Password Reset | `auth/forgot_password.php` | Backend logic |
| Reset UI | `public/forgot_password.php` | Frontend form |
| Login | `public/index.php` | Login page |
| Test | `test_otp_setup.php` | Verification script |

## Next Steps After Testing

1. ✅ Update security settings for production
2. ✅ Enable HTTPS
3. ✅ Add rate limiting
4. ✅ Configure backups
5. ✅ Set up monitoring
6. ✅ Enable audit logging
7. ✅ Add CAPTCHA to forms
8. ✅ Implement email verification

---

**Happy Testing!**
