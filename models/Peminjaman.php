<?php
class Peminjaman {
    private $conn;
    private $table_name = "tb_peminjaman";

    public $id;
    public $user_id;
    public $alat_id;
    public $tanggal_pinjam;
    public $tanggal_kembali_rencana;
    public $tanggal_kembali_aktual;
    public $status;
    public $error;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function checkAvailability($alat_id, $tanggal_pinjam) {
        $query = "SELECT COUNT(*) as total_booked FROM " . $this->table_name . " WHERE alat_id = ? AND tanggal_pinjam = ? AND status IN ('Menunggu', 'Disetujui', 'Dipinjam')";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $alat_id);
        $stmt->bindParam(2, $tanggal_pinjam);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total_booked'];
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . " SET user_id=:user_id, alat_id=:alat_id, tanggal_pinjam=:tanggal_pinjam, tanggal_kembali_rencana=:tanggal_kembali_rencana, status=:status";
        
        $stmt = $this->conn->prepare($query);

        $this->user_id = htmlspecialchars(strip_tags($this->user_id));
        $this->alat_id = htmlspecialchars(strip_tags($this->alat_id));
        $this->tanggal_pinjam = htmlspecialchars(strip_tags($this->tanggal_pinjam));
        $this->tanggal_kembali_rencana = htmlspecialchars(strip_tags($this->tanggal_kembali_rencana));
        $this->status = htmlspecialchars(strip_tags($this->status));

        $stmt->bindParam(":user_id", $this->user_id);
        $stmt->bindParam(":alat_id", $this->alat_id);
        $stmt->bindParam(":tanggal_pinjam", $this->tanggal_pinjam);
        $stmt->bindParam(":tanggal_kembali_rencana", $this->tanggal_kembali_rencana);
        $stmt->bindParam(":status", $this->status);

        if($stmt->execute()) {
            return true;
        }

        $this->error = $stmt->errorInfo()[2];
        return false;
    }

    public function updateStatus() {
        $query = "UPDATE " . $this->table_name . " SET status = :status WHERE id = :id";
        $stmt = $this->conn->prepare($query);

        $this->status = htmlspecialchars(strip_tags($this->status));
        $this->id = htmlspecialchars(strip_tags($this->id));

        $stmt->bindParam(":status", $this->status);
        $stmt->bindParam(":id", $this->id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function readHistory($user_id = null) {
        $query = "SELECT p.id, p.tanggal_pinjam, p.tanggal_kembali_rencana, p.tanggal_kembali_aktual, p.status, 
                         u.nama as nama_mahasiswa, a.nama_alat 
                  FROM " . $this->table_name . " p
                  LEFT JOIN tb_users u ON p.user_id = u.id
                  LEFT JOIN tb_alat a ON p.alat_id = a.id";
        
        if($user_id != null) {
            $query .= " WHERE p.user_id = :user_id";
        }
        
        $query .= " ORDER BY p.tanggal_pinjam DESC";
        
        $stmt = $this->conn->prepare($query);
        
        if($user_id != null) {
            $user_id = htmlspecialchars(strip_tags($user_id));
            $stmt->bindParam(":user_id", $user_id);
        }
        
        $stmt->execute();
        return $stmt;
    }
}
?>