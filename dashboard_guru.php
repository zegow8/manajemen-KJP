<?php
include 'config.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'guru') {
    header("Location: index.php");
    exit;
}

$uid = $_SESSION['user_id'];
$nama = $_SESSION['nama_lengkap'];
$page = $_GET['page'] ?? 'kelas';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Guru - KJP LAT USK</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="card card-wide">
        <!-- HEADER -->
        <div class="header-dash">
            <h2>Dashboard Guru</h2>
            <div class="user-info">
                <span>👤 <?= $nama; ?> (Guru)</span>
                <a href="proses.php?logout=1" class="btn btn-danger" style="padding: 8px 16px; font-size: 12px;">Logout</a>
            </div>
        </div>

        <!-- NAVBAR GURU -->
        <div class="navbar">
            <a href="dashboard_guru.php?page=kelas" class="<?= $page == 'kelas' ? 'active' : ''; ?>">📚 Kelola Kelas</a>
            <a href="dashboard_guru.php?page=laporan" class="<?= $page == 'laporan' ? 'active' : ''; ?>">📊 Laporan KJP</a>
        </div>

        <?php if ($page == 'kelas'): ?>
            <!-- ==================== KELOLA KELAS ==================== -->
            
            <div style="background: #FFF5F5; padding: 25px; border-radius: 16px; margin-bottom: 30px; border: 1px solid #ffe0e0;">
                <h3>➕ Tambah Data Kelas</h3>
                <form action="proses.php" method="POST" style="display: flex; gap: 15px; align-items: flex-end; flex-wrap: wrap;">
                    <div style="flex: 1; min-width: 200px;">
                        <label>Nama Kelas</label>
                        <input type="text" name="nama_kelas" placeholder="Contoh: 12 PPLG" required style="margin-bottom:0;">
                    </div>
                    <div style="flex: 0 0 200px;">
                        <label>Jumlah Murid</label>
                        <input type="number" name="jumlah_murid" placeholder="Contoh: 30" min="1" required style="margin-bottom:0;">
                    </div>
                    <button type="submit" name="tambah_kelas" class="btn btn-success">Tambah</button>
                </form>
            </div>

            <h3>📚 Daftar Kelas</h3>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Kelas</th>
                            <th>Total Murid</th>
                            <th>Sudah Input KJP</th>
                            <th>Belum Input KJP</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $no = 1;
                        $q_kelas = mysqli_query($conn, "SELECT * FROM kelas ORDER BY nama_kelas ASC");
                        
                        if (mysqli_num_rows($q_kelas) == 0) {
                            echo "<tr><td colspan='6' style='text-align:center; padding:30px; color:#888;'>Belum ada kelas. Silakan tambah kelas dulu.</td></tr>";
                        }
                        
                        while ($k = mysqli_fetch_assoc($q_kelas)):
                            $kelas_id = $k['id'];
                            $total = (int) $k['jumlah_murid'];
                            
                            $q_sudah = mysqli_query($conn, "SELECT COUNT(*) as sudah FROM kjp WHERE kelas_id='$kelas_id' AND tahun_ajaran IS NOT NULL AND no_kartu IS NOT NULL");
                            $sudah = mysqli_fetch_assoc($q_sudah)['sudah'];
                            
                            $belum = $total - $sudah;
                            if ($belum < 0) $belum = 0;
                        ?>
                        <tr>
                            <td><?= $no++; ?></td>
                            <td><b><?= $k['nama_kelas']; ?></b></td>
                            <td><span class="badge badge-sebelum"><?= $total; ?> Murid</span></td>
                            <td><span class="badge badge-selesai"><?= $sudah; ?> Murid</span></td>
                            <td><span class="badge badge-belum"><?= $belum; ?> Murid</span></td>
                            <td>
                                <a href="proses.php?hapus_kelas=<?= $k['id']; ?>" onclick="return confirm('Yakin hapus kelas ini?')" class="btn btn-danger" style="padding: 8px 16px; font-size: 12px;">Hapus</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

        <?php else: ?>
            <!-- ==================== LAPORAN KJP ==================== -->
            
            <h3>📊 Laporan Pengumpulan KJP</h3>
            
            <form method="GET" action="dashboard_guru.php" class="search-box">
                <input type="hidden" name="page" value="laporan">
                
                <input type="text" name="search_nama" placeholder="🔍 Cari Nama..." value="<?= $_GET['search_nama'] ?? ''; ?>">
                <input type="text" name="search_nis" placeholder="🔍 Cari NIS..." value="<?= $_GET['search_nis'] ?? ''; ?>">
                
                <select name="search_kelas">
                    <option value="">Semua Kelas</option>
                    <?php 
                    $q_kelas2 = mysqli_query($conn, "SELECT * FROM kelas ORDER BY nama_kelas ASC");
                    while($k2 = mysqli_fetch_assoc($q_kelas2)): 
                        $selected = (isset($_GET['search_kelas']) && $_GET['search_kelas'] == $k2['id']) ? 'selected' : '';
                    ?>
                        <option value="<?= $k2['id']; ?>" <?= $selected; ?>><?= $k2['nama_kelas']; ?></option>
                    <?php endwhile; ?>
                </select>
                
                <select name="sort_by">
                    <option value="terbaru" <?= (($_GET['sort_by'] ?? '') == 'terbaru') ? 'selected' : ''; ?>>🆕 Terbaru</option>
                    <option value="terlama" <?= (($_GET['sort_by'] ?? '') == 'terlama') ? 'selected' : ''; ?>>📜 Terlama</option>
                    <option value="a-z" <?= (($_GET['sort_by'] ?? '') == 'a-z') ? 'selected' : ''; ?>>🔤 Nama A-Z</option>
                    <option value="z-a" <?= (($_GET['sort_by'] ?? '') == 'z-a') ? 'selected' : ''; ?>>🔤 Nama Z-A</option>
                </select>
                
                <button type="submit" class="btn">Cari</button>
                <a href="dashboard_guru.php?page=laporan" class="btn btn-outline">Reset</a>
            </form>

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Murid</th>
                            <th>NIS</th>
                            <th>Kelas</th>
                            <th>Tahun Ajaran</th>
                            <th>No Kartu</th>
                            <th>Status KJP</th>
                            <th>Validasi Guru</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $no = 1;
                        $where = "WHERE 1=1";
                        
                        if (!empty($_GET['search_nama'])) {
                            $sn = mysqli_real_escape_string($conn, $_GET['search_nama']);
                            $where .= " AND u.nama_lengkap LIKE '%$sn%'";
                        }
                        if (!empty($_GET['search_nis'])) {
                            $si = mysqli_real_escape_string($conn, $_GET['search_nis']);
                            $where .= " AND u.nis LIKE '%$si%'";
                        }
                        if (!empty($_GET['search_kelas'])) {
                            $sk = mysqli_real_escape_string($conn, $_GET['search_kelas']);
                            $where .= " AND k.kelas_id = '$sk'";
                        }

                        $sort_by = $_GET['sort_by'] ?? 'terbaru';
                        $order = "ORDER BY k.id DESC";
                        
                        if ($sort_by == 'terlama') $order = "ORDER BY k.id ASC";
                        elseif ($sort_by == 'a-z') $order = "ORDER BY u.nama_lengkap ASC";
                        elseif ($sort_by == 'z-a') $order = "ORDER BY u.nama_lengkap DESC";

                        $q = mysqli_query($conn, "SELECT k.*, u.nama_lengkap, u.nis, c.nama_kelas 
                                                  FROM kjp k 
                                                  JOIN users u ON k.user_id = u.id 
                                                  LEFT JOIN kelas c ON k.kelas_id = c.id 
                                                  $where
                                                  $order");
                        
                        if (mysqli_num_rows($q) == 0) {
                            echo "<tr><td colspan='9' style='text-align:center; padding:30px; color:#888;'>Belum ada data KJP yang masuk.</td></tr>";
                        }
                        
                        while ($d = mysqli_fetch_assoc($q)):
                            $badge = 'badge-tunggu';
                            if($d['status_kjp'] == 'penerima aktif') $badge = 'badge-aktif';
                            elseif($d['status_kjp'] == 'penerima sebelumnya') $badge = 'badge-sebelum';
                            
                            $validasi_badge = $d['validasi_guru'] == 'sudah selesai' ? 'badge-selesai' : 'badge-belum';
                        ?>
                        <tr>
                            <td><?= $no++; ?></td>
                            <td><b><?= $d['nama_lengkap']; ?></b></td>
                            <td><?= $d['nis']; ?></td>
                            <td><?= $d['nama_kelas'] ?? '-'; ?></td>
                            <td><?= $d['tahun_ajaran'] ?: '-'; ?></td>
                            <td><?= $d['no_kartu'] ?: '-'; ?></td>
                            <td><span class="badge <?= $badge; ?>"><?= $d['status_kjp']; ?></span></td>
                            <td><span class="badge <?= $validasi_badge; ?>"><?= ucfirst($d['validasi_guru']); ?></span></td>
                            <td>
                                <?php if($d['validasi_guru'] == 'belum selesai'): ?>
                                    <a href="proses.php?acc_kjp=<?= $d['id']; ?>" class="btn btn-success" style="padding: 8px 16px; font-size: 12px;" onclick="return confirm('ACC data KJP ini?')">ACC</a>
                                <?php else: ?>
                                    <a href="proses.php?batal_acc=<?= $d['id']; ?>" class="btn btn-danger" style="padding: 8px 16px; font-size: 12px;" onclick="return confirm('Batal ACC?')">Batal</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>