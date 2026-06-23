<?php
// Handle CORS preflight (OPTIONS) at the very beginning of the script
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, Accept, Origin");
    header("Access-Control-Max-Age: 86400");
    http_response_code(204);
    exit();
}

// CORS headers for normal requests
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, Accept, Origin");
header("Content-Type: application/json; charset=UTF-8");

require_once 'middleware/AuthMiddleware.php';

$request_uri = $_SERVER['REQUEST_URI'];
$script_path = dirname($_SERVER['SCRIPT_NAME']);
$path = parse_url($request_uri, PHP_URL_PATH);

if ($script_path !== '/' && $script_path !== '\\') {
    if (strpos($path, $script_path) === 0) {
        $path = substr($path, strlen($script_path));
    }
}

if (strpos($path, '/index.php') === 0) {
    $path = substr($path, strlen('/index.php'));
}

$public_routes = ['/api/login', '/api/register'];
$admin_routes = ['/api/peminjaman/persetujuan', '/api/admin/denda', '/api/admin/denda/lunas'];
$user_data = null;

if (!in_array($path, $public_routes)) {
    if (in_array($path, $admin_routes) || ($path === '/api/alat' && $_SERVER['REQUEST_METHOD'] !== 'GET')) {
        $user_data = AuthMiddleware::authorizeRole(['Admin']);
    } else {
        $user_data = AuthMiddleware::authenticate();
    }
}

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

    case '/api/upload':
        require 'controllers/UploadController.php';
        $controller = new UploadController();
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $controller->uploadFoto();
        }
        break;

    case '/api/booking':
        require 'controllers/PeminjamanController.php';
        $controller = new PeminjamanController();
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $controller->booking($user_data);
        }
        break;

    case '/api/peminjaman/riwayat':
        require 'controllers/PeminjamanController.php';
        $controller = new PeminjamanController();
        if ($_SERVER["REQUEST_METHOD"] == "GET") {
            $controller->riwayat($user_data);
        }
        break;

    case '/api/peminjaman/persetujuan':
        require 'controllers/PeminjamanController.php';
        $controller = new PeminjamanController();
        if ($_SERVER["REQUEST_METHOD"] == "PUT") {
            $controller->persetujuan();
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

    case '/api/pengembalian':
        require 'controllers/PengembalianController.php';
        $controller = new PengembalianController();
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $controller->kembalikanAlat();
        }
        break;

    case '/api/denda/bayar':
        require 'controllers/DendaController.php';
        $controller = new DendaController();
        if ($_SERVER["REQUEST_METHOD"] == "PUT") {
            $controller->pelunasan();
        }
        break;

    case '/api/admin/denda':
        require 'controllers/DendaController.php';
        $controller = new DendaController();
        if ($_SERVER["REQUEST_METHOD"] == "GET") {
            $controller->index();
        }
        break;

    case '/api/user/denda':
        require 'controllers/DendaController.php';
        $controller = new DendaController();
        if ($_SERVER["REQUEST_METHOD"] == "GET") {
            $controller->userDenda($user_data);
        }
        break;

    case '/api/admin/denda/lunas':
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