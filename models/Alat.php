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
    public $qr_code;
    public $kategori;
    public $denda_per_hari;
    public $denda_rusak;
    public $denda_hilang;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function readAll($search = "", $kategori = "") {
        $query = "SELECT * FROM " . $this->table_name;
        $conditions = array();

        if (!empty($search)) {
            $conditions[] = "(kode_alat LIKE :search OR nama_alat LIKE :search OR spesifikasi LIKE :search)";
        }

        if (!empty($kategori)) {
            $kat_arr = explode('|', $kategori);
            $kat_conditions = array();
            foreach ($kat_arr as $k) {
                $kat_conditions[] = "kategori LIKE '%" . htmlspecialchars(strip_tags($k)) . "%'";
            }
            $conditions[] = "(" . implode(' OR ', $kat_conditions) . ")";
        }

        if (count($conditions) > 0) {
            $query .= " WHERE " . implode(' AND ', $conditions);
        }

        $query .= " ORDER BY nama_alat ASC";

        $stmt = $this->conn->prepare($query);

        if (!empty($search)) {
            $search_param = "%" . htmlspecialchars(strip_tags($search)) . "%";
            $stmt->bindParam(":search", $search_param);
        }

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
            $this->qr_code = $row['qr_code'];
            $this->kategori = isset($row['kategori']) ? $row['kategori'] : "";
            $this->denda_per_hari = isset($row['denda_per_hari']) ? $row['denda_per_hari'] : 0;
            $this->denda_rusak = isset($row['denda_rusak']) ? $row['denda_rusak'] : 0;
            $this->denda_hilang = isset($row['denda_hilang']) ? $row['denda_hilang'] : 0;
            return true;
        }
        return false;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . " SET kode_alat=:kode_alat, nama_alat=:nama_alat, spesifikasi=:spesifikasi, foto=:foto, stok_total=:stok_total, stok_tersedia=:stok_tersedia, qr_code=:qr_code, kategori=:kategori, denda_per_hari=:denda_per_hari, denda_rusak=:denda_rusak, denda_hilang=:denda_hilang";
        
        $stmt = $this->conn->prepare($query);

        $this->kode_alat = htmlspecialchars(strip_tags($this->kode_alat));
        $this->nama_alat = htmlspecialchars(strip_tags($this->nama_alat));
        $this->spesifikasi = htmlspecialchars(strip_tags($this->spesifikasi));
        $this->foto = htmlspecialchars(strip_tags($this->foto));
        $this->stok_total = htmlspecialchars(strip_tags($this->stok_total));
        $this->stok_tersedia = htmlspecialchars(strip_tags($this->stok_tersedia));
        $this->qr_code = htmlspecialchars(strip_tags($this->qr_code));
        $this->kategori = htmlspecialchars(strip_tags($this->kategori));
        $this->denda_per_hari = htmlspecialchars(strip_tags($this->denda_per_hari));
        $this->denda_rusak = htmlspecialchars(strip_tags($this->denda_rusak));
        $this->denda_hilang = htmlspecialchars(strip_tags($this->denda_hilang));

        $stmt->bindParam(":kode_alat", $this->kode_alat);
        $stmt->bindParam(":nama_alat", $this->nama_alat);
        $stmt->bindParam(":spesifikasi", $this->spesifikasi);
        $stmt->bindParam(":foto", $this->foto);
        $stmt->bindParam(":stok_total", $this->stok_total);
        $stmt->bindParam(":stok_tersedia", $this->stok_tersedia);
        $stmt->bindParam(":qr_code", $this->qr_code);
        $stmt->bindParam(":kategori", $this->kategori);
        $stmt->bindParam(":denda_per_hari", $this->denda_per_hari);
        $stmt->bindParam(":denda_rusak", $this->denda_rusak);
        $stmt->bindParam(":denda_hilang", $this->denda_hilang);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function update() {
        $query = "UPDATE " . $this->table_name . " SET kode_alat=:kode_alat, nama_alat=:nama_alat, spesifikasi=:spesifikasi, foto=:foto, stok_total=:stok_total, stok_tersedia=:stok_tersedia, kategori=:kategori, denda_per_hari=:denda_per_hari, denda_rusak=:denda_rusak, denda_hilang=:denda_hilang WHERE id=:id";
        
        $stmt = $this->conn->prepare($query);

        $this->kode_alat = htmlspecialchars(strip_tags($this->kode_alat));
        $this->nama_alat = htmlspecialchars(strip_tags($this->nama_alat));
        $this->spesifikasi = htmlspecialchars(strip_tags($this->spesifikasi));
        $this->foto = htmlspecialchars(strip_tags($this->foto));
        $this->stok_total = htmlspecialchars(strip_tags($this->stok_total));
        $this->stok_tersedia = htmlspecialchars(strip_tags($this->stok_tersedia));
        $this->kategori = htmlspecialchars(strip_tags($this->kategori));
        $this->denda_per_hari = htmlspecialchars(strip_tags($this->denda_per_hari));
        $this->denda_rusak = htmlspecialchars(strip_tags($this->denda_rusak));
        $this->denda_hilang = htmlspecialchars(strip_tags($this->denda_hilang));
        $this->id = htmlspecialchars(strip_tags($this->id));

        $stmt->bindParam(":kode_alat", $this->kode_alat);
        $stmt->bindParam(":nama_alat", $this->nama_alat);
        $stmt->bindParam(":spesifikasi", $this->spesifikasi);
        $stmt->bindParam(":foto", $this->foto);
        $stmt->bindParam(":stok_total", $this->stok_total);
        $stmt->bindParam(":stok_tersedia", $this->stok_tersedia);
        $stmt->bindParam(":kategori", $this->kategori);
        $stmt->bindParam(":denda_per_hari", $this->denda_per_hari);
        $stmt->bindParam(":denda_rusak", $this->denda_rusak);
        $stmt->bindParam(":denda_hilang", $this->denda_hilang);
        $stmt->bindParam(":id", $this->id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function updateStok() {
        $query = "UPDATE " . $this->table_name . " SET stok_tersedia = :stok_tersedia WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        
        $this->stok_tersedia = htmlspecialchars(strip_tags($this->stok_tersedia));
        $this->id = htmlspecialchars(strip_tags($this->id));
        
        $stmt->bindParam(":stok_tersedia", $this->stok_tersedia);
        $stmt->bindParam(":id", $this->id);
        
        return $stmt->execute();
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
            return true;
        }
        return false;
    }
}
?>