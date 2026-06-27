<?php

class Product {
    private $conn;
    private $table_name = "products";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getAll($limit = null) {
        $query = "SELECT * FROM {$this->table_name} ORDER BY id DESC";
        if ($limit) {
            $query .= " LIMIT " . (int)$limit;
        }
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function search($keyword = '') {
        if ($keyword === '') {
            return $this->getAll();
        }
        $query = "SELECT * FROM {$this->table_name}
                  WHERE name LIKE :q OR description LIKE :q
                  ORDER BY id DESC";
        $stmt = $this->conn->prepare($query);
        $like = '%' . $keyword . '%';
        $stmt->bindParam(':q', $like);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getTopSelling($limit = 10) {
        $query = "SELECT * FROM {$this->table_name} ORDER BY sales_total DESC LIMIT " . (int)$limit;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getNewest($limit = 10) {
        return $this->getAll($limit);
    }

    public function getById($id) {
        $query = "SELECT * FROM {$this->table_name} WHERE id = :id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch();
    }

    public function getForCarousel($limit = 6) {
        return $this->getAll($limit);
    }
}
