<?php
/**
 * Auth Controller
 * Handles authentication: login, register, logout, password reset
 */

namespace App\Controllers;

use Core\Auth\Authentication;
use Core\Security\CsrfProtection;

class AuthController extends Controller
{
    private Authentication $auth;
    
    public function __construct()
    {
        parent::__construct();
        $this->auth = new Authentication();
    }
    
    /**
     * Show login page
     */
    public function showLogin(): void
    {
        // Redirect if already logged in
        if (Authentication::check()) {
            redirect('/dashboard');
        }
        
        CsrfProtection::generateToken();
        
        $this->view('auth/login', [
            'pageTitle' => 'Login',
        ]);
    }
    
    /**
     * Process login
     */
    public function login(): void
    {
        // Verify CSRF token
        if (!CsrfProtection::validateToken($_POST['csrf_token'] ?? '')) {
            set_flash('error', 'Invalid security token. Please try again.');
            redirect('/login');
        }
        
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $remember = isset($_POST['remember']);
        
        if (empty($email) || empty($password)) {
            set_flash('error', 'Please enter both email and password.');
            redirect('/login');
        }
        
        $result = $this->auth->login($email, $password, $remember);
        
        if ($result['success'] === true) {
            // Create workspace session if user has one
            $workspaces = $this->db->fetchOne(
                "SELECT w.* FROM eb_workspaces w
                 JOIN eb_workspace_members wm ON w.id = wm.workspace_id
                 WHERE wm.user_id = ? AND wm.role = 'owner'
                 ORDER BY w.created_at ASC LIMIT 1",
                [$result['user']['id']]
            );
            
            if ($workspaces) {
                $_SESSION['workspace_id'] = $workspaces['id'];
                $_SESSION['workspace_name'] = $workspaces['name'];
            }
            
            set_flash('success', 'Welcome back!');
            redirect('/dashboard');
        } elseif ($result['success'] === '2fa_required') {
            redirect('/2fa-verify');
        } else {
            set_flash('error', $result['error'] ?? 'Login failed. Please check your credentials.');
            redirect('/login');
        }
    }
    
    /**
     * Show registration page
     */
    public function showRegister(): void
    {
        // Redirect if already logged in
        if (Authentication::check()) {
            redirect('/dashboard');
        }
        
        CsrfProtection::generateToken();
        
        $this->view('auth/register', [
            'pageTitle' => 'Register',
            'errors' => [],
        ]);
    }
    
    /**
     * Process registration
     */
    public function register(): void
    {
        // Verify CSRF token
        if (!CsrfProtection::validateToken($_POST['csrf_token'] ?? '')) {
            set_flash('error', 'Invalid security token. Please try again.');
            redirect('/register');
        }
        
        $data = [
            'email' => trim($_POST['email'] ?? ''),
            'password' => $_POST['password'] ?? '',
            'first_name' => trim($_POST['first_name'] ?? ''),
            'last_name' => trim($_POST['last_name'] ?? ''),
            'country' => $_POST['country'] ?? 'BE',
            'terms' => isset($_POST['terms']),
        ];
        
        $result = $this->auth->register($data);
        
        if ($result['success']) {
            set_flash('success', $result['message']);
            redirect('/login');
        } else {
            if (isset($result['errors'])) {
                $_SESSION['flash']['errors'] = $result['errors'];
            } else {
                set_flash('error', $result['error'] ?? 'Registration failed. Please try again.');
            }
            redirect('/register');
        }
    }
    
    /**
     * Logout
     */
    public function logout(): void
    {
        $this->auth->logout();
        redirect('/login');
    }
    
    /**
     * Show forgot password page
     */
    public function showForgotPassword(): void
    {
        CsrfProtection::generateToken();
        
        $this->view('auth/forgot-password', [
            'pageTitle' => 'Forgot Password',
        ]);
    }
    
    /**
     * Process forgot password request
     */
    public function forgotPassword(): void
    {
        // Verify CSRF token
        if (!CsrfProtection::validateToken($_POST['csrf_token'] ?? '')) {
            set_flash('error', 'Invalid security token. Please try again.');
            redirect('/forgot-password');
        }
        
        $email = trim($_POST['email'] ?? '');
        
        if (empty($email)) {
            set_flash('error', 'Please enter your email address.');
            redirect('/forgot-password');
        }
        
        $result = $this->auth->requestPasswordReset($email);
        
        if ($result['success']) {
            set_flash('success', $result['message']);
        } else {
            set_flash('error', $result['error'] ?? 'Request failed. Please try again.');
        }
        
        redirect('/forgot-password');
    }
    
    /**
     * Show reset password page
     */
    public function showResetPassword(string $token): void
    {
        CsrfProtection::generateToken();
        
        $this->view('auth/reset-password', [
            'pageTitle' => 'Reset Password',
            'token' => $token,
            'errors' => [],
        ]);
    }
    
    /**
     * Process password reset
     */
    public function resetPassword(): void
    {
        // Verify CSRF token
        if (!CsrfProtection::validateToken($_POST['csrf_token'] ?? '')) {
            set_flash('error', 'Invalid security token. Please try again.');
            redirect('/login');
        }
        
        $token = $_POST['token'] ?? '';
        $password = $_POST['password'] ?? '';
        $passwordConfirm = $_POST['password_confirm'] ?? '';
        
        if ($password !== $passwordConfirm) {
            set_flash('error', 'Passwords do not match.');
            redirect("/reset-password?token={$token}");
            return;
        }
        
        $result = $this->auth->resetPassword($token, $password);
        
        if ($result['success']) {
            set_flash('success', $result['message']);
            redirect('/login');
        } else {
            if (isset($result['errors'])) {
                $_SESSION['flash']['errors'] = $result['errors'];
            } else {
                set_flash('error', $result['error'] ?? 'Reset failed. Please try again.');
            }
            redirect("/reset-password?token={$token}");
        }
    }
    
    /**
     * Show 2FA verification page
     */
    public function show2FAVerify(): void
    {
        if (!isset($_SESSION['_2fa_pending_user_id'])) {
            redirect('/login');
        }
        
        CsrfProtection::generateToken();
        
        $this->view('auth/2fa-verify', [
            'pageTitle' => 'Two-Factor Authentication',
        ]);
    }
    
    /**
     * Verify 2FA code
     */
    public function verify2FA(): void
    {
        // Verify CSRF token
        if (!CsrfProtection::validateToken($_POST['csrf_token'] ?? '')) {
            set_flash('error', 'Invalid security token. Please try again.');
            redirect('/2fa-verify');
        }
        
        $code = trim($_POST['code'] ?? '');
        
        if (empty($code)) {
            set_flash('error', 'Please enter the 6-digit code.');
            redirect('/2fa-verify');
        }
        
        $result = $this->auth->verify2FA($code);
        
        if ($result['success']) {
            set_flash('success', 'Authentication successful!');
            redirect('/dashboard');
        } else {
            set_flash('error', $result['error'] ?? 'Invalid code. Please try again.');
            redirect('/2fa-verify');
        }
    }
}
