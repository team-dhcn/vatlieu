<?php
namespace Core;

class Router {
    private $routes = [];

    public function add($method, $path, $callback) {
        $path = $this->normalizePath($path);
        $this->routes[] = [
            'method'   => strtoupper($method),
            'path'     => $path,
            'callback' => $callback,
        ];
    }

    public function dispatch($method, $uri) {
        $fullPath = parse_url($uri, PHP_URL_PATH);
        $method   = strtoupper($method);

        foreach ($this->routes as $route) {
            $routePath = $route['path'];
            if ($route['method'] === $method) {
                $pattern = $this->getPattern($routePath);

                if (preg_match($pattern, $fullPath, $matches) ||
                    preg_match($this->getPattern('.*' . $routePath), $fullPath, $matches)) {
                    $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                    return $this->executeCallback($route['callback'], $params);
                }
            }
        }

        http_response_code(404);
        echo json_encode([
            'success'      => false,
            'message'      => 'Endpoint not found',
            'debug_path'   => $fullPath,
            'debug_method' => $method,
        ]);
        exit;
    }

    private function normalizePath($path) {
        return '/' . trim($path, '/');
    }

    private function getPattern($path) {
        $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[^/]+)', $path);
        return '#^' . $pattern . '$#';
    }

    private function executeCallback($callback, $params) {
        if (is_array($callback)) {
            $controller = new $callback[0]();
            return $controller->{$callback[1]}($params);
        }
        return call_user_func($callback, $params);
    }
}
