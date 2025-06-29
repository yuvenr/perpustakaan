<?php
// File: api/admin_verify_user.php
// Endpoint untuk admin menyetujui atau menolak pendaftaran pengguna.

require_once __DIR__ . '/../config/database.php';
// Middleware: Memastikan hanya admin yang sudah login bisa mengakses.
require_once __DIR__ . '/middleware/auth_admin.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die(json_encode(['status' => 'error', 'message' => 'Metode tidak diizinkan.']));
}

$data = json_decode(file_get_contents("php://input"));

// Validasi input
if (!isset($data->user_id) || !isset($data->action)) {
    http_response_code(400);
    die(json_encode(['status' => 'error', 'message' => 'User ID dan action harus diisi.']));
}

$user_id = (int)$data->user_id;
$action = $data->action; // 'approve' or 'reject'

// Tentukan status baru berdasarkan action
$new_status = '';
if ($action === 'approve') {
    $new_status = 'approved';
} elseif ($action === 'reject') {
    $new_status = 'rejected';
} else {
    http_response_code(400);
    die(json_encode(['status' => 'error', 'message' => 'Action tidak valid. Gunakan "approve" atau "reject".']));
}

$conn = get_db_connection();
if(!$conn) { exit(); }

$stmt = $conn->prepare("UPDATE users SET status = ? WHERE id = ? AND status = 'pending'");
$stmt->bind_param("si", $new_status, $user_id);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        echo json_encode(['status' => 'success', 'message' => "Pengguna berhasil di-{$action}."]);
    } else {
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => 'Pengguna tidak ditemukan atau sudah diverifikasi sebelumnya.']);
    }
} else {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Gagal memperbarui status pengguna.']);
}

$stmt->close();
$conn->close();
?>
