<?php
namespace Models;

class Customer extends BaseModel {
    protected $table = 'Khachhang';
    protected $primaryKey = 'Makh';

    public function getAllWithTypes() {
        $sql = "SELECT kh.*, lkh.Tenloaikh FROM {$this->table} kh 
                LEFT JOIN Loaikhachhang lkh ON kh.Maloaikh = lkh.Maloaikh
                ORDER BY kh.Makh";
        return $this->pdo->query($sql)->fetchAll();
    }

    public function getByIdWithType($id) {
        $stmt = $this->pdo->prepare("SELECT kh.*, lkh.Tenloaikh FROM {$this->table} kh
                LEFT JOIN Loaikhachhang lkh ON kh.Maloaikh = lkh.Maloaikh
                WHERE kh.Makh = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function create($data) {
        $stmt = $this->pdo->prepare("INSERT INTO {$this->table} (Makh, Tenkh, Sdtkh, Diachikh, Maloaikh) VALUES (?, ?, ?, ?, ?)");
        return $stmt->execute([
            $data['Makh'],
            $data['Tenkh'],
            $data['Sdtkh'] ?? '',
            $data['Diachikh'] ?? '',
            empty($data['Maloaikh']) ? null : $data['Maloaikh']
        ]);
    }

    public function update($id, $data) {
        $stmt = $this->pdo->prepare("UPDATE {$this->table} SET Tenkh=?, Sdtkh=?, Diachikh=?, Maloaikh=? WHERE {$this->primaryKey}=?");
        return $stmt->execute([
            $data['Tenkh'],
            $data['Sdtkh'] ?? '',
            $data['Diachikh'] ?? '',
            empty($data['Maloaikh']) ? null : $data['Maloaikh'],
            $id
        ]);
    }
}
