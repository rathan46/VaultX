# VaultX OTP System - Quick Start Checklist

## 🚀 Get Started in 5 Minutes

### Step 1: Install Dependencies (1 min)
```bash
cd /path/to/vaultx
composer install
composer require phpmailer/phpmailer
```

**Expected**: Creates `vendor/` directory with PHPMailer

### Step 2: Create Database Tables (1 min)
```bash
mysql -u root secure_vault < database-otp-migration.sql
```

**Verify**:
```bash
mysql -u root secure_vault -e "SHOW TABLES;" | grep otp_verification
```

### Step 3: Configure Email (2 min)

Edit `/config/email.php`:

**For Gmail**:
```php
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'your-email@gmail.com');
define('SMTP_PASS', 'your-app-password');  // From: https://myaccount.google.com/apppasswords
define('FROM_EMAIL', 'your-email@gmail.com');
define('FROM_NAME', 'VaultX Security');
```

**For Mailtrap** (free sandbox):
```php
define('SMTP_HOST', 'smtp.mailtrap.io');
define('SMTP_PORT', 2525);
define('SMTP_USER', 'your-mailtrap-user');
define('SMTP_PASS', 'your-mailtrap-pass');
define('FROM_EMAIL', 'noreply@vaultx.com');
define('FROM_NAME', 'VaultX Security');
```

### Step 4: Verify Setup (1 min)
```bash
php test_otp_setup.php
```

**Look for**: ✓ OK marks for all checks

### Step 5: Test It Out (Go!)

1. **Registration Test**
   - Open: http://localhost/public/register.php
   - Fill form with any details
   - Check email for OTP
   - Enter OTP to complete signup

2. **Login Test**
   - Open: http://localhost/public/index.php
   - Login with just-created account

3. **Password Reset Test**
   - Open: http://localhost/public/forgot_password.php
   - Enter email, check inbox
   - Enter OTP and new password

---

## 📋 Deployment Checklist

- [ ] Install Composer and dependencies
- [ ] Run database migration
- [ ] Configure email provider
- [ ] Test OTP delivery
- [ ] Test registration flow
- [ ] Test password reset
- [ ] Enable HTTPS in production
- [ ] Update security headers
- [ ] Set up monitoring/logging
- [ ] Configure backups

---

## 🔗 Important Files

| File | Purpose |
|------|---------|
| `/config/email.php` | ⚙️ Email configuration |
| `/utils/otp_manager.php` | 🔐 OTP logic |
| `/auth/register.php` | 📝 Registration backend |
| `/auth/forgot_password.php` | 🔑 Password reset backend |
| `/public/register.php` | 🎨 Registration form |
| `/public/forgot_password.php` | 🎨 Password reset form |

---

## ⚡ Quick Commands

```bash
# Test setup
php test_otp_setup.php

# Check database
mysql -u root secure_vault -e "SELECT * FROM otp_verification;"

# View logs
tail -f /var/log/php-fpm.log

# Clean expired OTPs
mysql -u root secure_vault -e "DELETE FROM otp_verification WHERE expires_at < NOW();"
```

---

## 🛠️ Troubleshooting

| Problem | Solution |
|---------|----------|
| "Failed to send OTP" | Check `/config/email.php` credentials |
| Email not received | Check SPAM folder, test with Mailtrap |
| "Class not found: PHPMailer" | Run `composer install` |
| "OTP table not found" | Run `mysql -u root secure_vault < database-otp-migration.sql` |
| Session errors | Clear browser cookies, check PHP session config |

---

## 📚 Full Documentation

- **Setup Guide**: Read `OTP_SETUP_GUIDE.md` for detailed configuration
- **Testing Guide**: Read `TESTING_GUIDE.md` for comprehensive test cases
- **Implementation Summary**: Read `OTP_IMPLEMENTATION_SUMMARY.md` for technical details

---

## 🎯 What's Working

✅ User registration with email OTP  
✅ OTP verification (6-digit, 10-min expiry)  
✅ Resend OTP (1-minute cooldown)  
✅ Password reset with OTP  
✅ Password hashing with bcrypt  
✅ Session management  
✅ Activity logging  
✅ Mobile-responsive UI  
✅ Real-time OTP timer  
✅ Error handling  

---

## 🔒 Security Features

✅ Password hashing  
✅ Email validation  
✅ OTP expiry  
✅ Session-based flow  
✅ HTTP-only sessions  
✅ Input sanitization  
✅ Activity logging  

---

## 💡 Pro Tips

1. **Use Mailtrap for testing** - Free sandbox, no real emails
2. **Generate Gmail app password** - Won't work with regular password
3. **Check spam folder** - Always check when testing
4. **Use test_otp_setup.php** - Verify everything before going live
5. **Save OTP codes** - Screenshot for testing reference
6. **Monitor logs** - Check `/var/log/php-fpm.log` for errors

---

## 🚀 Next: Production Setup

Once working in development:

1. Enable HTTPS (SSL/TLS certificate)
2. Add rate limiting
3. Enable CAPTCHA on forms
4. Set up email domain authentication (SPF/DKIM)
5. Configure backup database
6. Set up monitoring alerts
7. Add audit logging
8. Implement IP blocking for failures

---

## 📞 Support

For detailed help:
- Email configuration: See `OTP_SETUP_GUIDE.md` → "Email Provider Configuration"
- Testing issues: See `TESTING_GUIDE.md` → "Troubleshooting"
- Technical details: See `OTP_IMPLEMENTATION_SUMMARY.md`
- Installation: See `OTP_SETUP_GUIDE.md` → "Installation Steps"

---

**Status**: ✅ Ready to Deploy  
**Last Updated**: 2024  
**Version**: 1.0.0

Happy coding! 🎉
