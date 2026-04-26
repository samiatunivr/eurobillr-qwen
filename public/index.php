<?php
/**
 * Application Entry Point
 * Eurobillr - Enterprise SaaS Invoicing Platform
 */

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Define constants
define('ROOT_PATH', dirname(__DIR__));
define('APP_PATH', ROOT_PATH . '/app');
define('CORE_PATH', ROOT_PATH . '/core');
define('PUBLIC_PATH', ROOT_PATH . '/public');

// Autoloader
spl_autoload_register(function ($class) {
    // Convert namespace to path
    $prefixes = [
        'App\\' => APP_PATH . '/',
        'Core\\' => CORE_PATH . '/'
    ];
    
    foreach ($prefixes as $prefix => $baseDir) {
        $len = strlen($prefix);
        if (strncmp($prefix, $class, $len) !== 0) {
            continue;
        }
        
        $relativeClass = substr($class, $len);
        $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
        
        if (file_exists($file)) {
            require $file;
            return;
        }
    }
});

// Load helper functions
require_once CORE_PATH . '/Support/helpers.php';

// Load configuration
$configFile = ROOT_PATH . '/config/app.php';
if (file_exists($configFile)) {
    $config = require $configFile;
} else {
    $config = [];
}

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Initialize router
$router = new Core\Routing\Router();

// ============================================
// PUBLIC ROUTES
// ============================================

// Home / Landing
$router->get('/', function() {
    if (isset($_SESSION['user_id'])) {
        header('Location: /dashboard');
        exit;
    }
    include APP_PATH . '/Views/welcome.php';
});

// Login
$router->get('/login', [App\Controllers\AuthController::class, 'showLogin']);
$router->post('/login', [App\Controllers\AuthController::class, 'login']);

// Register
$router->get('/register', [App\Controllers\AuthController::class, 'showRegister']);
$router->post('/register', [App\Controllers\AuthController::class, 'register']);

// Forgot password
$router->get('/forgot-password', [App\Controllers\AuthController::class, 'showForgotPassword']);
$router->post('/forgot-password', [App\Controllers\AuthController::class, 'forgotPassword']);

// Reset password
$router->get('/reset-password/{token}', [App\Controllers\AuthController::class, 'showResetPassword']);
$router->post('/reset-password', [App\Controllers\AuthController::class, 'resetPassword']);

// Email verification
$router->get('/verify-email/{token}', [App\Controllers\AuthController::class, 'verifyEmail']);

// Logout
$router->get('/logout', [App\Controllers\AuthController::class, 'logout']);

// ============================================
// DASHBOARD ROUTES
// ============================================

$router->get('/dashboard', [App\Controllers\DashboardController::class, 'index']);
$router->get('/api/dashboard/stats', [App\Controllers\DashboardController::class, 'apiStats']);

// ============================================
// INVOICE ROUTES
// ============================================

$router->get('/invoices', [App\Controllers\InvoiceController::class, 'index']);
$router->get('/invoices/create', [App\Controllers\InvoiceController::class, 'create']);
$router->post('/invoices', [App\Controllers\InvoiceController::class, 'store']);
$router->get('/invoices/{id}', [App\Controllers\InvoiceController::class, 'show']);
$router->get('/invoices/{id}/edit', [App\Controllers\InvoiceController::class, 'edit']);
$router->post('/invoices/{id}', [App\Controllers\InvoiceController::class, 'update']);
$router->delete('/invoices/{id}', [App\Controllers\InvoiceController::class, 'destroy']);
$router->post('/invoices/{id}/send', [App\Controllers\InvoiceController::class, 'send']);
$router->post('/invoices/{id}/cancel', [App\Controllers\InvoiceController::class, 'cancel']);
$router->post('/invoices/{id}/duplicate', [App\Controllers\InvoiceController::class, 'duplicate']);
$router->post('/invoices/{id}/payment', [App\Controllers\InvoiceController::class, 'recordPayment']);
$router->get('/invoices/{id}/pdf', [App\Controllers\InvoiceController::class, 'downloadPdf']);

// ============================================
// CLIENT ROUTES (Placeholder)
// ============================================

$router->get('/clients', [App\Controllers\ClientController::class, 'index']);
$router->get('/clients/create', [App\Controllers\ClientController::class, 'create']);
$router->post('/clients', [App\Controllers\ClientController::class, 'store']);
$router->get('/clients/{id}', [App\Controllers\ClientController::class, 'show']);
$router->get('/clients/{id}/edit', [App\Controllers\ClientController::class, 'edit']);
$router->post('/clients/{id}', [App\Controllers\ClientController::class, 'update']);
$router->delete('/clients/{id}', [App\Controllers\ClientController::class, 'destroy']);

// ============================================
// EXPENSE ROUTES (Placeholder)
// ============================================

$router->get('/expenses', [App\Controllers\ExpenseController::class, 'index']);
$router->get('/expenses/create', [App\Controllers\ExpenseController::class, 'create']);
$router->post('/expenses', [App\Controllers\ExpenseController::class, 'store']);
$router->get('/expenses/{id}', [App\Controllers\ExpenseController::class, 'show']);
$router->get('/expenses/{id}/edit', [App\Controllers\ExpenseController::class, 'edit']);
$router->post('/expenses/{id}', [App\Controllers\ExpenseController::class, 'update']);
$router->delete('/expenses/{id}', [App\Controllers\ExpenseController::class, 'destroy']);

// ============================================
// WORKSPACE ROUTES
// ============================================

$router->get('/workspaces', [App\Controllers\WorkspaceController::class, 'index']);
$router->get('/workspaces/create', [App\Controllers\WorkspaceController::class, 'create']);
$router->post('/workspaces', [App\Controllers\WorkspaceController::class, 'store']);
$router->get('/workspaces/{id}', [App\Controllers\WorkspaceController::class, 'show']);
$router->get('/workspaces/{id}/edit', [App\Controllers\WorkspaceController::class, 'edit']);
$router->post('/workspaces/{id}', [App\Controllers\WorkspaceController::class, 'update']);
$router->post('/workspaces/{id}/switch', [App\Controllers\WorkspaceController::class, 'switch']);
$router->get('/workspaces/select', [App\Controllers\WorkspaceController::class, 'select']);

// ============================================
// SETTINGS ROUTES
// ============================================

$router->get('/settings', [App\Controllers\SettingsController::class, 'index']);
$router->post('/settings/profile', [App\Controllers\SettingsController::class, 'updateProfile']);
$router->post('/settings/password', [App\Controllers\SettingsController::class, 'updatePassword']);
$router->post('/settings/workspace', [App\Controllers\SettingsController::class, 'updateWorkspace']);
$router->post('/settings/notifications', [App\Controllers\SettingsController::class, 'updateNotifications']);

// ============================================
// API ROUTES (for AJAX/future expansion)
// ============================================

$router->get('/api/clients/search', [App\Controllers\Api\ClientApiController::class, 'search']);
$router->get('/api/products/search', [App\Controllers\Api\ProductApiController::class, 'search']);
$router->get('/api/invoices/{id}', [App\Controllers\Api\InvoiceApiController::class, 'show']);

// Dispatch the request
$router->dispatch();
