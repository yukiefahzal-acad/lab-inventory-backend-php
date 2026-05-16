<?php
class Denda {
    private $conn;
    private $table_name = "tb_denda";

    public $id;
    public $peminjaman_id;
    public $jenis_denda;
    public $jumlah_denda;
    public $status_bayar;
    public $keterangan;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . " SET peminjaman_id=:peminjaman_id, jenis_denda=:jenis_denda, jumlah_denda=:jumlah_denda, status_bayar=:status_bayar, keterangan=:keterangan";
        $stmt = $this->conn->prepare($query);

        $this->peminjaman_id = htmlspecialchars(strip_tags($this->peminjaman_id));
        $this->jenis_denda = htmlspecialchars(strip_tags($this->jenis_denda));
        $this->jumlah_denda = htmlspecialchars(strip_tags($this->jumlah_denda));
        $this->status_bayar = htmlspecialchars(strip_tags($this->status_bayar));
        $this->keterangan = htmlspecialchars(strip_tags($this->keterangan));

        $stmt->bindParam(":peminjaman_id", $this->peminjaman_id);
        $stmt->bindParam(":jenis_denda", $this->jenis_denda);
        $stmt->bindParam(":jumlah_denda", $this->jumlah_denda);
        $stmt->bindParam(":status_bayar", $this->status_bayar);
        $stmt->bindParam(":keterangan", $this->keterangan);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function cekDendaBelumLunas($user_id){
        $query = "SELECT d.id FROM " . $this->table_name . " d JOIN tb_peminjaman p ON d.peminjaman_id = p.id WHERE p.user_id = ? AND d.status_bayar = 'Belum Lunas' LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $user_id);
        $stmt->execute();

        if($stmt->rowCount() > 0) {
            return true;
        }
        return false;
    }

    public function lunasiDenda() {
        $query = "UPDATE " . $this->table_name . " SET status_bayar = 'Lunas' WHERE id = :id AND status_bayar = 'Belum Lunas'";        $stmt = $this->conn->prepare($query);
        
        $this->id = htmlspecialchars(strip_tags($this->id));
        $stmt->bindParam(":id", $this->id);

        if($stmt->execute()) {
            if($stmt->rowCount() > 0) {
                return true;
            }
        }
        
        return false;
    }
}
?>