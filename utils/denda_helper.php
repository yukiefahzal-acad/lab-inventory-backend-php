<?php
class DendaHelper {
    public static function hitungDendaKeterlambatan($tanggal_kembali_rencana, $tanggal_kembali_aktual, $tarif_per_hari) {
        $tgl_rencana = strtotime($tanggal_kembali_rencana);
        $tgl_aktual = strtotime($tanggal_kembali_aktual);
        
        if ($tgl_aktual > $tgl_rencana) {
            $selisih_detik = $tgl_aktual - $tgl_rencana;
            $selisih_hari = floor($selisih_detik / (60 * 60 * 24));
            return $selisih_hari * $tarif_per_hari;
        }
        return 0;
    }
}
?>