<?php
namespace Models;

class Formula extends BaseModel {
    protected $table = 'Congthucsanpham';
    protected $primaryKey = 'Masp'; // Masp is used as id in many cases, but it's a composite key (Masp, Manvl)

    public function getAllDetailed() {
        $sql = "SELECT c.Masp, s.Tensp, c.Manvl, n.Tennvl, c.Soluong, n.Dvt 
                FROM Congthucsanpham c
                LEFT JOIN Sanpham s ON c.Masp = s.Masp
                LEFT JOIN Nguyenvatlieu n ON c.Manvl = n.Manvl ORDER BY c.Masp";
        return $this->pdo->query($sql)->fetchAll();
    }

    public function getByProduct($masp) {
        $stmt = $this->pdo->prepare("SELECT c.*, n.Tennvl, n.Dvt FROM Congthucsanpham c
                LEFT JOIN Nguyenvatlieu n ON c.Manvl = n.Manvl WHERE c.Masp = ? ORDER BY c.Manvl");
        $stmt->execute([$masp]);
        return $stmt->fetchAll();
    }

    public function createOrUpdate($data) {
        $chk = $this->pdo->prepare("SELECT 1 FROM Congthucsanpham WHERE Masp=? AND Manvl=?");
        $chk->execute([$data['Masp'], $data['Manvl']]);
        if ($chk->fetchColumn()) {
            $stmt = $this->pdo->prepare("UPDATE Congthucsanpham SET Soluong=? WHERE Masp=? AND Manvl=?");
            return $stmt->execute([$data['Soluong'], $data['Masp'], $data['Manvl']]);
        } else {
            $stmt = $this->pdo->prepare("INSERT INTO Congthucsanpham (Masp, Manvl, Soluong) VALUES (?, ?, ?)");
            return $stmt->execute([$data['Masp'], $data['Manvl'], $data['Soluong']]);
        }
    }

    public function deleteDetailed($id) {
        $p = explode('_', $id, 2);
        if (count($p) === 2) {
            $stmt = $this->pdo->prepare("DELETE FROM Congthucsanpham WHERE Masp=? AND Manvl=?");
            return $stmt->execute([$p[0], $p[1]]);
        } else {
            $stmt = $this->pdo->prepare("DELETE FROM Congthucsanpham WHERE Masp=?");
            return $stmt->execute([$id]);
        }
    }
}
