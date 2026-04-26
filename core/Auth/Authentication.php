<?php
/**
 * Authentication Service
 * Handles user registration, login, password management, and 2FA
 */

namespace Core\Auth;

use Core\Database\Database;
use Core\Security\CsrfProtection;
use Core\Security\RateLimiter;

class Authentication
{
    private Database $db;
    private RateLimiter $rateLimiter;
    
    public function __construct()
    {
        $this->db = Database::getInstance(config('database'));
        $this->rateLimiter = new RateLimiter();
    }
    
    /**
     * Register a new user
     */
    public function register(array $data): array
    {
        // Check rate limiting
        if (!$this->rateLimiter->check('register', $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1', 5, 3600)) {
            return ['success' => false, 'error' => 'Too many registration attempts. Please try again later.'];
        }
        
        // Validate input
        $validation = $this->validateRegistration($data);
        if (!$validation['valid']) {
            return ['success' => false, 'errors' => $validation['errors']];
        }
        
        // Check if email already exists
        $existing = $this->db->fetchOne(
            "SELECT id FROM eb_users WHERE email = ?",
            [$data['email']]
        );
        
        if ($existing) {
            return ['success' => false, 'errors' => ['email' => 'This email is already registered.']];
        }
        
        // Detect country from IP for defaults
        $countryCode = $this->detectCountryFromIP($_SERVER['REMOTE_ADDR'] ?? '');
        $currency = config('country_currency')[$countryCode] ?? 'EUR';
        
        // Create user
        $verificationToken = bin2hex(random_bytes(32));
        $userId = $this->db->insert('users', [
            'email' => $data['email'],
            'password_hash' => hash_password($data['password']),
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'country_code' => $countryCode,
            'language' => config('default_language'),
            'timezone' => $this->getTimezoneForCountry($countryCode),
            'verification_token' => $verificationToken,
            'status' => 'active',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        
        // Create verification record
        $this->db->insert('email_verifications', [
            'user_id' => $userId,
            'token' => $verificationToken,
            'expires_at' => date('Y-m-d H:i:s', strtotime('+24 hours')),
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        
        // Log activity
        logger('info', 'User registered', ['user_id' => $userId, 'email' => $data['email']]);
        
        // Send verification email (queued)
        queue_job('App\Jobs\SendVerificationEmail', [
            'user_id' => $userId,
            'token' => $verificationToken,
        ]);
        
        return [
            'success' => true,
            'user_id' => $userId,
            'message' => 'Registration successful. Please check your email to verify your account.',
        ];
    }
    
    /**
     * Authenticate user login
     */
    public function login(string $email, string $password, bool $remember = false): array
    {
        // Check rate limiting
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        if (!$this->rateLimiter->check('login', $ip, 5, 900)) {
            return ['success' => false, 'error' => 'Too many login attempts. Please try again in 15 minutes.'];
        }
        
        // Find user by email
        $user = $this->db->fetchOne(
            "SELECT * FROM eb_users WHERE email = ? AND status = 'active' AND deleted_at IS NULL",
            [$email]
        );
        
        if (!$user) {
            // Still do a password hash to prevent timing attacks
            password_verify($password, '$2y$12$dummy.hash.to.prevent.timing.attacks');
            return ['success' => false, 'error' => 'Invalid credentials.'];
        }
        
        // Check if account is locked
        if ($user['locked_until'] && strtotime($user['locked_until']) > time()) {
            $remaining = strtotime($user['locked_until']) - time();
            return ['success' => false, 'error' => sprintf('Account locked for %d minutes due to too many failed attempts.', ceil($remaining / 60))];
        }
        
        // Verify password
        if (!verify_password($password, $user['password_hash'])) {
            // Increment login attempts
            $attempts = $user['login_attempts'] + 1;
            $lockUntil = null;
            
            if ($attempts >= config('security.max_login_attempts')) {
                $lockUntil = date('Y-m-d H:i:s', time() + config('security.lockout_time'));
                logger('warning', 'Account locked', ['user_id' => $user['id'], 'email' => $email]);
            }
            
            $this->db->update('users', [
                'login_attempts' => $attempts,
                'locked_until' => $lockUntil,
                'updated_at' => date('Y-m-d H:i:s'),
            ], 'id = ?', [$user['id']]);
            
            return ['success' => false, 'error' => 'Invalid credentials.'];
        }
        
        // Check if 2FA is enabled
        if ($user['two_factor_enabled']) {
            // Store temporary session for 2FA verification
            $_SESSION['_2fa_pending_user_id'] = $user['id'];
            return ['success' => '2fa_required', 'user_id' => $user['id']];
        }
        
        // Successful login
        $this->completeLogin($user, $remember);
        
        return ['success' => true, 'user' => $user];
    }
    
    /**
     * Complete login process
     */
    private function completeLogin(array $user, bool $remember): void
    {
        // Reset login attempts
        $this->db->update('users', [
            'login_attempts' => 0,
            'locked_until' => null,
            'last_login_at' => date('Y-m-d H:i:s'),
            'last_login_ip' => $_SERVER['REMOTE_ADDR'] ?? null,
            'updated_at' => date('Y-m-d H:i:s'),
        ], 'id = ?', [$user['id']]);
        
        // Regenerate session ID
        session_regenerate_id(true);
        
        // Set session data
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['authenticated'] = true;
        $_SESSION['auth_time'] = time();
        $_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'] ?? '';
        $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? '';
        
        // Set remember me cookie if requested
        if ($remember) {
            $token = bin2hex(random_bytes(32));
            setcookie(
                'remember_token',
                hash('sha256', $token),
                time() + (30 * 24 * 60 * 60), // 30 days
                '/',
                '',
                true,
                true
            );
            
            // Store token hash in database (simplified - would use separate table in production)
            $this->db->update('users', [
                'remember_token' => hash('sha256', $token),
            ], 'id = ?', [$user['id']]);
        }
        
        // Log activity
        logger('info', 'User logged in', ['user_id' => $user['id'], 'email' => $user['email']]);
        
        // Create audit log
        $this->db->insert('audit_logs', [
            'user_id' => $user['id'],
            'action' => 'login',
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }
    
    /**
     * Verify 2FA code
     */
    public function verify2FA(string $code): array
    {
        $userId = $_SESSION['_2fa_pending_user_id'] ?? null;
        
        if (!$userId) {
            return ['success' => false, 'error' => 'No pending 2FA verification.'];
        }
        
        $user = $this->db->fetchOne("SELECT * FROM eb_users WHERE id = ?", [$userId]);
        
        if (!$user || !$user['two_factor_secret']) {
            unset($_SESSION['_2fa_pending_user_id']);
            return ['success' => false, 'error' => '2FA not configured.'];
        }
        
        // Verify TOTP code
        if ($this->verifyTOTP($user['two_factor_secret'], $code)) {
            unset($_SESSION['_2fa_pending_user_id']);
            $this->completeLogin($user, false);
            return ['success' => true, 'user' => $user];
        }
        
        return ['success' => false, 'error' => 'Invalid 2FA code.'];
    }
    
    /**
     * Verify TOTP code
     */
    private function verifyTOTP(string $secret, string $code): bool
    {
        // Base32 decode secret
        $secret = str_replace(' ', '', strtoupper($secret));
        $key = $this->base32Decode($secret);
        
        // Get current time window (30 seconds)
        $timeWindow = floor(time() / 30);
        
        // Check current and adjacent windows (for clock skew)
        for ($i = -1; $i <= 1; $i++) {
            $hmac = hash_hmac('sha1', pack('J', $timeWindow + $i), $key, true);
            $offset = ord(substr($hmac, -1)) & 0x0F;
            $hash = unpack('N', substr($hmac, $offset, 4))[1] & 0x7FFFFFFF;
            $generated = str_pad($hash % 1000000, 6, '0', STR_PAD_LEFT);
            
            if (hash_equals($generated, $code)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Base32 decode
     */
    private function base32Decode(string $data): string
    {
        $encoding = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $decoded = '';
        $buffer = 0;
        $bitsLeft = 0;
        
        for ($i = 0; $i < strlen($data); $i++) {
            $val = strpos($encoding, $data[$i]);
            if ($val === false) continue;
            
            $buffer = ($buffer << 5) | $val;
            $bitsLeft += 5;
            
            if ($bitsLeft >= 8) {
                $bitsLeft -= 8;
                $decoded .= chr(($buffer >> $bitsLeft) & 0xFF);
            }
        }
        
        return $decoded;
    }
    
    /**
     * Logout user
     */
    public function logout(): void
    {
        $userId = $_SESSION['user_id'] ?? null;
        
        if ($userId) {
            // Log activity
            logger('info', 'User logged out', ['user_id' => $userId]);
            
            // Create audit log
            $this->db->insert('audit_logs', [
                'user_id' => $userId,
                'action' => 'logout',
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }
        
        // Clear remember me cookie
        if (isset($_COOKIE['remember_token'])) {
            setcookie('remember_token', '', time() - 3600, '/');
        }
        
        // Destroy session
        session_destroy();
        $_SESSION = [];
    }
    
    /**
     * Request password reset
     */
    public function requestPasswordReset(string $email): array
    {
        // Rate limit
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        if (!$this->rateLimiter->check('password_reset', $ip, 3, 3600)) {
            return ['success' => false, 'error' => 'Too many reset requests. Please try again later.'];
        }
        
        $user = $this->db->fetchOne("SELECT id, email FROM eb_users WHERE email = ? AND status = 'active'", [$email]);
        
        // Always return success to prevent email enumeration
        if (!$user) {
            return ['success' => true, 'message' => 'If an account exists with this email, you will receive a password reset link.'];
        }
        
        // Generate reset token
        $token = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        $this->db->insert('password_resets', [
            'email' => $email,
            'token' => hash('sha256', $token),
            'expires_at' => $expiresAt,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        
        // Queue email
        queue_job('App\Jobs\SendPasswordResetEmail', [
            'email' => $email,
            'token' => $token,
        ]);
        
        logger('info', 'Password reset requested', ['email' => $email]);
        
        return ['success' => true, 'message' => 'If an account exists with this email, you will receive a password reset link.'];
    }
    
    /**
     * Reset password
     */
    public function resetPassword(string $token, string $newPassword): array
    {
        $reset = $this->db->fetchOne(
            "SELECT * FROM eb_password_resets 
             WHERE token = ? AND used = 0 AND expires_at > NOW()
             ORDER BY created_at DESC LIMIT 1",
            [hash('sha256', $token)]
        );
        
        if (!$reset) {
            return ['success' => false, 'error' => 'Invalid or expired reset token.'];
        }
        
        // Validate password
        if (strlen($newPassword) < 8) {
            return ['success' => false, 'errors' => ['password' => 'Password must be at least 8 characters.']];
        }
        
        // Find user
        $user = $this->db->fetchOne("SELECT id FROM eb_users WHERE email = ?", [$reset['email']]);
        
        if (!$user) {
            return ['success' => false, 'error' => 'User not found.'];
        }
        
        // Update password
        $this->db->update('users', [
            'password_hash' => hash_password($newPassword),
            'remember_token' => null, // Invalidate all remember tokens
            'updated_at' => date('Y-m-d H:i:s'),
        ], 'id = ?', [$user['id']]);
        
        // Mark token as used
        $this->db->update('password_resets', [
            'used' => 1,
        ], 'id = ?', [$reset['id']]);
        
        logger('info', 'Password reset completed', ['user_id' => $user['id']]);
        
        return ['success' => true, 'message' => 'Password has been reset successfully.'];
    }
    
    /**
     * Validate registration data
     */
    private function validateRegistration(array $data): array
    {
        $errors = [];
        
        if (empty($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Please enter a valid email address.';
        }
        
        if (empty($data['password']) || strlen($data['password']) < 8) {
            $errors['password'] = 'Password must be at least 8 characters.';
        }
        
        if (empty($data['first_name'])) {
            $errors['first_name'] = 'First name is required.';
        }
        
        if (empty($data['last_name'])) {
            $errors['last_name'] = 'Last name is required.';
        }
        
        if (empty($data['terms'])) {
            $errors['terms'] = 'You must accept the terms and conditions.';
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors,
        ];
    }
    
    /**
     * Detect country from IP address
     */
    private function detectCountryFromIP(string $ip): string
    {
        // In production, use a GeoIP database or API
        // Default to Belgium for EU-focused platform
        return 'BE';
    }
    
    /**
     * Get timezone for country
     */
    private function getTimezoneForCountry(string $countryCode): string
    {
        $timezones = [
            'BE' => 'Europe/Brussels',
            'NL' => 'Europe/Amsterdam',
            'FR' => 'Europe/Paris',
            'DE' => 'Europe/Berlin',
            'ES' => 'Europe/Madrid',
            'GB' => 'Europe/London',
            'US' => 'America/New_York',
        ];
        
        return $timezones[$countryCode] ?? 'Europe/Brussels';
    }
    
    /**
     * Check if user is authenticated
     */
    public static function check(): bool
    {
        return isset($_SESSION['authenticated']) && $_SESSION['authenticated'] === true;
    }
    
    /**
     * Get current user
     */
    public static function user(): ?array
    {
        if (!self::check()) {
            return null;
        }
        
        $db = Database::getInstance(config('database'));
        return $db->fetchOne("SELECT * FROM eb_users WHERE id = ?", [$_SESSION['user_id']]);
    }
    
    /**
     * Require authentication
     */
    public static function require(): void
    {
        if (!self::check()) {
            redirect('/login');
        }
    }
}
