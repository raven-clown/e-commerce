<?php

class User {
    private $conn;
    private $table_name = "users";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function login($username, $password) {
        $query = "SELECT id, username, password, role FROM {$this->table_name} WHERE username = :username LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch();
            if (password_verify($password, $row['password'])) {
                return $row;
            }
        }
        return false;
    }

    public function isUsernameExists($username) {
        $query = "SELECT id FROM {$this->table_name} WHERE username = :username LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    public function register($first_name, $last_name, $username, $password, $email, $phone, $address) {
        if ($this->isUsernameExists($username)) {
            return "username_exists";
        }

        $query = "INSERT INTO {$this->table_name}
                  (first_name, last_name, username, password, email, phone, address, role)
                  VALUES (:fname, :lname, :uname, :pass, :email, :phone, :address, 'member')";

        $stmt = $this->conn->prepare($query);
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $stmt->bindParam(':fname', $first_name);
        $stmt->bindParam(':lname', $last_name);
        $stmt->bindParam(':uname', $username);
        $stmt->bindParam(':pass', $hashed_password);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':phone', $phone);
        $stmt->bindParam(':address', $address);

        return $stmt->execute() ? true : false;
    }

    public function getById($id) {
        $query = "SELECT * FROM {$this->table_name} WHERE id = :id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch();
    }

    public function updateProfile($id, $first_name, $last_name, $email, $phone, $address, $password = null) {
        if ($password) {
            $query = "UPDATE {$this->table_name}
                      SET first_name=:fname, last_name=:lname, email=:email, phone=:phone, address=:address, password=:pass
                      WHERE id=:id";
            $stmt = $this->conn->prepare($query);
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt->bindParam(':pass', $hashed);
        } else {
            $query = "UPDATE {$this->table_name}
                      SET first_name=:fname, last_name=:lname, email=:email, phone=:phone, address=:address
                      WHERE id=:id";
            $stmt = $this->conn->prepare($query);
        }

        $stmt->bindParam(':fname', $first_name);
        $stmt->bindParam(':lname', $last_name);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':phone', $phone);
        $stmt->bindParam(':address', $address);
        $stmt->bindParam(':id', $id);

        return $stmt->execute();
    }
}
