<?php
namespace Models;

use PDO;

class User extends BaseModel {
    protected $table = 'Nguoidung';
    protected $primaryKey = 'Manv';

    public function getByUsername($username) {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE Tendangnhap = ?");
        $stmt->execute([$username]);
        return $stmt->fetch();
    }

    public function create($data) {
        $hashedPw = password_hash($data['Matkhau'], PASSWORD_DEFAULT);
        $stmt = $this->pdo->prepare("INSERT INTO {$this->table} (Manv, Tendangnhap, Matkhau, Hovaten, Email, Vaitro) VALUES (?, ?, ?, ?, ?, ?)");
        return $stmt->execute([
            $data['Manv'],
            $data['Tendangnhap'],
            $hashedPw,
            $data['Hovaten'] ?? '',
            $data['Email'] ?? '',
            $data['Vaitro'] ?? 'staff'
        ]);
    }

    public function update($id, $data) {
        $fields = [];
        $params = [];
        if (isset($data['Hovaten'])) { $fields[] = 'Hovaten = ?'; $params[] = $data['Hovaten']; }
        if (isset($data['Email']))   { $fields[] = 'Email = ?';   $params[] = $data['Email']; }
        if (isset($data['Vaitro'])) { $fields[] = 'Vaitro = ?';  $params[] = $data['Vaitro']; }
        if (isset($data['Matkhau']) && $data['Matkhau']) {
            $fields[] = 'Matkhau = ?';
            $params[] = password_hash($data['Matkhau'], PASSWORD_DEFAULT);
        }
        
        if (empty($fields)) return false;
        
        $params[] = $id;
        $sql = "UPDATE {$this->table} SET " . implode(', ', $fields) . " WHERE {$this->primaryKey} = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }
}
