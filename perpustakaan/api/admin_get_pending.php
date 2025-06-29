<?php
// File: api/admin_get_pending.php
// Endpoint untuk admin melihat daftar pengguna yang menunggu persetujuan.

require_once __DIR__ . '/../config/database.php';
// Middleware: Memastikan hanya admin yang sudah login bisa mengakses.
require_once __DIR__ . '/middleware/auth_admin.php';

$conn = get_db_connection();
if(!$conn) { exit(); }

// Ambil semua pengguna dengan status 'pending'
$result = $conn->query("SELECT id, full_name, nim, email, registration_date FROM users WHERE status = 'pending' ORDER BY registration_date ASC");

$pending_users = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $pending_users[] = $row;
    }
}

echo json_encode(['status' => 'success', 'data' => $pending_users]);

$conn->close();
?>
