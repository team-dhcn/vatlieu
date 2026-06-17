<?php
namespace Models;

class Product extends BaseModel {
    protected $table = 'Sanpham';
    protected $primaryKey = 'Masp';

    public function getAllWithCategory() {
        $sql = "SELECT t.Masp, t.Tensp, dm.Tendm, t.Madm, t.Dvt, t.Giaban 
                FROM Sanpham t LEFT JOIN Danhmucsp dm ON t.Madm = dm.Madm ORDER BY t.Masp";
        return $this->pdo->query($sql)->fetchAll();
    }

    public function getByIdWithCategory($id) {
        $stmt = $this->pdo->prepare("SELECT t.*, dm.Tendm FROM Sanpham t LEFT JOIN Danhmucsp dm ON t.Madm = dm.Madm WHERE t.Masp = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function create($data) {
        $stmt = $this->pdo->prepare("INSERT INTO Sanpham (Masp, Tensp, Madm, Dvt, Giaban) VALUES (?, ?, ?, ?, ?)");
        return $stmt->execute([$data['Masp'], $data['Tensp'], $data['Madm'] ?? null, $data['Dvt'] ?? '', $data['Giaban'] ?? 0]);
    }

    public function update($id, $data) {
        $stmt = $this->pdo->prepare("UPDATE Sanpham SET Tensp=?, Madm=?, Dvt=?, Giaban=? WHERE Masp=?");
        return $stmt->execute([$data['Tensp'], $data['Madm'] ?? null, $data['Dvt'] ?? '', $data['Giaban'] ?? 0, $id]);
    }
}
