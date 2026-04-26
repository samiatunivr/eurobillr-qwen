<?php
/**
 * Base Controller
 * All controllers extend this class
 */

namespace App\Controllers;

use Core\Session\SessionManager;

abstract class Controller
{
    protected SessionManager $session;
    protected array $middleware = [];
    protected ?int $currentUserId = null;
    protected ?int $currentWorkspaceId = null;
    
    public function __construct()
    {
        $this->session = SessionManager::getInstance();
    }
    
    /**
     * Register middleware
     */
    protected function middleware(string $name, array $options = []): void
    {
        $this->middleware[] = [
            'name' => $name,
            'options' => $options
        ];
    }
    
    /**
     * Run middleware checks
     */
    protected function runMiddleware(): void
    {
        foreach ($this->middleware as $middleware) {
            $this->runSingleMiddleware($middleware['name'], $middleware['options']);
        }
    }
    
    /**
     * Run single middleware
     */
    private function runSingleMiddleware(string $name, array $options): void
    {
        switch ($name) {
            case 'auth':
                $this->requireAuth();
                break;
            case 'guest':
                $this->requireGuest();
                break;
            case 'role':
                $this->requireRole($options['roles'] ?? []);
                break;
            case 'workspace':
                $this->requireWorkspaceAccess();
                break;
        }
    }
    
    /**
     * Require authentication
     */
    protected function requireAuth(): void
    {
        if (!$this->session->isAuthenticated()) {
            redirect('/login');
        }
        
        $this->currentUserId = $this->session->get('user_id');
    }
    
    /**
     * Require guest (not authenticated)
     */
    protected function requireGuest(): void
    {
        if ($this->session->isAuthenticated()) {
            redirect('/dashboard');
        }
    }
    
    /**
     * Require specific role(s)
     */
    protected function requireRole(array $roles): void
    {
        $this->requireAuth();
        
        $userRole = $this->session->get('workspace_role');
        
        if (!in_array($userRole, $roles)) {
            http_response_code(403);
            $this->render('errors/403');
            exit;
        }
    }
    
    /**
     * Require workspace access
     */
    protected function requireWorkspaceAccess(): void
    {
        $this->requireAuth();
        
        $workspaceId = $_GET['workspace'] ?? $this->session->get('current_workspace_id');
        
        if (!$workspaceId) {
            redirect('/workspaces/select');
        }
        
        // Verify user has access to this workspace
        $hasAccess = $this->verifyWorkspaceAccess($workspaceId);
        
        if (!$hasAccess) {
            http_response_code(403);
            $this->render('errors/403');
            exit;
        }
        
        $this->currentWorkspaceId = $workspaceId;
    }
    
    /**
     * Verify user has access to workspace
     */
    private function verifyWorkspaceAccess(int $workspaceId): bool
    {
        $userId = $this->session->get('user_id');
        
        $sql = "SELECT COUNT(*) as count FROM eb_workspace_members 
                WHERE user_id = :user_id AND workspace_id = :workspace_id AND status = 'active'";
        
        $result = \Core\Database\Database::getInstance(config('database'))->fetchOne($sql, [
            'user_id' => $userId,
            'workspace_id' => $workspaceId
        ]);
        
        return (int) ($result['count'] ?? 0) > 0;
    }
    
    /**
     * Get current user ID
     */
    protected function getCurrentUserId(): int
    {
        if ($this->currentUserId === null) {
            $this->currentUserId = $this->session->get('user_id');
        }
        
        return $this->currentUserId ?: 0;
    }
    
    /**
     * Get current workspace ID
     */
    protected function getCurrentWorkspaceId(): int
    {
        if ($this->currentWorkspaceId === null) {
            $this->currentWorkspaceId = $this->session->get('current_workspace_id');
        }
        
        return $this->currentWorkspaceId ?: 0;
    }
    
    /**
     * Validate CSRF token
     */
    protected function validateCsrfToken(string $token): bool
    {
        if (empty($token)) {
            return false;
        }
        
        $storedToken = $this->session->get('csrf_token');
        
        if (empty($storedToken)) {
            return false;
        }
        
        return hash_equals($storedToken, $token);
    }
    
    /**
     * Generate CSRF token
     */
    protected function generateCsrfToken(): string
    {
        $token = bin2hex(random_bytes(32));
        $this->session->set('csrf_token', $token);
        return $token;
    }
    
    /**
     * Render view template
     */
    protected function render(string $view, array $data = []): void
    {
        extract($data);
        
        $viewPath = __DIR__ . '/../Views/' . $view . '.php';
        
        if (!file_exists($viewPath)) {
            throw new \Exception("View not found: {$view}");
        }
        
        // Include layout wrapper
        $layoutPath = __DIR__ . '/../Views/layouts/app.php';
        
        if (file_exists($layoutPath)) {
            ob_start();
            include $viewPath;
            $content = ob_get_clean();
            include $layoutPath;
        } else {
            include $viewPath;
        }
    }
    
    /**
     * Return JSON response
     */
    protected function json(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
    
    /**
     * Handle AJAX request validation
     */
    protected function expectJson(): void
    {
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        
        if (stripos($contentType, 'application/json') === false) {
            $this->json(['error' => 'Expected JSON request'], 400);
        }
    }
    
    /**
     * Get JSON input
     */
    protected function getJsonInput(): array
    {
        $input = file_get_contents('php://input');
        return json_decode($input, true) ?? [];
    }
    
    /**
     * Flash success message
     */
    protected function flashSuccess(string $message): void
    {
        $this->session->setFlash('success', $message);
    }
    
    /**
     * Flash error message
     */
    protected function flashError(string $message): void
    {
        $this->session->setFlash('error', $message);
    }
    
    /**
     * Check if request is POST
     */
    protected function isPost(): bool
    {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }
    
    /**
     * Sanitize output for HTML
     */
    protected function e(string $string): string
    {
        return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Format currency
     */
    protected function formatCurrency(float $amount, string $currency = 'EUR'): string
    {
        $symbols = [
            'EUR' => '€',
            'USD' => '$',
            'GBP' => '£',
            'CHF' => 'CHF',
            'SEK' => 'kr',
            'NOK' => 'kr',
            'DKK' => 'kr',
            'PLN' => 'zł',
            'CZK' => 'Kč',
            'HUF' => 'Ft'
        ];
        
        $symbol = $symbols[$currency] ?? $currency . ' ';
        $formatted = number_format(abs($amount), 2, '.', ',');
        
        return $symbol . $formatted;
    }
    
    /**
     * Format date
     */
    protected function formatDate(string $date, string $format = 'medium'): string
    {
        $timestamp = strtotime($date);
        
        if ($format === 'short') {
            return date('d/m/Y', $timestamp);
        } elseif ($format === 'long') {
            return date('F j, Y', $timestamp);
        } elseif ($format === 'full') {
            return date('l, F j, Y', $timestamp);
        }
        
        return date('Y-m-d', $timestamp);
    }
    
    /**
     * Get asset URL
     */
    protected function asset(string $path): string
    {
        $basePath = config('app.base_path') ?? '/';
        return rtrim($basePath, '/') . '/public/' . ltrim($path, '/');
    }
    
    /**
     * Get route URL
     */
    protected function route(string $path): string
    {
        $basePath = config('app.base_path') ?? '/';
        return rtrim($basePath, '/') . '/' . ltrim($path, '/');
    }
}
