<?php
/**
 * Database Configuration
 * Elegant Shoes Admin Panel
 */

class Database {
    private $host = 'localhost';
    private $db_name = 'elegant_shoes_db';
    private $username = 'root';
    private $password = '';
    private $conn;

    public function getConnection() {
        $this->conn = null;

        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8",
                $this->username,
                $this->password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch(PDOException $exception) {
            echo "Connection error: " . $exception->getMessage();
            die();
        }

        return $this->conn;
    }
}

// Create global database connection
$database = new Database();
$db = $database->getConnection();

// Test connection
if (!$db) {
    die("Database connection failed!");
}
?>





