<?php
class QrcodeHelper {
    public static function generateQRUrl($data, $size = "200x200") {
        $encoded_data = urlencode($data);
        return "https://api.qrserver.com/v1/create-qr-code/?size={$size}&data={$encoded_data}";
    }
}
?>