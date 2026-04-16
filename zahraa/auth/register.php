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
    <title>Daftar | Mading Digital SMK Negeri 1 Banjar</title>
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
            0%,100% { transform: translate(0,0) scale(1); }
            33% { transform: translate(30px,-30px) scale(1.1); }
            66% { transform: translate(-20px,20px) scale(0.9); }
        }
        .auth-container {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 480px;
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
            margin-bottom: 22px;
        }
        .form-group label {
            display: block;
            margin-bottom: 6px;
            color: #334155;
            font-weight: 500;
            font-size: 0.9rem;
        }
        .form-group input {
            width: 100%;
            padding: 12px 16px;
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
        .form-group small {
            display: block;
            margin-top: 5px;
            font-size: 0.7rem;
            color: #6c757d;
        }
        .password-strength {
            margin-top: 8px;
        }
        .strength-bar {
            height: 4px;
            background: #d9f0f7;
            border-radius: 2px;
            margin-top: 5px;
            overflow: hidden;
        }
        .strength-bar-fill {
            height: 100%;
            width: 0;
            transition: width 0.3s;
        }
        .strength-weak { background: #ff7675; }
        .strength-medium { background: #fdcb6e; }
        .strength-strong { background: #00b894; }
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
            margin-top: 10px;
            box-shadow: 0 4px 12px rgba(44,125,160,0.3);
        }
        .btn-auth:hover {
            transform: translateY(-2px);
            background: #2c7da0;
        }
        .btn-auth:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
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
            display: inline-block;
            padding: 5px 15px;
            border-radius: 30px;
            background: rgba(44,125,160,0.1);
        }
        .auth-links a:hover {
            background: #2c7da0;
            color: white;
        }
        .alert {
            padding: 12px 18px;
            border-radius: 14px;
            margin-bottom: 20px;
            font-size: 0.9rem;
            position: relative;
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
        }
        @media (max-width: 500px) {
            .auth-box { padding: 30px 20px; }
            .auth-header h1 { font-size: 1.6rem; }
            .auth-header h2 { font-size: 1.4rem; }
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
            <h2>Daftar Akun Baru</h2>
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
        <form action="proses_register.php" method="POST" id="registerForm">
            <div class="form-group">
                <label for="username">Nama Pengguna</label>
                <input type="text" id="username" name="username" 
                       placeholder="Minimal 3 karakter" required minlength="3">
                <small>Minimal 3 karakter</small>
            </div>
            <div class="form-group">
                <label for="password">Kata Sandi</label>
                <input type="password" id="password" name="password" 
                       placeholder="Minimal 6 karakter" required minlength="6">
                <div class="password-strength">
                    <div class="strength-bar">
                        <div class="strength-bar-fill" id="strengthBar"></div>
                    </div>
                    <small id="strengthText">Kekuatan kata sandi: -</small>
                </div>
            </div>
            <div class="form-group">
                <label for="confirm_password">Konfirmasi Kata Sandi</label>
                <input type="password" id="confirm_password" name="confirm_password" 
                       placeholder="Ketik ulang kata sandi" required minlength="6">
                <small id="matchMessage"></small>
            </div>
            <button type="submit" class="btn-auth" id="submitBtn">Daftar</button>
        </form>
        <div class="auth-links">
            <p>Sudah memiliki akun?</p>
            <a href="login.php">Masuk Sekarang</a>
        </div>
    </div>
</div>
<script>
    const password = document.getElementById('password');
    const confirmPassword = document.getElementById('confirm_password');
    const strengthBar = document.getElementById('strengthBar');
    const strengthText = document.getElementById('strengthText');
    const matchMessage = document.getElementById('matchMessage');
    const submitBtn = document.getElementById('submitBtn');

    function checkPasswordStrength() {
        const val = password.value;
        let strength = 0;
        if (val.length >= 6) strength += 25;
        if (val.match(/[a-z]+/)) strength += 25;
        if (val.match(/[A-Z]+/)) strength += 25;
        if (val.match(/[0-9]+/)) strength += 15;
        if (val.match(/[$@#&!]+/)) strength += 10;

        strengthBar.style.width = strength + '%';
        if (strength < 30) {
            strengthBar.className = 'strength-bar-fill strength-weak';
            strengthText.innerHTML = 'Kekuatan kata sandi: Lemah';
        } else if (strength < 60) {
            strengthBar.className = 'strength-bar-fill strength-medium';
            strengthText.innerHTML = 'Kekuatan kata sandi: Sedang';
        } else {
            strengthBar.className = 'strength-bar-fill strength-strong';
            strengthText.innerHTML = 'Kekuatan kata sandi: Kuat';
        }
    }

    function checkPasswordMatch() {
        if (confirmPassword.value === '') {
            matchMessage.innerHTML = '';
            submitBtn.disabled = false;
            return;
        }
        if (password.value === confirmPassword.value) {
            matchMessage.innerHTML = 'Kata sandi cocok';
            matchMessage.style.color = '#10b981';
            submitBtn.disabled = false;
        } else {
            matchMessage.innerHTML = 'Kata sandi tidak cocok';
            matchMessage.style.color = '#ef4444';
            submitBtn.disabled = true;
        }
    }

    password.addEventListener('input', function() {
        checkPasswordStrength();
        checkPasswordMatch();
    });
    confirmPassword.addEventListener('input', checkPasswordMatch);

    setTimeout(function() {
        const alert = document.querySelector('.alert');
        if (alert) {
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 300);
        }
    }, 5000);
</script>
</body>
</html>