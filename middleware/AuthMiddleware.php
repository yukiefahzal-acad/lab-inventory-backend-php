<?php
require_once 'config/core.php';
require_once 'vendor/autoload.php';

use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;

class AuthMiddleware {
    
    public static function authenticate() {
        $headers = apache_request_headers();
        $authHeader = isset($headers['Authorization']) ? $headers['Authorization'] : null;

        if (!$authHeader && isset($_SERVER['HTTP_AUTHORIZATION'])) {
            $authHeader = $_SERVER['HTTP_AUTHORIZATION'];
        }

        if ($authHeader) {
            $parts = explode(" ", $authHeader);
            
            if (count($parts) == 2 && $parts[0] === "Bearer") {
                $jwt = $parts[1];
                global $key; 
                try {
                    $decoded = JWT::decode($jwt, new Key($key, 'HS256'));
                    
                    return $decoded->data; 
                    
                } catch (Exception $e) {
                    http_response_code(401);
                    echo json_encode(array(
                        "message" => "Akses ditolak. Token tidak valid atau sudah kadaluarsa.",
                        "error" => $e->getMessage()
                    ));
                    exit();
                }
            }
        }

        http_response_code(401);
        echo json_encode(array("message" => "Akses ditolak. Token otentikasi tidak ditemukan."));
        exit();
    }

    public static function authorizeRole($allowed_roles) {
        $user_data = self::authenticate();
        
        if (!in_array($user_data->role, $allowed_roles)) {
            http_response_code(403);
            echo json_encode(array("message" => "Akses ditolak. Anda tidak memiliki izin (Hak Akses Terbatas)."));
            exit();
        }
        
        return $user_data;
    }
}
?>