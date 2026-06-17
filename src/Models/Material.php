<?php
namespace Models;

class Material extends BaseModel {
    protected $table = 'Nguyenvatlieu';
    protected $primaryKey = 'Manvl';

    public function create($data) {
        $stmt = $this->pdo->prepare("INSERT INTO Nguyenvatlieu (Manvl, Tennvl, Dvt, Giavon) VALUES (?, ?, ?, ?)");
        return $stmt->execute([$data['Manvl'], $data['Tennvl'], $data['Dvt'] ?? '', $data['Giavon'] ?? 0]);
    }

    public function update($id, $data) {
        $stmt = $this->pdo->prepare("UPDATE Nguyenvatlieu SET Tennvl=?, Dvt=?, Giavon=? WHERE Manvl=?");
        return $stmt->execute([$data['Tennvl'], $data['Dvt'] ?? '', $data['Giavon'] ?? 0, $id]);
    }
}
