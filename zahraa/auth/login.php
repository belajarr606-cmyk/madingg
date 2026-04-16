<?php
session_start();
include "../config/database.php";

if (isset($_SESSION['role'])) {
    if ($_SESSION['role'] == 'admin') {
        header("Location: ../admin/dashboard.php");
    } else {
        header("Location: ../user/dashboard.php");
    }
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
    <title>Masuk | Mading Digital SMK Negeri 1 Banjar</title>
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
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
        }

        /* Dekorasi latar belakang */
        .decoration {
            position: fixed;
            top: 0;
            left: 0;
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

        /* Kotak login */
        .auth-container {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 450px;
        }
        .auth-box {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 30px;
            box-shadow: 0 20px 60px rgba(44, 125, 160, 0.15);
            padding: 40px;
            border: 1px solid rgba(255, 255, 255, 0.5);
            animation: slideUp 0.6s ease-out;
        }
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .auth-header {
            text-align: center;
            margin-bottom: 35px;
        }
        .auth-header h1 {
            font-size: 2rem;
            color: #2c7da0;
            margin-bottom: 10px;
            text-shadow: 2px 2px 4px rgba(44,125,160,0.1);
        }
        .auth-header h2 {
            font-size: 1.6rem;
            color: #334155;
            margin-bottom: 10px;
            position: relative;
            display: inline-block;
        }
        .auth-header h2::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 50px;
            height: 3px;
            background: linear-gradient(90deg, #61a5c2, #2c7da0);
            border-radius: 2px;
        }
        .school-name {
            font-size: 0.9rem;
            color: #6c757d;
            letter-spacing: 2px;
            margin-top: 15px;
        }

        .form-group {
            margin-bottom: 25px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #334155;
            font-weight: 500;
            font-size: 0.9rem;
        }
        .form-group input {
            width: 100%;
            padding: 14px 18px;
            border: 2px solid #d9f0f7;
            border-radius: 15px;
            font-size: 1rem;
            transition: 0.2s;
            background: #ffffff;
            outline: none;
        }
        .form-group input:focus {
            border-color: #61a5c2;
            box-shadow: 0 0 0 3px rgba(97,165,194,0.2);
        }
        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 25px;
        }
        .checkbox-group input {
            width: 18px;
            height: 18px;
            margin: 0;
        }
        .checkbox-group label {
            margin: 0;
            font-size: 0.9rem;
            color: #334155;
        }

        .btn-auth {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #61a5c2, #2c7da0);
            color: white;
            border: none;
            border-radius: 40px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: 0.3s;
            box-shadow: 0 4px 12px rgba(44,125,160,0.3);
        }
        .btn-auth:hover {
            transform: translateY(-2px);
            background: #2c7da0;
            box-shadow: 0 6px 16px rgba(44,125,160,0.4);
        }

        .auth-links {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 2px dashed #d9f0f7;
        }
        .auth-links p {
            color: #6c757d;
            margin-bottom: 10px;
            font-size: 0.9rem;
        }
        .auth-links a {
            color: #2c7da0;
            text-decoration: none;
            font-weight: 600;
            transition: 0.2s;
            display: inline-block;
            padding: 5px 15px;
            border-radius: 30px;
            background: rgba(44,125,160,0.1);
        }
        .auth-links a:hover {
            background: #2c7da0;
            color: white;
            transform: scale(1.02);
        }

        /* Alert */
        .alert {
            padding: 12px 18px;
            border-radius: 14px;
            margin-bottom: 20px;
            font-size: 0.9rem;
            position: relative;
            padding-right: 35px;
        }
        .alert-error {
            background: #ffe5e5;
            color: #c0392b;
            border-left: 4px solid #c0392b;
        }
        .alert-success {
            background: #e5ffe5;
            color: #27ae60;
            border-left: 4px solid #27ae60;
        }
        .close-alert {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            font-size: 1.2rem;
            opacity: 0.6;
        }
        .close-alert:hover { opacity: 1; }

        @media (max-width: 500px) {
            .auth-box { padding: 30px 20px; }
            .auth-header h1 { font-size: 1.6rem; }
            .auth-header h2 { font-size: 1.4rem; }
            .form-group input { padding: 12px 16px; }
        }
    </style>
</head>
<body>
<div class="decoration">
    <span></span>
    <span></span>
    <span></span>
</div>

<div class="auth-container">
    <div class="auth-box">
        <div class="auth-header">
            <h1>Mading Digital</h1>
            <h2>Selamat Datang Kembali</h2>
            <div class="school-name">SMK Negeri 1 Banjar</div>
        </div>

        <?php if ($error): ?>
        <div class="alert alert-error">
            <?php echo htmlspecialchars($error); ?>
            <span class="close-alert" onclick="this.parentElement.remove()">&times;</span>
        </div>
        <?php endif; ?>

        <?php if ($success): ?>
        <div class="alert alert-success">
            <?php echo htmlspecialchars($success); ?>
            <span class="close-alert" onclick="this.parentElement.remove()">&times;</span>
        </div>
        <?php endif; ?>

        <form action="proses_login.php" method="POST">
            <div class="form-group">
                <label for="username">Nama Pengguna</label>
                <input type="text" id="username" name="username" 
                       placeholder="Masukkan nama pengguna" 
                       value="<?php echo isset($_COOKIE['username']) ? htmlspecialchars($_COOKIE['username']) : ''; ?>"
                       required autofocus>
            </div>

            <div class="form-group">
                <label for="password">Kata Sandi</label>
                <input type="password" id="password" name="password" 
                       placeholder="Masukkan kata sandi" required>
            </div>

            <div class="checkbox-group">
                <input type="checkbox" id="remember" name="remember">
                <label for="remember">Ingat saya</label>
            </div>

            <button type="submit" class="btn-auth">Masuk</button>
        </form>

        <div class="auth-links">
            <p>Belum memiliki akun?</p>
            <a href="register.php">Daftar Sekarang</a>
        </div>
    </div>
</div>

<script>
    setTimeout(function() {
        let alert = document.querySelector('.alert');
        if (alert) {
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 300);
        }
    }, 5000);
</script>
</body>
</html>