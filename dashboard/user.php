<?php
session_start();
include('../component/connectdatabase.php');

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM users WHERE id = :id");
    $stmt->execute(['id' => $id]);
    header("Location: user.php");
    exit();
}

if (isset($_POST['add_user'])) {
    $username = $_POST['user_username'];
    $password = password_hash($_POST['user_password'], PASSWORD_DEFAULT);
    $fname    = $_POST['user_fname'];
    $lname    = $_POST['user_lname'];
    $email    = $_POST['user_email'];
    $tel      = $_POST['user_tel'];
    $address  = $_POST['user_address'];
    $role     = $_POST['user_rules'];

    $sql = "INSERT INTO users (username, password, first_name, last_name, email, phone, address, role)
            VALUES (:un, :pw, :fn, :ln, :em, :ph, :ad, :rl)";
    $stmt = $conn->prepare($sql);
    $stmt->execute([
        'un' => $username, 'pw' => $password, 'fn' => $fname, 'ln' => $lname,
        'em' => $email, 'ph' => $tel, 'ad' => $address, 'rl' => $role
    ]);
    header("Location: user.php");
    exit();
}

if (isset($_POST['edit_user'])) {
    $id       = intval($_POST['user_id']);
    $fname    = $_POST['user_fname'];
    $lname    = $_POST['user_lname'];
    $email    = $_POST['user_email'];
    $tel      = $_POST['user_tel'];
    $address  = $_POST['user_address'];
    $role     = $_POST['user_rules'];

    $sql = "UPDATE users SET
                first_name = :fn,
                last_name = :ln,
                email = :em,
                phone = :ph,
                address = :ad,
                role = :rl
            WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->execute([
        'fn' => $fname, 'ln' => $lname, 'em' => $email,
        'ph' => $tel, 'ad' => $address, 'rl' => $role, 'id' => $id
    ]);
    header("Location: user.php");
    exit();
}

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$sql = "SELECT * FROM users
        WHERE username LIKE :search
        OR first_name LIKE :search
        OR last_name LIKE :search";
$stmt = $conn->prepare($sql);
$stmt->execute(['search' => "%$search%"]);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

