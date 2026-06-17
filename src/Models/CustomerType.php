<?php
namespace Models;

class CustomerType extends BaseModel {
    protected $table = 'Loaikhachhang';
    protected $primaryKey = 'Maloaikh';

    public function getAll($orderBy = null) {
        $sql = "SELECT Maloaikh, Tenloaikh, Motaloaikh as Mota FROM {$this->table}";
        if ($orderBy) {
            $sql .= " ORDER BY {$orderBy}";
        }
        return $this->pdo->query($sql)->fetchAll();
    }

    public function getById($id) {
        $stmt = $this->pdo->prepare("SELECT Maloaikh, Tenloaikh, Motaloaikh as Mota FROM {$this->table} WHERE {$this->primaryKey} = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function create($data) {
        // Handle case where user provides string like "LKH001", MySQL INT AUTO_INCREMENT will fail or truncate.
        // If it's auto increment, we should probably ignore Maloaikh, but if we must insert it (e.g. as an INT),
        // we'll try to insert it. If the DB expects string, it will be fine.
        $stmt = $this->pdo->prepare("INSERT INTO {$this->table} (Tenloaikh, Motaloaikh) VALUES (?, ?)");
        return $stmt->execute([
            $data['Tenloaikh'],
            $data['Mota'] ?? ''
        ]);
    }

    public function update($id, $data) {
        $stmt = $this->pdo->prepare("UPDATE {$this->table} SET Tenloaikh = ?, Motaloaikh = ? WHERE {$this->primaryKey} = ?");
        return $stmt->execute([
            $data['Tenloaikh'],
            $data['Mota'] ?? '',
            $id
        ]);
    }
}
