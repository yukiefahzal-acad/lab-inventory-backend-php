<?php
class Alat {
    private $conn;
    private $table_name = "tb_alat";

    public $id;
    public $kode_alat;
    public $nama_alat;
    public $spesifikasi;
    public $foto;
    public $stok_total;
    public $stok_tersedia;
    public $status;
    public $qr_code;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function readAll() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY nama_alat ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function readOne() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if($row) {
            $this->kode_alat = $row['kode_alat'];
            $this->nama_alat = $row['nama_alat'];
            $this->spesifikasi = $row['spesifikasi'];
            $this->foto = $row['foto'];
            $this->stok_total = $row['stok_total'];
            $this->stok_tersedia = $row['stok_tersedia'];
            $this->status = $row['status'];
            $this->qr_code = $row['qr_code'];
            return true;
        }
        return false;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . " SET kode_alat=:kode_alat, nama_alat=:nama_alat, spesifikasi=:spesifikasi, foto=:foto, stok_total=:stok_total, stok_tersedia=:stok_tersedia, status=:status, qr_code=:qr_code";
        
        $stmt = $this->conn->prepare($query);

        $this->kode_alat = htmlspecialchars(strip_tags($this->kode_alat));
        $this->nama_alat = htmlspecialchars(strip_tags($this->nama_alat));
        $this->spesifikasi = htmlspecialchars(strip_tags($this->spesifikasi));
        $this->foto = htmlspecialchars(strip_tags($this->foto));
        $this->stok_total = htmlspecialchars(strip_tags($this->stok_total));
        $this->stok_tersedia = htmlspecialchars(strip_tags($this->stok_tersedia));
        $this->status = htmlspecialchars(strip_tags($this->status));
        $this->qr_code = htmlspecialchars(strip_tags($this->qr_code));

        $stmt->bindParam(":kode_alat", $this->kode_alat);
        $stmt->bindParam(":nama_alat", $this->nama_alat);
        $stmt->bindParam(":spesifikasi", $this->spesifikasi);
        $stmt->bindParam(":foto", $this->foto);
        $stmt->bindParam(":stok_total", $this->stok_total);
        $stmt->bindParam(":stok_tersedia", $this->stok_tersedia);
        $stmt->bindParam(":status", $this->status);
        $stmt->bindParam(":qr_code", $this->qr_code);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function update() {
        $query = "UPDATE " . $this->table_name . " SET nama_alat=:nama_alat, spesifikasi=:spesifikasi, stok_total=:stok_total, stok_tersedia=:stok_tersedia, status=:status WHERE id=:id";
        
        $stmt = $this->conn->prepare($query);

        $this->nama_alat = htmlspecialchars(strip_tags($this->nama_alat));
        $this->spesifikasi = htmlspecialchars(strip_tags($this->spesifikasi));
        $this->stok_total = htmlspecialchars(strip_tags($this->stok_total));
        $this->stok_tersedia = htmlspecialchars(strip_tags($this->stok_tersedia));
        $this->status = htmlspecialchars(strip_tags($this->status));
        $this->id = htmlspecialchars(strip_tags($this->id));

        $stmt->bindParam(":nama_alat", $this->nama_alat);
        $stmt->bindParam(":spesifikasi", $this->spesifikasi);
        $stmt->bindParam(":stok_total", $this->stok_total);
        $stmt->bindParam(":stok_tersedia", $this->stok_tersedia);
        $stmt->bindParam(":status", $this->status);
        $stmt->bindParam(":id", $this->id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $this->id = htmlspecialchars(strip_tags($this->id));
        $stmt->bindParam(1, $this->id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function readByQRCode() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE qr_code = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        
        $this->qr_code = htmlspecialchars(strip_tags($this->qr_code));
        $stmt->bindParam(1, $this->qr_code);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if($row) {
            $this->id = $row['id'];
            $this->kode_alat = $row['kode_alat'];
            $this->nama_alat = $row['nama_alat'];
            $this->spesifikasi = $row['spesifikasi'];
            $this->foto = $row['foto'];
            $this->stok_total = $row['stok_total'];
            $this->stok_tersedia = $row['stok_tersedia'];
            $this->status = $row['status'];
            return true;
        }
        return false;
    }
}
?>