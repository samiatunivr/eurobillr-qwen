<?php
/**
 * Simple Router
 * Lightweight routing system
 */

namespace Core\Routing;

class Router
{
    private array $routes = [];
    private string $basePath = '';
    
    public function __construct(string $basePath = '')
    {
        $this->basePath = rtrim($basePath, '/');
    }
    
    /**
     * Register GET route
     */
    public function get(string $path, callable|array $handler): self
    {
        return $this->addRoute('GET', $path, $handler);
    }
    
    /**
     * Register POST route
     */
    public function post(string $path, callable|array $handler): self
    {
        return $this->addRoute('POST', $path, $handler);
    }
    
    /**
     * Register PUT route
     */
    public function put(string $path, callable|array $handler): self
    {
        return $this->addRoute('PUT', $path, $handler);
    }
    
    /**
     * Register DELETE route
     */
    public function delete(string $path, callable|array $handler): self
    {
        return $this->addRoute('DELETE', $path, $handler);
    }
    
    /**
     * Add route to collection
     */
    private function addRoute(string $method, string $path, callable|array $handler): self
    {
        $path = $this->basePath . '/' . trim($path, '/');
        $path = '/' . trim($path, '/');
        
        // Extract parameters
        preg_match_all('/\{(\w+)\}/', $path, $matches);
        $params = $matches[1];
        
        // Convert to regex pattern
        $pattern = preg_replace('/\{(\w+)\}/', '([^/]+)', $path);
        $pattern = '#^' . $pattern . '$#';
        
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'pattern' => $pattern,
            'params' => $params,
            'handler' => $handler
        ];
        
        return $this;
    }
    
    /**
     * Dispatch request to handler
     */
    public function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/';
        $uri = '/' . trim($uri, '/');
        
        // Handle method override
        if ($method === 'POST' && isset($_POST['_method'])) {
            $method = strtoupper($_POST['_method']);
        }
        
        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }
            
            if (preg_match($route['pattern'], $uri, $matches)) {
                // Extract parameters
                $params = [];
                foreach ($route['params'] as $index => $param) {
                    $params[$param] = $matches[$index + 1] ?? null;
                }
                
                // Call handler
                $this->callHandler($route['handler'], $params);
                return;
            }
        }
        
        // No route found - 404
        http_response_code(404);
        
        // Try to serve static file
        $staticPath = __DIR__ . '/../../public' . $uri;
        if (file_exists($staticPath) && is_file($staticPath)) {
            $this->serveStatic($staticPath);
            return;
        }
        
        // Show 404 page
        $this->render404();
    }
    
    /**
     * Call route handler
     */
    private function callHandler(callable|array $handler, array $params): void
    {
        if (is_callable($handler)) {
            call_user_func_array($handler, $params);
            return;
        }
        
        if (is_array($handler) && count($handler) === 2) {
            [$controllerClass, $method] = $handler;
            
            if (!class_exists($controllerClass)) {
                throw new \Exception("Controller not found: {$controllerClass}");
            }
            
            $controller = new $controllerClass();
            
            // Run middleware
            if (method_exists($controller, 'runMiddleware')) {
                $controller->runMiddleware();
            }
            
            if (!method_exists($controller, $method)) {
                throw new \Exception("Method {$method} not found in {$controllerClass}");
            }
            
            call_user_func_array([$controller, $method], $params);
            return;
        }
        
        throw new \Exception('Invalid route handler');
    }
    
    /**
     * Serve static file
     */
    private function serveStatic(string $path): void
    {
        $extension = pathinfo($path, PATHINFO_EXTENSION);
        
        $mimeTypes = [
            'css' => 'text/css',
            'js' => 'application/javascript',
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'svg' => 'image/svg+xml',
            'ico' => 'image/x-icon',
            'woff' => 'font/woff',
            'woff2' => 'font/woff2',
            'ttf' => 'font/ttf',
            'eot' => 'application/vnd.ms-fontobject'
        ];
        
        $mimeType = $mimeTypes[$extension] ?? 'application/octet-stream';
        
        header('Content-Type: ' . $mimeType);
        header('Cache-Control: public, max-age=31536000');
        
        readfile($path);
        exit;
    }
    
    /**
     * Render 404 page
     */
    private function render404(): void
    {
        if (file_exists(__DIR__ . '/../../app/Views/errors/404.php')) {
            http_response_code(404);
            include __DIR__ . '/../../app/Views/errors/404.php';
        } else {
            echo '<h1>404 - Page Not Found</h1>';
        }
        exit;
    }
    
    /**
     * Get all registered routes (for debugging)
     */
    public function getRoutes(): array
    {
        return $this->routes;
    }
}
