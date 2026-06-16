<?php
require_once 'config/database.php';
require_once 'models/Denda.php';

class DendaController {
    private $db;
    private $denda;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->denda = new Denda($this->db);
    }

    public function pelunasan() {
        $data = json_decode(file_get_contents("php://input"));

        // Membutuhkan ID dari tabel tb_denda
        if(!empty($data->id)) {
            $this->denda->id = $data->id;

            if($this->denda->lunasiDenda()) {
                http_response_code(200);
                echo json_encode(array("message" => "Denda berhasil dilunasi. Mahasiswa kini dapat meminjam alat kembali."));
            } else {
                http_response_code(404);
                echo json_encode(array("message" => "Gagal melunasi. ID Denda tidak ditemukan atau denda tersebut sudah lunas sebelumnya."));            }
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "Data tidak lengkap. Harap sertakan ID denda."));
        }
    }

    public function index() {
        $search = isset($_GET['search']) ? $_GET['search'] : "";
        $stmt = $this->denda->readAll($search);
        
        $denda_arr = array();
        $denda_arr["data"] = array();
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            array_push($denda_arr["data"], $row);
        }
        
        http_response_code(200);
        echo json_encode($denda_arr);
    }

    public function userDenda($user_data) {
        $search = isset($_GET['search']) ? $_GET['search'] : "";
        $stmt = $this->denda->readByUser($user_data->id, $search);
        
        $denda_arr = array();
        $denda_arr["data"] = array();
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            array_push($denda_arr["data"], $row);
        }
        
        http_response_code(200);
        echo json_encode($denda_arr);
    }
}
?>