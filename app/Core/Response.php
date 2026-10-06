<?php

class Response {
    public function setStatusCode(int $code) {
        http_response_code($code);
    }

    public function redirect(string $url) {
        header("Location: " . BASE_PATH . $url);
        exit;
    }

    public function json(array $data, int $statusCode = 200) {
        $this->setStatusCode($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}
