<?php

class Router {
    private $routes = [];

    public function get($path, $action) {
        $this->add('GET', $path, $action);
    }

    public function post($path, $action) {
        $this->add('POST', $path, $action);
    }

    private function add($method, $path, $action) {
        $pattern = '#^' . preg_replace('#\{([a-zA-Z0-9_]+)\}#', '(?P<$1>[^/]+)', $path) . '$#';
        $this->routes[$method][] = [
            'path' => $path,
            'action' => $action,
            'pattern' => $pattern,
        ];
    }

    public function resolve($uri, $method) {
        // Validation CSRF pour toutes les requêtes POST
        if ($method === 'POST') {
            validate_csrf_token();
        }

        $uri = rtrim($uri, '/') ?: '/';

        if (!isset($this->routes[$method])) {
            abort(404, 'Méthode non autorisée pour cette route.');
        }

        foreach ($this->routes[$method] as $route) {
            if (preg_match($route['pattern'], $uri, $matches)) {
                $params = [];
                foreach ($matches as $key => $value) {
                    if (!is_int($key)) {
                        $params[$key] = $value;
                    }
                }

                $positionalParams = array_values($params);

                if (is_callable($route['action'])) {
                    return call_user_func_array($route['action'], $positionalParams);
                }

                if (is_array($route['action'])) {
                    return call_user_func_array($route['action'], $positionalParams);
                }
            }
        }

        abort(404, 'Page introuvable.');
    }
}
