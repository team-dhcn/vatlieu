<?php
namespace Models;

class Category extends BaseModel {
    protected $table = 'Danhmucsp';
    protected $primaryKey = 'Madm';

    public function create($data) {
        $stmt = $this->pdo->prepare("INSERT INTO Danhmucsp (Madm, Tendm, Mota) VALUES (?, ?, ?)");
        return $stmt->execute([$data['Madm'] ?? null, $data['Tendm'], $data['Mota'] ?? '']);
    }

    public function update($id, $data) {
        $stmt = $this->pdo->prepare("UPDATE Danhmucsp SET Tendm=?, Mota=? WHERE Madm=?");
        return $stmt->execute([$data['Tendm'], $data['Mota'] ?? '', $id]);
    }
}
