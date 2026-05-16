<?php
require_once 'config/database.php';
require_once 'models/Peminjaman.php';
require_once 'models/Alat.php';
require_once 'models/User.php';
require_once 'models/Denda.php';

class PeminjamanController {
    private $db;
    private $peminjaman;
    private $alat;
    private $user;
    private $denda;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->peminjaman = new Peminjaman($this->db);
        $this->alat = new Alat($this->db);
        $this->user = new User($this->db);
        $this->denda = new Denda($this->db);
    }

    public function booking() {
        $data = json_decode(file_get_contents("php://input"));

        if(!empty($data->user_id) && !empty($data->alat_id) && !empty($data->tanggal_pinjam) && !empty($data->tanggal_kembali_rencana)) {
            // UserID validation
            $this->user->id = $data->user_id;
            if(!$this->user->checkExists()) {
                http_response_code(404);
                echo json_encode(array("message" => "User tidak ditemukan."));
                return;
            }

            // Fines validation
            if($this->denda->cekDendaBelumLunas($data->user_id)) {
                http_response_code(403);
                echo json_encode(array("message" => "Anda memiliki denda yang belum lunas. Silakan selesaikan denda terlebih dahulu."));
                return;
            }
            
            // AlatID and stock validation
            $this->alat->id = $data->alat_id;
            $this->alat->readOne();
            
            $total_booked = $this->peminjaman->checkAvailability($data->alat_id, $data->tanggal_pinjam);
            
            if($this->alat->stok_tersedia > $total_booked) {
                $this->peminjaman->user_id = $data->user_id;
                $this->peminjaman->alat_id = $data->alat_id;
                $this->peminjaman->tanggal_pinjam = $data->tanggal_pinjam;
                $this->peminjaman->tanggal_kembali_rencana = $data->tanggal_kembali_rencana;
                $this->peminjaman->status = "Menunggu";

                if($this->peminjaman->create()) {
                    http_response_code(201);
                    echo json_encode(array("message" => "Booking berhasil."));
                } else {
                    http_response_code(503);
                    echo json_encode(array("message" => "Gagal melakukan booking.", "error_db" => $this->peminjaman->error));
                }
            } else {
                http_response_code(400);
                echo json_encode(array("message" => "Stok alat habis pada tanggal tersebut."));
            }
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "Data tidak lengkap."));
        }
    }

    public function persetujuan() {
        $data = json_decode(file_get_contents("php://input"));

        if(!empty($data->id) && !empty($data->status)) {
            $this->peminjaman->id = $data->id;
            $this->peminjaman->status = $data->status;

            if($this->peminjaman->updateStatus()) {
                http_response_code(200);
                echo json_encode(array("message" => "Status peminjaman berhasil diupdate menjadi " . $data->status));
            } else {
                http_response_code(503);
                echo json_encode(array("message" => "Gagal mengupdate status peminjaman."));
            }
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "Data tidak lengkap."));
        }
    }

    public function riwayat() {
        $user_id = isset($_GET['user_id']) ? $_GET['user_id'] : null;
        
        $stmt = $this->peminjaman->readHistory($user_id);
        $num = $stmt->rowCount();

        if($num > 0) {
            $riwayat_arr = array();
            $riwayat_arr["data"] = array();

            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                extract($row);
                $riwayat_item = array(
                    "id" => $id,
                    "nama_mahasiswa" => $nama_mahasiswa,
                    "nama_alat" => $nama_alat,
                    "tanggal_pinjam" => $tanggal_pinjam,
                    "tanggal_kembali_rencana" => $tanggal_kembali_rencana,
                    "tanggal_kembali_aktual" => $tanggal_kembali_aktual,
                    "status" => $status
                );
                array_push($riwayat_arr["data"], $riwayat_item);
            }
            http_response_code(200);
            echo json_encode($riwayat_arr);
        } else {
            http_response_code(404);
            echo json_encode(array("message" => "Tidak ada riwayat peminjaman ditemukan."));
        }
    }
}
?>