<?php
include 'config.php';

// ==================== LOGIN ====================
if (isset($_POST['login'])) {
    $user = mysqli_real_escape_string($conn, $_POST['username']);
    $pass = md5($_POST['password']);
    $q = mysqli_query($conn, "SELECT * FROM users WHERE username='$user' AND password='$pass'");
    
    if (mysqli_num_rows($q) > 0) {
        $d = mysqli_fetch_assoc($q);
        $_SESSION['user_id'] = $d['id'];
        $_SESSION['username'] = $d['username'];
        $_SESSION['role'] = $d['role'];
        $_SESSION['nama_lengkap'] = $d['nama_lengkap'];
        $_SESSION['nis'] = $d['nis'];
        
        if ($d['role'] == 'guru') {
            header("Location: dashboard_guru.php");
        } else {
            header("Location: dashboard_murid.php");
        }
        exit;
    } else {
        echo "<script>alert('Login Gagal! Cek username/password.'); window.location='index.php';</script>";
        exit;
    }
}

// ==================== REGISTRASI MURID ====================
if (isset($_POST['register'])) {
    $nama = mysqli_real_escape_string($conn, $_POST['nama_lengkap']);
    $nis = mysqli_real_escape_string($conn, $_POST['nis']);
    $user = mysqli_real_escape_string($conn, $_POST['username']);
    $pass = md5($_POST['password']);
    $role = 'murid';
    $kelas_id = (int) $_POST['kelas_id'];
    
    // Cek username unik
    $cek = mysqli_query($conn, "SELECT * FROM users WHERE username='$user'");
    if (mysqli_num_rows($cek) > 0) {
        echo "<script>alert('Username sudah dipakai!'); window.location='register.php';</script>";
        exit;
    }

    // Insert ke users
    $q1 = mysqli_query($conn, "INSERT INTO users (username, password, role, nama_lengkap, nis) VALUES ('$user', '$pass', '$role', '$nama', '$nis')");
    
    if ($q1) {
        $user_id = mysqli_insert_id($conn);
        
        // AUTO-CREATE row KJP kosong untuk murid ini
        mysqli_query($conn, "INSERT INTO kjp (user_id, kelas_id, status_kjp, validasi_guru) 
                             VALUES ('$user_id', '$kelas_id', 'penunggu penetapan', 'belum selesai')");
        
        echo "<script>alert('Registrasi Berhasil! Silakan Login.'); window.location='index.php';</script>";
        exit;
    } else {
        echo "<script>alert('Gagal Registrasi!'); window.location='register.php';</script>";
        exit;
    }
}

// ==================== LOGOUT ====================
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit;
}

// ==================== TAMBAH KELAS (GURU) ====================
if (isset($_POST['tambah_kelas']) && $_SESSION['role'] == 'guru') {
    $nama = mysqli_real_escape_string($conn, $_POST['nama_kelas']);
    $jumlah = (int) $_POST['jumlah_murid'];
    
    mysqli_query($conn, "INSERT INTO kelas (nama_kelas, jumlah_murid) VALUES ('$nama', '$jumlah')");
    header("Location: dashboard_guru.php?page=kelas");
    exit;
}

// ==================== HAPUS KELAS (GURU) ====================
if (isset($_GET['hapus_kelas']) && $_SESSION['role'] == 'guru') {
    $id = (int) $_GET['hapus_kelas'];
    mysqli_query($conn, "DELETE FROM kelas WHERE id='$id'");
    header("Location: dashboard_guru.php?page=kelas");
    exit;
}

// ==================== ACC (GURU) ====================
if (isset($_GET['acc_kjp']) && $_SESSION['role'] == 'guru') {
    $id = (int) $_GET['acc_kjp'];
    mysqli_query($conn, "UPDATE kjp SET validasi_guru='sudah selesai' WHERE id='$id'");
    header("Location: dashboard_guru.php?page=laporan");
    exit;
}

// ==================== BATAL ACC (GURU) ====================
if (isset($_GET['batal_acc']) && $_SESSION['role'] == 'guru') {
    $id = (int) $_GET['batal_acc'];
    mysqli_query($conn, "UPDATE kjp SET validasi_guru='belum selesai' WHERE id='$id'");
    header("Location: dashboard_guru.php?page=laporan");
    exit;
}

// ==================== SIMPAN DATA KJP (MURID) ====================
if (isset($_POST['simpan_kjp']) && $_SESSION['role'] == 'murid') {
    $id = (int) $_POST['id'];
    $tahun = mysqli_real_escape_string($conn, $_POST['tahun_ajaran']);
    $status = mysqli_real_escape_string($conn, $_POST['status_kjp']);
    $no_kartu = mysqli_real_escape_string($conn, $_POST['no_kartu']);
    $tgl = mysqli_real_escape_string($conn, $_POST['tgl_pengumpulan']);
    $ket = mysqli_real_escape_string($conn, $_POST['keterangan']);
    $uid = $_SESSION['user_id'];
    
    // Cek apakah row-nya ada
    $cek = mysqli_query($conn, "SELECT id FROM kjp WHERE user_id='$uid'");
    
    if (mysqli_num_rows($cek) > 0) {
        // UPDATE
        mysqli_query($conn, "UPDATE kjp SET tahun_ajaran='$tahun', status_kjp='$status', no_kartu='$no_kartu', tgl_pengumpulan='$tgl', keterangan='$ket' WHERE user_id='$uid'");
    } else {
        // INSERT (kalo row-nya ilang)
        mysqli_query($conn, "INSERT INTO kjp (user_id, tahun_ajaran, status_kjp, no_kartu, tgl_pengumpulan, keterangan, validasi_guru) VALUES ('$uid', '$tahun', '$status', '$no_kartu', '$tgl', '$ket', 'belum selesai')");
    }
    
    header("Location: dashboard_murid.php");
    exit;
}

// ==================== HAPUS DATA KJP (MURID) ====================
if (isset($_GET['hapus_kjp']) && $_SESSION['role'] == 'murid') {
    $uid = $_SESSION['user_id'];
    // Reset data KJP jadi kosong (NULL)
    mysqli_query($conn, "UPDATE kjp SET tahun_ajaran=NULL, status_kjp='penunggu penetapan', no_kartu=NULL, tgl_pengumpulan=NULL, keterangan=NULL, validasi_guru='belum selesai' WHERE user_id='$uid'");
    header("Location: dashboard_murid.php");
    exit;
}

// ==================== FALLBACK ====================
header("Location: index.php");
exit;
?>