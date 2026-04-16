<?php
session_start();
include "../config/database.php";

// Cek role user
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'user') {
    header("Location: ../auth/login.php");
    exit;
}

// Ambil data mading dengan pencarian
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 9;
$offset = ($page - 1) * $limit;

$where = '';
if ($search) {
    $where = " WHERE judul LIKE '%$search%' OR isi LIKE '%$search%' OR penulis LIKE '%$search%'";
}

// Hitung total data
$count_query = "SELECT COUNT(*) as total FROM mading" . $where;
$count_result = mysqli_query($conn, $count_query);
$count_data = mysqli_fetch_assoc($count_result);
$total_data = $count_data['total'];
$total_pages = ceil($total_data / $limit);

// Ambil data mading
$query = "SELECT * FROM mading" . $where . " ORDER BY id DESC LIMIT $limit OFFSET $offset";
$result = mysqli_query($conn, $query);

// Ambil data user
$user_id = $_SESSION['user_id'];
$user_query = "SELECT username FROM users WHERE id = $user_id";
$user_result = mysqli_query($conn, $user_query);
$user_data = mysqli_fetch_assoc($user_result);

// Format tanggal dan waktu Indonesia
date_default_timezone_set('Asia/Jakarta');
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
} elseif ($waktu >= 10 && $waktu < 15) {
    $salam = "Selamat Siang";
} elseif ($waktu >= 15 && $waktu < 18) {
    $salam = "Selamat Sore";
} else {
    $salam = "Selamat Malam";
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>Beranda | Mading Digital SMKN 1 Banjar</title>
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
            color: #334155;
        }

        /* Dekorasi float */
        .decoration {
            position: fixed;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 0;
        }

        .decoration span {
            position: absolute;
            background: rgba(97, 165, 194, 0.08);
            border-radius: 50%;
            animation: float 20s infinite ease-in-out;
        }

        .decoration span:nth-child(1) {
            width: 300px;
            height: 300px;
            top: -100px;
            left: -100px;
            background: radial-gradient(circle, rgba(97,165,194,0.2) 0%, rgba(44,125,160,0.05) 70%);
        }

        .decoration span:nth-child(2) {
            width: 400px;
            height: 400px;
            bottom: -150px;
            right: -150px;
            background: radial-gradient(circle, rgba(97,165,194,0.2) 0%, rgba(44,125,160,0.05) 70%);
            animation-delay: 5s;
        }

        .decoration span:nth-child(3) {
            width: 200px;
            height: 200px;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: radial-gradient(circle, rgba(97,165,194,0.15) 0%, rgba(44,125,160,0.03) 70%);
            animation-delay: 10s;
        }

        @keyframes float {
            0%, 100% { transform: translate(0,0) scale(1); }
            33% { transform: translate(30px,-30px) scale(1.1); }
            66% { transform: translate(-20px,20px) scale(0.9); }
        }

        .user-container {
            position: relative;
            z-index: 1;
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }

        /* Navbar */
        .user-navbar {
            background: white;
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 15px 30px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
            border: 1px solid #d9f0f7;
        }

        .nav-brand h2 {
            color: #2c7da0;
            font-size: 1.4rem;
        }

        .nav-brand p {
            color: #6c757d;
            font-size: 0.8rem;
        }

        .nav-menu {
            display: flex;
            gap: 15px;
        }

        .nav-menu a {
            color: #334155;
            text-decoration: none;
            padding: 8px 20px;
            border-radius: 40px;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.9rem;
        }

        .nav-menu a:hover {
            background: #2c7da0;
            color: white;
        }

        .nav-menu a.logout {
            background: #ef4444;
            color: white;
        }

        .nav-menu a.logout:hover {
            background: #dc2626;
        }

        /* Welcome Banner */
        .welcome-banner {
            background: linear-gradient(135deg, #61a5c2, #2c7da0);
            border-radius: 24px;
            padding: 30px;
            margin-bottom: 30px;
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
            box-shadow: 0 8px 20px rgba(44,125,160,0.2);
        }

        .banner-content h1 {
            font-size: 1.8rem;
            margin-bottom: 8px;
        }

        .banner-content p {
            font-size: 0.9rem;
            opacity: 0.9;
        }

        .datetime-user {
            text-align: right;
            background: rgba(255,255,255,0.15);
            padding: 10px 20px;
            border-radius: 40px;
            font-size: 0.9rem;
        }

        /* Search Section */
        .search-section {
            background: white;
            border-radius: 20px;
            padding: 20px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.03);
            border: 1px solid #d9f0f7;
        }

        .search-wrapper {
            display: flex;
            gap: 15px;
            max-width: 600px;
            margin: 0 auto;
        }

        .search-wrapper input {
            flex: 1;
            padding: 12px 20px;
            border: 1px solid #cbd5e1;
            border-radius: 40px;
            font-size: 0.9rem;
            outline: none;
            transition: 0.2s;
        }

        .search-wrapper input:focus {
            border-color: #61a5c2;
            box-shadow: 0 0 0 3px rgba(97,165,194,0.2);
        }

        .search-wrapper button {
            background: #2c7da0;
            color: white;
            border: none;
            padding: 0 25px;
            border-radius: 40px;
            cursor: pointer;
            font-weight: 600;
            transition: 0.2s;
        }

        .search-wrapper button:hover {
            background: #1f5068;
        }

        .search-wrapper a {
            background: #e2e8f0;
            padding: 0 25px;
            border-radius: 40px;
            text-decoration: none;
            color: #334155;
            display: inline-flex;
            align-items: center;
        }

        /* Mading Grid */
        .mading-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 25px;
            margin-top: 20px;
        }

        .mading-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            transition: all 0.3s;
            border: 1px solid #d9f0f7;
        }

        .mading-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 24px rgba(44,125,160,0.1);
        }

        .card-image {
            height: 200px;
            overflow: hidden;
        }

        .card-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.4s;
        }

        .mading-card:hover .card-image img {
            transform: scale(1.05);
        }

        .card-image.no-image {
            background: linear-gradient(135deg, #d9f0f7, #61a5c2);
            display: flex;
            align-items: center;
            justify-content: center;
            color: rgba(255,255,255,0.7);
            font-size: 3rem;
        }

        .card-content {
            padding: 20px;
        }

        .card-content h3 {
            color: #2c7da0;
            font-size: 1.2rem;
            margin-bottom: 10px;
            line-height: 1.4;
        }

        .content-preview {
            color: #4a5568;
            font-size: 0.85rem;
            line-height: 1.6;
            margin-bottom: 15px;
            max-height: 80px;
            overflow: hidden;
        }

        .card-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-top: 1px solid #e2e8f0;
            padding-top: 15px;
        }

        .author-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .author-avatar {
            width: 32px;
            height: 32px;
            background: linear-gradient(135deg, #61a5c2, #2c7da0);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 0.8rem;
        }

        .author-name {
            font-weight: 500;
            font-size: 0.8rem;
        }

        .read-more {
            background: #2c7da0;
            color: white;
            padding: 5px 15px;
            border-radius: 30px;
            text-decoration: none;
            font-size: 0.75rem;
            transition: 0.2s;
        }

        .read-more:hover {
            background: #1f5068;
        }

        /* Pagination */
        .pagination {
            display: flex;
            justify-content: center;
            gap: 8px;
            margin-top: 40px;
        }

        .page-link {
            padding: 6px 14px;
            background: white;
            color: #334155;
            border-radius: 8px;
            text-decoration: none;
            border: 1px solid #cbd5e1;
            transition: 0.2s;
        }

        .page-link:hover,
        .page-link.active {
            background: #2c7da0;
            color: white;
            border-color: #2c7da0;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 24px;
            margin-top: 30px;
        }

        .empty-state h3 {
            color: #2c7da0;
            margin-bottom: 10px;
        }

        .empty-state p {
            color: #6c757d;
            margin-bottom: 20px;
        }

        .btn-add {
            background: #2c7da0;
            color: white;
            padding: 10px 25px;
            border-radius: 40px;
            text-decoration: none;
            display: inline-block;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .user-navbar {
                flex-direction: column;
                text-align: center;
            }
            .search-wrapper {
                flex-direction: column;
            }
            .welcome-banner {
                flex-direction: column;
                text-align: center;
            }
            .datetime-user {
                text-align: center;
            }
            .mading-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="decoration">
        <span></span>
        <span></span>
        <span></span>
    </div>

    <div class="user-container">
        <!-- Navbar -->
        <nav class="user-navbar">
            <div class="nav-brand">
                <h2>Mading Digital</h2>
                <p>SMK Negeri 1 Banjar</p>
            </div>
            <div class="nav-menu">
                <a href="dashboard.php" class="active">Beranda</a>
                <a href="../auth/logout.php" class="logout">Keluar</a>
            </div>
        </nav>

        <!-- Welcome Banner -->
        <div class="welcome-banner">
            <div class="banner-content">
                <h1><?php echo $salam; ?>, <?php echo htmlspecialchars($user_data['username']); ?></h1>
                <p>Selamat datang di Mading Digital SMK Negeri 1 Banjar. Temukan informasi dan karya terbaru di sini.</p>
            </div>
            <div class="datetime-user">
                <div><?php echo $hari; ?>, <?php echo $tanggal; ?></div>
                <div><?php echo $jam; ?></div>
            </div>
        </div>

        <!-- Search Section -->
        <div class="search-section">
            <form method="GET" class="search-wrapper">
                <input type="text" name="search" placeholder="Cari mading berdasarkan judul, penulis, atau isi..." 
                       value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit">Cari</button>
                <?php if ($search): ?>
                    <a href="dashboard.php">Reset</a>
                <?php endif; ?>
            </form>
        </div>

        <!-- Mading Grid -->
        <?php if (mysqli_num_rows($result) > 0): ?>
            <div class="mading-grid">
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                    <div class="mading-card">
                        <?php if (!empty($row['foto'])): ?>
                            <div class="card-image">
                                <img src="../uploads/<?php echo htmlspecialchars($row['foto']); ?>" 
                                     alt="<?php echo htmlspecialchars($row['judul']); ?>">
                            </div>
                        <?php else: ?>
                            <div class="card-image no-image">
                                <span>📄</span>
                            </div>
                        <?php endif; ?>
                        
                        <div class="card-content">
                            <h3><?php echo htmlspecialchars($row['judul']); ?></h3>
                            <div class="content-preview">
                                <?php 
                                $isi = htmlspecialchars($row['isi']);
                                echo strlen($isi) > 150 ? substr($isi, 0, 150) . '...' : $isi;
                                ?>
                            </div>
                            <div class="card-meta">
                                <div class="author-info">
                                    <div class="author-avatar">
                                        <?php echo strtoupper(substr($row['penulis'], 0, 1)); ?>
                                    </div>
                                    <span class="author-name"><?php echo htmlspecialchars($row['penulis']); ?></span>
                                </div>
                                <a href="detail.php?id=<?php echo $row['id']; ?>" class="read-more">Baca</a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>

            <!-- Pagination -->
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

        <?php else: ?>
            <div class="empty-state">
                <h3>Belum Ada Mading</h3>
                <p>Belum ada mading yang dipublikasikan. Silakan cek kembali nanti.</p>
                <?php if ($search): ?>
                    <a href="dashboard.php" class="btn-add">Reset Pencarian</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <script>
        // Live clock update
        function updateClock() {
            const now = new Date();
            const jam = String(now.getHours()).padStart(2,'0');
            const menit = String(now.getMinutes()).padStart(2,'0');
            const detik = String(now.getSeconds()).padStart(2,'0');
            document.querySelector('.datetime-user div:last-child').innerHTML = jam+':'+menit+':'+detik;
        }
        setInterval(updateClock, 1000);
    </script>
</body>
</html>