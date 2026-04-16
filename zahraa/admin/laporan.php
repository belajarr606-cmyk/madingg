<?php
session_start();
include "../config/database.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

$total_mading = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM mading"))['total'];
$total_user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM users WHERE role='user'"))['total'];
$total_admin = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM users WHERE role='admin'"))['total'];

$bulan_ini = date('m');
$tahun_ini = date('Y');
$bulan_lalu = date('m', strtotime('-1 month'));
$tahun_bulan_lalu = date('Y', strtotime('-1 month'));
$mading_bulan_ini = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM mading WHERE MONTH(created_at)=$bulan_ini AND YEAR(created_at)=$tahun_ini"))['total'];
$mading_bulan_lalu = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM mading WHERE MONTH(created_at)=$bulan_lalu AND YEAR(created_at)=$tahun_bulan_lalu"))['total'];
$persen_mading = $mading_bulan_lalu > 0 ? round((($mading_bulan_ini - $mading_bulan_lalu) / $mading_bulan_lalu) * 100) : 100;

$mading_terbaru = mysqli_query($conn, "SELECT id, judul, penulis, created_at FROM mading ORDER BY id DESC LIMIT 10");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>Laporan | Admin SMK Negeri 1 Banjar</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
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
        .admin-container { display: flex; min-height: 100vh; }
        .admin-sidebar {
            width: 280px;
            background: var(--putih);
            box-shadow: 2px 0 20px rgba(0,0,0,0.05);
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            transition: 0.3s;
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
        .sidebar-menu { padding: 15px 0; }
        .sidebar-menu .menu-label { padding: 10px 20px; font-size: 0.7rem; color: #94a3b8; text-transform: uppercase; }
        .sidebar-menu a {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            margin: 4px 12px;
            color: var(--abu-teks);
            text-decoration: none;
            border-radius: 12px;
            transition: 0.3s;
        }
        .sidebar-menu a span { margin-right: 12px; font-size: 1.2rem; width: 24px; }
        .sidebar-menu a:hover, .sidebar-menu a.active { background: var(--biru-utama); color: white; transform: translateX(5px); }
        .sidebar-menu a.logout { margin-top: 20px; border-top: 1px solid var(--abu-border); color: #e74c3c; }
        .sidebar-menu a.logout:hover { background: #e74c3c; color: white; }
        .admin-main {
            flex: 1;
            margin-left: 280px;
            padding: 20px 25px;
        }
        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: var(--putih);
            border-radius: 20px;
            padding: 15px 25px;
            margin-bottom: 25px;
            flex-wrap: wrap;
            gap: 15px;
        }
        .header-title h2 { color: var(--biru-utama); font-size: 1.3rem; }
        .header-title p { color: var(--abu-teks); font-size: 0.8rem; margin-top: 5px; }
        .btn-print {
            background: var(--biru-utama);
            color: white;
            padding: 8px 20px;
            border-radius: 40px;
            border: none;
            cursor: pointer;
            font-size: 0.8rem;
        }
        .stats-wrapper {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 25px;
        }
        .stat-card {
            background: var(--putih);
            border-radius: 18px;
            padding: 20px;
            text-align: center;
        }
        .stat-number { font-size: 2rem; font-weight: 700; color: var(--biru-utama); }
        .stat-label { color: var(--abu-teks); font-size: 0.8rem; margin-top: 5px; }
        .stat-trend { font-size: 0.7rem; margin-top: 5px; }
        .trend-up { color: #10b981; }
        .trend-down { color: #ef4444; }
        .table-container {
            background: var(--putih);
            border-radius: 18px;
            padding: 20px;
            overflow-x: auto;
        }
        .table-container h3 { color: var(--biru-utama); margin-bottom: 15px; }
        .admin-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 500px;
        }
        .admin-table th, .admin-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid var(--abu-border);
        }
        .admin-table th { background: #f8fafc; color: var(--abu-teks); font-weight: 600; }
        .footer {
            margin-top: 30px;
            text-align: center;
            padding: 20px;
            color: var(--abu-teks);
            font-size: 0.7rem;
            border-top: 1px solid var(--abu-border);
        }
        @media (max-width: 768px) {
            .admin-sidebar { transform: translateX(-100%); width: 260px; }
            .admin-main { margin-left: 0; padding: 65px 15px 15px; }
            .stats-wrapper { grid-template-columns: 1fr; }
            .admin-header { flex-direction: column; align-items: flex-start; }
        }
        @media print {
            .admin-sidebar, .btn-print, .footer, .admin-header { display: none; }
            .admin-main { margin: 0; padding: 0; }
        }
    </style>
</head>
<body>
<div class="admin-container">
    <div class="admin-sidebar">
        <div class="profile-card">
            <div class="profile-avatar"><?php echo strtoupper(substr($_SESSION['username'],0,1)); ?></div>
            <h4><?php echo htmlspecialchars($_SESSION['username']); ?></h4>
            <p>Administrator</p>
            <div class="profile-badge">SMK Negeri 1 Banjar</div>
        </div>
        <div class="sidebar-menu">
            <div class="menu-label">Menu Utama</div>
            <a href="dashboard.php"><span>📊</span> Dashboard</a>
            <a href="tambah.php"><span>📝</span> Tambah Mading</a>
            <a href="kelola_user.php"><span>👥</span> Kelola User</a>
            <a href="laporan.php" class="active"><span>📄</span> Laporan</a>
            <div class="menu-label">Pengaturan</div>
            <a href="pengaturan.php"><span>⚙️</span> Pengaturan</a>
            <a href="../auth/logout.php" class="logout"><span>🚪</span> Keluar</a>
        </div>
    </div>
    <div class="admin-main">
        <div class="admin-header">
            <div class="header-title"><h2>Laporan Statistik</h2><p>Data statistik mading dan pengguna</p></div>
            <button class="btn-print" onclick="window.print()">🖨️ Cetak Laporan</button>
        </div>
        <div class="stats-wrapper">
            <div class="stat-card">
                <div class="stat-number"><?php echo $total_mading; ?></div>
                <div class="stat-label">Total Mading</div>
                <div class="stat-trend <?php echo $persen_mading>=0?'trend-up':'trend-down'; ?>"><?php echo $persen_mading>=0?'📈':'📉'; ?> <?php echo abs($persen_mading); ?>% dari bulan lalu</div>
            </div>
            <div class="stat-card"><div class="stat-number"><?php echo $total_user; ?></div><div class="stat-label">Total Pengguna (User)</div></div>
            <div class="stat-card"><div class="stat-number"><?php echo $total_admin; ?></div><div class="stat-label">Total Administrator</div></div>
        </div>
        <div class="table-container">
            <h3>📋 Mading Terbaru</h3>
            <table class="admin-table">
                <thead><tr><th>ID</th><th>Judul</th><th>Penulis</th><th>Tanggal</th></tr></thead>
                <tbody>
                    <?php while($row = mysqli_fetch_assoc($mading_terbaru)): ?>
                    <tr><td><?php echo $row['id']; ?></td><td><?php echo htmlspecialchars($row['judul']); ?></td><td><?php echo htmlspecialchars($row['penulis']); ?></td><td><?php echo date('d/m/Y H:i', strtotime($row['created_at'])); ?></td></tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <div class="footer"><p>&copy; <?php echo date('Y'); ?> Mading Digital - SMK Negeri 1 Banjar</p></div>
    </div>
</div>
</body>
</html>