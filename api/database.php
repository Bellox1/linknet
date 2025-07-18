<?php
class Database {
    private $host = "localhost";
    private $db_name = "if0_39310327_linknet";
    private $username = "if0_39310327";
    private $password = "your_password_here";
    public $conn;

    public function getConnection() {
        $this->conn = null;

        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->conn->exec("set names utf8");
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $exception) {
            echo "Connection error: " . $exception->getMessage();
        }

        return $this->conn;
    }
}

// Créer une instance de la base de données
$database = new Database();
$conn = $database->getConnection();
?> 