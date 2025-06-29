<?php
// File: /proyek_perpus/admin_panel/login.php (Versi Perbaikan)
session_start();
// Jika sudah login, langsung arahkan ke dashboard
if (isset($_SESSION['admin_id'])) {
    header("Location: dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login Admin</title>
    <style>
        body { font-family: sans-serif; max-width: 400px; margin: 50px auto; }
        #message { margin-top: 15px; padding: 10px; border-radius: 5px; display: none; }
        .error { background-color: #f8d7da; color: #721c24; }
    </style>
</head>
<body>
    <h1>Login Panel Admin</h1>
    <div id="message" class="error"></div>
    <form id="loginForm">
        <p><input type="text" id="username" placeholder="Username" required style="width: 95%; padding: 8px;"></p>
        <p><input type="password" id="password" placeholder="Password" required style="width: 95%; padding: 8px;"></p>
        <p><button type="submit">Login</button></p>
    </form>

    <script>
        document.getElementById('loginForm').addEventListener('submit', async function(event) {
            event.preventDefault();
            const username = document.getElementById('username').value;
            const password = document.getElementById('password').value;
            const messageDiv = document.getElementById('message');

            try {
                const response = await fetch('../api/login_admin.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ username, password })
                });

                const result = await response.json();

                if (response.ok) {
                    window.location.href = 'dashboard.php';
                } else {
                    messageDiv.textContent = result.message;
                    messageDiv.style.display = 'block';
                }
            } catch (error) {
                messageDiv.textContent = 'Tidak dapat terhubung ke server.';
                messageDiv.style.display = 'block';
            }
        });
    </script>
</body>
</html>