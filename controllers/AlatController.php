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
        $stmt = $this->alat->readAll();
        $num = $stmt->rowCount();

        $alat_arr = array();
        $alat_arr["data"] = array();

        if($num > 0) {
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                extract($row);
                $alat_item = array(
                    "id" => $id,
                    "kode_alat" => $kode_alat,
                    "nama_alat" => $nama_alat,
                    "spesifikasi" => $spesifikasi,
                    "foto" => $foto,
                    "stok_total" => $stok_total,
                    "stok_tersedia" => $stok_tersedia,
                    "status" => $status,
                    "qr_code" => $qr_code
                );
                array_push($alat_arr["data"], $alat_item);
            }
            http_response_code(200);
            echo json_encode($alat_arr);
        } else {
            http_response_code(404);
            echo json_encode(array("message" => "Tidak ada alat ditemukan."));
        }
    }

    public function create() {
        if(!empty($_POST['kode_alat']) && !empty($_POST['nama_alat']) && !empty($_POST['stok_total'])) {
            
            $this->alat->kode_alat = $_POST['kode_alat'];
            $this->alat->nama_alat = $_POST['nama_alat'];
            $this->alat->spesifikasi = isset($_POST['spesifikasi']) ? $_POST['spesifikasi'] : "";
            $this->alat->stok_total = $_POST['stok_total'];
            $this->alat->stok_tersedia = $_POST['stok_total']; // Awal buat, stok tersedia = stok total
            $this->alat->status = isset($_POST['status']) ? $_POST['status'] : "Tersedia";
            
            $clean_kode_alat = preg_replace('/[^A-Za-z0-9\-]/', '', $_POST['kode_alat']);
            $this->alat->qr_code = "QR_" . $clean_kode_alat . "_" . uniqid();

            $nama_foto_baru = ""; 
            if(isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
                $target_dir = "uploads/alat/";
                
                if (!is_dir($target_dir)) {
                    mkdir($target_dir, 0777, true);
                }

                $file_extension = pathinfo($_FILES["foto"]["name"], PATHINFO_EXTENSION);
                
                $nama_foto_baru = "foto_" . $clean_kode_alat . "_" . uniqid() . "." . $file_extension;
                $target_file = $target_dir . $nama_foto_baru;

                if (!move_uploaded_file($_FILES["foto"]["tmp_name"], $target_file)) {
                    http_response_code(500);
                    echo json_encode(array("message" => "Gagal mengupload foto. Alat batal ditambahkan."));
                    return; 
                }
            }
            
            $this->alat->foto = $nama_foto_baru;

            if($this->alat->create()) {
                http_response_code(201);
                echo json_encode(array(
                    "message" => "Alat berhasil ditambahkan.",
                    "qr_code_generated" => $this->alat->qr_code,
                    "foto_tersimpan" => $nama_foto_baru
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
            $this->alat->nama_alat = $data->nama_alat;
            $this->alat->spesifikasi = $data->spesifikasi;
            $this->alat->stok_total = $data->stok_total;
            $this->alat->stok_tersedia = $data->stok_tersedia;
            $this->alat->status = $data->status;

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