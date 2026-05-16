<?php
require_once 'config/database.php';
require_once 'models/Denda.php';
require_once 'utils/denda_helper.php';

class PengembalianController {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    public function kembalikanAlat() {
        $data = json_decode(file_get_contents("php://input"));

        if(!empty($data->peminjaman_id) && !empty($data->tanggal_kembali_aktual) && !empty($data->kondisi_alat)) {
            $query = "SELECT tanggal_kembali_rencana FROM tb_peminjaman WHERE id = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(1, $data->peminjaman_id);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if($row) {
                $tgl_rencana = $row['tanggal_kembali_rencana'];
                $denda_terlambat = DendaHelper::hitungDendaKeterlambatan($tgl_rencana, $data->tanggal_kembali_aktual, 5000); 
                
                $denda_rusak = 0;
                if($data->kondisi_alat === 'Rusak') {
                    $denda_rusak = 50000;
                }

                $queryUpdate = "UPDATE tb_peminjaman SET tanggal_kembali_aktual = ?, status = 'Dikembalikan' WHERE id = ?";
                $stmtUpdate = $this->db->prepare($queryUpdate);
                $stmtUpdate->bindParam(1, $data->tanggal_kembali_aktual);
                $stmtUpdate->bindParam(2, $data->peminjaman_id);
                $stmtUpdate->execute();

                if($denda_terlambat > 0 || $denda_rusak > 0) {
                    $denda = new Denda($this->db);
                    $denda->peminjaman_id = $data->peminjaman_id;
                    
                    if($denda_terlambat > 0) {
                        $denda->jenis_denda = 'Terlambat';
                        $denda->jumlah_denda = $denda_terlambat;
                        $denda->status_bayar = 'Belum Lunas';
                        $denda->keterangan = 'Terlambat mengembalikan alat.';
                        $denda->create();
                    }

                    if($denda_rusak > 0) {
                        $denda->jenis_denda = 'Rusak';
                        $denda->jumlah_denda = $denda_rusak;
                        $denda->status_bayar = 'Belum Lunas';
                        $denda->keterangan = 'Alat dikembalikan dalam kondisi rusak.';
                        $denda->create();
                    }
                }

                http_response_code(200);
                echo json_encode(array(
                    "message" => "Pengembalian berhasil diproses.",
                    "total_denda_terlambat" => $denda_terlambat,
                    "total_denda_rusak" => $denda_rusak
                ));
            } else {
                http_response_code(404);
                echo json_encode(array("message" => "Data peminjaman tidak ditemukan."));
            }
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "Data tidak lengkap."));
        }
    }
}
?>