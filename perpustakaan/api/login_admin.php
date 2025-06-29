<?php
// File: api/login_admin.php
// Endpoint untuk login admin.

// Selalu mulai sesi di bagian paling atas
session_start();

require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die(json_encode(['status' => 'error', 'message' => 'Metode tidak diizinkan.']));
}

$data = json_decode(file_get_contents("php://input"));

if (!isset($data->username) || !isset($data->password)) {
    http_response_code(400);
    die(json_encode(['status' => 'error', 'message' => 'Username dan password harus diisi.']));
}

$conn = get_db_connection();
if(!$conn) { exit(); }

$username = $data->username;
$password = $data->password;

$stmt = $conn->prepare("SELECT id, password FROM admins WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $admin = $result->fetch_assoc();
    if (password_verify($password, $admin['password'])) {
        // Password benar, buat sesi
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_username'] = $username;
        
        echo json_encode(['status' => 'success', 'message' => 'Login berhasil.']);
    } else {
        http_response_code(401);
        echo json_encode(['status' => 'error', 'message' => 'Username atau password salah.']);
    }
} else {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Username atau password salah.']);
}

$stmt->close();
$conn->close();
?>
