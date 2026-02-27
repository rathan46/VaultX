# VaultX OTP System Architecture

## System Overview

```
┌─────────────────────────────────────────────────────────────┐
│                    VaultX OTP System                         │
├─────────────────────────────────────────────────────────────┤
│                                                               │
│  Frontend (HTML/CSS/JS)  →  Backend (PHP)  →  Database (MySQL)
│                               ↓
│                        Email Service
│                        (PHP Mailer)
│
└─────────────────────────────────────────────────────────────┘
```

---

## Component Architecture

```
┌──────────────────────────────────────────────────────────────┐
│                     CLIENT SIDE                               │
├──────────────────────────────────────────────────────────────┤
│                                                                │
│  Registration UI          Password Reset UI      Login UI     │
│  ┌──────────────┐        ┌──────────────┐      ┌──────────┐  │
│  │ - Step 1     │        │ - Step 1     │      │ Username │  │
│  │ - Step 2     │        │ - Step 2     │      │ Password │  │
│  │ - Timer      │        │ - Step 3     │      │ Login    │  │
│  │ - Resend     │        │ - Timer      │      │ + Link   │  │
│  └──────────────┘        │ - Resend     │      └──────────┘  │
│                          └──────────────┘                      │
│                                                                │
│              All making AJAX requests to backend              │
│                                                                │
└──────────────────────────────────────────────────────────────┘
                              ↓
┌──────────────────────────────────────────────────────────────┐
│                    SERVER SIDE (PHP)                          │
├──────────────────────────────────────────────────────────────┤
│                                                                │
│  AUTHENTICATION LAYER                                         │
│  ┌──────────────────────────────────────────────────────┐    │
│  │  /auth/register.php     /auth/forgot_password.php    │    │
│  │  - request_otp          - request_reset              │    │
│  │  - verify_otp           - verify_otp                 │    │
│  │  - resend_otp           - reset_password             │    │
│  │                         - resend_otp                 │    │
│  └──────────────────────────────────────────────────────┘    │
│                           ↓                                    │
│  UTILITY LAYER                                               │
│  ┌──────────────────────────────────────────────────────┐    │
│  │  OTPManager Class (utils/otp_manager.php)            │    │
│  │  ┌─────────────────────────────────────────────────┐ │    │
│  │  │ generateOTP()        - Create 6-digit OTP       │ │    │
│  │  │ createOTP()          - Store in database        │ │    │
│  │  │ sendOTPEmail()       - Send via PHP Mailer      │ │    │
│  │  │ verifyOTP()          - Validate OTP             │ │    │
│  │  │ isOTPValid()         - Check expiry             │ │    │
│  │  │ getOTPExpiryTime()   - Get remaining time       │ │    │
│  │  │ cleanExpiredOTPs()   - Maintenance              │ │    │
│  │  └─────────────────────────────────────────────────┘ │    │
│  └──────────────────────────────────────────────────────┘    │
│                           ↓                                    │
│  EMAIL SERVICE LAYER                                         │
│  ┌──────────────────────────────────────────────────────┐    │
│  │  PHP Mailer (vendor/phpmailer/phpmailer)             │    │
│  │  - SMTP Connection (Gmail, SendGrid, Mailgun, etc)  │    │
│  │  - HTML Email Templates                             │    │
│  │  - TLS Encryption                                   │    │
│  │  - Error Handling & Logging                         │    │
│  └──────────────────────────────────────────────────────┘    │
│                                                                │
└──────────────────────────────────────────────────────────────┘
                              ↓
┌──────────────────────────────────────────────────────────────┐
│                    DATABASE (MySQL)                           │
├──────────────────────────────────────────────────────────────┤
│                                                                │
│  OTP Verification Table          Users Table                 │
│  ┌──────────────────────┐        ┌─────────────────────┐    │
│  │ id (PK)              │        │ id (PK)             │    │
│  │ email (IDX)          │        │ user_uid            │    │
│  │ otp                  │        │ username            │    │
│  │ otp_type (IDX)       │        │ email (NEW)         │    │
│  │ is_verified          │        │ password_hash       │    │
│  │ expires_at           │        │ created_at          │    │
│  │ created_at           │        └─────────────────────┘    │
│  └──────────────────────┘                                    │
│                                                                │
│  Activity Log Table                                          │
│  ┌──────────────────────┐                                    │
│  │ id (PK)              │                                    │
│  │ user_id (FK)         │                                    │
│  │ action               │                                    │
│  │ file_name            │                                    │
│  │ target_user          │                                    │
│  │ created_at           │                                    │
│  └──────────────────────┘                                    │
│                                                                │
└──────────────────────────────────────────────────────────────┘
                              ↓
┌──────────────────────────────────────────────────────────────┐
│                  EXTERNAL EMAIL SERVICE                       │
├──────────────────────────────────────────────────────────────┤
│                                                                │
│  Options: Gmail, SendGrid, Mailgun, Mailtrap, etc.          │
│  Protocol: SMTP with TLS Encryption                          │
│  Port: 587 (TLS) or 465 (SSL)                                │
│                                                                │
└──────────────────────────────────────────────────────────────┘
```

