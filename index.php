<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once 'middleware/AuthMiddleware.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$request = $_SERVER['REQUEST_URI'];
$base_path = '/pw2_uas/labbackend/index.php';
$path = str_replace($base_path, '', $request);
$path = explode('?', $path)[0];

switch ($path) {
    case '/api/alat':
        require 'controllers/AlatController.php';
        $controller = new AlatController();
        if ($_SERVER["REQUEST_METHOD"] == "GET") {
            $controller->index();
        } elseif ($_SERVER["REQUEST_METHOD"] == "POST") {
            $controller->create();
        } elseif ($_SERVER["REQUEST_METHOD"] == "PUT") {
            $controller->update();
        } elseif ($_SERVER["REQUEST_METHOD"] == "DELETE") {
            $controller->delete();
        }
        break;

    case '/api/scan':
        require 'controllers/ScanController.php';
        $controller = new ScanController();
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $controller->scanQR();
        }
        break;

    // case '/api/upload':
    //     require 'controllers/UploadController.php';
    //     $controller = new UploadController();
    //     if ($_SERVER["REQUEST_METHOD"] == "POST") {
    //         $controller->uploadFoto();
    //     }
    //     break;

    case '/api/booking':
        require 'controllers/PeminjamanController.php';
        $controller = new PeminjamanController();
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $user_data = AuthMiddleware::authenticate();
            $controller->booking($user_data);
        }
        break;

    case '/api/peminjaman/riwayat':
        require 'controllers/PeminjamanController.php';
        $controller = new PeminjamanController();
        if ($_SERVER["REQUEST_METHOD"] == "GET") {
            $user_data = AuthMiddleware::authenticate();
            $controller->riwayat($user_data);
        }
        break;

    case '/api/peminjaman/persetujuan':
        require 'controllers/PeminjamanController.php';
        $controller = new PeminjamanController();
        if ($_SERVER["REQUEST_METHOD"] == "PUT") {
            $user_data = AuthMiddleware::authenticate();
            $controller->persetujuan($user_data);
        }
        break;

    case '/api/login':
        require 'controllers/AuthController.php';
        $controller = new AuthController();
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $controller->login();
        }
        break;

    case '/api/register':
        require 'controllers/AuthController.php';
        $controller = new AuthController();
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $controller->register();
        }
        break;

    case '/api/logout':
        require 'controllers/AuthController.php';
        $controller = new AuthController();
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $controller->logout();
        }
        break;

    case '/api/pengembalian':
        require 'controllers/PengembalianController.php';
        $controller = new PengembalianController();
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $user_data = AuthMiddleware::authenticate();
            $controller->kembalikanAlat($user_data);
        }
        break;

    case '/api/denda/bayar':
        require 'controllers/DendaController.php';
        $controller = new DendaController();
        if ($_SERVER["REQUEST_METHOD"] == "PUT") {
            $controller->pelunasan();
        }
        break;
        

    default:
        http_response_code(404);
        echo json_encode(array("message" => "Endpoint tidak ditemukan."));
        break;
}
?>