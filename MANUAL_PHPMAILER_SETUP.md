# Manual PHP Mailer Installation Guide

## Quick Summary
Yes! You can absolutely install PHP Mailer manually without Composer. Follow these simple steps.

---

## Step 1: Download PHP Mailer

1. Go to: **https://github.com/PHPMailer/PHPMailer**
2. Click **"Code"** button (green button on the right)
3. Click **"Download ZIP"**
4. Extract the ZIP file to your computer

---

## Step 2: Create Vendor Directory (if needed)

In your project root, create this folder structure if it doesn't exist:

```
/vendor/
  /PHPMailer/
    /src/
```

---

## Step 3: Copy PHP Mailer Files

From the extracted PHPMailer ZIP:

1. Navigate to the `src/` folder inside the extracted files
2. You'll see 3 main files:
   - `PHPMailer.php`
   - `Exception.php`
   - `SMTP.php`

3. Copy these 3 files to: `/vendor/PHPMailer/src/`

Your final structure should look like:
```
your-project/
├── vendor/
│   └── PHPMailer/
│       └── src/
│           ├── PHPMailer.php
│           ├── Exception.php
│           └── SMTP.php
├── config/
├── auth/
├── utils/
└── public/
```

---

## Step 4: Configuration

The OTP system is already configured to use manual installation. Just set up your email credentials:

### In `/config/email.php`, add your settings:

```php
<?php

// SMTP Configuration
define('SMTP_HOST', 'smtp.gmail.com');          // Your SMTP host
define('SMTP_USER', 'your-email@gmail.com');    // Your email
define('SMTP_PASS', 'your-app-password');       // Your email password
define('SMTP_PORT', 587);                        // SMTP port (usually 587 or 465)

// From address
define('FROM_EMAIL', 'your-email@gmail.com');
define('FROM_NAME', 'VaultX');

// OTP Settings
define('OTP_LENGTH', 6);                         // 6-digit OTP
define('OTP_EXPIRY_MINUTES', 10);               // 10 minutes
```

---

## Step 5: Setup Database

Run the migration to create OTP tables:

```bash
mysql -u username -p database_name < /path/to/database-otp-migration.sql
```

Or manually run the SQL from `database-otp-migration.sql` in your database client.

---

## Step 6: Test It

1. Visit your registration page: `http://localhost/public/register.php`
2. Enter your details
3. Click "Send OTP to Email"
4. Check your email inbox for the OTP
5. Enter the OTP and verify

---

## Troubleshooting

### PHP Mailer files not found
- Make sure the path in `otp_manager.php` matches your folder structure
- The code looks for: `/vendor/PHPMailer/src/PHPMailer.php`

### Emails not sending
- Check your SMTP credentials in `/config/email.php`
- Make sure your email provider allows SMTP access
- For Gmail: Use an [App Password](https://support.google.com/accounts/answer/185833)
- Check server error logs: `tail -f error.log`

### Database errors
- Make sure the OTP migration was run successfully
- Check database connection in `/config/database.php`

### "Class not found" error
- Clear PHP opcode cache if you have one
- Make sure you copied the files to the correct location
- Restart your PHP server

---

## Email Provider Setup

### Gmail
1. Enable 2-factor authentication
2. Generate an App Password: https://support.google.com/accounts/answer/185833
3. Use the App Password in `SMTP_PASS`

```php
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_USER', 'your-email@gmail.com');
define('SMTP_PASS', 'your-16-character-app-password');
define('SMTP_PORT', 587);
```

### SendGrid
```php
define('SMTP_HOST', 'smtp.sendgrid.net');
define('SMTP_USER', 'apikey');
define('SMTP_PASS', 'SG.your-sendgrid-api-key');
define('SMTP_PORT', 587);
```

### Mailgun
```php
define('SMTP_HOST', 'smtp.mailgun.org');
define('SMTP_USER', 'postmaster@your-domain.mailgun.org');
define('SMTP_PASS', 'your-mailgun-password');
define('SMTP_PORT', 587);
```

### Mailtrap (for testing)
```php
define('SMTP_HOST', 'smtp.mailtrap.io');
define('SMTP_USER', 'your-mailtrap-user');
define('SMTP_PASS', 'your-mailtrap-password');
define('SMTP_PORT', 2525);
```

---

## That's It!

Your OTP system is now ready to use with manual PHP Mailer installation. No Composer required!

Need help? Check the other documentation files in the project.