---

## Data Flow Diagrams

### Registration Flow

```
User
  ↓
┌─────────────────────────────────────────────────────────┐
│ Step 1: Submit Registration Details                     │
├─────────────────────────────────────────────────────────┤
│ Frontend /public/register.php                           │
│   → Validate form (client-side)                         │
│   → POST to /auth/register.php?action=request_otp       │
│                                                          │
│ Backend /auth/register.php                              │
│   → Validate inputs (email, password)                   │
│   → Check for duplicates in users table                 │
│   → OTPManager::createOTP(email, 'registration')        │
│   → OTPManager::sendOTPEmail(email, otp)                │
│   → Store temp data in $_SESSION                        │
│   → Return JSON: {success: true, step: 'verify_otp'}    │
│                                                          │
│ Email Service (PHP Mailer)                              │
│   → Connect to SMTP server (e.g., Gmail)                │
│   → Send HTML email with OTP                            │
│   → Return success/failure                              │
└─────────────────────────────────────────────────────────┘
  ↓
User receives email with OTP (6-digit code)
  ↓
┌─────────────────────────────────────────────────────────┐
│ Step 2: Verify OTP                                      │
├─────────────────────────────────────────────────────────┤
│ Frontend /public/register.php                           │
│   → Enter 6-digit OTP                                   │
│   → POST to /auth/register.php?action=verify_otp        │
│                                                          │
│ Backend /auth/register.php                              │
│   → OTPManager::verifyOTP(email, otp, 'registration')   │
│   → Check: OTP matches, not expired, not verified       │
│   → If valid:                                           │
│     - Generate user_uid                                 │
│     - Hash password with bcrypt                         │
│     - INSERT into users table                           │
│     - Mark OTP as verified in database                  │
│     - LOG activity                                      │
│     - Clear $_SESSION['temp_register']                  │
│   → Return JSON: {success: true, redirect: '/login'}    │
└─────────────────────────────────────────────────────────┘
  ↓
User account created successfully, redirects to login
  ↓
User can now login with username/password
```

### Password Reset Flow

```
User
  ↓
┌─────────────────────────────────────────────────────────┐
│ Step 1: Request Password Reset                          │
├─────────────────────────────────────────────────────────┤
│ Frontend /public/forgot_password.php                    │
│   → Enter email address                                 │
│   → POST to /auth/forgot_password.php?action=request_reset
│                                                          │
│ Backend /auth/forgot_password.php                       │
│   → Validate email format                               │
│   → Check if user exists (security: same message all)   │
│   → OTPManager::createOTP(email, 'password_reset')      │
│   → OTPManager::sendOTPEmail(email, otp)                │
│   → Store email in $_SESSION['reset_email']             │
│   → Return JSON: {success: true, step: 'verify_otp'}    │
└─────────────────────────────────────────────────────────┘
  ↓
User receives email with OTP
  ↓
┌─────────────────────────────────────────────────────────┐
│ Step 2: Verify OTP                                      │
├─────────────────────────────────────────────────────────┤
│ Frontend /public/forgot_password.php                    │
│   → Enter 6-digit OTP                                   │
│   → POST to /auth/forgot_password.php?action=verify_otp │
│                                                          │
│ Backend /auth/forgot_password.php                       │
│   → OTPManager::verifyOTP(email, otp, 'password_reset') │
│   → If valid:                                           │
│     - Set $_SESSION['otp_verified'] = true              │
│   → Return JSON: {success: true, step: 'reset_password'}│
└─────────────────────────────────────────────────────────┘
  ↓
┌─────────────────────────────────────────────────────────┐
│ Step 3: Set New Password                                │
├─────────────────────────────────────────────────────────┤
│ Frontend /public/forgot_password.php                    │
│   → Enter new password (2x)                             │
│   → POST to /auth/forgot_password.php?action=reset_password
│                                                          │
│ Backend /auth/forgot_password.php                       │
│   → Validate: OTP verified, passwords match, min 8 chars
│   → Hash new password with bcrypt                       │
│   → UPDATE users table with new password_hash           │
│   → LOG activity: 'Password reset'                      │
│   → Clear session variables                             │
│   → Return JSON: {success: true, redirect: '/login'}    │
└─────────────────────────────────────────────────────────┘
  ↓
User can now login with new password
```

