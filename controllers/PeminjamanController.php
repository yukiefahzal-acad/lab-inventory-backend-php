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

    public function booking($user_data) {
        $data = json_decode(file_get_contents("php://input"));

        if(!empty($data->alat_id) && !empty($data->tanggal_pinjam) && !empty($data->tanggal_kembali_rencana) && !empty($data->jumlah)) {
            
            $logged_in_user_id = $user_data->id;

            if($this->denda->cekDendaBelumLunas($logged_in_user_id)) {
                http_response_code(403);
                echo json_encode(array("message" => "Anda memiliki denda yang belum lunas. Silakan selesaikan denda terlebih dahulu."));
                return;
            }
            
            $this->alat->id = $data->alat_id;
            $this->alat->readOne();
            
            if($this->alat->stok_tersedia >= $data->jumlah) {
                $this->peminjaman->user_id = $logged_in_user_id;
                $this->peminjaman->alat_id = $data->alat_id;
                $this->peminjaman->tanggal_pinjam = $data->tanggal_pinjam;
                $this->peminjaman->tanggal_kembali_rencana = $data->tanggal_kembali_rencana;
                $this->peminjaman->status = "Menunggu";
                $this->peminjaman->jumlah = $data->jumlah;
                $this->peminjaman->catatan_pinjaman = isset($data->catatan_pinjaman) ? $data->catatan_pinjaman : "";

                if($this->peminjaman->create()) {
                    $this->alat->stok_tersedia -= $data->jumlah;
                    $this->alat->updateStok();

                    http_response_code(201);
                    echo json_encode(array("message" => "Booking berhasil."));
                } else {
                    http_response_code(503);
                    echo json_encode(array("message" => "Gagal melakukan booking.", "error_db" => $this->peminjaman->error));
                }
            } else {
                http_response_code(400);
                echo json_encode(array("message" => "Stok alat habis."));
            }
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "Data tidak lengkap. Pastikan mengisi jumlah alat."));
        }
    }

    public function persetujuan() {
        $data = json_decode(file_get_contents("php://input"));

        if(!empty($data->id) && !empty($data->status)) {
            if($data->status !== 'Disetujui' && $data->status !== 'Ditolak') {
                http_response_code(400);
                echo json_encode(array("message" => "Status tidak valid. Hanya menerima 'Disetujui' atau 'Ditolak'."));
                return;
            }

            $query = "SELECT status, alat_id, jumlah FROM tb_peminjaman WHERE id = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(1, $data->id);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if($row) {
                if($row['status'] !== 'Menunggu') {
                    http_response_code(400);
                    echo json_encode(array("message" => "Persetujuan gagal. Status peminjaman saat ini bukan Menunggu."));
                    return;
                }

                $this->peminjaman->id = $data->id;
                $this->peminjaman->status = $data->status;

                if($this->peminjaman->updateStatus()) {
                    if($data->status === 'Ditolak') {
                        $this->alat->id = $row['alat_id'];
                        $this->alat->readOne();
                        $this->alat->stok_tersedia += $row['jumlah'];
                        $this->alat->updateStok();
                    }

                    http_response_code(200);
                    echo json_encode(array("message" => "Status peminjaman berhasil diupdate menjadi " . $data->status));
                } else {
                    http_response_code(503);
                    echo json_encode(array("message" => "Gagal mengupdate status peminjaman."));
                }
            } else {
                http_response_code(404);
                echo json_encode(array("message" => "Data peminjaman tidak ditemukan."));
            }
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "Data tidak lengkap."));
        }
    }

    public function riwayat() {
        $user_id = isset($_GET['user_id']) ? $_GET['user_id'] : null;
        $search = isset($_GET['search']) ? $_GET['search'] : "";
        
        $stmt = $this->peminjaman->readHistory($user_id, $search);
        $num = $stmt->rowCount();

        if($num > 0) {
            $riwayat_arr = array();
            $riwayat_arr["data"] = array();

            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                array_push($riwayat_arr["data"], $row);
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