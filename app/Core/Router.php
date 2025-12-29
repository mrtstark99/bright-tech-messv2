<?php
/**
 * MessV2 Router
 * Clean URL routing with controller dispatch
 */

class Router {
    private string $basePath;
    private array $routes = [];
    
    public function __construct(string $basePath = '') {
        $this->basePath = rtrim($basePath, '/');
    }
    
    public function get(string $path, string $controller, string $action): void {
        $this->addRoute('GET', $path, $controller, $action);
    }
    
    public function post(string $path, string $controller, string $action): void {
        $this->addRoute('POST', $path, $controller, $action);
    }
    
    public function any(string $path, string $controller, string $action): void {
        $this->addRoute('ANY', $path, $controller, $action);
    }
    
    private function addRoute(string $method, string $path, string $controller, string $action): void {
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'controller' => $controller,
            'action' => $action,
            'pattern' => $this->pathToPattern($path)
        ];
    }
    
    private function pathToPattern(string $path): string {
        $pattern = preg_replace('/\{(\w+)\}/', '(?P<$1>[^/]+)', $path);
        return '#^' . $pattern . '$#';
    }
    
    public function dispatch(): void {
        $uri = $this->getUri();
        $method = $_SERVER['REQUEST_METHOD'];
        
        foreach ($this->routes as $route) {
            if ($route['method'] !== 'ANY' && $route['method'] !== $method) {
                continue;
            }
            
            if (preg_match($route['pattern'], $uri, $matches)) {
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                $this->callController($route['controller'], $route['action'], $params);
                return;
            }
        }
        
        // 404
        http_response_code(404);
        echo "404 - Page Not Found";
    }
    
    private function getUri(): string {
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        
        if ($this->basePath && strpos($uri, $this->basePath) === 0) {
            $uri = substr($uri, strlen($this->basePath));
        }
        
        return $uri ?: '/';
    }
    
    private function callController(string $controller, string $action, array $params = []): void {
        // Handle namespaced controllers (e.g., Api\MessageController)
        $controllerPath = str_replace('\\', '/', $controller);
        $controllerFile = APP_PATH . '/Controllers/' . $controllerPath . '.php';
        
        // Get just the class name for instantiation
        $className = str_replace('\\', '', $controller);
        $parts = explode('/', $controllerPath);
        $className = end($parts);
        
        if (!file_exists($controllerFile)) {
            throw new Exception("Controller not found: {$controller} (looked for {$controllerFile})");
        }
        
        require_once $controllerFile;
        
        if (!class_exists($className)) {
            throw new Exception("Controller class not found: {$className}");
        }
        
        $instance = new $className();
        
        if (!method_exists($instance, $action)) {
            throw new Exception("Action not found: {$action} in {$className}");
        }
        
        call_user_func_array([$instance, $action], $params);
    }
}
