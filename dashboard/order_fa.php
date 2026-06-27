<?php
session_start();
include('../component/connectdatabase.php');

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 'factory') {
    header("Location: ../login.php");
    exit();
}

$userRole = $_SESSION['user_role'];

if ($userRole === 'admin') {
    $allowed_status = ['0','1','2','5'];
} elseif ($userRole === 'factory') {
    $allowed_status = ['3','4'];
} else {
    $allowed_status = [];
}

if(isset($_POST['update_order'])){
    $id = intval($_POST['orde_id']);
    $status = $_POST['orde_status'];

    if (in_array($status, $allowed_status)) {
        $stmt = $conn->prepare("UPDATE orders SET status = :st WHERE id = :id");
        $stmt->execute(['st' => $status, 'id' => $id]);
    }

    header("Location: order_fa.php");
    exit();
}

$sql = "
    SELECT o.*,
           u.username,
           u.first_name,
           u.last_name,
           u.email,
           u.phone,
           u.address
    FROM orders o
    JOIN users u ON o.user_id = u.id
    ORDER BY o.id DESC
";
$stmt = $conn->prepare($sql);
$stmt->execute();
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

$status_text = [
    '0'=>'รอชำระเงิน',
    '1'=>'รอตรวจสอบ',
    '2'=>'ชำระเงินแล้ว (รอดำเนินการ)',
    '3'=>'กำลังผลิตสินค้า',
    '4'=>'จัดส่งสินค้าแล้ว',
    '5'=>'ยกเลิกออเดอร์'
];
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>จัดการออเดอร์ (ส่วนโรงงานผลิต)</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body>
<div class="d-flex">
    <div class="d-flex flex-column p-3 bg-dark text-white" style="width: 250px; height:auto; min-height:100vh;">
        <h4 class="text-center mb-4">Dashboard</h4>
        <ul class="nav nav-pills flex-column mb-auto">
            <li><a href="order_fa.php" class="nav-link text-white active">🏭 Orders for Factory</a></li>
        </ul>
        <hr>
        <div class="text-center">
            <button id="logoutBtn" class="btn btn-danger btn-sm">Logout</button>
        </div>
    </div>

    <div class="container-fluid p-4" style="margin-left:20px;">
        <h3 class="mb-3">จัดการคิวผลิต/จัดส่งสินค้า (สำหรับพนักงานโรงงาน)</h3>

        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>ID ออเดอร์</th>
                    <th>ชื่อผู้ใช้</th>
                    <th>ชื่อ-นามสกุล</th>
                    <th>อีเมล</th>
                    <th>เบอร์โทร</th>
                    <th>วันที่สั่งซื้อ</th>
                    <th>สถานะการผลิต</th>
                    <th>จัดการ</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($orders as $row): ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td><?= htmlspecialchars($row['username']) ?></td>
                    <td><?= htmlspecialchars($row['first_name'].' '.$row['last_name']) ?></td>
                    <td><?= htmlspecialchars($row['email']) ?></td>
                    <td><?= htmlspecialchars($row['phone']) ?></td>
                    <td><?= $row['created_at'] ?></td>
                    <td>
                        <?= isset($status_text[$row['status']]) ? $status_text[$row['status']] : 'ไม่ระบุ'; ?>
                    </td>
                    <td>
                        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#editModal<?= $row['id'] ?>">รายละเอียด</button>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if(empty($orders)): ?>
                    <tr><td colspan="8" class="text-center">ยังไม่มีข้อมูลออเดอร์ในระบบ</td></tr>
                <?php endif; ?>
            </tbody>
        </table>

        <?php foreach($orders as $row): ?>
        <div class="modal fade" id="editModal<?= $row['id'] ?>" tabindex="-1" aria-hidden="true">
          <div class="modal-dialog modal-xl">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title">รายละเอียดข้อมูล #<?= $row['id'] ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
              </div>
              <div class="modal-body">
                <div class="row">
                  <div class="col-md-6">
                    <h6 class="fw-bold">ข้อมูลลูกค้าสำหรับจัดส่ง</h6>
                    <p class="mb-1"><strong>ชื่อลูกค้า:</strong> <?= htmlspecialchars($row['first_name'].' '.$row['last_name']) ?></p>
                    <p class="mb-1"><strong>โทร:</strong> <?= htmlspecialchars($row['phone']) ?></p>
                    <p class="mb-1"><strong>ที่อยู่ปัจจุบัน:</strong> <?= nl2br(htmlspecialchars($row['address'])) ?></p>
                  </div>
                  <div class="col-md-6">
                    <h6 class="fw-bold">ข้อมูลเพิ่มเติม</h6>
                    <p class="mb-1"><strong>สร้างคำสั่งซื้อเมื่อ:</strong> <?= $row['created_at'] ?></p>
                  </div>
                </div>
                <hr>

                <h6 class="fw-bold">สินค้าที่ต้องจัดเตรียมและผลิต</h6>
                <?php
                $order_id = $row['id'];
                $sqlItems = "SELECT i.price, i.quantity, p.name, p.image
                             FROM order_items i
                             JOIN products p ON i.product_id = p.id
                             WHERE i.order_id = :oid";
                $stmtItems = $conn->prepare($sqlItems);
                $stmtItems->execute(['oid' => $order_id]);
                $items = $stmtItems->fetchAll(PDO::FETCH_ASSOC);
                ?>
                <table class="table table-sm table-bordered">
                  <thead>
                    <tr>
                      <th>สินค้า</th>
                      <th>รูปสินค้าอ้างอิง</th>
                      <th>จำนวน</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach($items as $item): ?>
                    <tr>
                      <td class="fs-5"><?= htmlspecialchars($item['name']) ?></td>
                      <td>
                        <?php if(!empty($item['image'])): ?>
                            <img src="../assets/image/product/<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>" class="img-thumbnail" style="max-height:80px;">
                        <?php endif; ?>
                      </td>
                      <td class="fs-4 text-primary fw-bold"><?= $item['quantity'] ?></td>
                    </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
                <hr>

                <form method="post">
                  <input type="hidden" name="orde_id" value="<?= $row['id'] ?>">
                  <div class="mb-3">
                    <label class="fw-bold">ควบคุมคิวการผลิต</label>
                    <?php if(!empty($allowed_status)): ?>
                    <select name="orde_status" class="form-select">
                        <?php foreach($allowed_status as $key): ?>
                            <option value="<?= $key ?>" <?= ($row['status']==$key)?'selected':'' ?>>
                                <?= $status_text[$key] ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php else: ?>
                    <input type="text" class="form-control" value="<?= $status_text[$row['status']] ?>" readonly>
                    <?php endif; ?>
                  </div>
                  <button type="submit" name="update_order" class="btn btn-success w-100 fw-bold">อัปเดตความคืบหน้า</button>
                </form>

              </div>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
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
