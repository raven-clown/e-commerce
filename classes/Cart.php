<?php

class Cart {
    private $conn;
    private $table_name = "cart";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function addItem($user_id, $product_id, $quantity) {
        $query_stock = "SELECT stock FROM products WHERE id = :pid LIMIT 1";
        $stmt_stock = $this->conn->prepare($query_stock);
        $stmt_stock->bindParam(':pid', $product_id);
        $stmt_stock->execute();
        $product = $stmt_stock->fetch();

        if (!$product) {
            return "product_not_found";
        }

        $available_stock = (int)$product['stock'];

        $query_check = "SELECT id, quantity FROM {$this->table_name} WHERE user_id = :uid AND product_id = :pid LIMIT 1";
        $stmt_check = $this->conn->prepare($query_check);
        $stmt_check->bindParam(':uid', $user_id);
        $stmt_check->bindParam(':pid', $product_id);
        $stmt_check->execute();

        if ($stmt_check->rowCount() > 0) {
            $row = $stmt_check->fetch();
            $new_quantity = (int)$row['quantity'] + (int)$quantity;

            if ($new_quantity > $available_stock) {
                return "stock_error";
            }

            $query_update = "UPDATE {$this->table_name} SET quantity = :qty WHERE id = :cart_id";
            $stmt_update = $this->conn->prepare($query_update);
            $stmt_update->bindParam(':qty', $new_quantity);
            $stmt_update->bindParam(':cart_id', $row['id']);
            return $stmt_update->execute() ? "success" : "error";
        }

        if ((int)$quantity > $available_stock) {
            return "stock_error";
        }

        $query_insert = "INSERT INTO {$this->table_name} (user_id, product_id, quantity) VALUES (:uid, :pid, :qty)";
        $stmt_insert = $this->conn->prepare($query_insert);
        $stmt_insert->bindParam(':uid', $user_id);
        $stmt_insert->bindParam(':pid', $product_id);
        $stmt_insert->bindParam(':qty', $quantity);
        return $stmt_insert->execute() ? "success" : "error";
    }

    public function getByUser($user_id) {
        $query = "SELECT c.id as cart_id, c.quantity, p.id as product_id, p.name, p.price, p.image, p.stock
                  FROM {$this->table_name} c
                  JOIN products p ON c.product_id = p.id
                  WHERE c.user_id = :uid";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':uid', $user_id);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function updateQty($user_id, $cart_id, $new_quantity) {
        $query_stock = "SELECT p.stock FROM {$this->table_name} c
                        JOIN products p ON c.product_id = p.id
                        WHERE c.id = :cid AND c.user_id = :uid LIMIT 1";
        $stmt_stock = $this->conn->prepare($query_stock);
        $stmt_stock->bindParam(':cid', $cart_id);
        $stmt_stock->bindParam(':uid', $user_id);
        $stmt_stock->execute();

        $row = $stmt_stock->fetch();
        if ($row) {
            $stock = (int)$row['stock'];
            if ($new_quantity > $stock) {
                return false;
            }

            $query = "UPDATE {$this->table_name} SET quantity = :qty WHERE id = :cid AND user_id = :uid";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':qty', $new_quantity);
            $stmt->bindParam(':cid', $cart_id);
            $stmt->bindParam(':uid', $user_id);
            return $stmt->execute();
        }
        return false;
    }

    public function removeItem($user_id, $cart_id) {
        $query = "DELETE FROM {$this->table_name} WHERE id = :cid AND user_id = :uid";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':cid', $cart_id);
        $stmt->bindParam(':uid', $user_id);
        return $stmt->execute();
    }

    public function getCount($user_id) {
        $query = "SELECT COUNT(*) as total FROM {$this->table_name} WHERE user_id = :uid";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':uid', $user_id);
        $stmt->execute();
        $row = $stmt->fetch();
        return $row ? (int)$row['total'] : 0;
    }

    public function clearCart($user_id) {
        $query = "DELETE FROM {$this->table_name} WHERE user_id = :uid";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':uid', $user_id);
        return $stmt->execute();
    }
}
