<?php
namespace Models;

use Core\Database;

abstract class BaseModel {
    protected $pdo;
    protected $table;
    protected $primaryKey = 'id';

    public function __construct() {
        $this->pdo = Database::getInstance();
    }

    public function getAll($orderBy = null) {
        $sql = "SELECT * FROM {$this->table}";
        if ($orderBy) {
            $sql .= " ORDER BY {$orderBy}";
        }
        return $this->pdo->query($sql)->fetchAll();
    }

    public function getById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE {$this->primaryKey} = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function delete($id) {
        $stmt = $this->pdo->prepare("DELETE FROM {$this->table} WHERE {$this->primaryKey} = ?");
        return $stmt->execute([$id]);
    }

    public function getLastInsertId() {
        return $this->pdo->lastInsertId();
    }
}
