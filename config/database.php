<?php
class Database {
    private $host = "sql211.infinityfree.com";
    private $db_name = "if0_42222732_lab_inventory";
    private $username = "if0_42222732";
    private $password = "gnToXLyWVR4a";
	private $port = 3306;
    
    public $conn;

    // Fungsi untuk mendapatkan koneksi database
    public function getConnection() {
        $this->conn = null;

        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";port=" . $this->port . ";dbname=" . $this->db_name, $this->username, $this->password);
            
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            $this->conn->exec("set names utf8");
            
        } catch(PDOException $exception) {
            echo "Koneksi database gagal: " . $exception->getMessage();
        }

        return $this->conn;
    }
}
?>