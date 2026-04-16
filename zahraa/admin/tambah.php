<?php
session_start();
include "../config/database.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

$error = isset($_SESSION['error']) ? $_SESSION['error'] : '';
$success = isset($_SESSION['success']) ? $_SESSION['success'] : '';
unset($_SESSION['error']);
unset($_SESSION['success']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>Tambah Mading | Admin SMK Negeri 1 Banjar</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #e8f4f8 0%, #d4eaf7 100%);
            min-height: 100vh;
        }

        :root {
            --biru-utama: #2c7da0;
            --biru-muda: #61a5c2;
            --biru-sangat-muda: #e6f4f1;
            --biru-gelap: #1f5068;
            --abu-teks: #334155;
            --abu-border: #cbd5e1;
            --putih: #ffffff;
            --merah: #ef4444;
        }

        .admin-container {
            display: flex;
            min-height: 100vh;
        }

        /* SIDEBAR */
        .admin-sidebar {
            width: 280px;
            background: var(--putih);
            box-shadow: 2px 0 20px rgba(0,0,0,0.05);
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            z-index: 10;
            transition: all 0.3s ease;
        }

        .profile-card {
            padding: 25px 20px;
            text-align: center;
            background: linear-gradient(135deg, var(--biru-utama), var(--biru-gelap));
            margin: 15px;
            border-radius: 20px;
            color: white;
        }

        .profile-avatar {
            width: 70px;
            height: 70px;
            background: rgba(255,255,255,0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            font-size: 2rem;
            font-weight: bold;
        }

        .profile-card h4 {
            font-size: 1rem;
            margin-bottom: 5px;
        }

        .profile-card p {
            font-size: 0.7rem;
            opacity: 0.8;
        }

        .profile-badge {
            background: rgba(255,255,255,0.2);
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.7rem;
            margin-top: 10px;
        }

        .sidebar-menu {
            padding: 15px 0;
        }

        .sidebar-menu .menu-label {
            padding: 10px 20px;
            font-size: 0.7rem;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .sidebar-menu a {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            margin: 4px 12px;
            color: var(--abu-teks);
            text-decoration: none;
            border-radius: 12px;
            transition: all 0.3s ease;
        }

        .sidebar-menu a span:first-child {
            margin-right: 12px;
            font-size: 1.2rem;
            width: 24px;
        }

        .sidebar-menu a:hover,
        .sidebar-menu a.active {
            background: var(--biru-utama);
            color: white;
            transform: translateX(5px);
        }

        .sidebar-menu a.logout {
            margin-top: 20px;
            border-top: 1px solid var(--abu-border);
            color: var(--merah);
        }

        .sidebar-menu a.logout:hover {
            background: var(--merah);
            color: white;
        }

        /* MAIN CONTENT */
        .admin-main {
            flex: 1;
            margin-left: 280px;
            padding: 20px 25px;
            transition: all 0.3s ease;
        }

        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: var(--putih);
            border-radius: 20px;
            padding: 15px 25px;
            margin-bottom: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.03);
            flex-wrap: wrap;
            gap: 15px;
        }

        .header-title h2 {
            color: var(--biru-utama);
            font-size: 1.3rem;
        }

        .header-title p {
            color: var(--abu-teks);
            font-size: 0.8rem;
            margin-top: 5px;
        }

        .form-container {
            background: var(--putih);
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.03);
            max-width: 900px;
            margin: 0 auto;
        }

        .form-title {
            text-align: center;
            margin-bottom: 30px;
        }

        .form-title h2 {
            color: var(--biru-utama);
            font-size: 1.5rem;
            margin-bottom: 10px;
        }

        .form-title p {
            color: var(--abu-teks);
            font-size: 0.85rem;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: var(--abu-teks);
            font-weight: 500;
            font-size: 0.85rem;
        }

        .form-group label .required {
            color: var(--merah);
            margin-left: 5px;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid var(--abu-border);
            border-radius: 12px;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            font-family: inherit;
        }

        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--biru-utama);
            box-shadow: 0 0 0 3px rgba(44,125,160,0.1);
        }

        .form-group textarea {
            min-height: 150px;
            resize: vertical;
        }

        .file-info {
            margin-top: 10px;
            font-size: 0.7rem;
            color: #94a3b8;
        }

        .preview-container {
            margin-top: 15px;
            position: relative;
            display: inline-block;
        }

        .preview-image {
            max-width: 200px;
            max-height: 150px;
            border-radius: 12px;
            display: none;
        }

        .form-actions {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-top: 30px;
        }

        .btn-primary {
            background: var(--biru-utama);
            color: white;
            padding: 12px 30px;
            border-radius: 40px;
            border: none;
            cursor: pointer;
            font-size: 0.85rem;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            background: var(--biru-gelap);
            transform: scale(1.02);
        }

        .btn-secondary {
            background: #e2e8f0;
            color: var(--abu-teks);
            padding: 12px 30px;
            border-radius: 40px;
            text-decoration: none;
            font-size: 0.85rem;
            transition: all 0.3s ease;
        }

        .btn-secondary:hover {
            background: #cbd5e1;
        }

        .tips-card {
            background: var(--biru-sangat-muda);
            border-radius: 16px;
            padding: 20px;
            margin-top: 25px;
        }

        .tips-card h4 {
            color: var(--biru-utama);
            font-size: 1rem;
            margin-bottom: 15px;
        }

        .tips-card ul {
            list-style: none;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 10px;
        }

        .tips-card li {
            color: var(--abu-teks);
            font-size: 0.8rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .alert {
            padding: 12px 20px;
            border-radius: 12px;
            margin-bottom: 20px;
        }

        .alert-success {
            background: #d1fae5;
            color: #065f46;
        }

        .alert-error {
            background: #fee2e2;
            color: #991b1b;
        }

        .footer {
            margin-top: 30px;
            text-align: center;
            padding: 20px;
            color: var(--abu-teks);
            font-size: 0.7rem;
            border-top: 1px solid var(--abu-border);
        }

        @media (max-width: 768px) {
            .admin-sidebar {
                transform: translateX(-100%);
                width: 260px;
            }
            .admin-main {
                margin-left: 0;
                padding: 65px 15px 15px;
            }
            .form-actions {
                flex-direction: column;
            }
            .btn-primary, .btn-secondary {
                text-align: center;
            }
        }
    </style>
</head>
<body>
<div class="admin-container">
    <div class="admin-sidebar">
        <div class="profile-card">
            <div class="profile-avatar">
                <?php echo strtoupper(substr($_SESSION['username'], 0, 1)); ?>
            </div>
            <h4><?php echo htmlspecialchars($_SESSION['username']); ?></h4>
            <p>Administrator</p>
            <div class="profile-badge">SMK Negeri 1 Banjar</div>
        </div>
        <div class="sidebar-menu">
            <div class="menu-label">Menu Utama</div>
            <a href="dashboard.php"><span>📊</span> Dashboard</a>
            <a href="tambah.php" class="active"><span>📝</span> Tambah Mading</a>
            <a href="kelola_user.php"><span>👥</span> Kelola User</a>
            <a href="laporan.php"><span>📄</span> Laporan</a>
            <div class="menu-label">Pengaturan</div>
            <a href="pengaturan.php"><span>⚙️</span> Pengaturan</a>
            <a href="../auth/logout.php" class="logout"><span>🚪</span> Keluar</a>
        </div>
    </div>

    <div class="admin-main">
        <div class="admin-header">
            <div class="header-title">
                <h2>Tambah Mading</h2>
                <p>Menambahkan mading baru</p>
            </div>
        </div>

        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="form-container">
            <div class="form-title">
                <h2>Tulis Mading Baru</h2>
                <p>Bagikan informasi menarik untuk warga sekolah</p>
            </div>

            <form action="../process/simpan.php" method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label>Judul Mading <span class="required">*</span></label>
                    <input type="text" name="judul" placeholder="Masukkan judul mading" required>
                </div>

                <div class="form-group">
                    <label>Nama Penulis <span class="required">*</span></label>
                    <input type="text" name="penulis" placeholder="Masukkan nama penulis" required>
                </div>

                <div class="form-group">
                    <label>Isi Mading <span class="required">*</span></label>
                    <textarea name="isi" placeholder="Tulis isi mading di sini..." required></textarea>
                </div>

                <div class="form-group">
                    <label>Foto Mading</label>
                    <input type="file" name="foto" id="fotoInput" accept="image/jpeg, image/png, image/jpg, image/gif">
                    <div class="file-info">Format: JPG, PNG, GIF | Maks: 2MB | Kosongkan jika tidak ingin menambah foto</div>
                    <div class="preview-container">
                        <img id="preview" class="preview-image" src="#" alt="Preview Foto">
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-primary">Simpan Mading</button>
                    <a href="dashboard.php" class="btn-secondary">Batal</a>
                </div>
            </form>

            <div class="tips-card">
                <h4>Tips Menulis Mading</h4>
                <ul>
                    <li>• Gunakan judul yang menarik perhatian</li>
                    <li>• Tulis dengan bahasa yang jelas dan mudah dipahami</li>
                    <li>• Tambahkan foto yang relevan dengan konten</li>
                    <li>• Periksa kembali sebelum mempublikasikan</li>
                </ul>
            </div>
        </div>

        <div class="footer">
            <p>&copy; <?php echo date('Y'); ?> Mading Digital - SMK Negeri 1 Banjar</p>
        </div>
    </div>
</div>

<script>
    document.getElementById('fotoInput').addEventListener('change', function(e) {
        const preview = document.getElementById('preview');
        const file = e.target.files[0];
        if (file) {
            if (file.size > 2 * 1024 * 1024) {
                alert('Ukuran file terlalu besar! Maksimal 2MB');
                this.value = '';
                preview.style.display = 'none';
                return;
            }
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.style.display = 'block';
            };
            reader.readAsDataURL(file);
        } else {
            preview.style.display = 'none';
        }
    });
</script>
</body>
</html>