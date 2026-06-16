<?php
require_once 'config/database.php';
require_once 'models/Denda.php';
require_once 'models/Alat.php';
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
            $query = "SELECT tanggal_kembali_rencana, alat_id, jumlah, status FROM tb_peminjaman WHERE id = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(1, $data->peminjaman_id);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if($row) {
                if($row['status'] !== 'Disetujui') {
                    http_response_code(400);
                    echo json_encode(array("message" => "Pengembalian gagal."));
                    return;
                }

                $tgl_rencana = $row['tanggal_kembali_rencana'];
                $alat_id = $row['alat_id'];
                $jumlah_pinjam = $row['jumlah'];
                $jumlah_kembali = isset($data->jumlah_kembali) ? $data->jumlah_kembali : $jumlah_pinjam;
                $catatan_pengembalian = isset($data->catatan_pengembalian) ? $data->catatan_pengembalian : "";

                $alat = new Alat($this->db);
                $alat->id = $alat_id;
                $alat->readOne();

                $tarif_denda_harian = isset($alat->denda_per_hari) ? $alat->denda_per_hari : 0;
                $denda_terlambat = DendaHelper::hitungDendaKeterlambatan($tgl_rencana, $data->tanggal_kembali_aktual, $tarif_denda_harian); 
                
                $denda_rusak = 0;
                if($data->kondisi_alat === 'Rusak') {
                    $denda_rusak = isset($alat->denda_rusak) ? $alat->denda_rusak : 0;
                }

                $queryUpdate = "UPDATE tb_peminjaman SET tanggal_kembali_aktual = ?, status = 'Dikembalikan', jumlah_kembali = ?, catatan_pengembalian = ? WHERE id = ?";
                $stmtUpdate = $this->db->prepare($queryUpdate);
                $stmtUpdate->bindParam(1, $data->tanggal_kembali_aktual);
                $stmtUpdate->bindParam(2, $jumlah_kembali);
                $stmtUpdate->bindParam(3, $catatan_pengembalian);
                $stmtUpdate->bindParam(4, $data->peminjaman_id);
                $stmtUpdate->execute();

                $alat->stok_tersedia += $jumlah_kembali;
                $alat->updateStok();

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