<?php
namespace Controllers;

class BaseController {
    protected function jsonResponse($success, $message, $data = null, $statusCode = 200) {
        http_response_code($statusCode);
        echo  json_encode(['success' => $success, 'message' => $message, 'data' => $data]);
        exit;
    }

    protected function getBody() {
        return json_decode(file_get_contents('php://input'), true) ?? $_POST;
    }

    protected function getUserRole() {
        $auth = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        if (preg_match('/Bearer\s(\S+)/', $auth, $matches)) {
            $token = $matches[1];
            $payload = json_decode(base64_decode($token), true);
            if ($payload) {
                return strtolower($payload['Vaitro'] ?? 'guest');
            }
        }
        return 'guest';
    }
}
