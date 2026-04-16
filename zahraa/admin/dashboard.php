<?php
session_start();
date_default_timezone_set('Asia/Jakarta');
include "../config/database.php";

// Cek role admin
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

// Ambil data mading
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 6;
$offset = ($page - 1) * $limit;

// Query dengan pencarian
$where = '';
if ($search) {
    $where = " WHERE judul LIKE '%$search%' OR penulis LIKE '%$search%' OR isi LIKE '%$search%'";
}

// Query untuk menghitung total data
$count_query = "SELECT COUNT(*) as total FROM mading" . $where;
$count_result = mysqli_query($conn, $count_query);
$count_data = mysqli_fetch_assoc($count_result);
$total_data = $count_data['total'];
$total_pages = ceil($total_data / $limit);

// Query untuk mengambil data mading
$query = "SELECT * FROM mading" . $where . " ORDER BY id DESC LIMIT $limit OFFSET $offset";
$result = mysqli_query($conn, $query);

// Ambil statistik
$stat_query = "SELECT COUNT(*) as total FROM mading";
$stat_result = mysqli_query($conn, $stat_query);
$stat_data = mysqli_fetch_assoc($stat_result);
$total_mading = $stat_data['total'];

$user_query = "SELECT COUNT(*) as total FROM users WHERE role = 'user'";
$user_result = mysqli_query($conn, $user_query);
$user_data = mysqli_fetch_assoc($user_result);
$total_users = $user_data['total'];

$admin_query = "SELECT COUNT(*) as total FROM users WHERE role = 'admin'";
$admin_result = mysqli_query($conn, $admin_query);
$admin_data = mysqli_fetch_assoc($admin_result);
$total_admin = $admin_data['total'];

$today_mading = 0;
$check_column = mysqli_query($conn, "SHOW COLUMNS FROM mading LIKE 'created_at'");
if (mysqli_num_rows($check_column) > 0) {
    $today_query = "SELECT COUNT(*) as total FROM mading WHERE DATE(created_at) = CURDATE()";
    $today_result = mysqli_query($conn, $today_query);
    $today_data = mysqli_fetch_assoc($today_result);
    $today_mading = $today_data['total'];
}

// Ambil data untuk grafik (7 hari terakhir)
$chart_labels = [];
$chart_data = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $date_label = date('d M', strtotime("-$i days"));
    $query_chart = "SELECT COUNT(*) as total FROM mading WHERE DATE(created_at) = '$date'";
    $result_chart = mysqli_query($conn, $query_chart);
    $row_chart = mysqli_fetch_assoc($result_chart);
    $chart_labels[] = $date_label;
    $chart_data[] = $row_chart['total'];
}

// Ambil 5 mading terbaru untuk recent activity
$recent_query = "SELECT id, judul, penulis, created_at FROM mading ORDER BY id DESC LIMIT 5";
$recent_result = mysqli_query($conn, $recent_query);

// Hitung mading bulan ini
$bulan_ini = date('m');
$tahun_ini = date('Y');
$bulanan_query = "SELECT COUNT(*) as total FROM mading WHERE MONTH(created_at) = $bulan_ini AND YEAR(created_at) = $tahun_ini";
$bulanan_result = mysqli_query($conn, $bulanan_query);
$bulanan_data = mysqli_fetch_assoc($bulanan_result);
$mading_bulan_ini = $bulanan_data['total'];

// Format tanggal, hari, jam Indonesia
$hariIndonesia = [
    'Sunday' => 'Minggu',
    'Monday' => 'Senin',
    'Tuesday' => 'Selasa',
    'Wednesday' => 'Rabu',
    'Thursday' => 'Kamis',
    'Friday' => 'Jumat',
    'Saturday' => 'Sabtu'
];
$hari = $hariIndonesia[date('l')];
$tanggal = date('d F Y');
$jam = date('H:i:s');

