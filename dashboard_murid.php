<?php
include 'config.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'murid') {
    header("Location: index.php");
    exit;
}

$uid = $_SESSION['user_id'];
$nama = $_SESSION['nama_lengkap'];

// Ambil data KJP murid (LEFT JOIN biar aman kalo kelas kehapus)
$q_kjp = mysqli_query($conn, "SELECT k.*, c.nama_kelas FROM kjp k 
                              LEFT JOIN kelas c ON k.kelas_id = c.id 
                              WHERE k.user_id='$uid'");
$data = mysqli_fetch_assoc($q_kjp);

// AUTO-CREATE: Kalo data KJP belum ada di DB, bikin baru
if (!$data) {
    $q_kelas_default = mysqli_query($conn, "SELECT id FROM kelas LIMIT 1");
    $kelas_default = mysqli_fetch_assoc($q_kelas_default);
    $kelas_id = $kelas_default ? $kelas_default['id'] : 1;
    
    mysqli_query($conn, "INSERT INTO kjp (user_id, kelas_id, status_kjp, validasi_guru) 
                         VALUES ('$uid', '$kelas_id', 'penunggu penetapan', 'belum selesai')");
    
    // Ambil ulang
    $q_kjp = mysqli_query($conn, "SELECT k.*, c.nama_kelas FROM kjp k 
                                  LEFT JOIN kelas c ON k.kelas_id = c.id 
                                  WHERE k.user_id='$uid'");
    $data = mysqli_fetch_assoc($q_kjp);
}

// Cek apakah data KJP udah diisi (HARUS 2-2nya terisi)
$sudah_isi = !empty($data['tahun_ajaran']) && !empty($data['no_kartu']);

$q_kelas = mysqli_query($conn, "SELECT * FROM kelas ORDER BY nama_kelas ASC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Murid - KJP LAT USK</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="card card-wide">
        <div class="header-dash">
            <h2>Dashboard Murid</h2>
            <div class="user-info">
                <span>👤 <?= $nama; ?> (Murid)</span>
                <a href="proses.php?logout=1" class="btn btn-danger" style="padding: 8px 16px; font-size: 12px;">Logout</a>
            </div>
        </div>

        <h3>📋 Data KJP Saya</h3>

        <?php if (!$sudah_isi): ?>
            <!-- ==================== DATA KOSONG ==================== -->
            
            <div class="data-empty">
                <h3>📭 Data KJP Belum Diisi</h3>
                <p style="margin-bottom: 20px;">Silakan klik tombol di bawah untuk mengisi data KJP kamu.</p>
                <button type="button" onclick="document.getElementById('formTambah').style.display='block'; this.style.display='none';" class="btn btn-success" style="padding: 14px 30px; font-size: 14px;">➕ Tambah Data KJP</button>
            </div>

            <div class="btn-action-group">
                <button class="btn btn-disabled" disabled>✏️ Edit Data</button>
                <button class="btn btn-disabled" disabled>🗑️ Hapus Data</button>
            </div>

            <div id="formTambah" style="display:none; margin-top: 30px;">
                <div class="data-card">
                    <h3>📝 Formulir Data KJP</h3>
                    <form action="proses.php" method="POST">
                        <input type="hidden" name="id" value="<?= $data['id']; ?>">
                        
                        <div class="grid-2">
                            <div>
                                <label>Tahun Ajaran</label>
                                <select name="tahun_ajaran" required>
                                    <option value="">-- Pilih --</option>
                                    <option value="2022/2023">2022/2023</option>
                                    <option value="2023/2024">2023/2024</option>
                                    <option value="2024/2025">2024/2025</option>
                                    <option value="2025/2026">2025/2026</option>
                                </select>
                            </div>
                            <div>
                                <label>Nomor Kartu KJP</label>
                                <input type="text" name="no_kartu" placeholder="Contoh: KJP-12345" required>
                            </div>
                        </div>

                        <div class="grid-2">
                            <div>
                                <label>Status KJP</label>
                                <select name="status_kjp" required>
                                    <option value="penerima aktif">Penerima Aktif</option>
                                    <option value="penunggu penetapan">Penunggu Penetapan</option>
                                    <option value="penerima sebelumnya">Penerima Sebelumnya</option>
                                </select>
                            </div>
                            <div>
                                <label>Tanggal Pengumpulan</label>
                                <input type="date" name="tgl_pengumpulan" required>
                            </div>
                        </div>

                        <label>Keterangan Tambahan (Opsional)</label>
                        <textarea name="keterangan" rows="3" placeholder="Contoh: Sudah melengkapi berkas..."></textarea>

                        <div class="btn-action-group">
                            <button type="submit" name="simpan_kjp" class="btn btn-success">💾 Simpan Data</button>
                            <button type="button" onclick="document.getElementById('formTambah').style.display='none'; document.querySelector('.btn-success').style.display='inline-block';" class="btn btn-outline">❌ Batal</button>
                        </div>
                    </form>
                </div>
            </div>

        <?php else: ?>
            <!-- ==================== DATA UDAH DIISI ==================== -->
            
            <div class="data-card">
                <div class="data-row">
                    <span class="data-label">Nama Lengkap</span>
                    <span class="data-value"><?= $nama; ?></span>
                </div>
                <div class="data-row">
                    <span class="data-label">NIS</span>
                    <span class="data-value"><?= $_SESSION['nis'] ?? '-'; ?></span>
                </div>
                <div class="data-row">
                    <span class="data-label">Kelas</span>
                    <span class="data-value"><?= $data['nama_kelas'] ?? '-'; ?></span>
                </div>
                <div class="data-row">
                    <span class="data-label">Tahun Ajaran</span>
                    <span class="data-value"><?= $data['tahun_ajaran'] ?: '-'; ?></span>
                </div>
                <div class="data-row">
                    <span class="data-label">Nomor Kartu KJP</span>
                    <span class="data-value"><?= $data['no_kartu'] ?: '-'; ?></span>
                </div>
                <div class="data-row">
                    <span class="data-label">Status KJP</span>
                    <span class="data-value">
                        <?php 
                        $badge = 'badge-tunggu';
                        if($data['status_kjp'] == 'penerima aktif') $badge = 'badge-aktif';
                        elseif($data['status_kjp'] == 'penerima sebelumnya') $badge = 'badge-sebelum';
                        ?>
                        <span class="badge <?= $badge; ?>"><?= $data['status_kjp']; ?></span>
                    </span>
                </div>
                <div class="data-row">
                    <span class="data-label">Tanggal Pengumpulan</span>
                    <span class="data-value"><?= $data['tgl_pengumpulan'] ?: '-'; ?></span>
                </div>
                <div class="data-row">
                    <span class="data-label">Keterangan</span>
                    <span class="data-value"><?= $data['keterangan'] ?: '-'; ?></span>
                </div>
                <div class="data-row">
                    <span class="data-label">Validasi Guru</span>
                    <span class="data-value">
                        <?php 
                        $validasi_badge = $data['validasi_guru'] == 'sudah selesai' ? 'badge-selesai' : 'badge-belum';
                        ?>
                        <span class="badge <?= $validasi_badge; ?>"><?= ucfirst($data['validasi_guru']); ?></span>
                    </span>
                </div>
            </div>

            <div class="btn-action-group">
                <button class="btn btn-disabled" disabled>➕ Tambah Data</button>
                <button class="btn btn-success" onclick="document.getElementById('formEdit').style.display='block';">✏️ Edit Data</button>
                <a href="proses.php?hapus_kjp=<?= $data['id']; ?>" onclick="return confirm('Yakin mau hapus data KJP ini? Data akan dikosongkan kembali.')" class="btn btn-danger">🗑️ Hapus Data</a>
            </div>

            <div id="formEdit" style="display:none; margin-top: 30px;">
                <div class="data-card">
                    <h3>✏️ Edit Data KJP</h3>
                    <form action="proses.php" method="POST">
                        <input type="hidden" name="id" value="<?= $data['id']; ?>">
                        
                        <div class="grid-2">
                            <div>
                                <label>Tahun Ajaran</label>
                                <select name="tahun_ajaran" required>
                                    <option value="2022/2023" <?= $data['tahun_ajaran'] == '2022/2023' ? 'selected' : ''; ?>>2022/2023</option>
                                    <option value="2023/2024" <?= $data['tahun_ajaran'] == '2023/2024' ? 'selected' : ''; ?>>2023/2024</option>
                                    <option value="2024/2025" <?= $data['tahun_ajaran'] == '2024/2025' ? 'selected' : ''; ?>>2024/2025</option>
                                    <option value="2025/2026" <?= $data['tahun_ajaran'] == '2025/2026' ? 'selected' : ''; ?>>2025/2026</option>
                                </select>
                            </div>
                            <div>
                                <label>Nomor Kartu KJP</label>
                                <input type="text" name="no_kartu" value="<?= $data['no_kartu']; ?>" required>
                            </div>
                        </div>

                        <div class="grid-2">
                            <div>
                                <label>Status KJP</label>
                                <select name="status_kjp" required>
                                    <option value="penerima aktif" <?= $data['status_kjp'] == 'penerima aktif' ? 'selected' : ''; ?>>Penerima Aktif</option>
                                    <option value="penunggu penetapan" <?= $data['status_kjp'] == 'penunggu penetapan' ? 'selected' : ''; ?>>Penunggu Penetapan</option>
                                    <option value="penerima sebelumnya" <?= $data['status_kjp'] == 'penerima sebelumnya' ? 'selected' : ''; ?>>Penerima Sebelumnya</option>
                                </select>
                            </div>
                            <div>
                                <label>Tanggal Pengumpulan</label>
                                <input type="date" name="tgl_pengumpulan" value="<?= $data['tgl_pengumpulan']; ?>" required>
                            </div>
                        </div>

                        <label>Keterangan Tambahan (Opsional)</label>
                        <textarea name="keterangan" rows="3"><?= $data['keterangan']; ?></textarea>

                        <div class="btn-action-group">
                            <button type="submit" name="simpan_kjp" class="btn btn-success">💾 Update Data</button>
                            <button type="button" onclick="document.getElementById('formEdit').style.display='none';" class="btn btn-outline">❌ Batal</button>
                        </div>
                    </form>
                </div>
            </div>

        <?php endif; ?>
    </div>
</body>
</html>