<?php
require_once 'config/database.php';
require_once 'models/Alat.php';

class ScanController {
    private $db;
    private $alat;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->alat = new Alat($this->db);
    }

    public function scanQR() {
        $data = json_decode(file_get_contents("php://input"));

        if(!empty($data->qr_code)) {
            $query = "SELECT id FROM tb_alat WHERE qr_code = ? LIMIT 0,1";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(1, $data->qr_code);
            $stmt->execute();

            if($stmt->rowCount() > 0) {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                $this->alat->id = $row['id'];
                $this->alat->readOne();

                $alat_arr = array(
                    "id" => $this->alat->id,
                    "kode_alat" => $this->alat->kode_alat,
                    "nama_alat" => $this->alat->nama_alat,
                    "spesifikasi" => $this->alat->spesifikasi,
                    "stok_tersedia" => $this->alat->stok_tersedia,
                    "status" => $this->alat->status
                );

                http_response_code(200);
                echo json_encode($alat_arr);
            } else {
                http_response_code(404);
                echo json_encode(array("message" => "Alat tidak ditemukan."));
            }
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "Data QR Code kosong."));
        }
    }
}
?>