<?php

require_once __DIR__ . "/../../config/db.php";

class Users {
    public $id;
    public $name;
    public $email;
    public $password;
    public $role;
    private $conn;

    function __construct() {
        $database = new Database();
        $this->conn = $database->connect();
    }

    function createUser() {
        $stmt = $this->conn->prepare("INSERT INTO users(name,email,password, role) VALUES(:name, :email, :password, :role)");
        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":password", $this->password);
        $stmt->bindParam(":role", $this->role);
        return $stmt->execute();
    }

    function getAllUsers() {
        $stmt = $this->conn->prepare("SELECT * FROM users");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    function getAdmins() {
        $stmt = $this->conn->prepare("SELECT id, name FROM users WHERE role = 'admin'");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    function login($email) {
        $stmt = $this->conn->prepare("SELECT * FROM users WHERE email= :email LIMIT 1");
        $stmt->bindParam(":email", $email);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
