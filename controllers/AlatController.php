<?php
require_once 'config/database.php';
require_once 'models/Alat.php';

class AlatController {
    private $db;
    private $alat;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->alat = new Alat($this->db);
    }

    public function index() {
        $search = isset($_GET['search']) ? $_GET['search'] : "";
        $kategori = isset($_GET['kategori']) ? $_GET['kategori'] : "";

        $stmt = $this->alat->readAll($search, $kategori);
        $num = $stmt->rowCount();

        $alat_arr = array();
        $alat_arr["data"] = array();

        if($num > 0) {
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                array_push($alat_arr["data"], $row);
            }
            http_response_code(200);
            echo json_encode($alat_arr);
        } else {
            http_response_code(404);
            echo json_encode(array("message" => "Tidak ada alat ditemukan."));
        }
    }

    public function create() {
        $data = json_decode(file_get_contents("php://input"));

        if(!empty($data->kode_alat) && !empty($data->nama_alat) && !empty($data->stok_total)) {
            
            $this->alat->kode_alat = $data->kode_alat;
            $this->alat->nama_alat = $data->nama_alat;
            $this->alat->spesifikasi = isset($data->spesifikasi) ? $data->spesifikasi : "";
            $this->alat->stok_total = $data->stok_total;
            $this->alat->stok_tersedia = $data->stok_total;
            $this->alat->status = isset($data->status) ? $data->status : "Baik";
            
            $clean_kode_alat = preg_replace('/[^A-Za-z0-9\-]/', '', $data->kode_alat);
            $this->alat->qr_code = "QR_" . $clean_kode_alat . "_" . uniqid();

            $this->alat->foto = isset($data->foto) ? $data->foto : "";
            $this->alat->kategori = isset($data->kategori) ? $data->kategori : "";
            $this->alat->denda_per_hari = isset($data->denda_per_hari) ? $data->denda_per_hari : 0;
            $this->alat->denda_rusak = isset($data->denda_rusak) ? $data->denda_rusak : 0;
            $this->alat->denda_hilang = isset($data->denda_hilang) ? $data->denda_hilang : 0;

            if($this->alat->create()) {
                http_response_code(201);
                echo json_encode(array(
                    "message" => "Alat berhasil ditambahkan.",
                    "qr_code_generated" => $this->alat->qr_code,
                ));
            } else {
                http_response_code(503);
                echo json_encode(array("message" => "Gagal menambahkan alat ke database."));
            }
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "Data tidak lengkap. Kode alat, nama alat, dan stok wajib diisi."));
        }
    }

    public function update() {
        $data = json_decode(file_get_contents("php://input"));

        if(!empty($data->id) && !empty($data->nama_alat)) {
            $this->alat->id = $data->id;
            $this->alat->kode_alat = isset($data->kode_alat) ? $data->kode_alat : "";
            $this->alat->nama_alat = $data->nama_alat;
            $this->alat->spesifikasi = isset($data->spesifikasi) ? $data->spesifikasi : "";
            $this->alat->foto = isset($data->foto) ? $data->foto : "";
            $this->alat->stok_total = isset($data->stok_total) ? $data->stok_total : 0;
            $this->alat->stok_tersedia = isset($data->stok_tersedia) ? $data->stok_tersedia : 0;
            $this->alat->status = isset($data->status) ? $data->status : "Baik";
            $this->alat->kategori = isset($data->kategori) ? $data->kategori : "";
            $this->alat->denda_per_hari = isset($data->denda_per_hari) ? $data->denda_per_hari : 0;
            $this->alat->denda_rusak = isset($data->denda_rusak) ? $data->denda_rusak : 0;
            $this->alat->denda_hilang = isset($data->denda_hilang) ? $data->denda_hilang : 0;

            if($this->alat->update()) {
                http_response_code(200);
                echo json_encode(array("message" => "Alat berhasil diupdate."));
            } else {
                http_response_code(503);
                echo json_encode(array("message" => "Gagal mengupdate alat."));
            }
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "Data tidak lengkap."));
        }
    }

    public function delete() {
        $data = json_decode(file_get_contents("php://input"));

        if(!empty($data->id)) {
            $this->alat->id = $data->id;
            
            $this->alat->readOne();
            if(!empty($this->alat->foto) && file_exists($this->alat->foto)) {
                unlink($this->alat->foto);
            }

            if($this->alat->delete()) {
                http_response_code(200);
                echo json_encode(array("message" => "Alat berhasil dihapus."));
            } else {
                http_response_code(503);
                echo json_encode(array("message" => "Gagal menghapus alat."));
            }
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "ID tidak ditemukan."));
        }
    }
}
?>