$userRole = $_SESSION['user_role'];
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>จัดการผู้ใช้</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body>
<div class="d-flex">
    <div class="d-flex flex-column p-3 bg-dark text-white" style="width: 250px; height:100vh;">
        <h4 class="text-center mb-4">Dashboard</h4>
        <ul class="nav nav-pills flex-column mb-auto">
            <?php if ($userRole === 'admin'): ?>
                <li class="nav-item"><a href="user.php" class="nav-link text-white active">👤 Users</a></li>
                <li><a href="product.php" class="nav-link text-white">📦 Products</a></li>
                <li><a href="order.php" class="nav-link text-white">🛒 Orders</a></li>
            <?php elseif ($userRole === 'factory'): ?>
                <li><a href="order_fa.php" class="nav-link text-white">🛒 Orders</a></li>
            <?php endif; ?>
        </ul>
        <hr>
        <div class="text-center">
            <button id="logoutBtn" class="btn btn-danger btn-sm">Logout</button>
        </div>
    </div>

    <div class="container-fluid p-4" style="margin-left:20px;">
        <h3 class="mb-3">จัดการข้อมูลผู้ใช้</h3>

        <form class="d-flex mb-3" method="get">
            <input type="text" class="form-control me-2" name="search" placeholder="ค้นหา Username / ชื่อ / นามสกุล" value="<?= htmlspecialchars($search) ?>">
            <button class="btn btn-primary">ค้นหา</button>
        </form>

        <button class="btn btn-success mb-3" data-bs-toggle="collapse" data-bs-target="#addForm">+ เพิ่มผู้ใช้</button>

        <div id="addForm" class="collapse mb-4">
            <div class="card card-body">
                <form method="post">
                    <input type="hidden" name="add_user" value="1">
                    <div class="row mb-2">
                        <div class="col"><input type="text" name="user_username" class="form-control" placeholder="Username" required></div>
                        <div class="col"><input type="password" name="user_password" class="form-control" placeholder="Password" required></div>
                    </div>
                    <div class="row mb-2">
                        <div class="col"><input type="text" name="user_fname" class="form-control" placeholder="ชื่อจริง" required></div>
                        <div class="col"><input type="text" name="user_lname" class="form-control" placeholder="นามสกุล" required></div>
                    </div>
                    <div class="row mb-2">
                        <div class="col"><input type="email" name="user_email" class="form-control" placeholder="Email" required></div>
                        <div class="col"><input type="text" name="user_tel" class="form-control" placeholder="เบอร์โทร" required></div>
                    </div>
                    <div class="mb-2"><textarea name="user_address" class="form-control" placeholder="ที่อยู่"></textarea></div>
                    <div class="mb-2">
                        <select name="user_rules" class="form-control">
                            <option value="member">Member</option>
                            <option value="factory">Factory</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                    <button class="btn btn-success">บันทึก</button>
                </form>
            </div>
        </div>

        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>ชื่อจริง</th>
                    <th>นามสกุล</th>
                    <th>Email</th>
                    <th>Tel</th>
                    <th>ที่อยู่</th>
                    <th>สิทธิ์</th>
                    <th>แก้ไข</th>
                    <th>ลบ</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($users) > 0): ?>
                    <?php foreach ($users as $row): ?>
                        <tr>
                            <td><?= $row['id'] ?></td>
                            <td><?= htmlspecialchars($row['username']) ?></td>
                            <td><?= htmlspecialchars($row['first_name']) ?></td>
                            <td><?= htmlspecialchars($row['last_name']) ?></td>
                            <td><?= htmlspecialchars($row['email']) ?></td>
                            <td><?= htmlspecialchars($row['phone']) ?></td>
                            <td><?= htmlspecialchars($row['address']) ?></td>
                            <td><?= $row['role'] ?></td>
                            <td>
                                <button class="btn btn-warning btn-sm" data-bs-toggle="collapse" data-bs-target="#edit<?= $row['id'] ?>">Edit</button>
                                <div id="edit<?= $row['id'] ?>" class="collapse mt-2 px-1">
                                        <form method="post">
                                            <input type="hidden" name="edit_user" value="1">
                                            <input type="hidden" name="user_id" value="<?= $row['id'] ?>">
                                            <div class="row mb-1">
                                                <div class="col"><input type="text" name="user_fname" value="<?= htmlspecialchars($row['first_name']) ?>" class="form-control form-control-sm" required></div>
                                                <div class="col"><input type="text" name="user_lname" value="<?= htmlspecialchars($row['last_name']) ?>" class="form-control form-control-sm" required></div>
                                            </div>
                                            <div class="row mb-1">
                                                <div class="col"><input type="email" name="user_email" value="<?= htmlspecialchars($row['email']) ?>" class="form-control form-control-sm" required></div>
                                                <div class="col"><input type="text" name="user_tel" value="<?= htmlspecialchars($row['phone']) ?>" class="form-control form-control-sm" required></div>
                                            </div>
                                            <div class="mb-1"><textarea name="user_address" class="form-control form-control-sm"><?= htmlspecialchars($row['address']) ?></textarea></div>
                                            <div class="mb-1">
                                                <select name="user_rules" class="form-control form-control-sm">
                                                    <option value="member" <?= $row['role']=="member"?"selected":"" ?>>Member</option>
                                                    <option value="factory" <?= $row['role']=="factory"?"selected":"" ?>>Factory</option>
                                                    <option value="admin" <?= $row['role']=="admin"?"selected":"" ?>>Admin</option>
                                                </select>
                                            </div>
                                            <button class="btn btn-success btn-sm mt-1">อัปเดต</button>
                                        </form>
                                </div>
                            </td>
                            <td><a href="user.php?delete=<?= $row['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('ยืนยันการลบผู้ใช้นี้?')">Del</a></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="10" class="text-center">ไม่มีข้อมูลผู้ใช้</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.getElementById("logoutBtn").addEventListener("click", function() {
    Swal.fire({
        title: "ยืนยันการออกจากระบบ?",
        text: "คุณต้องการออกจากระบบใช่หรือไม่",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "ใช่, ออกจากระบบ",
        cancelButtonText: "ยกเลิก"
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = "../logout.php";
        }
    });
});
</script>
</body>
</html>
