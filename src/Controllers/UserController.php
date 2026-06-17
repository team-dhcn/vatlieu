<?php
namespace Controllers;

use Models\User;

class UserController extends BaseController {
    private $userModel;

    public function __construct() {
        $this->userModel = new User();
    }

    public function login() {
        $body = $this->getBody();
        $u = trim($body['Tendangnhap'] ?? '');
        $p = trim($body['Matkhau'] ?? '');

        if (!$u || !$p) {
            $this->jsonResponse(false, 'Vui lòng nhập đầy đủ tài khoản và mật khẩu.', null, 400);
        }

        $user = $this->userModel->getByUsername($u);

        $valid = false;
        if ($user) {
            if (password_verify($p, $user['Matkhau']) || $p === $user['Matkhau']) {
                $valid = true;
            }
        }

        if ($valid) {
            $payload = [
                'Manv'       => $user['Manv'],
                'Tendangnhap'=> $user['Tendangnhap'],
                'Vaitro'     => $user['Vaitro'],
                'exp'        => time() + 86400
            ];
            $token = base64_encode(json_encode($payload));

            $this->jsonResponse(true, 'Đăng nhập thành công', [
                'token' => $token,
                'user'  => [
                    'Manv'        => $user['Manv'],
                    'Tendangnhap' => $user['Tendangnhap'],
                    'Hovaten'     => $user['Hovaten'],
                    'Email'       => $user['Email'],
                    'Vaitro'      => $user['Vaitro'],
                ]
            ]);
        } else {
            $this->jsonResponse(false, 'Sai tài khoản hoặc mật khẩu!', null, 401);
        }
    }

    public function getUsers() {
        $users = $this->userModel->getAll('Manv');
        // Hide passwords
        foreach ($users as &$u) unset($u['Matkhau']);
        $this->jsonResponse(true, 'Users retrieved', ['users' => $users]);
    }

    public function getUser($params) {
        $user = $this->userModel->getById($params['id']);
        if ($user) {
            unset($user['Matkhau']);
            $this->jsonResponse(true, 'User found', $user);
        }
        $this->jsonResponse(false, 'User not found', null, 404);
    }

    public function createUser() {
        $body = $this->getBody();
        if ($this->userModel->getByUsername($body['Tendangnhap'])) {
            $this->jsonResponse(false, 'Tên đăng nhập đã tồn tại', null, 409);
        }
        if ($this->userModel->create($body)) {
            $this->jsonResponse(true, 'User created successfully', ['id' => $body['Manv']], 201);
        }
        $this->jsonResponse(false, 'Create failed', null, 400);
    }

    public function updateUser($params) {
        $body = $this->getBody();
        if ($this->userModel->update($params['id'], $body)) {
            $this->jsonResponse(true, 'User updated');
        }
        $this->jsonResponse(false, 'Update failed', null, 400);
    }

    public function deleteUser($params) {
        if ($this->userModel->delete($params['id'])) {
            $this->jsonResponse(true, 'User deleted');
        }
        $this->jsonResponse(false, 'Delete failed', null, 400);
    }
}
