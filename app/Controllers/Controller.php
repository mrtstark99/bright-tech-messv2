<?php
/**
 * Base Controller
 * All controllers extend this class
 */

class Controller {
    
    /**
     * Render a view with data
     */
    protected function view(string $view, array $data = []): void {
        extract($data);
        
        $viewPath = RESOURCES_PATH . '/views/' . str_replace('.', '/', $view) . '.php';
        
        if (!file_exists($viewPath)) {
            throw new Exception("View not found: {$view}");
        }
        
        require $viewPath;
    }
    
    /**
     * Return JSON response
     */
    protected function json(array $data, int $status = 200): void {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    /**
     * Redirect to URL
     */
    protected function redirect(string $url): void {
        header('Location: ' . APP_URL . $url);
        exit;
    }
    
    /**
     * Get POST data
     */
    protected function input(string $key = null, $default = null) {
        $data = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        
        if ($key === null) return $data;
        return $data[$key] ?? $default;
    }
    
    /**
     * Get query parameter
     */
    protected function query(string $key = null, $default = null) {
        if ($key === null) return $_GET;
        return $_GET[$key] ?? $default;
    }
}
