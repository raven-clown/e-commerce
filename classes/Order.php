<?php

class Order {
    private $conn;
    private $table_orders = "orders";
    private $table_items = "order_items";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function placeOrder($user_id, Cart $cart) {
        $cart_items = $cart->getByUser($user_id);
        if (empty($cart_items)) {
            return ['success' => false, 'message' => 'empty_cart'];
        }

        $total_amount = 0;
        foreach ($cart_items as $item) {
            $total_amount += $item['price'] * $item['quantity'];
        }

        return $this->checkout($user_id, $cart_items, $total_amount);
    }

    public function checkout($user_id, $cart_items, $total_amount) {
        try {
            $this->conn->beginTransaction();

            foreach ($cart_items as $item) {
                $check_stmt = $this->conn->prepare("SELECT stock, name FROM products WHERE id = :pid FOR UPDATE");
                $check_stmt->bindParam(':pid', $item['product_id']);
                $check_stmt->execute();
                $product = $check_stmt->fetch();

                if (!$product || $product['stock'] < $item['quantity']) {
                    throw new Exception("Insufficient stock for {$item['name']}");
                }
            }

            $query_order = "INSERT INTO {$this->table_orders} (user_id, total_amount, status, created_at)
                            VALUES (:uid, :amount, '0', NOW())";
            $stmt_order = $this->conn->prepare($query_order);
            $stmt_order->bindParam(':uid', $user_id);
            $stmt_order->bindParam(':amount', $total_amount);
            $stmt_order->execute();

            $order_id = $this->conn->lastInsertId();

            $query_item = "INSERT INTO {$this->table_items} (order_id, product_id, price, quantity)
                           VALUES (:oid, :pid, :price, :qty)";
            $stmt_item = $this->conn->prepare($query_item);

            $query_stock = "UPDATE products SET stock = stock - :qty, sales_total = sales_total + :qty WHERE id = :pid";
            $stmt_stock = $this->conn->prepare($query_stock);

            foreach ($cart_items as $item) {
                $stmt_item->bindParam(':oid', $order_id);
                $stmt_item->bindParam(':pid', $item['product_id']);
                $stmt_item->bindParam(':price', $item['price']);
                $stmt_item->bindParam(':qty', $item['quantity']);
                $stmt_item->execute();

                $stmt_stock->bindParam(':qty', $item['quantity']);
                $stmt_stock->bindParam(':pid', $item['product_id']);
                $stmt_stock->execute();
            }

            $query_clear = "DELETE FROM cart WHERE user_id = :uid";
            $stmt_clear = $this->conn->prepare($query_clear);
            $stmt_clear->bindParam(':uid', $user_id);
            $stmt_clear->execute();

            $this->conn->commit();
            return ['success' => true, 'order_id' => $order_id];
        } catch (Exception $e) {
            $this->conn->rollBack();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function getUserOrders($user_id) {
        $query = "SELECT * FROM {$this->table_orders} WHERE user_id = :uid ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':uid', $user_id);
        $stmt->execute();
        $orders = $stmt->fetchAll();

        foreach ($orders as &$order) {
            $query_items = "SELECT i.*, p.name, p.image
                            FROM {$this->table_items} i
                            JOIN products p ON i.product_id = p.id
                            WHERE i.order_id = :oid";
            $stmt_items = $this->conn->prepare($query_items);
            $stmt_items->bindParam(':oid', $order['id']);
            $stmt_items->execute();
            $order['items'] = $stmt_items->fetchAll();
        }

        return $orders;
    }

    public function getOrderDetails($order_id, $user_id) {
        $query = "SELECT * FROM {$this->table_orders} WHERE id = :oid AND user_id = :uid LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':oid', $order_id);
        $stmt->bindParam(':uid', $user_id);
        $stmt->execute();
        return $stmt->fetch();
    }

    public function confirmPayment($order_id, $user_id, $bank_name, $transfer_date, $transfer_time, $file) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/jpg'];
        $max_size = 5 * 1024 * 1024;

        if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
            return "Payment slip file is required";
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mime, $allowed_types)) {
            return "Only JPG and PNG images are allowed";
        }

        if ($file['size'] > $max_size) {
            return "File size must not exceed 5MB";
        }

        $upload_dir = __DIR__ . "/../assets/image/slip/";
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $new_filename = "slip_" . $order_id . "_" . time() . "." . $ext;
        $target_file = $upload_dir . $new_filename;

        if (move_uploaded_file($file['tmp_name'], $target_file)) {
            $query = "UPDATE {$this->table_orders}
                      SET slip_image = :slip, bank_name = :bank, transfer_date = :tdate, transfer_time = :ttime, status = '1'
                      WHERE id = :oid AND user_id = :uid";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':slip', $new_filename);
            $stmt->bindParam(':bank', $bank_name);
            $stmt->bindParam(':tdate', $transfer_date);
            $stmt->bindParam(':ttime', $transfer_time);
            $stmt->bindParam(':oid', $order_id);
            $stmt->bindParam(':uid', $user_id);

            return $stmt->execute() ? true : "Failed to save payment data";
        }

        return "File upload failed";
    }
}
