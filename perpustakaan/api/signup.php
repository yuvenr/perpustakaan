<?php
// File: api/signup.php
// Endpoint untuk pendaftaran pengguna baru (mahasiswa).

require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die(json_encode(['status' => 'error', 'message' => 'Metode tidak diizinkan.']));
}

$data = json_decode(file_get_contents("php://input"));

// Validasi input dasar
if (!isset($data->full_name) || !isset($data->nim) || !isset($data->email) || !isset($data->password)) {
    http_response_code(400);
    die(json_encode(['status' => 'error', 'message' => 'Data tidak lengkap.']));
}

$full_name = trim($data->full_name);
$nim = trim($data->nim);
$email = trim($data->email);
$password = $data->password;

// Validasi domain email
$allowed_domains = ['@student.ub.ac.id', '@ub.ac.id'];
$is_valid_domain = false;
foreach ($allowed_domains as $domain) {
    if (substr($email, -strlen($domain)) === $domain) {
        $is_valid_domain = true;
        break;
    }
}

if (!$is_valid_domain) {
    http_response_code(400);
    die(json_encode(['status' => 'error', 'message' => 'Registrasi gagal. Hanya email domain @ub.ac.id atau @student.ub.ac.id yang diizinkan.']));
}

$conn = get_db_connection();
if(!$conn) { exit(); }

// Cek duplikasi NIM atau Email
$stmt = $conn->prepare("SELECT id FROM users WHERE nim = ? OR email = ?");
$stmt->bind_param("ss", $nim, $email);
$stmt->execute();
if ($stmt->get_result()->num_rows > 0) {
    http_response_code(409); // Conflict
    die(json_encode(['status' => 'error', 'message' => 'NIM atau Email sudah terdaftar.']));
}

// Hash password
$hashed_password = password_hash($password, PASSWORD_BCRYPT);

// Insert data pengguna baru dengan status 'pending'
$stmt = $conn->prepare("INSERT INTO users (full_name, nim, email, password, status) VALUES (?, ?, ?, ?, 'pending')");
$stmt->bind_param("ssss", $full_name, $nim, $email, $hashed_password);

if ($stmt->execute()) {
    http_response_code(201); // Created
    echo json_encode(['status' => 'success', 'message' => 'Pendaftaran berhasil. Akun Anda sedang menunggu persetujuan admin.']);
} else {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Terjadi kesalahan saat pendaftaran.']);
}

$stmt->close();
$conn->close();
?>
