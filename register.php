<?php 
include 'config.php'; 
$kelas_query = mysqli_query($conn, "SELECT * FROM kelas ORDER BY nama_kelas ASC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi Murid</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="card">
        <h2>Daftar Akun Baru</h2>
        <form action="proses.php" method="POST">
            <label>Nama Lengkap</label>
            <input type="text" name="nama_lengkap" placeholder="Masukkan nama lengkap" required>
            
            <label>NIS</label>
            <input type="text" name="nis" placeholder="Contoh: 12345678" required>
            
            <label>Pilih Kelas</label>
            <select name="kelas_id" required>
                <option value="">-- Pilih Kelas --</option>
                <?php while($k = mysqli_fetch_assoc($kelas_query)): ?>
                    <option value="<?= $k['id']; ?>"><?= $k['nama_kelas']; ?></option>
                <?php endwhile; ?>
            </select>
            
            <label>Username</label>
            <input type="text" name="username" placeholder="Username untuk login" required>
            
            <label>Password</label>
            <input type="password" name="password" placeholder="Minimal 6 karakter" required>
            
            <button type="submit" name="register" style="width:100%;">DAFTAR</button>
        </form>
        <p style="text-align:center; margin-top:20px; color:#888;">
            Sudah punya akun? <a href="index.php" style="color: #800000; font-weight:bold;">Login Disini</a>
        </p>
    </div>
</body>
</html>