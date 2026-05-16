<?php
class User {
    private $conn;
    private $table_name = "tb_users";

    public $id;
    public $nim_nip;
    public $nama;
    public $role;
    public $password;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function login() {
        $query = "SELECT id, nama, role, password FROM " . $this->table_name . " WHERE nim_nip = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->nim_nip);
        $stmt->execute();

        if($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if(password_verify($this->password, $row['password'])) {
                $this->id = $row['id'];
                $this->nama = $row['nama'];
                $this->role = $row['role'];
                return true;
            }
        }
        return false;
    }

    public function register() {
        $query = "INSERT INTO " . $this->table_name . " SET nim_nip=:nim_nip, nama=:nama, role=:role, password=:password";
        $stmt = $this->conn->prepare($query);

        $this->nim_nip = htmlspecialchars(strip_tags($this->nim_nip));
        $this->nama = htmlspecialchars(strip_tags($this->nama));
        $this->role = htmlspecialchars(strip_tags($this->role));
        
        // Enkripsi password sebelum disimpan ke database
        $this->password = password_hash($this->password, PASSWORD_BCRYPT);

        $stmt->bindParam(":nim_nip", $this->nim_nip);
        $stmt->bindParam(":nama", $this->nama);
        $stmt->bindParam(":role", $this->role);
        $stmt->bindParam(":password", $this->password);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function checkExists() {
        $query = "SELECT id FROM " . $this->table_name . " WHERE id = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        
        $this->id = htmlspecialchars(strip_tags($this->id));
        $stmt->bindParam(1, $this->id);
        $stmt->execute();

        if($stmt->rowCount() > 0) {
            return true;
        }
        return false;
    }
}
?>