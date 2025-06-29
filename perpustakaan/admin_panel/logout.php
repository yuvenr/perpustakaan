<?php
session_start(); // Wajib dipanggil sebelum session_destroy
session_destroy();
header("Location: login.php");
exit();
?>
