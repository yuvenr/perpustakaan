<?php
// File: config/database.php
// Konfigurasi koneksi database terpusat

// Set header default untuk semua response API
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *"); // Izinkan akses dari mana saja (untuk development)
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-API-KEY");

// Handle preflight request (OPTIONS)
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}


define('DB_HOST', 'localhost'); // sesuaikan ip
define('DB_USER', 'root');
define('DB_PASS', ''); // Sesuaikan dengan password database Anda
define('DB_NAME', 'library');

// Kunci rahasia untuk API Key
define('API_KEY', ''); // generate dan insert ke db

// Kunci rahasia untuk JWT (JSON Web Token)
define('JWT_SECRET', ''); // generate


/**
 * Fungsi untuk membuat koneksi ke database.
 * @return mysqli|null Objek koneksi mysqli atau null jika gagal.
 */
function get_db_connection(): ?mysqli {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

    if ($conn->connect_error) {
        // Jangan tampilkan error detail di produksi
        // Sebaiknya dicatat ke dalam log file
        error_log("Database Connection Error: " . $conn->connect_error);
        
        // Kirim response error umum
        http_response_code(500); // Internal Server Error
        echo json_encode(['status' => 'error', 'message' => 'Gagal terhubung ke server.']);
        return null;
    }
    return $conn;
}

?>