<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - VaultX</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .container {
            width: 100%;
            max-width: 400px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            padding: 40px;
        }

        .logo {
            text-align: center;
            margin-bottom: 30px;
        }

        .logo h1 {
            color: #667eea;
            font-size: 32px;
            font-weight: bold;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
            font-size: 14px;
        }

        input[type="text"],
        input[type="email"],
        input[type="password"],
        input[type="number"] {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            transition: border-color 0.3s;
        }

        input[type="text"]:focus,
        input[type="email"]:focus,
        input[type="password"]:focus,
        input[type="number"]:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 5px rgba(102, 126, 234, 0.1);
        }

        .otp-input {
            font-size: 20px;
            letter-spacing: 10px;
            text-align: center;
            font-weight: bold;
        }

        button {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }

        button:active {
            transform: translateY(0);
        }

        .alert {
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-size: 14px;
            display: none;
        }

        .alert.show {
            display: block;
        }

        .alert.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .form-section {
            display: none;
        }

        .form-section.active {
            display: block;
        }

        .footer-text {
            text-align: center;
            margin-top: 20px;
            color: #666;
            font-size: 14px;
        }

        .footer-text a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
        }

        .footer-text a:hover {
            text-decoration: underline;
        }

        .resend-otp {
            text-align: center;
            margin-top: 15px;
        }

        .resend-otp a {
            color: #667eea;
            text-decoration: none;
            font-size: 14px;
            cursor: pointer;
        }

        .resend-otp a:hover {
            text-decoration: underline;
        }

        .timer {
            text-align: center;
            color: #666;
            font-size: 13px;
            margin-top: 10px;
        }

        .loading {
            display: none;
            text-align: center;
        }

        .spinner {
            border: 3px solid #f3f3f3;
            border-top: 3px solid #667eea;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            animation: spin 1s linear infinite;
            margin: 0 auto 10px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .back-btn {
            background: #6c757d;
            margin-top: 10px;
        }

        .back-btn:hover {
            box-shadow: 0 5px 15px rgba(108, 117, 125, 0.4);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">
            <h1>VaultX</h1>
        </div>

        <div id="alert" class="alert"></div>

        <!-- Step 1: Email Request -->
        <div id="step-email" class="form-section active">
            <h2 style="text-align: center; margin-bottom: 10px; color: #333;">Reset Password</h2>
            <p style="text-align: center; color: #666; margin-bottom: 30px; font-size: 14px;">
                Enter your email to receive a password reset code
            </p>

            <form id="emailForm">
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" required placeholder="your@email.com">
                </div>

                <button type="submit">Send Reset Code</button>

                <div class="footer-text">
                    Remember your password? <a href="index.php">Login here</a>
                </div>
            </form>
        </div>

        <!-- Step 2: OTP Verification -->
        <div id="step-otp" class="form-section">
            <h2 style="text-align: center; margin-bottom: 10px; color: #333;">Verify Code</h2>
            <p style="text-align: center; color: #666; margin-bottom: 30px; font-size: 14px;">
                Enter the 6-digit code sent to your email
            </p>

            <form id="otpForm">
                <div class="form-group">
                    <label for="otp">One-Time Password</label>
                    <input type="number" id="otp" name="otp" class="otp-input" required placeholder="000000" min="0" max="999999">
                </div>

                <button type="submit">Verify Code</button>

                <div class="timer">
                    <span id="timerText">Code expires in <span id="timerValue">10:00</span></span>
                </div>

                <div class="resend-otp">
                    <a id="resendBtn" onclick="resendOTP()">Resend Code</a>
                </div>

                <button type="button" class="back-btn" onclick="goBack()">Back</button>
            </form>
        </div>

        <!-- Step 3: New Password -->
        <div id="step-password" class="form-section">
            <h2 style="text-align: center; margin-bottom: 30px; color: #333;">Create New Password</h2>

            <form id="passwordForm">
                <div class="form-group">
                    <label for="newPassword">New Password</label>
                    <input type="password" id="newPassword" name="password" required placeholder="At least 8 characters">
                </div>

                <div class="form-group">
                    <label for="confirmPassword">Confirm Password</label>
                    <input type="password" id="confirmPassword" name="confirm_password" required placeholder="Confirm your password">
                </div>

                <button type="submit">Reset Password</button>

                <button type="button" class="back-btn" onclick="goBack()">Back</button>
            </form>
        </div>

        <!-- Loading State -->
        <div id="loading" class="loading">
            <div class="spinner"></div>
            <p style="color: #666;">Processing...</p>
        </div>
    </div>

    <script>
        const alert = document.getElementById('alert');
        let resendDisabled = false;
        let timerInterval;
        let timerSeconds = 600; // 10 minutes in seconds
        let previousStep = 'step-email';

        // Show alert message
        function showAlert(message, type = 'error') {
            alert.textContent = message;
            alert.className = `alert show ${type}`;
            alert.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }

        // Hide alert
        function hideAlert() {
            alert.classList.remove('show');
        }

        // Toggle loading state
        function setLoading(isLoading) {
            document.getElementById('loading').style.display = isLoading ? 'block' : 'none';
            const activeForm = document.querySelector('.form-section.active form');
            if (activeForm) {
                activeForm.style.display = isLoading ? 'none' : 'block';
            }
        }

        // Switch to step
        function switchStep(stepId) {
            previousStep = document.querySelector('.form-section.active').id;
            document.querySelectorAll('.form-section').forEach(el => {
                el.classList.remove('active');
            });
            document.getElementById(stepId).classList.add('active');
            hideAlert();
        }

        // Email form submission
        document.getElementById('emailForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            hideAlert();
            setLoading(true);

            const formData = new FormData();
            formData.append('action', 'request_reset');
            formData.append('email', document.getElementById('email').value);

            try {
                const response = await fetch('../auth/forgot_password.php', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();
                setLoading(false);

                if (data.success) {
                    showAlert(data.message, 'success');
                    switchStep('step-otp');
                    startTimer();
                } else {
                    showAlert(data.message, 'error');
                }
            } catch (error) {
                setLoading(false);
                showAlert('An error occurred. Please try again.', 'error');
            }
        });

        // OTP form submission
        document.getElementById('otpForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            hideAlert();
            setLoading(true);

            const formData = new FormData();
            formData.append('action', 'verify_otp');
            formData.append('otp', document.getElementById('otp').value);

            try {
                const response = await fetch('../auth/forgot_password.php', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();
                setLoading(false);

                if (data.success) {
                    showAlert(data.message, 'success');
                    switchStep('step-password');
                    clearInterval(timerInterval);
                } else {
                    showAlert(data.message, 'error');
                }
            } catch (error) {
                setLoading(false);
                showAlert('An error occurred. Please try again.', 'error');
            }
        });

        // Password reset form submission
        document.getElementById('passwordForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            hideAlert();
            setLoading(true);

            const formData = new FormData();
            formData.append('action', 'reset_password');
            formData.append('password', document.getElementById('newPassword').value);
            formData.append('confirm_password', document.getElementById('confirmPassword').value);

            try {
                const response = await fetch('../auth/forgot_password.php', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();
                setLoading(false);

                if (data.success) {
                    showAlert(data.message, 'success');
                    setTimeout(() => {
                        window.location.href = data.redirect;
                    }, 2000);
                } else {
                    showAlert(data.message, 'error');
                }
            } catch (error) {
                setLoading(false);
                showAlert('An error occurred. Please try again.', 'error');
            }
        });

        // Resend OTP
        async function resendOTP() {
            if (resendDisabled) {
                showAlert('Please wait before requesting another code', 'error');
                return;
            }

            hideAlert();
            setLoading(true);

            const formData = new FormData();
            formData.append('action', 'resend_otp');

            try {
                const response = await fetch('../auth/forgot_password.php', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();
                setLoading(false);

                if (data.success) {
                    showAlert(data.message, 'success');
                    resendDisabled = true;
                    document.getElementById('resendBtn').style.opacity = '0.5';
                    document.getElementById('resendBtn').style.cursor = 'not-allowed';
                    timerSeconds = 600;
                    startTimer();
                    setTimeout(() => {
                        resendDisabled = false;
                        document.getElementById('resendBtn').style.opacity = '1';
                        document.getElementById('resendBtn').style.cursor = 'pointer';
                    }, 60000); // 1 minute cooldown
                } else {
                    showAlert(data.message, 'error');
                }
            } catch (error) {
                setLoading(false);
                showAlert('An error occurred. Please try again.', 'error');
            }
        }

        // Go back to previous step
        function goBack() {
            const activeStep = document.querySelector('.form-section.active').id;
            if (activeStep === 'step-otp') {
                switchStep('step-email');
                clearInterval(timerInterval);
            } else if (activeStep === 'step-password') {
                switchStep('step-otp');
                startTimer();
            }
        }

        // Timer for OTP expiry
        function startTimer() {
            timerSeconds = 600;
            clearInterval(timerInterval);
            updateTimer();
            timerInterval = setInterval(updateTimer, 1000);
        }

        function updateTimer() {
            const minutes = Math.floor(timerSeconds / 60);
            const seconds = timerSeconds % 60;
            document.getElementById('timerValue').textContent = 
                `${minutes}:${seconds.toString().padStart(2, '0')}`;
            
            if (timerSeconds <= 0) {
                clearInterval(timerInterval);
                showAlert('Code has expired. Please request a new one.', 'error');
            }

            timerSeconds--;
        }

        // Auto-limit OTP field
        document.getElementById('otp').addEventListener('input', (e) => {
            if (e.target.value.length > 6) {
                e.target.value = e.target.value.slice(0, 6);
            }
        });
    </script>
</body>
</html>
