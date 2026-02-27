<?php
/**
 * VaultX OTP Setup Verification Script
 * 
 * Run this script to verify all OTP components are properly set up
 * Usage: php test_otp_setup.php
 */

echo "========================================\n";
echo "VaultX OTP Setup Verification\n";
echo "========================================\n\n";

$errors = [];
$warnings = [];
$success = [];

// Check 1: PHP Version
echo "[1] Checking PHP Version... ";
if (version_compare(PHP_VERSION, '7.0.0', '>=')) {
    echo "✓ OK\n";
    $success[] = "PHP version: " . PHP_VERSION;
} else {
    echo "✗ FAIL\n";
    $errors[] = "PHP 7.0+ required (current: " . PHP_VERSION . ")";
}

// Check 2: Required Extensions
echo "[2] Checking Required Extensions... ";
$required_extensions = ['pdo', 'pdo_mysql'];
$missing_extensions = [];
foreach ($required_extensions as $ext) {
    if (!extension_loaded($ext)) {
        $missing_extensions[] = $ext;
    }
}

if (empty($missing_extensions)) {
    echo "✓ OK\n";
    $success[] = "All required extensions loaded";
} else {
    echo "✗ FAIL\n";
    $errors[] = "Missing extensions: " . implode(', ', $missing_extensions);
}

// Check 3: Composer/PHP Mailer
echo "[3] Checking PHP Mailer Installation... ";
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    if (file_exists(__DIR__ . '/vendor/phpmailer')) {
        echo "✓ OK\n";
        $success[] = "PHP Mailer is installed";
    } else {
        echo "✗ FAIL\n";
        $errors[] = "PHP Mailer not found in vendor directory";
    }
} else {
    echo "✗ FAIL\n";
    $errors[] = "Composer vendor directory not found. Run: composer install";
}

// Check 4: Configuration Files
echo "[4] Checking Configuration Files... ";
$config_files = [
    __DIR__ . '/config/config.php' => 'Session Config',
    __DIR__ . '/config/database.php' => 'Database Config',
    __DIR__ . '/config/email.php' => 'Email Config',
];

$missing_configs = [];
foreach ($config_files as $file => $name) {
    if (!file_exists($file)) {
        $missing_configs[] = $name;
    }
}

if (empty($missing_configs)) {
    echo "✓ OK\n";
    $success[] = "All configuration files present";
} else {
    echo "⚠ WARNING\n";
    $warnings[] = "Missing files: " . implode(', ', $missing_configs);
}

// Check 5: Utility Files
echo "[5] Checking Utility Files... ";
$utility_files = [
    __DIR__ . '/utils/otp_manager.php' => 'OTP Manager',
    __DIR__ . '/utils/helpers.php' => 'Helpers',
];

$missing_utils = [];
foreach ($utility_files as $file => $name) {
    if (!file_exists($file)) {
        $missing_utils[] = $name;
    }
}

if (empty($missing_utils)) {
    echo "✓ OK\n";
    $success[] = "All utility files present";
} else {
    echo "✗ FAIL\n";
    $errors[] = "Missing utilities: " . implode(', ', $missing_utils);
}

// Check 6: Authentication Files
echo "[6] Checking Authentication Files... ";
$auth_files = [
    __DIR__ . '/auth/register.php' => 'Registration',
    __DIR__ . '/auth/login.php' => 'Login',
    __DIR__ . '/auth/forgot_password.php' => 'Password Reset',
];

$missing_auth = [];
foreach ($auth_files as $file => $name) {
    if (!file_exists($file)) {
        $missing_auth[] = $name;
    }
}

if (empty($missing_auth)) {
    echo "✓ OK\n";
    $success[] = "All authentication files present";
} else {
    echo "✗ FAIL\n";
    $errors[] = "Missing auth files: " . implode(', ', $missing_auth);
}

// Check 7: Frontend Files
echo "[7] Checking Frontend Pages... ";
$frontend_files = [
    __DIR__ . '/public/register.php' => 'Registration Page',
    __DIR__ . '/public/forgot_password.php' => 'Password Reset Page',
    __DIR__ . '/public/index.php' => 'Login Page',
];

$missing_frontend = [];
foreach ($frontend_files as $file => $name) {
    if (!file_exists($file)) {
        $missing_frontend[] = $name;
    }
}

if (empty($missing_frontend)) {
    echo "✓ OK\n";
    $success[] = "All frontend pages present";
} else {
    echo "✗ FAIL\n";
    $errors[] = "Missing pages: " . implode(', ', $missing_frontend);
}