---

## OTP Lifecycle

```
┌─────────────────────────────────────────────────────┐
│ OTP LIFECYCLE (10 minutes total)                     │
├─────────────────────────────────────────────────────┤
│                                                      │
│ T=0min   OTP Created                                │
│   ↓      ├─ Generate 6-digit random code            │
│   ↓      ├─ Store in database                       │
│   ↓      ├─ Set expires_at = NOW + 10 minutes      │
│   ↓      └─ Send via email                          │
│   ↓                                                 │
│ T=0-10m  OTP Valid & Unverified                     │
│   ↓      ├─ User can enter OTP                      │
│   ↓      ├─ OTP matches & not expired = SUCCESS    │
│   ↓      └─ OTP mismatch = FAIL (try again)        │
│   ↓                                                 │
│ T=10m    OTP Expires                                │
│   ↓      ├─ expires_at timestamp reached            │
│   ↓      ├─ OTP no longer valid                     │
│   ↓      ├─ Query fails: expires_at > NOW()        │
│   ↓      └─ User must request new OTP              │
│   ↓                                                 │
│ T=10m+   Cleanup                                    │
│   ↓      ├─ Cron job runs                           │
│   ↓      ├─ DELETE WHERE expires_at < NOW()        │
│   ↓      └─ Database space freed                    │
│   ↓                                                 │
│ Complete                                            │
│                                                      │
└─────────────────────────────────────────────────────┘
```

---

## File Interactions

```
/public/register.php
    ↓ (AJAX POST)
/auth/register.php
    ├─ Requires: /config/database.php (PDO connection)
    ├─ Requires: /utils/helpers.php (generateUserUID)
    ├─ Requires: /utils/otp_manager.php (OTPManager class)
    └─ Uses: $_SESSION (temporary registration data)
        ↓
    /utils/otp_manager.php
        ├─ Requires: /config/email.php (SMTP settings)
        ├─ Requires: vendor/phpmailer (PHP Mailer)
        └─ Uses: $pdo (database connection)
            ↓
        Database (users, otp_verification, activity_log tables)
        
        Email Service (SMTP)
            ↓
        User's Email Inbox
```

---

## Security Architecture

```
┌─────────────────────────────────────────────────────┐
│ INPUT VALIDATION LAYER                              │
├─────────────────────────────────────────────────────┤
│ ✓ Email format validation                           │
│ ✓ Password requirements (8+ chars)                  │
│ ✓ OTP format validation (6 digits only)             │
│ ✓ Username length check                             │
│ ✓ Type casting & trimming                           │
└─────────────────────────────────────────────────────┘
                  ↓
┌─────────────────────────────────────────────────────┐
│ AUTHENTICATION LAYER                                │
├─────────────────────────────────────────────────────┤
│ ✓ Bcrypt password hashing (PASSWORD_DEFAULT)        │
│ ✓ OTP generation (cryptographically random)         │
│ ✓ One-time use validation                           │
│ ✓ Expiry timestamp validation                       │
│ ✓ Email verification requirement                    │
└─────────────────────────────────────────────────────┘
                  ↓
┌─────────────────────────────────────────────────────┐
│ SESSION SECURITY LAYER                              │
├─────────────────────────────────────────────────────┤
│ ✓ HTTP-only cookies (not JS-accessible)             │
│ ✓ SameSite=Strict (CSRF protection)                 │
│ ✓ Session regeneration on successful operations     │
│ ✓ Session data validation before use                │
│ ✓ Automatic cleanup after use                       │
└─────────────────────────────────────────────────────┘
                  ↓
┌─────────────────────────────────────────────────────┐
│ NETWORK SECURITY LAYER                              │
├─────────────────────────────────────────────────────┤
│ ✓ SMTP TLS Encryption (port 587)                   │
│ ✓ Parameterized queries (PDO prepared statements)   │
│ ✓ HTTPS-ready configuration                         │
│ ✓ Error message sanitization                        │
│ ✓ Secure logging (no sensitive data)                │
└─────────────────────────────────────────────────────┘
                  ↓
┌─────────────────────────────────────────────────────┐
│ DATABASE SECURITY LAYER                             │
├─────────────────────────────────────────────────────┤
│ ✓ Unique constraints (no duplicates)                │
│ ✓ Foreign keys (referential integrity)              │
│ ✓ Indexes for performance (email, otp_type)         │
│ ✓ AUTO_INCREMENT for ID generation                  │
│ ✓ Timestamp tracking (audit trail)                  │
└─────────────────────────────────────────────────────┘
```

---

## Database Schema

