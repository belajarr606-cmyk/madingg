<?php
session_start();
include "../config/database.php";

// Cek role user
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'user') {
    header("Location: ../auth/login.php");
    exit;
}

// Ambil ID dari URL
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    header("Location: dashboard.php");
    exit;
}

// Ambil data mading
$query = "SELECT * FROM mading WHERE id = $id";
$result = mysqli_query($conn, $query);
if (mysqli_num_rows($result) == 0) {
    header("Location: dashboard.php");
    exit;
}
$row = mysqli_fetch_assoc($result);

// Ambil data user
$user_id = $_SESSION['user_id'];
$user_query = "SELECT username FROM users WHERE id = $user_id";
$user_result = mysqli_query($conn, $user_query);
$user_data = mysqli_fetch_assoc($user_result);

// Format tanggal Indonesia
date_default_timezone_set('Asia/Jakarta');
$tanggal = date('d F Y', strtotime($row['created_at']));
$waktu_baca = ceil(str_word_count($row['isi']) / 200);

// Ambil rekomendasi (3 mading random selain yang sedang dibaca)
$rekom_query = "SELECT id, judul, penulis, foto FROM mading WHERE id != $id ORDER BY RAND() LIMIT 3";
$rekom_result = mysqli_query($conn, $rekom_query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($row['judul']); ?> | Mading Digital</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #e8f4f8 0%, #d4eaf7 100%);
            color: #334155;
            line-height: 1.6;
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
            background: rgba(97,165,194,0.08);
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
            transform: translate(-50%,-50%);
            background: radial-gradient(circle, rgba(97,165,194,0.15) 0%, rgba(44,125,160,0.03) 70%);
            animation-delay: 10s;
        }
        @keyframes float {
            0%,100% { transform: translate(0,0) scale(1); }
            33% { transform: translate(30px,-30px) scale(1.1); }
            66% { transform: translate(-20px,20px) scale(0.9); }
        }

        .user-container {
            position: relative;
            z-index: 1;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        /* Navbar */
        .user-navbar {
            background: white;
            border-radius: 20px;
            padding: 15px 30px;
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.03);
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
            transition: 0.3s;
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

        /* Detail Card */
        .detail-card {
            background: white;
            border-radius: 28px;
            padding: 40px;
            margin-bottom: 40px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.05);
            border: 1px solid #d9f0f7;
        }
        .detail-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .detail-category {
            background: #e6f4f1;
            color: #2c7da0;
            display: inline-block;
            padding: 6px 20px;
            border-radius: 40px;
            font-size: 0.8rem;
            font-weight: 500;
            margin-bottom: 20px;
        }
        .detail-header h1 {
            color: #2c7da0;
            font-size: 2.2rem;
            margin-bottom: 15px;
        }
        .detail-meta {
            display: flex;
            justify-content: center;
            gap: 25px;
            color: #6c757d;
            font-size: 0.85rem;
            flex-wrap: wrap;
        }
        .detail-meta span {
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .reading-time {
            background: #e6f4f1;
            padding: 4px 12px;
            border-radius: 30px;
        }
        .detail-image {
            margin: 30px 0;
            text-align: center;
        }
        .detail-image img {
            max-width: 100%;
            max-height: 450px;
            border-radius: 20px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
            object-fit: cover;
        }
        .detail-content {
            background: #fafcff;
            padding: 30px;
            border-radius: 20px;
            margin: 30px 0;
            border: 1px solid #e2e8f0;
        }
        .detail-content p {
            font-size: 1rem;
            line-height: 1.8;
            white-space: pre-line;
        }
        .detail-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
            padding-top: 20px;
            border-top: 1px solid #e2e8f0;
        }
        .author-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .author-avatar {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #61a5c2, #2c7da0);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.8rem;
            font-weight: bold;
        }
        .author-detail .label {
            font-size: 0.75rem;
            color: #6c757d;
        }
        .author-name {
            font-size: 1.2rem;
            font-weight: 600;
            color: #2c7da0;
        }
        .action-buttons {
            display: flex;
            gap: 15px;
        }
        .btn-back, .btn-share {
            padding: 10px 25px;
            border-radius: 40px;
            text-decoration: none;
            font-weight: 500;
            transition: 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 0.85rem;
        }
        .btn-back {
            background: #2c7da0;
            color: white;
        }
        .btn-back:hover {
            background: #1f5068;
            transform: translateX(-3px);
        }
        .btn-share {
            background: white;
            color: #2c7da0;
            border: 1px solid #cbd5e1;
        }
        .btn-share:hover {
            background: #e6f4f1;
            transform: translateY(-2px);
        }

        /* Rekomendasi */
        .rekomendasi {
            margin-top: 40px;
        }
        .rekomendasi h3 {
            color: #2c7da0;
            margin-bottom: 20px;
            font-size: 1.3rem;
        }
        .rekom-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
            gap: 25px;
        }
        .rekom-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            transition: 0.3s;
            cursor: pointer;
            border: 1px solid #d9f0f7;
        }
        .rekom-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 24px rgba(44,125,160,0.1);
        }
        .rekom-card .card-image {
            height: 160px;
            overflow: hidden;
        }
        .rekom-card .card-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: 0.4s;
        }
        .rekom-card:hover .card-image img {
            transform: scale(1.05);
        }
        .rekom-card .no-image {
            background: linear-gradient(135deg, #d9f0f7, #61a5c2);
            height: 160px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 2rem;
        }
        .rekom-card .card-content {
            padding: 15px;
        }
        .rekom-card h4 {
            color: #2c7da0;
            font-size: 1rem;
            margin-bottom: 8px;
        }
        .rekom-card .penulis {
            font-size: 0.7rem;
            color: #6c757d;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .rekom-card .penulis span {
            background: #2c7da0;
            color: white;
            width: 24px;
            height: 24px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            font-size: 0.7rem;
        }

        @media (max-width: 768px) {
            .user-navbar {
                flex-direction: column;
                text-align: center;
            }
            .detail-card {
                padding: 20px;
            }
            .detail-header h1 {
                font-size: 1.5rem;
            }
            .detail-footer {
                flex-direction: column;
                align-items: flex-start;
            }
            .action-buttons {
                width: 100%;
                justify-content: space-between;
            }
            .rekom-grid {
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
                <a href="dashboard.php">Beranda</a>
                <a href="../auth/logout.php" class="logout">Keluar</a>
            </div>
        </nav>

        <!-- Detail Mading -->
        <div class="detail-card">
            <div class="detail-header">
                <div class="detail-category">Mading Digital</div>
                <h1><?php echo htmlspecialchars($row['judul']); ?></h1>
                <div class="detail-meta">
                    <span>📅 <?php echo $tanggal; ?></span>
                    <span class="reading-time">⏱️ <?php echo $waktu_baca; ?> menit baca</span>
                </div>
            </div>

            <?php if (!empty($row['foto'])): ?>
            <div class="detail-image">
                <img src="../uploads/<?php echo htmlspecialchars($row['foto']); ?>" alt="<?php echo htmlspecialchars($row['judul']); ?>">
            </div>
            <?php endif; ?>

            <div class="detail-content">
                <p><?php echo nl2br(htmlspecialchars($row['isi'])); ?></p>
            </div>

            <div class="detail-footer">
                <div class="author-info">
                    <div class="author-avatar">
                        <?php echo strtoupper(substr($row['penulis'], 0, 1)); ?>
                    </div>
                    <div class="author-detail">
                        <div class="label">Ditulis oleh</div>
                        <div class="author-name"><?php echo htmlspecialchars($row['penulis']); ?></div>
                    </div>
                </div>
                <div class="action-buttons">
                    <a href="dashboard.php" class="btn-back">← Kembali</a>
                    <button onclick="shareMading()" class="btn-share">📤 Bagikan</button>
                </div>
            </div>
        </div>

        <!-- Rekomendasi Mading Lainnya -->
        <?php if (mysqli_num_rows($rekom_result) > 0): ?>
        <div class="rekomendasi">
            <h3>📖 Mading Lainnya</h3>
            <div class="rekom-grid">
                <?php while ($rekom = mysqli_fetch_assoc($rekom_result)): ?>
                <div class="rekom-card" onclick="window.location.href='detail.php?id=<?php echo $rekom['id']; ?>'">
                    <?php if (!empty($rekom['foto'])): ?>
                    <div class="card-image">
                        <img src="../uploads/<?php echo htmlspecialchars($rekom['foto']); ?>" alt="<?php echo htmlspecialchars($rekom['judul']); ?>">
                    </div>
                    <?php else: ?>
                    <div class="no-image">📄</div>
                    <?php endif; ?>
                    <div class="card-content">
                        <h4><?php echo htmlspecialchars($rekom['judul']); ?></h4>
                        <div class="penulis">
                            <span><?php echo strtoupper(substr($rekom['penulis'], 0, 1)); ?></span>
                            <?php echo htmlspecialchars($rekom['penulis']); ?>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <script>
        function shareMading() {
            if (navigator.share) {
                navigator.share({
                    title: '<?php echo htmlspecialchars($row['judul']); ?>',
                    text: 'Baca mading menarik ini di Mading Digital SMKN 1 Banjar',
                    url: window.location.href
                }).catch(console.error);
            } else {
                navigator.clipboard.writeText(window.location.href).then(() => {
                    alert('Link berhasil disalin!');
                }).catch(() => {
                    alert('Gagal menyalin link');
                });
            }
        }

        // Animasi scroll halus
        window.addEventListener('scroll', function() {
            const card = document.querySelector('.detail-card');
            if (card) {
                const scrolled = window.pageYOffset;
                const rate = Math.min(scrolled * 0.05, 20);
                card.style.transform = `translateY(${rate * 0.1}px)`;
            }
        });
    </script>
</body>
</html>