<?php
include 'config.php';
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] == 'guru') {
        header("Location: dashboard_guru.php");
    } else {
        header("Location: dashboard_murid.php");
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - KJP LAT USK</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="card">
        <h1>KJP LAT USK</h1>
        <p style="text-align:center; color:#888; margin-bottom:30px;">Silakan login untuk melanjutkan</p>
        <form action="proses.php" method="POST">
            <label>Username</label>
            <input type="text" name="username" placeholder="Masukkan username" required>
            <label>Password</label>
            <input type="password" name="password" placeholder="Masukkan password" required>
            <button type="submit" name="login" style="width:100%;">MASUK</button>
        </form>
        <p style="text-align:center; margin-top:20px; color:#888;">
            Belum punya akun? <a href="register.php" style="color: #800000; font-weight:bold;">Registrasi Murid</a>
        </p>
    </div>
</body>
</html>