```
secure_vault/
│
├── users (existing, with new email column)
│   ├── id (INT, PK, AUTO_INCREMENT)
│   ├── user_uid (BIGINT, UNIQUE)
│   ├── username (VARCHAR, UNIQUE)
│   ├── email (VARCHAR, UNIQUE) ← NEW
│   ├── password_hash (VARCHAR)
│   └── created_at (TIMESTAMP)
│
├── otp_verification (NEW TABLE)
│   ├── id (INT, PK, AUTO_INCREMENT)
│   ├── email (VARCHAR, INDEX)
│   ├── otp (VARCHAR)
│   ├── otp_type (ENUM: registration, password_reset, INDEX)
│   ├── is_verified (BOOLEAN)
│   ├── expires_at (TIMESTAMP)
│   └── created_at (TIMESTAMP)
│
└── activity_log (existing, uses new email feature)
    ├── id (INT, PK, AUTO_INCREMENT)
    ├── user_id (INT, FK → users.id)
    ├── action (VARCHAR)
    ├── file_name (VARCHAR, NULL)
    ├── target_user (INT, FK → users.id, NULL)
    └── created_at (TIMESTAMP)
```

---

## API Response Format

```json
{
  "success": true/false,
  "message": "Human readable status message",
  "step": "verify_otp | reset_password | success | initial",
  "redirect": "/path/to/redirect (optional)"
}
```

### Example Responses

**OTP Request Success**
```json
{
  "success": true,
  "message": "OTP sent to your email. Please verify to complete registration.",
  "step": "verify_otp"
}
```

**OTP Verification Success**
```json
{
  "success": true,
  "message": "Registration successful! Redirecting to login...",
  "step": "success",
  "redirect": "../auth/login.php"
}
```

**Error Response**
```json
{
  "success": false,
  "message": "Invalid or expired OTP. Please try again.",
  "step": "verify_otp"
}
```

---

## Configuration Flow

```
.env (Environment Variables)
    ↓
/config/email.php
    ├── SMTP_HOST
    ├── SMTP_PORT
    ├── SMTP_USER
    ├── SMTP_PASS
    ├── FROM_EMAIL
    ├── FROM_NAME
    ├── OTP_LENGTH (6)
    └── OTP_EXPIRY_MINUTES (10)
        ↓
/utils/otp_manager.php
    └── Uses constants in email operations
        ↓
/auth/register.php & /auth/forgot_password.php
    └── Uses OTPManager with settings
```

---

## Performance Considerations

```
┌─────────────────────────────────────────────────────┐
│ OPTIMIZATION STRATEGIES                             │
├─────────────────────────────────────────────────────┤
│                                                      │
│ 1. DATABASE                                         │
│    ├─ Indexes on: email, otp_type                  │
│    ├─ Automatic cleanup of expired OTPs            │
│    └─ Efficient queries (prepared statements)      │
│                                                      │
│ 2. EMAIL SERVICE                                    │
│    ├─ Connection pooling ready                     │
│    ├─ Async sending capable                        │
│    └─ Error retry logic                            │
│                                                      │
│ 3. FRONTEND                                         │
│    ├─ No heavy frameworks (vanilla JS)             │
│    ├─ Client-side validation reduces server calls  │
│    └─ Minimal CSS (< 10KB)                         │
│                                                      │
│ 4. CACHING                                          │
│    ├─ Session-based caching                        │
│    ├─ No database query repetition                 │
│    └─ Efficient PDO use                            │
│                                                      │
└─────────────────────────────────────────────────────┘
```

---

## Deployment Architecture

```
Production Environment
│
├── Web Server (Apache/Nginx)
│   │
│   ├── /public/ (Document Root)
│   │   ├── register.php (HTTPS)
│   │   ├── forgot_password.php (HTTPS)
│   │   └── index.php (HTTPS)
│   │
│   ├── /auth/ (Protected from direct access)
│   │   ├── register.php
│   │   └── forgot_password.php
│   │
│   └── /config/ (Protected from direct access)
│       └── email.php
│
├── PHP-FPM (PHP Processor)
│   ├── Error Logging
│   ├── Session Handling
│   └── OTPManager Processing
│
├── MySQL Server
│   ├── secure_vault database
│   ├── otp_verification table
│   ├── users table (with email)
│   └── activity_log table
│
├── Email Service (SMTP)
│   ├── Gmail / SendGrid / Mailgun
│   └── TLS Encryption
│
└── Monitoring & Logging
    ├── Error logs
    ├── Access logs
    ├── Email delivery logs
    └── Security audit logs
```

---

This architecture provides:
- ✅ Secure OTP-based authentication
- ✅ Scalable email service integration
- ✅ Clear separation of concerns
- ✅ Easy maintenance and updates
- ✅ Production-ready design
