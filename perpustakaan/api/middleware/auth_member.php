<?php
// File: api/middleware/auth_member.php
// Middleware untuk memvalidasi JSON Web Token (JWT) dari anggota.

// Muat autoloader dari Composer
require_once __DIR__ . '/../../vendor/autoload.php';
// Muat file konfigurasi yang berisi JWT_SECRET
require_once __DIR__ . '/../../config/database.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

// Fungsi untuk mendapatkan header Authorization
function get_authorization_header(){
    $headers = null;
    if (isset($_SERVER['Authorization'])) {
        $headers = trim($_SERVER["Authorization"]);
    } else if (isset($_SERVER['HTTP_AUTHORIZATION'])) { // Nginx atau FastCGI
        $headers = trim($_SERVER["HTTP_AUTHORIZATION"]);
    } elseif (function_exists('apache_request_headers')) {
        $requestHeaders = apache_request_headers();
        // Konversi key header menjadi Title-Case
        $requestHeaders = array_combine(array_map('ucwords', array_keys($requestHeaders)), array_values($requestHeaders));
        if (isset($requestHeaders['Authorization'])) {
            $headers = trim($requestHeaders['Authorization']);
        }
    }
    return $headers;
}

// Ambil token dari header
$auth_header = get_authorization_header();

if (!$auth_header) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Akses ditolak. Token tidak ditemukan.']);
    exit();
}

// Pisahkan "Bearer" dari token-nya
$token_parts = explode(" ", $auth_header);
if (count($token_parts) < 2 || $token_parts[0] !== 'Bearer') {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Format token tidak valid.']);
    exit();
}

$jwt = $token_parts[1];

try {
    // Decode token
    $decoded = JWT::decode($jwt, new Key(JWT_SECRET, 'HS256'));
    
    // Konversi objek stdClass menjadi array asosiatif
    $decoded_array = (array) $decoded;
    
    // Kembalikan data dari payload token (khususnya data pengguna)
    return (array) $decoded_array['data'];

} catch (Exception $e) {
    // 1. CATAT DETAIL ERROR DI SISI SERVER (tidak akan terlihat oleh pengguna)
    // Pesan error akan masuk ke dalam file log error milik server (misal: /var/log/apache2/error.log)
    error_log('JWT Decode Error: ' . $e->getMessage());

    // 2. KIRIM PESAN GENERIC DAN AMAN KE PENGGUNA
    http_response_code(401);
    echo json_encode([
        'status' => 'error',
        'message' => 'Token tidak valid atau telah kadaluarsa. Silakan login kembali.'
        // Kunci 'error_detail' sudah dihapus
    ]);
    exit();
}
?>
