<?php
/**
 * Eurobillr - Main Entry Point
 * Enterprise SaaS Invoicing Platform
 * 
 * @package Eurobillr
 * @version 1.0.0
 */

// Define application constant
define('EUROBILLR', true);

// Load configuration
$config = require __DIR__ . '/app/Config/config.php';

// Set timezone
date_default_timezone_set($config['timezone']);

// Set error reporting based on environment
if ($config['logging']['level'] === 'debug') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Set default headers for security
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net; style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; img-src 'self' data: https:; font-src 'self' https://cdn.jsdelivr.net; connect-src 'self' https:;");

// Load autoloader
require __DIR__ . '/app/Config/autoload.php';

// Initialize core services
use Core\Database\Database;
use Core\Session\SessionManager;
use Core\Auth\Authentication;
use Core\Security\CsrfProtection;
use Core\Cache\CacheManager;

// Initialize database
Database::getInstance($config['database']);

// Initialize session
SessionManager::init($config['session']);

// Start output buffering
ob_start();

// Register shutdown function for cleanup
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE])) {
        // Log fatal error
        error_log("Fatal error: {$error['message']} in {$error['file']} on line {$error['line']}");
        
        if (DEBUG_MODE) {
            echo "<h1>Application Error</h1>";
            echo "<pre>" . htmlspecialchars($error['message']) . "</pre>";
        } else {
            http_response_code(500);
            echo "<h1>Internal Server Error</h1>";
        }
    }
    
    ob_end_flush();
});

// Global helper functions
if (!function_exists('config')) {
    function config($key = null, $default = null) {
        static $config = null;
        if ($config === null) {
            $config = require BASE_PATH . '/app/Config/config.php';
        }
        
        if ($key === null) {
            return $config;
        }
        
        $keys = explode('.', $key);
        $value = $config;
        
        foreach ($keys as $k) {
            if (isset($value[$k])) {
                $value = $value[$k];
            } else {
                return $default;
            }
        }
        
        return $value;
    }
}

if (!function_exists('redirect')) {
    function redirect($url, $statusCode = 302) {
        header('Location: ' . $url, true, $statusCode);
        exit;
    }
}

if (!function_exists('view')) {
    function view($template, $data = []) {
        extract($data);
        $templatePath = BASE_PATH . '/app/Views/' . str_replace('.', '/', $template) . '.php';
        
        if (!file_exists($templatePath)) {
            throw new InvalidArgumentException("View not found: {$template}");
        }
        
        include $templatePath;
    }
}

if (!function_exists('response')) {
    function response($data, $statusCode = 200, $headers = []) {
        http_response_code($statusCode);
        
        foreach ($headers as $key => $value) {
            header("$key: $value");
        }
        
        if (is_array($data) || is_object($data)) {
            header('Content-Type: application/json');
            echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        } else {
            echo $data;
        }
        
        exit;
    }
}

if (!function_exists('asset')) {
    function asset($path) {
        return config('urls.app') . '/public/assets/' . ltrim($path, '/');
    }
}

if (!function_exists('url')) {
    function url($path = '') {
        return config('urls.app') . '/' . ltrim($path, '/');
    }
}

if (!function_exists('secure_random')) {
    function secure_random($length = 32) {
        return bin2hex(random_bytes($length / 2));
    }
}

if (!function_exists('hash_password')) {
    function hash_password($password) {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => config('security.hash_cost')]);
    }
}

if (!function_exists('verify_password')) {
    function verify_password($password, $hash) {
        return password_verify($password, $hash);
    }
}

if (!function_exists('sanitize')) {
    function sanitize($data) {
        if (is_array($data)) {
            return array_map('sanitize', $data);
        }
        return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('format_currency')) {
    function format_currency($amount, $currency = 'EUR') {
        $currencies = config('currencies');
        $symbol = $currencies[$currency]['symbol'] ?? '€';
        $precision = $currencies[$currency]['precision'] ?? 2;
        
        return $symbol . ' ' . number_format($amount, $precision, '.', ',');
    }
}

if (!function_exists('format_date')) {
    function format_date($date, $format = null) {
        if ($format === null) {
            $format = config('date_formats')[config('default_language')] ?? 'Y-m-d';
        }
        
        if (is_string($date)) {
            $date = new DateTime($date);
        }
        
        return $date->format($format);
    }
}

if (!function_exists('logger')) {
    function logger($level, $message, $context = []) {
        static $logger = null;
        
        if ($logger === null) {
            $logger = new Core\Security\Logger();
        }
        
        $logger->log($level, $message, $context);
    }
}

if (!function_exists('event')) {
    function event($eventName, $payload = []) {
        static $dispatcher = null;
        
        if ($dispatcher === null) {
            $dispatcher = new Core\Events\EventDispatcher();
        }
        
        return $dispatcher->dispatch($eventName, $payload);
    }
}

if (!function_exists('queue_job')) {
    function queue_job($jobClass, $payload = [], $delay = 0) {
        static $queue = null;
        
        if ($queue === null) {
            $queue = new Core\Queue\QueueManager();
        }
        
        return $queue->push($jobClass, $payload, $delay);
    }
}

return $config;
