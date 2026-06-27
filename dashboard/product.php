<?php
session_start();
include('../component/connectdatabase.php');

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM products WHERE id = :id");
    $stmt->execute(['id' => $id]);
    header("Location: product.php");
    exit();
}

if (isset($_POST['add_product'])) {
    $name     = $_POST['prod_name'];
    $details  = $_POST['prod_details'];
    $price    = intval($_POST['prod_price']);
    $max      = intval($_POST['prod_max']);

    $img = "";
    if (!empty($_FILES['prod_img']['name'])) {
        $ext = pathinfo($_FILES['prod_img']['name'], PATHINFO_EXTENSION);
        $img = "prod_" . time() . "_" . uniqid() . "." . $ext;
        move_uploaded_file($_FILES['prod_img']['tmp_name'], "../assets/image/product/" . $img);
    } else {
        $img = "default.png";
    }

    $sql = "INSERT INTO products (name, description, price, stock, image)
            VALUES (:name, :desc, :price, :stock, :img)";
    $stmt = $conn->prepare($sql);
    $stmt->execute([
        'name' => $name, 'desc' => $details, 'price' => $price, 'stock' => $max, 'img' => $img
    ]);
    header("Location: product.php");
    exit();
}

if (isset($_POST['edit_product'])) {
    $id       = intval($_POST['prod_id']);
    $name     = $_POST['prod_name'];
    $details  = $_POST['prod_details'];
    $price    = intval($_POST['prod_price']);
    $max      = intval($_POST['prod_max']);

    $sql = "UPDATE products SET
                name = :name,
                description = :desc,
                price = :price,
                stock = :stock";

    $params = [
        'name' => $name, 'desc' => $details, 'price' => $price, 'stock' => $max, 'id' => $id
    ];

    if (!empty($_FILES['prod_img']['name'])) {
        $ext = pathinfo($_FILES['prod_img']['name'], PATHINFO_EXTENSION);
        $img = "prod_" . time() . "_" . uniqid() . "." . $ext;
        move_uploaded_file($_FILES['prod_img']['tmp_name'], "../assets/image/product/" . $img);

        $sql .= ", image = :img";
        $params['img'] = $img;
    }

    $sql .= " WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);

    header("Location: product.php");
    exit();
}

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$sql = "SELECT * FROM products
        WHERE name LIKE :search
        OR description LIKE :search
        ORDER BY id DESC";
$stmt = $conn->prepare($sql);
$stmt->execute(['search' => "%$search%"]);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

$userRole = $_SESSION['user_role'];
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>จัดการสินค้า</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body>
<div class="d-flex">
    <div class="d-flex flex-column p-3 bg-dark text-white" style="width: 250px; height:100vh;">
        <h4 class="text-center mb-4">Dashboard</h4>
        <ul class="nav nav-pills flex-column mb-auto">
            <?php if ($userRole === 'admin'): ?>
                <li class="nav-item"><a href="user.php" class="nav-link text-white">👤 Users</a></li>
                <li><a href="product.php" class="nav-link text-white active">📦 Products</a></li>
                <li><a href="order.php" class="nav-link text-white">🛒 Orders</a></li>
            <?php endif; ?>
        </ul>
        <hr>
        <div class="text-center">
            <button id="logoutBtn" class="btn btn-danger btn-sm">Logout</button>
        </div>
    </div>

    <div class="container-fluid p-4" style="margin-left:20px;">
        <h3 class="mb-3">จัดการสินค้า</h3>

        <form class="d-flex mb-3" method="get">
            <input type="text" class="form-control me-2" name="search" placeholder="ค้นหาสินค้า" value="<?= htmlspecialchars($search) ?>">
            <button class="btn btn-primary">ค้นหา</button>
        </form>

        <button class="btn btn-success mb-3" data-bs-toggle="collapse" data-bs-target="#addForm">+ เพิ่มสินค้า</button>

        <div id="addForm" class="collapse mb-4">
            <div class="card card-body">
                <form method="post" enctype="multipart/form-data">
                    <input type="hidden" name="add_product" value="1">
                    <div class="mb-2">
                        <label>รูปภาพสินค้า</label>
                        <input type="file" name="prod_img" class="form-control" accept="image/*">
                    </div>
                    <div class="mb-2">
                        <input type="text" name="prod_name" class="form-control" placeholder="ชื่อสินค้า" required>
                    </div>
                    <div class="mb-2">
                        <textarea name="prod_details" class="form-control" placeholder="รายละเอียดสินค้า"></textarea>
                    </div>
                    <div class="row mb-2">
                        <div class="col"><input type="number" name="prod_price" class="form-control" placeholder="ราคา" required></div>
                        <div class="col"><input type="number" name="prod_max" class="form-control" placeholder="จำนวนสต๊อกสูงสุด" required></div>
                    </div>
                    <button class="btn btn-success">บันทึก</button>
                </form>
            </div>
        </div>

        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>รูป</th>
                    <th>ชื่อสินค้า</th>
                    <th>รายละเอียด</th>
                    <th>ราคา</th>
                    <th>สต๊อกสูงสุด</th>
                    <th>แก้ไข</th>
                    <th>ลบ</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($products) > 0): ?>
                    <?php foreach ($products as $row): ?>
                        <tr>
                            <td><?= $row['id'] ?></td>
                            <td>
                                <?php if ($row['image']): ?>
                                    <img src="../assets/image/product/<?= htmlspecialchars($row['image']) ?>" width="60" style="object-fit:contain; max-height:60px;">
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($row['name']) ?></td>
                            <td><?= htmlspecialchars($row['description']) ?></td>
                            <td><?= number_format($row['price']) ?> บาท</td>
                            <td><?= $row['stock'] ?></td>
                            <td style="width: 250px;">
                                <button class="btn btn-warning btn-sm mb-1" data-bs-toggle="collapse" data-bs-target="#edit<?= $row['id'] ?>">Edit</button>
                                <div id="edit<?= $row['id'] ?>" class="collapse mt-1">
                                        <form method="post" enctype="multipart/form-data">
                                            <input type="hidden" name="edit_product" value="1">
                                            <input type="hidden" name="prod_id" value="<?= $row['id'] ?>">
                                            <div class="mb-1"><input type="file" name="prod_img" class="form-control form-control-sm" accept="image/*"></div>
                                            <div class="mb-1"><input type="text" name="prod_name" value="<?= htmlspecialchars($row['name']) ?>" class="form-control form-control-sm" required></div>
                                            <div class="mb-1"><textarea name="prod_details" class="form-control form-control-sm"><?= htmlspecialchars($row['description']) ?></textarea></div>
                                            <div class="row mb-1">
                                                <div class="col"><input type="number" name="prod_price" value="<?= $row['price'] ?>" class="form-control form-control-sm" required></div>
                                                <div class="col"><input type="number" name="prod_max" value="<?= $row['stock'] ?>" class="form-control form-control-sm" required></div>
                                            </div>
                                            <button class="btn btn-success btn-sm w-100 mt-1">อัปเดต</button>
                                        </form>
                                </div>
                            </td>
                            <td><a href="product.php?delete=<?= $row['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('ยืนยันการลบสินค้านี้?')">Del</a></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="8" class="text-center">ไม่มีข้อมูลสินค้า</td></tr>
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
