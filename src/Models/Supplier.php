<?php
namespace Models;

class Supplier extends BaseModel {
    protected $table = 'Nhacungcap';
    protected $primaryKey = 'Mancc';

    public function create($data) {
        $stmt = $this->pdo->prepare("INSERT INTO Nhacungcap (Mancc, Tenncc, Sdtncc, Diachincc) VALUES (?, ?, ?, ?)");
        return $stmt->execute([$data['Mancc'], $data['Tenncc'], $data['Sdtncc'] ?? '', $data['Diachincc'] ?? '']);
    }

    public function update($id, $data) {
        $stmt = $this->pdo->prepare("UPDATE Nhacungcap SET Tenncc=?, Sdtncc=?, Diachincc=? WHERE Mancc=?");
        return $stmt->execute([$data['Tenncc'], $data['Sdtncc'] ?? '', $data['Diachincc'] ?? '', $id]);
    }
}
