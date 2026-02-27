# VaultX OTP System - Setup Checklist

Follow these steps in order to get everything working:

## Pre-Setup Verification

- [ ] PHP version 7.0 or higher
- [ ] MySQL/MariaDB database available
- [ ] Access to project files (can upload/create folders)
- [ ] Email account for SMTP (Gmail, Mailtrap, SendGrid, etc.)

---

## Step 1: Download & Install PHP Mailer

**Priority: CRITICAL** ⚠️

This is the #1 reason registration fails!

1. [ ] Go to: https://github.com/PHPMailer/PHPMailer
2. [ ] Click "Code" → "Download ZIP"
3. [ ] Extract the ZIP file
4. [ ] Find the `src` folder inside the extracted files
5. [ ] Create folder: `vendor/PHPMailer/src/` in your project root
6. [ ] Copy these 3 files from extracted `src/` to your `vendor/PHPMailer/src/`:
   - [ ] `PHPMailer.php`
   - [ ] `Exception.php`
   - [ ] `SMTP.php`
7. [ ] Verify files exist by checking in file manager or running `check_setup.php`

**If this step is not done, registration WILL NOT work.**

---

## Step 2: Configure Email Provider

**Choose ONE of these options:**

### Option A: Gmail (Free, Easy)
- [ ] Go to: https://myaccount.google.com/apppasswords
- [ ] Generate an "App Password" for Mail
- [ ] Copy the generated password
- [ ] Open `config/email.php`
- [ ] Set:
  ```php
  define('SMTP_HOST', 'smtp.gmail.com');
  define('SMTP_PORT', 587);
  define('SMTP_USER', 'your-email@gmail.com');
  define('SMTP_PASS', 'the-16-character-app-password');
  define('FROM_EMAIL', 'your-email@gmail.com');
  define('FROM_NAME', 'VaultX');
  ```

### Option B: Mailtrap (Free Testing)
- [ ] Sign up at: https://mailtrap.io (free tier)
- [ ] Go to your inbox settings
- [ ] Copy SMTP credentials
- [ ] Open `config/email.php`
- [ ] Set:
  ```php
  define('SMTP_HOST', 'smtp.mailtrap.io');
  define('SMTP_PORT', 465);
  define('SMTP_USER', 'your-mailtrap-username');
  define('SMTP_PASS', 'your-mailtrap-password');
  define('FROM_EMAIL', 'any-email@example.com');
  define('FROM_NAME', 'VaultX');
  ```

### Option C: SendGrid (Free tier available)
- [ ] Sign up at: https://sendgrid.com
- [ ] Create API key
- [ ] Verify a sender email
- [ ] Open `config/email.php`
- [ ] Set:
  ```php
  define('SMTP_HOST', 'smtp.sendgrid.net');
  define('SMTP_PORT', 587);
  define('SMTP_USER', 'apikey');
  define('SMTP_PASS', 'SG.your-full-api-key');
  define('FROM_EMAIL', 'your-verified-sender@example.com');
  define('FROM_NAME', 'VaultX');
  ```

---

## Step 3: Create Database Tables

**Priority: HIGH** ⚠️

1. [ ] Open your database management tool (phpMyAdmin, MySQL Workbench, etc.)
2. [ ] Run this SQL query:

```sql
CREATE TABLE IF NOT EXISTS otp_verification (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    otp VARCHAR(6) NOT NULL,
    otp_type ENUM('registration', 'password_reset') DEFAULT 'registration',
    is_verified BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_otp_type (otp_type),
    INDEX idx_expires (expires_at)
);

ALTER TABLE users ADD COLUMN IF NOT EXISTS email VARCHAR(255) UNIQUE;
```

3. [ ] Verify table was created (you should see `otp_verification` in your database)

---

## Step 4: Verify Everything Works

1. [ ] Open in browser: `http://your-server/vaultx/check_setup.php`
2. [ ] Check that ALL these show ✓:
   - [ ] PHP Version ✓
   - [ ] Session Extension ✓
   - [ ] PDO Extension ✓
   - [ ] Database Configuration ✓
   - [ ] Email Configuration ✓
   - [ ] Database Connection ✓
   - [ ] Users Table ✓
   - [ ] OTP Verification Table ✓
   - [ ] Email Column in Users Table ✓
   - [ ] PHP Mailer Files ✓

**If any show ✗, fix before continuing!**

3. [ ] Send a test email from the `check_setup.php` page
4. [ ] Verify you received the test email

---

## Step 5: Test Registration

1. [ ] Go to: `http://your-server/vaultx/public/register.php`
2. [ ] Fill in the form:
   - [ ] Username: (any username)
   - [ ] Email: (your email address)
   - [ ] Password: (8+ characters)
   - [ ] Confirm Password: (same as above)
3. [ ] Click "Send OTP to Email"
4. [ ] Check your email for the 6-digit code
5. [ ] Enter the code in the form
6. [ ] Click "Verify OTP"
7. [ ] You should see "Registration successful"

---

## Step 6: Test Password Reset

1. [ ] Go to: `http://your-server/vaultx/public/forgot_password.php`
2. [ ] Enter the email you just registered
3. [ ] Click "Send Reset OTP"
4. [ ] Check your email for the code
5. [ ] Enter the code and new password
6. [ ] Click "Reset Password"
7. [ ] Try logging in with new password

---

## Troubleshooting

### "An error occurred. Please try again." on Registration

**Check in this order:**

1. [ ] Run `check_setup.php` and look for failures (✗)
2. [ ] Is PHP Mailer folder created at `vendor/PHPMailer/src/`?
3. [ ] Do all 3 PHP Mailer files exist in that folder?
4. [ ] Is the `otp_verification` table created in database?
5. [ ] Is there an `email` column in the `users` table?
6. [ ] Are SMTP settings correct in `config/email.php`?

### "Failed to send OTP" Error

**Check these:**

1. [ ] Go to `check_setup.php` and test email sending there
2. [ ] Check your email provider:
   - Gmail: Did you generate an "App Password"?
   - Mailtrap: Did you copy correct SMTP settings?
   - SendGrid: Did you verify a sender email?
3. [ ] Is the email address correct in `config/email.php`?
4. [ ] Is the password/API key correct?

### "OTP Verification Table" Not Created

**Fix:**

1. [ ] Copy-paste the SQL from **Step 3**
2. [ ] Run it in your database tool
3. [ ] Refresh `check_setup.php` to verify

### Email Not Received

**Try these:**

1. [ ] Check spam/junk folder
2. [ ] Add `noreply@vaultx.com` to safe senders list
3. [ ] Try a different email address
4. [ ] Test with Mailtrap first (easier to debug)
5. [ ] Check email provider's sending logs

---

## Summary

**If all checkboxes above are checked ✓, your OTP system should work perfectly!**

If you still have issues:

1. Check `check_setup.php` for specific errors
2. Read `SETUP_INSTRUCTIONS.txt` for detailed guidance
3. Check browser console (F12 → Console) for JavaScript errors
4. Check server error logs for PHP errors

---

**Good luck! Let me know if you get stuck.** 🚀
