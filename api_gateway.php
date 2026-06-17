<?php
/**
 * API Gateway — Kiến trúc monolith
 * Một cổng duy nhất, xử lý trực tiếp không proxy HTTP/cURL
 */

session_start();
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/src/bootstrap.php';
require_once __DIR__ . '/src/routes.php';

use Core\Router;

// Phân tích route từ URL hoặc query ?route=
$routePath = '';
if (!empty($_GET['route'])) {
    $routePath = '/' . trim($_GET['route'], '/');
} elseif (!empty($_SERVER['PATH_INFO'])) {
    $routePath = $_SERVER['PATH_INFO'];
} else {
    $fullPath  = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $routePath = preg_replace('#^.*/api_gateway\.php#', '', $fullPath);
}
$routePath = '/' . trim($routePath, '/');

// Kiểm tra quyền admin cho thao tác users (qua JWT token)
$method = $_SERVER['REQUEST_METHOD'];
$parts  = array_values(array_filter(explode('/', trim($routePath, '/'))));
if (($parts[0] ?? '') === 'users' && !in_array($method, ['GET'], true)) {
    $auth = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    $role = 'guest';
    if (preg_match('/Bearer\s(\S+)/', $auth, $m)) {
        $payload = json_decode(base64_decode($m[1]), true);
        if ($payload) {
            $role = strtolower($payload['Vaitro'] ?? 'guest');
        }
    }
    if ($role !== 'admin') {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Admin only']);
        exit;
    }
}

try {
    $router = new Router();
    registerApiRoutes($router);
    $router->dispatch($method, $routePath);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server Error: ' . $e->getMessage()]);
}
