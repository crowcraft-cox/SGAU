<?php

class Request {
    public function getMethod() {
        return $_SERVER['REQUEST_METHOD'];
    }

    public function getUrl() {
        $path = $_SERVER['REQUEST_URI'] ?? '/';
        $position = strpos($path, '?');
        if ($position !== false) {
            $path = substr($path, 0, $position);
        }
        return $path;
    }

    public function isGet() {
        return $this->getMethod() === 'GET';
    }

    public function isPost() {
        return $this->getMethod() === 'POST';
    }

    public function getBody() {
        $body = [];
        if ($this->isGet()) {
            foreach ($_GET as $key => $value) {
                $body[$key] = filter_input(INPUT_GET, $key, FILTER_SANITIZE_SPECIAL_CHARS);
            }
        }
        if ($this->isPost()) {
            foreach ($_POST as $key => $value) {
                if (is_array($value)) {
                    $body[$key] = filter_input(INPUT_POST, $key, FILTER_SANITIZE_SPECIAL_CHARS, FILTER_REQUIRE_ARRAY);
                } else {
                    $body[$key] = filter_input(INPUT_POST, $key, FILTER_SANITIZE_SPECIAL_CHARS);
                }
            }
        }
        return $body;
    }

    public function get($key, $default = null) {
        $body = $this->getBody();
        return $body[$key] ?? $default;
    }
}
