<?php
session_start();
include "../config/database.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

$error = '';
$success = '';

// Proses ganti password
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['ganti_password'])) {
    $password_lama = $_POST['password_lama'];
    $password_baru = $_POST['password_baru'];
    $konfirmasi = $_POST['konfirmasi_password'];
    
    $user_id = $_SESSION['user_id'];
    $query = "SELECT password FROM users WHERE id = $user_id";
    $result = mysqli_query($conn, $query);
    $user = mysqli_fetch_assoc($result);
    
    if ($password_lama == $user['password']) {
        if ($password_baru == $konfirmasi) {
            if (strlen($password_baru) >= 6) {
                $update_query = "UPDATE users SET password = '$password_baru' WHERE id = $user_id";
                if (mysqli_query($conn, $update_query)) {
                    $success = "Password berhasil diubah!";
                } else {
                    $error = "Gagal mengubah password!";
                }
            } else {
                $error = "Password baru minimal 6 karakter!";
            }
        } else {
            $error = "Password baru dan konfirmasi tidak cocok!";
        }
    } else {
        $error = "Password lama salah!";
    }
}

// Proses ganti username
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['ganti_username'])) {
    $username_baru = mysqli_real_escape_string($conn, $_POST['username_baru']);
    $user_id = $_SESSION['user_id'];
    
    if (strlen($username_baru) >= 3) {
        $check_query = "SELECT * FROM users WHERE username = '$username_baru' AND id != $user_id";
        $check_result = mysqli_query($conn, $check_query);
        if (mysqli_num_rows($check_result) == 0) {
            $update_query = "UPDATE users SET username = '$username_baru' WHERE id = $user_id";
            if (mysqli_query($conn, $update_query)) {
                $_SESSION['username'] = $username_baru;
                $success = "Username berhasil diubah!";
            } else {
                $error = "Gagal mengubah username!";
            }
        } else {
            $error = "Username sudah digunakan!";
        }
    } else {
        $error = "Username minimal 3 karakter!";
    }
}

// Ambil data user saat ini
$user_id = $_SESSION['user_id'];
$query = "SELECT username, role FROM users WHERE id = $user_id";
$result = mysqli_query($conn, $query);
$user_data = mysqli_fetch_assoc($result);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>Pengaturan | Admin SMK Negeri 1 Banjar</title>
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
            color: #e74c3c;
        }

        .sidebar-menu a.logout:hover {
            background: #e74c3c;
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

        .settings-card {
            background: var(--putih);
            border-radius: 18px;
            padding: 25px;
            margin-bottom: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.03);
        }

        .settings-card h3 {
            color: var(--biru-utama);
            font-size: 1.1rem;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--biru-sangat-muda);
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: var(--abu-teks);
            font-weight: 500;
            font-size: 0.85rem;
        }

        .form-group input {
            width: 100%;
            max-width: 400px;
            padding: 12px 15px;
            border: 1px solid var(--abu-border);
            border-radius: 12px;
            font-size: 0.9rem;
            outline: none;
            transition: all 0.3s ease;
        }

        .form-group input:focus {
            border-color: var(--biru-utama);
            box-shadow: 0 0 0 3px rgba(44,125,160,0.1);
        }

        .btn-primary {
            background: var(--biru-utama);
            color: white;
            padding: 10px 25px;
            border-radius: 40px;
            text-decoration: none;
            display: inline-block;
            font-size: 0.8rem;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            background: var(--biru-gelap);
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

        .info-text {
            background: var(--biru-sangat-muda);
            padding: 15px;
            border-radius: 12px;
            margin-top: 20px;
        }

        .info-text p {
            color: var(--abu-teks);
            font-size: 0.8rem;
            margin: 5px 0;
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
            .form-group input {
                max-width: 100%;
            }
            .admin-header {
                flex-direction: column;
                align-items: flex-start;
            }
        }
    </style>
</head>
<body>
<div class="admin-container">
    <div class="admin-sidebar">
        <div class="profile-card">
            <div class="profile-avatar"><?php echo strtoupper(substr($_SESSION['username'], 0, 1)); ?></div>
            <h4><?php echo htmlspecialchars($_SESSION['username']); ?></h4>
            <p>Administrator</p>
            <div class="profile-badge">SMK Negeri 1 Banjar</div>
        </div>
        <div class="sidebar-menu">
            <div class="menu-label">Menu Utama</div>
            <a href="dashboard.php"><span>📊</span> Dashboard</a>
            <a href="tambah.php"><span>📝</span> Tambah Mading</a>
            <a href="kelola_user.php"><span>👥</span> Kelola User</a>
            <a href="laporan.php"><span>📄</span> Laporan</a>
            <div class="menu-label">Pengaturan</div>
            <a href="pengaturan.php" class="active"><span>⚙️</span> Pengaturan</a>
            <a href="../auth/logout.php" class="logout"><span>🚪</span> Keluar</a>
        </div>
    </div>

    <div class="admin-main">
        <div class="admin-header">
            <div class="header-title">
                <h2>Pengaturan Akun</h2>
                <p>Mengubah nama pengguna dan kata sandi administrator</p>
            </div>
        </div>

        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>

        <!-- Form Ganti Username -->
        <div class="settings-card">
            <h3>Ubah Nama Pengguna</h3>
            <form method="POST">
                <div class="form-group">
                    <label>Nama Pengguna Saat Ini</label>
                    <input type="text" value="<?php echo htmlspecialchars($user_data['username']); ?>" disabled style="background:#f1f5f9;">
                </div>
                <div class="form-group">
                    <label>Nama Pengguna Baru</label>
                    <input type="text" name="username_baru" placeholder="Masukkan nama pengguna baru" required minlength="3">
                </div>
                <button type="submit" name="ganti_username" class="btn-primary">Simpan Nama Pengguna</button>
            </form>
        </div>

        <!-- Form Ganti Password -->
        <div class="settings-card">
            <h3>Ubah Kata Sandi</h3>
            <form method="POST">
                <div class="form-group">
                    <label>Kata Sandi Lama</label>
                    <input type="password" name="password_lama" placeholder="Masukkan kata sandi lama" required>
                </div>
                <div class="form-group">
                    <label>Kata Sandi Baru</label>
                    <input type="password" name="password_baru" placeholder="Minimal 6 karakter" required minlength="6">
                </div>
                <div class="form-group">
                    <label>Konfirmasi Kata Sandi Baru</label>
                    <input type="password" name="konfirmasi_password" placeholder="Ulangi kata sandi baru" required>
                </div>
                <button type="submit" name="ganti_password" class="btn-primary">Simpan Kata Sandi</button>
            </form>
        </div>

        <!-- Informasi Akun -->
        <div class="settings-card">
            <h3>Informasi Akun</h3>
            <div class="info-text">
                <p><strong>Peran:</strong> <?php echo $user_data['role'] == 'admin' ? 'Administrator' : 'Pengguna'; ?></p>
                <p><strong>Status:</strong> Aktif</p>
                <p><strong>Terakhir Masuk:</strong> <?php echo date('d F Y H:i:s', $_SESSION['login_time'] ?? time()); ?></p>
            </div>
        </div>

        <div class="footer">
            <p>&copy; <?php echo date('Y'); ?> Mading Digital - SMK Negeri 1 Banjar</p>
        </div>
    </div>
</div>
</body>
</html>