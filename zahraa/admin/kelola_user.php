<?php
session_start();
include "../config/database.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$where = '';
if ($search) {
    $where = " WHERE username LIKE '%$search%' ";
}

$count_query = "SELECT COUNT(*) as total FROM users" . $where;
$count_result = mysqli_query($conn, $count_query);
$count_data = mysqli_fetch_assoc($count_result);
$total_data = $count_data['total'];
$total_pages = ceil($total_data / $limit);

$query = "SELECT * FROM users" . $where . " ORDER BY id DESC LIMIT $limit OFFSET $offset";
$result = mysqli_query($conn, $query);

// Hapus user
if (isset($_GET['hapus']) && isset($_GET['id'])) {
    $id_hapus = (int)$_GET['id'];
    if ($id_hapus != $_SESSION['user_id']) {
        mysqli_query($conn, "DELETE FROM users WHERE id = $id_hapus");
        $_SESSION['success'] = "User berhasil dihapus!";
    } else {
        $_SESSION['error'] = "Tidak bisa menghapus akun sendiri!";
    }
    header("Location: kelola_user.php");
    exit;
}

// Tambah user
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['tambah_user'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];
    $role = mysqli_real_escape_string($conn, $_POST['role']);
    if (!empty($username) && !empty($password)) {
        $cek = mysqli_query($conn, "SELECT * FROM users WHERE username = '$username'");
        if (mysqli_num_rows($cek) == 0) {
            mysqli_query($conn, "INSERT INTO users (username, password, role) VALUES ('$username', '$password', '$role')");
            $_SESSION['success'] = "User berhasil ditambahkan!";
        } else {
            $_SESSION['error'] = "Username sudah ada!";
        }
    } else {
        $_SESSION['error'] = "Username dan password wajib diisi!";
    }
    header("Location: kelola_user.php");
    exit;
}