// Check 8: Database Connection
echo "[8] Checking Database Connection... ";
try {
    require_once __DIR__ . '/config/database.php';
    
    // Try a simple query
    $stmt = $pdo->query("SELECT 1");
    if ($stmt) {
        echo "✓ OK\n";
        $success[] = "Database connection successful";
        
        // Check if OTP table exists
        echo "[9] Checking OTP Table... ";
        $stmt = $pdo->query("SHOW TABLES LIKE 'otp_verification'");
        $table_exists = $stmt->rowCount() > 0;
        
        if ($table_exists) {
            echo "✓ OK\n";
            $success[] = "OTP verification table exists";
            
            // Check table structure
            $stmt = $pdo->query("DESCRIBE otp_verification");
            $columns = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
            $required_columns = ['id', 'email', 'otp', 'otp_type', 'is_verified', 'expires_at', 'created_at'];
            $missing_columns = array_diff($required_columns, $columns);
            
            if (empty($missing_columns)) {
                $success[] = "All OTP table columns present";
            } else {
                $errors[] = "Missing OTP columns: " . implode(', ', $missing_columns);
            }
        } else {
            echo "✗ FAIL\n";
            $errors[] = "OTP verification table not found. Run: mysql -u root secure_vault < database-otp-migration.sql";
        }
        
        // Check users table email column
        echo "[10] Checking Users Table Email Column... ";
        $stmt = $pdo->query("DESCRIBE users");
        $columns = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
        
        if (in_array('email', $columns)) {
            echo "✓ OK\n";
            $success[] = "Email column exists in users table";
        } else {
            echo "⚠ WARNING\n";
            $warnings[] = "Email column missing from users table. Run migration.";
        }
        
    } else {
        echo "✗ FAIL\n";
        $errors[] = "Database query failed";
    }
} catch (PDOException $e) {
    echo "✗ FAIL\n";
    $errors[] = "Database connection error: " . $e->getMessage();
}

// Check 11: Email Configuration
echo "[11] Checking Email Configuration... ";
try {
    require_once __DIR__ . '/config/email.php';
    
    $smtp_configured = defined('SMTP_HOST') && !empty(SMTP_HOST);
    $smtp_user_configured = defined('SMTP_USER') && !empty(SMTP_USER);
    $smtp_pass_configured = defined('SMTP_PASS') && !empty(SMTP_PASS);
    
    if ($smtp_configured && $smtp_user_configured && $smtp_pass_configured) {
        echo "✓ OK\n";
        $success[] = "Email configuration present";
        
        // Test SMTP connection
        echo "[12] Testing SMTP Connection... ";
        require_once __DIR__ . '/vendor/autoload.php';
        
        use PHPMailer\PHPMailer\PHPMailer;
        use PHPMailer\PHPMailer\Exception;
        
        try {
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host = SMTP_HOST;
            $mail->SMTPAuth = true;
            $mail->Username = SMTP_USER;
            $mail->Password = SMTP_PASS;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = SMTP_PORT;
            
            $mail->smtpConnect();
            echo "✓ OK\n";
            $success[] = "SMTP connection successful";
            
        } catch (Exception $e) {
            echo "⚠ WARNING\n";
            $warnings[] = "SMTP connection failed: " . $e->getMessage();
        }
    } else {
        echo "✗ FAIL\n";
        $errors[] = "Email configuration incomplete. Update /config/email.php";
    }
} catch (Exception $e) {
    echo "⚠ WARNING\n";
    $warnings[] = "Could not check email configuration: " . $e->getMessage();
}

// Check 12: File Permissions
echo "[13] Checking File Permissions... ";
$writable_dirs = [
    __DIR__ . '/vault_storage' => 'Vault Storage',
];

$permission_issues = [];
foreach ($writable_dirs as $dir => $name) {
    if (is_dir($dir) && !is_writable($dir)) {
        $permission_issues[] = $name;
    }
}

if (empty($permission_issues)) {
    echo "✓ OK\n";
    $success[] = "All directories are writable";
} else {
    echo "⚠ WARNING\n";
    $warnings[] = "Not writable: " . implode(', ', $permission_issues);
}

// Summary
echo "\n";
echo "========================================\n";
echo "SUMMARY\n";
echo "========================================\n\n";

if (!empty($success)) {
    echo "✓ SUCCESS (" . count($success) . ")\n";
    foreach ($success as $msg) {
        echo "  • $msg\n";
    }
    echo "\n";
}

if (!empty($warnings)) {
    echo "⚠ WARNINGS (" . count($warnings) . ")\n";
    foreach ($warnings as $msg) {
        echo "  • $msg\n";
    }
    echo "\n";
}

if (!empty($errors)) {
    echo "✗ ERRORS (" . count($errors) . ")\n";
    foreach ($errors as $msg) {
        echo "  • $msg\n";
    }
    echo "\n";
}

// Final verdict
echo "========================================\n";
if (empty($errors)) {
    echo "✓ SETUP OK - Ready to use!\n";
    echo "========================================\n\n";
    echo "Next steps:\n";
    echo "1. Update /config/email.php with your email provider\n";
    echo "2. Visit http://localhost/public/register.php to test\n";
    echo "3. Visit http://localhost/public/forgot_password.php to test\n";
} else {
    echo "✗ SETUP INCOMPLETE - Please fix errors above\n";
    echo "========================================\n";
}

?>