// Ucapan berdasarkan waktu
$waktu = (int)date('H');
if ($waktu >= 4 && $waktu < 10) {
    $salam = "Selamat Pagi";
    $icon_salam = "🌅";
} elseif ($waktu >= 10 && $waktu < 15) {
    $salam = "Selamat Siang";
    $icon_salam = "☀️";
} elseif ($waktu >= 15 && $waktu < 18) {
    $salam = "Selamat Sore";
    $icon_salam = "🌤️";
} else {
    $salam = "Selamat Malam";
    $icon_salam = "🌙";
}

// Hitung persentase perubahan
$last_week_query = "SELECT COUNT(*) as total FROM mading WHERE WEEK(created_at) = WEEK(CURDATE() - INTERVAL 1 WEEK)";
$last_week_result = mysqli_query($conn, $last_week_query);
$last_week_data = mysqli_fetch_assoc($last_week_result);
$last_week_total = $last_week_data['total'];
$percentage_change = $last_week_total > 0 ? round((($total_mading - $last_week_total) / $last_week_total) * 100) : 100;
$trend_class = $percentage_change >= 0 ? 'trend-up' : 'trend-down';
$trend_icon = $percentage_change >= 0 ? '📈' : '📉';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>Dashboard Administrator | Mading SMKN 1 Banjar</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
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
            --biru-pale: #d9f0f7;
            --abu-teks: #334155;
            --abu-border: #cbd5e1;
            --putih: #ffffff;
            --hijau: #10b981;
            --merah: #ef4444;
        }

        .admin-container {
            display: flex;
            min-height: 100vh;
        }

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

        .admin-main {
            flex: 1;
            margin-left: 280px;
            padding: 20px 25px;
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
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

        .datetime-display {
            background: linear-gradient(135deg, var(--biru-utama), var(--biru-gelap));
            border-radius: 16px;
            padding: 10px 20px;
            text-align: center;
            color: white;
        }

        .datetime-display .hari {
            font-size: 0.8rem;
            opacity: 0.9;
        }

        .datetime-display .tanggal {
            font-size: 0.75rem;
            opacity: 0.85;
        }

        .datetime-display .jam {
            font-size: 1.1rem;
            font-weight: 600;
            font-family: monospace;
        }

        .greeting-card {
            background: linear-gradient(135deg, var(--biru-utama), var(--biru-muda));
            border-radius: 20px;
            padding: 20px 25px;
            margin-bottom: 25px;
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }

        .greeting-text h3 {
            font-size: 1.2rem;
        }

        .greeting-text p {
            opacity: 0.9;
            font-size: 0.8rem;
            margin-top: 5px;
        }

        .greeting-stats {
            display: flex;
            gap: 25px;
            background: rgba(255,255,255,0.15);
            padding: 10px 20px;
            border-radius: 40px;
        }

        .greeting-stats .stat {
            text-align: center;
        }

        .greeting-stats .stat .angka {
            font-size: 1.3rem;
            font-weight: bold;
        }

        .greeting-stats .stat .label {
            font-size: 0.65rem;
            opacity: 0.8;
        }

        .stats-wrapper {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 25px;
        }

        .stat-card {
            background: var(--putih);
            border-radius: 18px;
            padding: 18px;
            display: flex;
            align-items: center;
            gap: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.03);
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(44,125,160,0.1);
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            background: var(--biru-sangat-muda);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        .stat-info {
            flex: 1;
        }

        .stat-number {
            font-size: 1.6rem;
            font-weight: 700;
            color: var(--biru-utama);
            line-height: 1;
        }

        .stat-label {
            color: var(--abu-teks);
            font-size: 0.7rem;
            margin-top: 5px;
        }

        .stat-trend {
            font-size: 0.7rem;
            margin-top: 5px;
        }

        .trend-up { color: var(--hijau); }
        .trend-down { color: var(--merah); }

        .chart-card {
            background: var(--putih);
            border-radius: 18px;
            padding: 20px;
            margin-bottom: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.03);
        }

        .chart-card h3 {
            color: var(--biru-utama);
            font-size: 1rem;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .chart-container {
            max-height: 250px;
        }

        canvas {
            max-height: 200px;
            width: 100%;
        }

        .two-column {
            display: flex;
            gap: 25px;
            margin-bottom: 25px;
            flex: 1;
        }

        .left-column { flex: 2; }
        .right-column { flex: 1; }

        .card-tulis-mading {
            background: var(--putih);
            border-radius: 18px;
            padding: 20px;
            margin-bottom: 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.03);
        }

        .card-tulis-mading h3 {
            color: var(--biru-utama);
            font-size: 1rem;
        }

        .card-tulis-mading p {
            color: var(--abu-teks);
            font-size: 0.8rem;
            margin-top: 5px;
        }

        .recent-card {
            background: var(--putih);
            border-radius: 18px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.03);
            height: 100%;
        }

        .recent-card h3 {
            color: var(--biru-utama);
            font-size: 1rem;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--biru-pale);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .recent-list {
            list-style: none;
        }

        .recent-list li {
            padding: 12px 0;
            border-bottom: 1px solid var(--abu-border);
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .recent-list li:last-child {
            border-bottom: none;
        }

        .recent-icon {
            width: 35px;
            height: 35px;
            background: var(--biru-sangat-muda);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--biru-utama);
        }

        .recent-info {
            flex: 1;
        }

        .recent-title {
            font-size: 0.85rem;
            font-weight: 500;
            color: var(--abu-teks);
        }

        .recent-meta {
            font-size: 0.65rem;
            color: #94a3b8;
            margin-top: 3px;
        }

        .recent-link {
            color: var(--biru-utama);
            text-decoration: none;
            font-size: 0.7rem;
        }

        .table-container {
            background: var(--putih);
            border-radius: 18px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.03);
            overflow-x: auto;
        }

        .table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 15px;
        }

        .table-header h3 {
            color: var(--biru-utama);
            font-size: 1rem;
        }

        .search-bar {
            margin-bottom: 20px;
        }

        .search-bar form {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .search-bar input {
            flex: 1;
            padding: 10px 15px;
            border: 1px solid var(--abu-border);
            border-radius: 12px;
            font-size: 0.85rem;
            outline: none;
            min-width: 150px;
        }

        .search-bar input:focus {
            border-color: var(--biru-utama);
            box-shadow: 0 0 0 3px rgba(44,125,160,0.1);
        }

        .btn-primary {
            background: var(--biru-utama);
            color: white;
            padding: 10px 22px;
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
            transform: scale(1.02);
        }

        .btn-secondary {
            background: #e2e8f0;
            color: var(--abu-teks);
            padding: 10px 20px;
            border-radius: 40px;
            text-decoration: none;
            font-size: 0.8rem;
        }

        .admin-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 600px;
        }

        .admin-table th {
            text-align: left;
            padding: 12px;
            background: #f8fafc;
            color: var(--abu-teks);
            font-weight: 600;
            font-size: 0.75rem;
            border-bottom: 1px solid var(--abu-border);
        }

        .admin-table td {
            padding: 12px;
            border-bottom: 1px solid var(--abu-border);
            color: var(--abu-teks);
            font-size: 0.8rem;
        }

        .admin-table tr:hover {
            background: var(--biru-sangat-muda);
        }

        .admin-table img {
            width: 40px;
            height: 40px;
            object-fit: cover;
            border-radius: 8px;
        }

        .action-btns {
            display: flex;
            gap: 6px;
            flex-wrap: wrap;
        }

        .btn-edit {
            background: var(--biru-muda);
            color: white;
            padding: 5px 12px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 0.7rem;
        }

        .btn-delete {
            background: #e74c3c;
            color: white;
            padding: 5px 12px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 0.7rem;
        }

        .pagination {
            display: flex;
            gap: 8px;
            margin-top: 20px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .page-link {
            padding: 6px 12px;
            background: white;
            color: var(--abu-teks);
            border-radius: 8px;
            text-decoration: none;
            border: 1px solid var(--abu-border);
            font-size: 0.8rem;
        }

        .page-link:hover,
        .page-link.active {
            background: var(--biru-utama);
            color: white;
        }

        /* FOOTER YANG SUDAH DIPERBAIKI - SELALU DI PALING BAWAH */
        .footer {
            margin-top: auto;
            text-align: center;
            padding: 20px 0;
            color: var(--abu-teks);
            font-size: 0.7rem;
            border-top: 1px solid var(--abu-border);
            background: transparent;
            width: 100%;
        }

        .mobile-menu-btn {
            display: none;
            position: fixed;
            top: 15px;
            left: 15px;
            z-index: 100;
            background: var(--biru-utama);
            color: white;
            border: none;
            width: 45px;
            height: 45px;
            border-radius: 12px;
            font-size: 1.3rem;
            cursor: pointer;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .empty-state {
            text-align: center;
            padding: 40px;
        }

        .empty-state h3 {
            color: var(--biru-utama);
            font-size: 1rem;
        }

        @media (max-width: 1024px) {
            .stats-wrapper { grid-template-columns: repeat(2, 1fr); }
            .two-column { flex-direction: column; }
        }

        @media (max-width: 768px) {
            .mobile-menu-btn { display: block; }
            .admin-sidebar { transform: translateX(-100%); width: 260px; }
            .admin-sidebar.open { transform: translateX(0); }
            .admin-main { margin-left: 0; padding: 65px 15px 15px; }
            .admin-header { flex-direction: column; text-align: center; }
            .stats-wrapper { grid-template-columns: 1fr; }
            .greeting-card { flex-direction: column; text-align: center; }
            .card-tulis-mading { flex-direction: column; text-align: center; }
            .search-bar form { flex-direction: column; }
            .search-bar button, .search-bar a { width: 100%; text-align: center; }
        }

        @media (max-width: 480px) {
            .admin-main { padding: 65px 12px 12px; }
            .stat-card { padding: 12px; }
            .stat-number { font-size: 1.3rem; }
        }
    </style>
</head>
<body>
    <button class="mobile-menu-btn" id="mobileMenuBtn">☰</button>

    <div class="admin-container">
        <div class="admin-sidebar" id="sidebar">
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
                <a href="dashboard.php" class="active">
                    <span>📊</span> Dashboard
                </a>
                <a href="tambah.php">
                    <span>📝</span> Tambah Mading
                </a>
                <a href="kelola_user.php">
                    <span>👥</span> Kelola User
                </a>
                <a href="laporan.php">
                    <span>📄</span> Laporan
                </a>
                <div class="menu-label" style="margin-top: 20px;">Pengaturan</div>
                <a href="pengaturan.php">
                    <span>⚙️</span> Pengaturan
                </a>
                <a href="../auth/logout.php" class="logout">
                    <span>🚪</span> Keluar
                </a>
            </div>
        </div>

        <div class="admin-main">
            <div class="admin-header">
                <div class="header-title">
                    <h2>Dashboard Administrator</h2>
                    <p><?php echo $salam; ?>, <?php echo htmlspecialchars($_SESSION['username']); ?></p>
                </div>
                <div class="datetime-display">
                    <div class="hari"><?php echo $hari; ?></div>
                    <div class="tanggal"><?php echo $tanggal; ?></div>
                    <div class="jam" id="liveJam"><?php echo $jam; ?></div>
                </div>
            </div>

            <div class="greeting-card">
                <div class="greeting-text">
                    <h3><?php echo $icon_salam; ?> <?php echo $salam; ?>, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h3>
                    <p>Semangat mengelola mading digital SMK Negeri 1 Banjar</p>
                </div>
                <div class="greeting-stats">
                    <div class="stat">
                        <div class="angka"><?php echo $total_mading; ?></div>
                        <div class="label">Total Mading</div>
                    </div>
                    <div class="stat">
                        <div class="angka"><?php echo $total_users; ?></div>
                        <div class="label">Pengguna</div>
                    </div>
                    <div class="stat">
                        <div class="angka"><?php echo $today_mading; ?></div>
                        <div class="label">Hari Ini</div>
                    </div>
                </div>
            </div>

            <div class="stats-wrapper">
                <div class="stat-card">
                    <div class="stat-icon">📄</div>
                    <div class="stat-info">
                        <div class="stat-number"><?php echo $total_mading; ?></div>
                        <div class="stat-label">Total Mading</div>
                        <div class="stat-trend <?php echo $trend_class; ?>">
                            <?php echo $trend_icon; ?> <?php echo abs($percentage_change); ?>% dari minggu lalu
                        </div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">👥</div>
                    <div class="stat-info">
                        <div class="stat-number"><?php echo $total_users; ?></div>
                        <div class="stat-label">Total Pengguna</div>
                        <div class="stat-trend trend-up">📈 +12% dari bulan lalu</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">👑</div>
                    <div class="stat-info">
                        <div class="stat-number"><?php echo $total_admin; ?></div>
                        <div class="stat-label">Total Admin</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">📅</div>
                    <div class="stat-info">
                        <div class="stat-number"><?php echo $today_mading; ?></div>
                        <div class="stat-label">Mading Hari Ini</div>
                    </div>
                </div>
            </div>

            <div class="chart-card">
                <h3><span>📊</span> Statistik Mading 7 Hari Terakhir</h3>
                <div class="chart-container">
                    <canvas id="madingChart"></canvas>
                </div>
            </div>

            <div class="card-tulis-mading">
                <div>
                    <h3>Tulis Mading Baru</h3>
                    <p>Buat dan publikasikan informasi terbaru untuk warga sekolah</p>
                </div>
                <a href="tambah.php" class="btn-primary">+ Tulis Mading</a>
            </div>

            <div class="two-column">
                <div class="left-column">
                    <div class="table-container">
                        <div class="table-header">
                            <h3>📋 Daftar Mading</h3>
                            <a href="tambah.php" class="btn-primary" style="padding: 8px 18px;">+ Tambah</a>
                        </div>

                        <div class="search-bar">
                            <form method="GET">
                                <input type="text" name="search" placeholder="Cari judul, penulis, atau isi..." 
                                       value="<?php echo htmlspecialchars($search); ?>">
                                <button type="submit" class="btn-primary">Cari</button>
                                <?php if ($search): ?>
                                    <a href="dashboard.php" class="btn-secondary">Reset</a>
                                <?php endif; ?>
                            </form>
                        </div>

                        <div style="overflow-x: auto;">
                            <table class="admin-table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Judul</th>
                                        <th>Penulis</th>
                                        <th>Isi</th>
                                        <th>Foto</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (mysqli_num_rows($result) > 0): ?>
                                        <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                        <tr>
                                            <td><?php echo $row['id']; ?></td>
                                            <td><strong><?php echo htmlspecialchars($row['judul']); ?></strong></td>
                                            <td><?php echo htmlspecialchars($row['penulis']); ?></td>
                                            <td>
                                                <?php 
                                                $isi = htmlspecialchars($row['isi']);
                                                echo strlen($isi) > 40 ? substr($isi, 0, 40) . '...' : $isi;
                                                ?>
                                            </td>
                                            <td>
                                                <?php if (!empty($row['foto'])): ?>
                                                    <img src="../uploads/<?php echo htmlspecialchars($row['foto']); ?>" alt="Foto">
                                                <?php else: ?>
                                                    <span style="color:#94a3b8;">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="action-btns">
                                                    <a href="edit.php?id=<?php echo $row['id']; ?>" class="btn-edit">Edit</a>
                                                    <a href="hapus.php?id=<?php echo $row['id']; ?>" 
                                                       class="btn-delete" 
                                                       onclick="return confirm('Yakin ingin menghapus mading ini?')">Hapus</a>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="6" class="empty-state">
                                                <h3>Belum Ada Data Mading</h3>
                                                <a href="tambah.php" class="btn-primary" style="margin-top: 10px;">Tambah Mading</a>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            <table>
                        </div>

                        <?php if ($total_pages > 1): ?>
                        <div class="pagination">
                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <a href="?page=<?php echo $i; ?><?php echo $search ? '&search='.urlencode($search) : ''; ?>" 
                                   class="page-link <?php echo $i == $page ? 'active' : ''; ?>">
                                    <?php echo $i; ?>
                                </a>
                            <?php endfor; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="right-column">
                    <div class="recent-card">
                        <h3><span>🕐</span> Aktivitas Terbaru</h3>
                        <?php if (mysqli_num_rows($recent_result) > 0): ?>
                            <ul class="recent-list">
                                <?php while ($recent = mysqli_fetch_assoc($recent_result)): ?>
                                <li>
                                    <div class="recent-icon">📄</div>
                                    <div class="recent-info">
                                        <div class="recent-title"><?php echo htmlspecialchars($recent['judul']); ?></div>
                                        <div class="recent-meta">
                                            <?php echo htmlspecialchars($recent['penulis']); ?> • 
                                            <?php echo date('d/m/Y H:i', strtotime($recent['created_at'])); ?>
                                        </div>
                                    </div>
                                    <a href="edit.php?id=<?php echo $recent['id']; ?>" class="recent-link">Edit</a>
                                </li>
                                <?php endwhile; ?>
                            </ul>
                        <?php else: ?>
                            <p style="color:#94a3b8; text-align:center; padding:20px;">Belum ada aktivitas</p>
                        <?php endif; ?>
                    </div>

                    <div class="recent-card" style="margin-top: 20px;">
                        <h3><span>ℹ️</span> Informasi Sistem</h3>
                        <ul class="recent-list">
                            <li>
                                <div class="recent-icon">📊</div>
                                <div class="recent-info">
                                    <div class="recent-title">Total Mading</div>
                                    <div class="recent-meta"><?php echo $total_mading; ?> konten</div>
                                </div>
                            </li>
                            <li>
                                <div class="recent-icon">👥</div>
                                <div class="recent-info">
                                    <div class="recent-title">Total Pengguna</div>
                                    <div class="recent-meta"><?php echo $total_users; ?> orang</div>
                                </div>
                            </li>
                            <li>
                                <div class="recent-icon">📅</div>
                                <div class="recent-info">
                                    <div class="recent-title">Mading Bulan Ini</div>
                                    <div class="recent-meta"><?php echo $mading_bulan_ini; ?> konten</div>
                                </div>
                            </li>
                            <li>
                                <div class="recent-icon">💾</div>
                                <div class="recent-info">
                                    <div class="recent-title">Database</div>
                                    <div class="recent-meta">MySQL Aktif</div>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="footer">
                <p>&copy; <?php echo date('Y'); ?> Mading Digital - SMK Negeri 1 Banjar</p>
                <p style="font-size: 0.65rem; margin-top: 5px;">Sistem Informasi Mading Sekolah</p>
            </div>
        </div>
    </div>

    <script>
        const mobileBtn = document.getElementById('mobileMenuBtn');
        const sidebar = document.getElementById('sidebar');
        if (mobileBtn) {
            mobileBtn.addEventListener('click', function() {
                sidebar.classList.toggle('open');
            });
        }

        function updateClock() {
            const now = new Date();
            const jam = String(now.getHours()).padStart(2, '0');
            const menit = String(now.getMinutes()).padStart(2, '0');
            const detik = String(now.getSeconds()).padStart(2, '0');
            const waktu = jam + ':' + menit + ':' + detik;
            const jamElement = document.getElementById('liveJam');
            if (jamElement) jamElement.textContent = waktu;
        }
        setInterval(updateClock, 1000);

        const ctx = document.getElementById('madingChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($chart_labels); ?>,
                datasets: [{
                    label: 'Jumlah Mading',
                    data: <?php echo json_encode($chart_data); ?>,
                    backgroundColor: 'rgba(44, 125, 160, 0.1)',
                    borderColor: '#2c7da0',
                    borderWidth: 2,
                    pointBackgroundColor: '#2c7da0',
                    pointBorderColor: '#fff',
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    tension: 0.3,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: { font: { size: 11 } }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 1, font: { size: 10 } }
                    },
                    x: {
                        ticks: { font: { size: 10 } }
                    }
                }
            }
        });
    </script>
</body>
</html>