# Registration Error Debugging Guide

## Quick Troubleshooting Checklist

### 1. Check if PHP Mailer files exist
Navigate to your project folder and verify these files exist:
```
/vendor/PHPMailer/src/
├── PHPMailer.php
├── Exception.php
└── SMTP.php
```

**If missing:** Download from https://github.com/PHPMailer/PHPMailer and extract to `/vendor/PHPMailer/src/`

---

### 2. Check Email Configuration
Open `/config/email.php` and verify SMTP credentials are set:
```php
define('SMTP_HOST', 'smtp.gmail.com');      // Your SMTP server
define('SMTP_PORT', 587);                   // Usually 587 or 465
define('SMTP_USER', 'your-email@gmail.com'); // Your email
define('SMTP_PASS', 'your-app-password');   // App password, not regular password
define('FROM_EMAIL', 'noreply@vaultx.com'); // Sender email
```

**Important:** For Gmail, use an **App Password**, not your regular password!

---

### 3. Check Database
Run this SQL to verify the OTP table exists:
```sql
DESC otp_verification;
```

**If it doesn't exist:** Run the migration:
```sql
CREATE TABLE otp_verification (
    id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(255) NOT NULL,
    otp VARCHAR(6) NOT NULL,
    otp_type VARCHAR(50) DEFAULT 'registration',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NOT NULL,
    verified BOOLEAN DEFAULT FALSE,
    INDEX idx_email (email),
    INDEX idx_expires (expires_at)
);
```

---

### 4. Check Users Table
Make sure the `email` column exists in the `users` table:
```sql
DESC users;
```

**If email column is missing:** Add it:
```sql
ALTER TABLE users ADD COLUMN email VARCHAR(255) UNIQUE NOT NULL;
```

---

### 5. Browser Console Error
1. Open your browser (Chrome, Firefox, etc.)
2. Press **F12** to open Developer Tools
3. Go to the **Console** tab
4. Try to register again
5. Share any red error messages

---

## Common Errors and Solutions

### Error: "Failed to send OTP"
**Cause:** PHP Mailer files missing or email config wrong
**Solution:** 
- Verify PHP Mailer files exist in `/vendor/PHPMailer/src/`
- Check SMTP credentials in `/config/email.php`
- For Gmail: Use an **App Password** instead of your regular password

### Error: "Session expired"
**Cause:** `session_start()` not called
**Solution:** Already fixed in register.php

### Error: "OTP table doesn't exist"
**Cause:** Database migration not run
**Solution:** Run the SQL migration above

### Error: "Invalid email format" or validation errors
**Cause:** Form validation failing
**Solution:** Check that you're entering valid values in the form

---

## Testing Without Email

If you want to test without sending real emails, use **Mailtrap**:

1. Go to https://mailtrap.io
2. Sign up free
3. Get your SMTP credentials
4. Update `/config/email.php` with Mailtrap credentials
5. All test emails go to your Mailtrap inbox (you can view them in your browser)

---

## Enable Detailed Logging

Edit `/config/email.php` and add this line:
```php
define('SMTP_DEBUG', true); // Set to true for detailed SMTP logs
```

This will show detailed error messages in logs when sending emails fails.

---

## Still Getting Errors?

Please share:
1. Exact error message shown on page
2. Browser console errors (F12 > Console tab)
3. Server error logs (check `/logs` folder if it exists)
4. Whether you've completed the "Manual PHP Mailer Setup" guide
