<?php
// File: api/login_member.php
// Endpoint untuk login anggota, menghasilkan JWT.

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../vendor/autoload.php';

use Firebase\JWT\JWT;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die(json_encode(['status' => 'error', 'message' => 'Metode tidak diizinkan.']));
}

$data = json_decode(file_get_contents("php://input"));

if (!isset($data->nim) || !isset($data->password)) {
    http_response_code(400);
    die(json_encode(['status' => 'error', 'message' => 'NIM dan password harus diisi.']));
}

$conn = get_db_connection();
if(!$conn) { exit(); }

$nim = $data->nim;
$password = $data->password;

$stmt = $conn->prepare("SELECT id, full_name, nim, password, status FROM users WHERE nim = ?");
$stmt->bind_param("s", $nim);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();
    
    // Verifikasi password
    if (password_verify($password, $user['password'])) {
        // Cek status akun
        if ($user['status'] !== 'approved') {
            http_response_code(403); // Forbidden
            die(json_encode(['status' => 'error', 'message' => 'Akun Anda belum disetujui oleh admin atau ditolak.']));
        }

        // Buat payload untuk JWT
        $iat = time();
        $exp = $iat + 60 * 60 * 24; // Token berlaku 24 jam
        $payload = [
            'iss' => 'http://localhost/proyek_perpus', // Issuer
            'aud' => 'http://localhost/proyek_perpus', // Audience
            'iat' => $iat, // Issued at
            'exp' => $exp, // Expiration time
            'data' => [
                'id' => $user['id'],
                'full_name' => $user['full_name'],
                'nim' => $user['nim']
            ]
        ];

        // Generate JWT
        $jwt = JWT::encode($payload, JWT_SECRET, 'HS256');

        echo json_encode([
            'status' => 'success',
            'message' => 'Login berhasil.',
            'token' => $jwt,
            'expires_at' => date('Y-m-d H:i:s', $exp)
        ]);

    } else {
        http_response_code(401);
        echo json_encode(['status' => 'error', 'message' => 'NIM atau password salah.']);
    }
} else {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'NIM atau password salah.']);
}

$stmt->close();
$conn->close();
?>
