<?php
/**
 * Session Manager
 * Secure session handling with database storage option
 */

namespace Core\Session;

use Core\Database\Database;

class SessionManager
{
    private static bool $initialized = false;
    private static array $config = [];
    
    /**
     * Initialize session management
     */
    public static function init(array $config): void
    {
        if (self::$initialized) {
            return;
        }
        
        self::$config = $config;
        
        // Set session configuration
        ini_set('session.cookie_httponly', '1');
        ini_set('session.cookie_secure', $config['secure'] ? '1' : '0');
        ini_set('session.cookie_samesite', ucfirst($config['same_site']));
        ini_set('session.use_strict_mode', '1');
        ini_set('session.use_only_cookies', '1');
        ini_set('session.gc_maxlifetime', $config['lifetime'] * 60);
        
        // Set session name
        session_name($config['cookie']);
        
        // Configure session handler based on driver
        switch ($config['driver']) {
            case 'database':
                session_set_save_handler(
                    [self::class, 'open'],
                    [self::class, 'close'],
                    [self::class, 'read'],
                    [self::class, 'write'],
                    [self::class, 'destroy'],
                    [self::class, 'gc'],
                    true
                );
                break;
            
            case 'redis':
                // Redis handler would be implemented here
                break;
            
            case 'file':
            default:
                // Use default file-based sessions
                ini_set('session.save_path', STORAGE_PATH . '/sessions');
                break;
        }
        
        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Regenerate session ID periodically for security
        self::checkSessionRegeneration();
        
        self::$initialized = true;
    }
    
    /**
     * Check if session should be regenerated
     */
    private static function checkSessionRegeneration(): void
    {
        $lastRegenerate = $_SESSION['_last_regenerate'] ?? 0;
        $regenerateInterval = 300; // 5 minutes
        
        if ($lastRegenerate + $regenerateInterval < time()) {
            self::regenerateId();
        }
    }
    
    /**
     * Regenerate session ID
     */
    public static function regenerateId(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
            $_SESSION['_last_regenerate'] = time();
        }
    }
    
    /**
     * Set a session value
     */
    public static function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }
    
    /**
     * Get a session value
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }
    
    /**
     * Check if session key exists
     */
    public static function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }
    
    /**
     * Remove a session value
     */
    public static function remove(string $key): void
    {
        unset($_SESSION[$key]);
    }
    
    /**
     * Get all session data
     */
    public static function all(): array
    {
        return $_SESSION ?? [];
    }
    
    /**
     * Set flash message
     */
    public static function flash(string $key, mixed $value = null): mixed
    {
        if ($value === null) {
            // Get and remove flash
            $flashKey = "_flash_{$key}";
            $flash = $_SESSION[$flashKey] ?? null;
            unset($_SESSION[$flashKey]);
            return $flash;
        }
        
        // Set flash
        $_SESSION["_flash_{$key}"] = $value;
        return null;
    }
    
    /**
     * Destroy session
     */
    public static function destroy(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            $_SESSION = [];
            
            if (ini_get('session.use_cookies')) {
                $params = session_get_cookie_params();
                setcookie(
                    session_name(),
                    '',
                    time() - 42000,
                    $params['path'],
                    $params['domain'],
                    $params['secure'],
                    $params['httponly']
                );
            }
            
            session_destroy();
            self::$initialized = false;
        }
    }
    
    // Session handler methods for database storage
    
    public static function open(string $savePath, string $sessionName): bool
    {
        return true;
    }
    
    public static function close(): bool
    {
        return true;
    }
    
    public static function read(string $sessionId): string
    {
        try {
            $db = Database::getInstance(config('database'));
            $result = $db->fetchOne(
                "SELECT data FROM eb_sessions WHERE session_id = ? AND expires_at > NOW()",
                [$sessionId]
            );
            
            return $result ? $result['data'] : '';
        } catch (\Exception $e) {
            logger('error', 'Session read failed: ' . $e->getMessage());
            return '';
        }
    }
    
    public static function write(string $sessionId, string $data): bool
    {
        try {
            $db = Database::getInstance(config('database'));
            $expiresAt = date('Y-m-d H:i:s', time() + (self::$config['lifetime'] * 60));
            
            // Try to update existing session
            $affected = $db->update(
                'sessions',
                ['data' => $data, 'expires_at' => $expiresAt],
                'session_id = ?',
                [$sessionId]
            );
            
            // Insert if no rows updated
            if ($affected === 0) {
                $db->insert('sessions', [
                    'session_id' => $sessionId,
                    'data' => $data,
                    'expires_at' => $expiresAt,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
            }
            
            return true;
        } catch (\Exception $e) {
            logger('error', 'Session write failed: ' . $e->getMessage());
            return false;
        }
    }
    
    public static function destroy(string $sessionId): bool
    {
        try {
            $db = Database::getInstance(config('database'));
            $db->delete('sessions', 'session_id = ?', [$sessionId]);
            return true;
        } catch (\Exception $e) {
            logger('error', 'Session destroy failed: ' . $e->getMessage());
            return false;
        }
    }
    
    public static function gc(int $maxLifetime): int|false
    {
        try {
            $db = Database::getInstance(config('database'));
            return $db->delete('sessions', 'expires_at < NOW()');
        } catch (\Exception $e) {
            logger('error', 'Session GC failed: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get session ID
     */
    public static function getId(): string
    {
        return session_id();
    }
    
    /**
     * Check if user is authenticated
     */
    public static function isAuthenticated(): bool
    {
        return self::has('user_id') && self::has('authenticated');
    }
    
    /**
     * Get current user ID
     */
    public static function getUserId(): ?int
    {
        return self::get('user_id');
    }
    
    /**
     * Get current workspace ID
     */
    public static function getWorkspaceId(): ?int
    {
        return self::get('workspace_id');
    }
}
