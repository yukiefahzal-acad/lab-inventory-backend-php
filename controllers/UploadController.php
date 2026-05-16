<?php
class UploadController {
    public function uploadFoto() {
        if(isset($_FILES['foto'])) {
            $target_dir = "uploads/foto/"; 
            
            $file_extension = pathinfo($_FILES["foto"]["name"], PATHINFO_EXTENSION);
            
            // unique filename
            $unique_name = "alat_" . uniqid() . "." . $file_extension;
            $target_file = $target_dir . $unique_name;

            // validasi file gambar
            $check = getimagesize($_FILES["foto"]["tmp_name"]);
            if($check !== false) {
                if (move_uploaded_file($_FILES["foto"]["tmp_name"], $target_file)) {
                    http_response_code(200);
                    echo json_encode(array(
                        "message" => "File berhasil diupload.",
                        "file_name" => $unique_name
                    ));
                } else {
                    http_response_code(500);
                    echo json_encode(array("message" => "Terjadi kesalahan saat menyimpan file di server."));
                }
            } else {
                http_response_code(400);
                echo json_encode(array("message" => "File yang diupload bukan gambar yang valid."));
            }
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "Tidak ada file yang dikirim. Pastikan key form-data adalah 'foto'."));
        }
    }
}
?>