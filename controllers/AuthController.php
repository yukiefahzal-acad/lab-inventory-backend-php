<?php
require_once 'config/database.php';
require_once 'models/User.php';

class AuthController {
    private $db;
    private $user;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->user = new User($this->db);
    }

    public function login() {
        $data = json_decode(file_get_contents("php://input"));

        if(!empty($data->nim_nip) && !empty($data->password)) {
            $this->user->nim_nip = $data->nim_nip;
            $this->user->password = $data->password;

            if($this->user->login()) {
                $user_arr = array(
                    "message" => "Login berhasil.",
                    "id" => $this->user->id,
                    "nama" => $this->user->nama,
                    "role" => $this->user->role
                );
                http_response_code(200);
                echo json_encode($user_arr);
            } else {
                http_response_code(401);
                echo json_encode(array("message" => "Login gagal. NIM/NIP atau password salah."));
            }
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "Data tidak lengkap."));
        }
    }

    public function register() {
        $data = json_decode(file_get_contents("php://input"));

        if(!empty($data->nim_nip) && !empty($data->nama) && !empty($data->role) && !empty($data->password)) {
            $this->user->nim_nip = $data->nim_nip;
            $this->user->nama = $data->nama;
            $this->user->role = $data->role;
            $this->user->password = $data->password;

            if($this->user->register()) {
                http_response_code(201);
                echo json_encode(array("message" => "User berhasil didaftarkan."));
            } else {
                http_response_code(503);
                echo json_encode(array("message" => "Gagal mendaftarkan user."));
            }
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "Data tidak lengkap."));
        }
    }
}
?>