// Edit user
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_user'])) {
    $id_edit = (int)$_POST['id'];
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $role = mysqli_real_escape_string($conn, $_POST['role']);
    if (!empty($username)) {
        $update = "UPDATE users SET username = '$username', role = '$role' WHERE id = $id_edit";
        if (!empty($_POST['password'])) {
            $update = "UPDATE users SET username = '$username', password = '{$_POST['password']}', role = '$role' WHERE id = $id_edit";
        }
        mysqli_query($conn, $update);
        $_SESSION['success'] = "User berhasil diupdate!";
    }
    header("Location: kelola_user.php");
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
    <title>Kelola User | Admin SMK Negeri 1 Banjar</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
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
        .btn-primary {
            background: var(--biru-utama);
            color: white;
            padding: 8px 18px;
            border-radius: 40px;
            border: none;
            cursor: pointer;
            font-size: 0.8rem;
            transition: 0.2s;
            display: inline-block;
            text-decoration: none;
        }
        .btn-primary:hover { background: var(--biru-gelap); transform: translateY(-2px); }
        .table-container {
            background: var(--putih);
            border-radius: 18px;
            padding: 20px;
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
        .search-bar form {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-bottom: 20px;
        }
        .search-bar input {
            flex: 1;
            padding: 10px 15px;
            border: 1px solid var(--abu-border);
            border-radius: 12px;
            min-width: 150px;
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
            border-bottom: 1px solid var(--abu-border);
        }
        .admin-table td {
            padding: 12px;
            border-bottom: 1px solid var(--abu-border);
            color: var(--abu-teks);
        }
        .admin-table tr:hover { background: var(--biru-sangat-muda); }
        .action-btns {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }
        .pagination {
            display: flex;
            gap: 8px;
            justify-content: center;
            margin-top: 20px;
            flex-wrap: wrap;
        }
        .page-link {
            padding: 6px 12px;
            background: white;
            color: var(--abu-teks);
            border-radius: 8px;
            text-decoration: none;
            border: 1px solid var(--abu-border);
        }
        .page-link:hover, .page-link.active { background: var(--biru-utama); color: white; }
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }
        .modal-content {
            background: white;
            border-radius: 20px;
            padding: 25px;
            width: 90%;
            max-width: 450px;
        }
        .modal-content input, .modal-content select {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid var(--abu-border);
            border-radius: 10px;
        }
        .alert {
            padding: 12px 20px;
            border-radius: 12px;
            margin-bottom: 20px;
        }
        .alert-success { background: #d1fae5; color: #065f46; }
        .alert-error { background: #fee2e2; color: #991b1b; }
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
            .admin-header { flex-direction: column; align-items: flex-start; }
            .table-header { flex-direction: column; align-items: flex-start; }
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
            <a href="kelola_user.php" class="active"><span>👥</span> Kelola User</a>
            <a href="laporan.php"><span>📄</span> Laporan</a>
            <div class="menu-label">Pengaturan</div>
            <a href="pengaturan.php"><span>⚙️</span> Pengaturan</a>
            <a href="../auth/logout.php" class="logout"><span>🚪</span> Keluar</a>
        </div>
    </div>
    <div class="admin-main">
        <div class="admin-header">
            <div class="header-title">
                <h2>Kelola User</h2>
                <p>Mengelola data pengguna sistem</p>
            </div>
            <button class="btn-primary" onclick="openTambahModal()">+ Tambah User</button>
        </div>
        <?php if ($success): ?><div class="alert alert-success"><?php echo $success; ?></div><?php endif; ?>
        <?php if ($error): ?><div class="alert alert-error"><?php echo $error; ?></div><?php endif; ?>
        <div class="table-container">
            <div class="search-bar">
                <form method="GET">
                    <input type="text" name="search" placeholder="Cari username..." value="<?php echo htmlspecialchars($search); ?>">
                    <button type="submit" class="btn-primary">Cari</button>
                    <?php if ($search): ?><a href="kelola_user.php" class="btn-primary" style="background:#94a3b8;">Reset</a><?php endif; ?>
                </form>
            </div>
            <div style="overflow-x: auto;">
                <table class="admin-table">
                    <thead><tr><th>ID</th><th>Username</th><th>Role</th><th>Tanggal Daftar</th><th>Aksi</th></tr></thead>
                    <tbody>
                        <?php while($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td><?php echo $row['id']; ?></td>
                            <td><?php echo htmlspecialchars($row['username']); ?></td>
                            <td><span style="background:<?php echo $row['role']=='admin'?'#2c7da0':'#61a5c2'; ?>; color:white; padding:4px 12px; border-radius:20px; font-size:0.7rem;"><?php echo $row['role']=='admin'?'Admin':'User'; ?></span></td>
                            <td><?php echo date('d/m/Y H:i', strtotime($row['created_at'])); ?></td>
                            <td class="action-btns">
                                <button onclick="openEditModal(<?php echo $row['id']; ?>, '<?php echo $row['username']; ?>', '<?php echo $row['role']; ?>')" class="btn-primary" style="padding:4px 12px;">Edit</button>
                                <?php if($row['id'] != $_SESSION['user_id']): ?>
                                <a href="?hapus=1&id=<?php echo $row['id']; ?>" onclick="return confirm('Yakin hapus user ini?')" class="btn-primary" style="background:#e74c3c; padding:4px 12px;">Hapus</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            <?php if($total_pages>1): ?>
            <div class="pagination">
                <?php for($i=1;$i<=$total_pages;$i++): ?>
                <a href="?page=<?php echo $i; ?><?php echo $search?'&search='.urlencode($search):''; ?>" class="page-link <?php echo $i==$page?'active':''; ?>"><?php echo $i; ?></a>
                <?php endfor; ?>
            </div>
            <?php endif; ?>
        </div>
        <div class="footer"><p>&copy; <?php echo date('Y'); ?> Mading Digital - SMK Negeri 1 Banjar</p></div>
    </div>
</div>
<div id="tambahModal" class="modal"><div class="modal-content"><h3>Tambah User Baru</h3><form method="POST"><input type="text" name="username" placeholder="Username" required><input type="password" name="password" placeholder="Password" required><select name="role"><option value="user">User</option><option value="admin">Admin</option></select><div style="display:flex; gap:10px; justify-content:flex-end;"><button type="button" class="btn-primary" style="background:#94a3b8;" onclick="closeTambahModal()">Batal</button><button type="submit" name="tambah_user" class="btn-primary">Simpan</button></div></form></div></div>
<div id="editModal" class="modal"><div class="modal-content"><h3>Edit User</h3><form method="POST"><input type="hidden" name="id" id="edit_id"><input type="text" name="username" id="edit_username" placeholder="Username" required><input type="password" name="password" placeholder="Password (kosongkan jika tidak diubah)"><select name="role" id="edit_role"><option value="user">User</option><option value="admin">Admin</option></select><div style="display:flex; gap:10px; justify-content:flex-end;"><button type="button" class="btn-primary" style="background:#94a3b8;" onclick="closeEditModal()">Batal</button><button type="submit" name="edit_user" class="btn-primary">Update</button></div></form></div></div>
<script>
function openTambahModal(){document.getElementById('tambahModal').style.display='flex';}
function closeTambahModal(){document.getElementById('tambahModal').style.display='none';}
function openEditModal(id,username,role){document.getElementById('edit_id').value=id;document.getElementById('edit_username').value=username;document.getElementById('edit_role').value=role;document.getElementById('editModal').style.display='flex';}
function closeEditModal(){document.getElementById('editModal').style.display='none';}
window.onclick=function(e){if(e.target.classList.contains('modal')) e.target.style.display='none';}
</script>
</body>
</html>