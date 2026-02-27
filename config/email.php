<?php
// Email Configuration for PHP Mailer
define('SMTP_HOST', getenv('SMTP_HOST') ?: 'smtp.gmail.com');
define('SMTP_PORT', getenv('SMTP_PORT') ?: 587);
define('SMTP_USER', getenv('SMTP_USER') ?: 'your-email@gmail.com');
define('SMTP_PASS', getenv('SMTP_PASS') ?: 'your-app-password');
define('FROM_EMAIL', getenv('FROM_EMAIL') ?: 'noreply@vaultx.com');
define('FROM_NAME', getenv('FROM_NAME') ?: 'VaultX Security');

// OTP Settings
define('OTP_LENGTH', 6);
define('OTP_EXPIRY_MINUTES', 10);
