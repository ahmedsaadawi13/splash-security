<?php
/**
 * Router Class
 * Handles URL routing and dispatches to appropriate controllers
 */
class Router {
    private $routes = [];
    private $currentRoute = null;

    public function get($path, $handler) {
        $this->addRoute('GET', $path, $handler);
        return $this;
    }

    public function post($path, $handler) {
        $this->addRoute('POST', $path, $handler);
        return $this;
    }

    public function put($path, $handler) {
        $this->addRoute('PUT', $path, $handler);
        return $this;
    }

    public function delete($path, $handler) {
        $this->addRoute('DELETE', $path, $handler);
        return $this;
    }

    private function addRoute($method, $path, $handler) {
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'handler' => $handler
        ];
    }

    public function dispatch() {
        $method = $_SERVER['REQUEST_METHOD'];
        $url = $_GET['url'] ?? '/';
        $url = '/' . trim($url, '/');

        foreach ($this->routes as $route) {
            $pattern = $this->convertPathToRegex($route['path']);

            if ($route['method'] === $method && preg_match($pattern, $url, $matches)) {
                array_shift($matches); // Remove full match
                $this->currentRoute = $route;
                return $this->executeHandler($route['handler'], $matches);
            }
        }

        // No route found - 404
        http_response_code(404);
        View::render('errors/404', ['title' => '404 - Page Not Found']);
        exit;
    }

    private function convertPathToRegex($path) {
        // Convert /users/{id} to regex pattern
        $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '([a-zA-Z0-9_-]+)', $path);
        return '#^' . $pattern . '$#';
    }

    private function executeHandler($handler, $params = []) {
        if (is_callable($handler)) {
            return call_user_func_array($handler, $params);
        }

        if (is_string($handler)) {
            $parts = explode('@', $handler);
            $controllerName = $parts[0];
            $method = $parts[1] ?? 'index';

            $controllerFile = APP_PATH . '/controllers/' . $controllerName . '.php';

            if (!file_exists($controllerFile)) {
                die("Controller not found: $controllerName");
            }

            require_once $controllerFile;

            if (!class_exists($controllerName)) {
                die("Controller class not found: $controllerName");
            }

            $controller = new $controllerName();

            if (!method_exists($controller, $method)) {
                die("Method not found: $controllerName::$method");
            }

            return call_user_func_array([$controller, $method], $params);
        }
    }
}
