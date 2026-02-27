# Setting Up VaultX OTP System on Your Local Machine

## The Problem
You're seeing "NOT FOUND" errors in `check_setup.php` because the project files haven't been copied to your local XAMPP installation yet.

## Solution: Copy Project Files

### Step 1: Download Project Files
1. Go to v0.app
2. Click the three dots (⋮) in the top right
3. Click "Download ZIP"
4. Extract the ZIP file

### Step 2: Copy to XAMPP
On Windows:
```
C:\xampp\htdocs\VaultX\
```

On Mac:
```
/Applications/XAMPP/xamppfiles/htdocs/VaultX/
```

On Linux:
```
/opt/lampp/htdocs/VaultX/
```

Your folder structure should look like:
```
VaultX/
├── auth/
├── config/
├── public/
├── utils/
├── storage/
├── check_setup.php
└── ... (other files)
```

### Step 3: Set Database Name
Make sure your MySQL database is named **`secure_vault`** (as defined in `/config/config.php`)

If your database has a different name:
1. Edit `/config/config.php`
2. Change `DB_NAME` to your database name:
```php
define('DB_NAME', 'your_database_name');
```

### Step 4: Create Required Tables
Run this SQL in your MySQL database:

```sql
-- If users table doesn't have email column, add it:
ALTER TABLE users ADD COLUMN email VARCHAR(255) UNIQUE DEFAULT NULL;

-- Create OTP verification table:
CREATE TABLE IF NOT EXISTS otp_verification (
    id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(255) NOT NULL UNIQUE,
    otp VARCHAR(6) NOT NULL,
    type ENUM('registration', 'password_reset') DEFAULT 'registration',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP,
    verified BOOLEAN DEFAULT FALSE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### Step 5: Download PHP Mailer
1. Go to: https://github.com/PHPMailer/PHPMailer/archive/master.zip
2. Extract the ZIP
3. Create folder: `VaultX/vendor/PHPMailer/src/`
4. Copy these 3 files from the extracted ZIP:
   - `src/PHPMailer.php`
   - `src/Exception.php`
   - `src/SMTP.php`

### Step 6: Configure Email (Optional for Testing)
Edit `/config/email.php` with your SMTP credentials:

**For Gmail:**
```php
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'your-email@gmail.com');
define('SMTP_PASS', 'your-app-password');  // Use App Password, not regular password
define('FROM_EMAIL', 'your-email@gmail.com');
```

**For Testing (Mailtrap - Free):**
1. Go to: https://mailtrap.io
2. Sign up for free
3. Get SMTP credentials
4. Add them to `/config/email.php`

### Step 7: Verify Setup
1. Open browser: `http://localhost/VaultX/check_setup.php`
2. All items should show green checkmarks
3. If any are red, follow the error message

## Quick Verification Checklist
- [ ] Files copied to `C:\xampp\htdocs\VaultX\` (or equivalent)
- [ ] Database `secure_vault` created
- [ ] Tables created with SQL commands above
- [ ] PHP Mailer files in `/vendor/PHPMailer/src/`
- [ ] `check_setup.php` shows all green checkmarks
- [ ] Can access `http://localhost/VaultX/public/register.php`

## Testing Registration
1. Go to: `http://localhost/VaultX/public/register.php`
2. Enter test credentials
3. Click "Send OTP to Email"
4. Check email for OTP code
5. Enter OTP and verify

## Troubleshooting

**Error: Database connection failed**
- Make sure MySQL is running
- Check database name in `/config/config.php`
- Verify MySQL credentials (user: root, pass: empty by default)

**Error: Files not found in require_once**
- Verify files are copied to correct location
- Check file paths in error message
- Use absolute paths if needed

**Error: PHP Mailer not found**
- Download from: https://github.com/PHPMailer/PHPMailer/archive/master.zip
- Extract and copy `/src/` files to `/vendor/PHPMailer/src/`
- Verify all 3 files exist: PHPMailer.php, Exception.php, SMTP.php

**Error: OTP not sending**
- Check SMTP credentials in `/config/email.php`
- Use Mailtrap for free testing
- Check email spam folder
- Verify email extension is enabled in PHP

## Need Help?
1. Run `check_setup.php` and screenshot the results
2. Check `/config/config.php` database settings
3. Verify PHP Mailer files are in correct location
4. Check error logs in XAMPP
