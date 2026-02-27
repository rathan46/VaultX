<?php
/**
 * VaultX OTP System - Setup Checker
 * This script checks if all required files and configurations are in place
 */

echo "<h1>VaultX OTP System - Setup Checker</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
    .check { padding: 15px; margin: 10px 0; border-radius: 5px; }
    .pass { background: #d4edda; border-left: 4px solid #28a745; }
    .fail { background: #f8d7da; border-left: 4px solid #dc3545; }
    .warn { background: #fff3cd; border-left: 4px solid #ffc107; }
    h2 { color: #333; margin-top: 30px; }
    code { background: #f0f0f0; padding: 2px 6px; border-radius: 3px; }
</style>";

$checks = [];
$allPass = true;

// Check 1: PHP Version
echo "<h2>PHP Environment</h2>";
if (version_compare(PHP_VERSION, '7.0.0', '>=')) {
    echo "<div class='check pass'><strong>✓ PHP Version:</strong> " . PHP_VERSION . " (OK)</div>";
} else {
    echo "<div class='check fail'><strong>✗ PHP Version:</strong> " . PHP_VERSION . " (PHP 7.0+ required)</div>";
    $allPass = false;
}

// Check 2: Session Support
echo "<h2>Session Support</h2>";
if (extension_loaded('session')) {
    echo "<div class='check pass'><strong>✓ Session Extension:</strong> Enabled</div>";
} else {
    echo "<div class='check fail'><strong>✗ Session Extension:</strong> Not loaded</div>";
    $allPass = false;
}

// Check 3: PDO Extensions
echo "<h2>Database Extensions</h2>";
if (extension_loaded('PDO')) {
    echo "<div class='check pass'><strong>✓ PDO Extension:</strong> Loaded</div>";
} else {
    echo "<div class='check fail'><strong>✗ PDO Extension:</strong> Not loaded</div>";
    $allPass = false;
}

if (extension_loaded('pdo_mysql')) {
    echo "<div class='check pass'><strong>✓ PDO MySQL Driver:</strong> Loaded</div>";
} else {
    echo "<div class='check warn'><strong>! PDO MySQL Driver:</strong> Not loaded (may still work)</div>";
}

// Check 4: Required Files
echo "<h2>Required Files</h2>";
$requiredFiles = [
    '../config/database.php' => 'Database Configuration',
    '../config/email.php' => 'Email Configuration',
    '../utils/helpers.php' => 'Helper Functions',
    '../utils/otp_manager.php' => 'OTP Manager Class',
];

foreach ($requiredFiles as $file => $name) {
    if (file_exists($file)) {
        echo "<div class='check pass'><strong>✓</strong> $name: <code>$file</code></div>";
    } else {
        echo "<div class='check fail'><strong>✗</strong> $name: <code>$file</code> (NOT FOUND)</div>";
        $allPass = false;
    }
}

// Check 5: PHP Mailer Files
echo "<h2>PHP Mailer Files</h2>";
$mailerFiles = [
    '../vendor/PHPMailer/src/PHPMailer.php' => 'PHPMailer.php',
    '../vendor/PHPMailer/src/Exception.php' => 'Exception.php',
    '../vendor/PHPMailer/src/SMTP.php' => 'SMTP.php',
];

$hasMailer = true;
foreach ($mailerFiles as $file => $name) {
    if (file_exists($file)) {
        echo "<div class='check pass'><strong>✓</strong> $name: <code>$file</code></div>";
    } else {
        echo "<div class='check fail'><strong>✗</strong> $name: <code>$file</code> (NOT FOUND)</div>";
        $hasMailer = false;
    }
}

if (!$hasMailer) {
    echo "<div class='check warn' style='margin-top: 10px;'><strong>Action Required:</strong> Download PHP Mailer from https://github.com/PHPMailer/PHPMailer/archive/master.zip and extract to <code>/vendor/PHPMailer/src/</code></div>";
    $allPass = false;
}

// Check 6: Database Connection
echo "<h2>Database Connection</h2>";
try {
    require_once '../config/database.php';
    $conn->query("SELECT 1");
    echo "<div class='check pass'><strong>✓ Database Connection:</strong> Success</div>";
} catch (Exception $e) {
    echo "<div class='check fail'><strong>✗ Database Connection:</strong> Failed - " . htmlspecialchars($e->getMessage()) . "</div>";
    $allPass = false;
}

// Check 7: Database Tables
echo "<h2>Database Tables</h2>";
try {
    require_once '../config/database.php';
    
    // Check users table
    $result = $conn->query("SELECT 1 FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'users'");
    if ($result && $result->rowCount() > 0) {
        echo "<div class='check pass'><strong>✓ Users Table:</strong> Exists</div>";
        
        // Check if email column exists
        $result = $conn->query("SHOW COLUMNS FROM users LIKE 'email'");
        if ($result && $result->rowCount() > 0) {
            echo "<div class='check pass'><strong>✓ Email Column:</strong> Exists in users table</div>";
        } else {
            echo "<div class='check fail'><strong>✗ Email Column:</strong> Missing from users table</div>";
            $allPass = false;
        }
    } else {
        echo "<div class='check fail'><strong>✗ Users Table:</strong> Does not exist</div>";
        $allPass = false;
    }
    
    // Check otp_verification table
    $result = $conn->query("SELECT 1 FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'otp_verification'");
    if ($result && $result->rowCount() > 0) {
        echo "<div class='check pass'><strong>✓ OTP Verification Table:</strong> Exists</div>";
    } else {
        echo "<div class='check fail'><strong>✗ OTP Verification Table:</strong> Does not exist</div>";
        $allPass = false;
    }
} catch (Exception $e) {
    echo "<div class='check fail'><strong>✗ Database Check:</strong> " . htmlspecialchars($e->getMessage()) . "</div>";
    $allPass = false;
}

// Check 8: Email Configuration
echo "<h2>Email Configuration</h2>";
try {
    require_once '../config/email.php';
    
    if (defined('SMTP_HOST') && SMTP_HOST) {
        echo "<div class='check pass'><strong>✓ SMTP Host:</strong> " . htmlspecialchars(SMTP_HOST) . "</div>";
    } else {
        echo "<div class='check fail'><strong>✗ SMTP Host:</strong> Not configured</div>";
        $allPass = false;
    }
    
    if (defined('SMTP_USER') && SMTP_USER) {
        echo "<div class='check pass'><strong>✓ SMTP User:</strong> Configured</div>";
    } else {
        echo "<div class='check fail'><strong>✗ SMTP User:</strong> Not configured</div>";
        $allPass = false;
    }
    
    if (defined('SMTP_PASSWORD') && SMTP_PASSWORD) {
        echo "<div class='check pass'><strong>✓ SMTP Password:</strong> Configured</div>";
    } else {
        echo "<div class='check fail'><strong>✗ SMTP Password:</strong> Not configured</div>";
        $allPass = false;
    }
    
    if (defined('SMTP_PORT') && SMTP_PORT) {
        echo "<div class='check pass'><strong>✓ SMTP Port:</strong> " . SMTP_PORT . "</div>";
    } else {
        echo "<div class='check fail'><strong>✗ SMTP Port:</strong> Not configured</div>";
        $allPass = false;
    }
    
    if (defined('FROM_EMAIL') && FROM_EMAIL) {
        echo "<div class='check pass'><strong>✓ From Email:</strong> " . htmlspecialchars(FROM_EMAIL) . "</div>";
    } else {
        echo "<div class='check fail'><strong>✗ From Email:</strong> Not configured</div>";
        $allPass = false;
    }
} catch (Exception $e) {
    echo "<div class='check fail'><strong>✗ Email Config:</strong> " . htmlspecialchars($e->getMessage()) . "</div>";
    $allPass = false;
}

// Summary
echo "<h2>Summary</h2>";
if ($allPass) {
    echo "<div class='check pass'><strong>✓ All checks passed!</strong> Your system is ready for OTP registration.</div>";
} else {
    echo "<div class='check fail'><strong>✗ Some checks failed.</strong> Please fix the issues above and refresh this page.</div>";
}

// Test Email Sending
echo "<h2>Test Email Sending</h2>";
if (isset($_POST['test_email'])) {
    try {
        require_once '../config/email.php';
        require_once '../utils/otp_manager.php';
        
        $email = $_POST['test_email'];
        $otp = OTPManager::generateOTP();
        
        if (OTPManager::sendOTPEmail($email, $otp, 'registration')) {
            echo "<div class='check pass'><strong>✓ Test Email Sent Successfully</strong><br>OTP: $otp<br>Check your email at: " . htmlspecialchars($email) . "</div>";
        } else {
            echo "<div class='check fail'><strong>✗ Failed to Send Test Email</strong></div>";
        }
    } catch (Exception $e) {
        echo "<div class='check fail'><strong>✗ Test Email Error:</strong> " . htmlspecialchars($e->getMessage()) . "</div>";
    }
}
?>

<h3>Send Test Email</h3>
<form method="POST">
    <input type="email" name="test_email" placeholder="Enter your email" required>
    <button type="submit">Send Test OTP Email</button>
</form>

<hr>
<p style="color: #666; font-size: 12px;">
    For detailed setup instructions, see: <code>QUICK_START.md</code> or <code>MANUAL_PHPMAILER_SETUP.md</code>
</p>
