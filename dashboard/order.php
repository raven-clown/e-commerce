<?php
session_start();
include('../component/connectdatabase.php');

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

if(isset($_GET['delete_id'])){
    $delete_id = intval($_GET['delete_id']);

    $conn->beginTransaction();
    try {
        $stmt_items = $conn->prepare("DELETE FROM order_items WHERE order_id = :id");
        $stmt_items->execute(['id' => $delete_id]);

        $stmt_order = $conn->prepare("DELETE FROM orders WHERE id = :id");
        $stmt_order->execute(['id' => $delete_id]);

        $conn->commit();
    } catch(Exception $e) {
        $conn->rollBack();
    }

    header("Location: order.php");
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

    header("Location: order.php");
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
    <title>จัดการข้อมูลออเดอร์</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body>
<div class="d-flex">
    <div class="d-flex flex-column p-3 bg-dark text-white" style="width: 250px; height:auto; min-height:100vh;">
        <h4 class="text-center mb-4">Dashboard</h4>
        <ul class="nav nav-pills flex-column mb-auto">
            <?php if ($userRole === 'admin'): ?>
                <li class="nav-item"><a href="user.php" class="nav-link text-white">👤 Users</a></li>
                <li><a href="product.php" class="nav-link text-white">📦 Products</a></li>
                <li><a href="order.php" class="nav-link text-white active">🛒 Orders</a></li>
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
        <h3 class="mb-3">จัดการข้อมูลออเดอร์</h3>

        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>ID ออเดอร์</th>
                    <th>ชื่อผู้ใช้</th>
                    <th>ชื่อ-นามสกุล</th>
                    <th>อีเมล</th>
                    <th>เบอร์โทร</th>
                    <th>วันที่สั่งซื้อ</th>
                    <th>สถานะ</th>
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
                        <a href="order.php?delete_id=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('ยืนยันการลบออเดอร์นี้และรายการสินค้าภายในทั้งหมด?')">ลบ</a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if(empty($orders)): ?>
                    <tr><td colspan="8" class="text-center">ยังไม่มีข้อมูลซื้อมาระบบ</td></tr>
                <?php endif; ?>
            </tbody>
        </table>

        <?php foreach($orders as $row): ?>
        <div class="modal fade" id="editModal<?= $row['id'] ?>" tabindex="-1" aria-hidden="true">
          <div class="modal-dialog modal-xl">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title">รายละเอียดออเดอร์ #<?= $row['id'] ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
              </div>
              <div class="modal-body">
                <div class="row">
                  <div class="col-md-6">
                    <h6 class="fw-bold">ข้อมูลผู้ใช้</h6>
                    <p class="mb-1"><strong>ชื่อผู้ใช้:</strong> <?= htmlspecialchars($row['username']) ?></p>
                    <p class="mb-1"><strong>ชื่อ-นามสกุล:</strong> <?= htmlspecialchars($row['first_name'].' '.$row['last_name']) ?></p>
                    <p class="mb-1"><strong>อีเมล:</strong> <?= htmlspecialchars($row['email']) ?></p>
                    <p class="mb-1"><strong>เบอร์โทร:</strong> <?= htmlspecialchars($row['phone']) ?></p>
                    <p class="mb-1"><strong>ที่อยู่:</strong> <?= nl2br(htmlspecialchars($row['address'])) ?></p>
                  </div>
                  <div class="col-md-6">
                    <h6 class="fw-bold">ข้อมูลการโอนเงิน</h6>
                    <?php if(!empty($row['slip_image'])): ?>
                        <p class="mb-1"><strong>ธนาคาร:</strong> <?= htmlspecialchars($row['bank_name']) ?></p>
                        <p class="mb-1"><strong>วันที่โอน:</strong> <?= htmlspecialchars($row['transfer_date']) ?></p>
                        <p class="mb-1"><strong>เวลาโอน:</strong> <?= htmlspecialchars($row['transfer_time']) ?></p>
                        <p class="mb-1"><strong>ยอดรวม:</strong> ฿<?= number_format($row['total_amount'], 2) ?></p>
                        <div class="mb-3">
                          <label class="form-label fw-bold">หลักฐานการโอนเงิน:</label><br>
                          <img src="../assets/image/slip/<?= htmlspecialchars($row['slip_image']) ?>" alt="Slip" class="img-fluid border rounded" style="max-height:250px;">
                        </div>
                    <?php else: ?>
                        <p class="text-muted">ผู้ใช้งานยังไม่ได้อัปโหลดหลักฐานการโอนเงิน</p>
                    <?php endif; ?>
                  </div>
                </div>
                <hr>

                <h6 class="fw-bold">รายการสินค้า</h6>
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
                      <th>รูปสินค้า</th>
                      <th>ราคา/ชิ้น</th>
                      <th>จำนวน</th>
                      <th>ราคารวม</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php
                    $total = 0;
                    foreach($items as $item):
                      $sum = $item['price'] * $item['quantity'];
                      $total += $sum;
                    ?>
                    <tr>
                      <td><?= htmlspecialchars($item['name']) ?></td>
                      <td>
                        <?php if(!empty($item['image'])): ?>
                            <img src="../assets/image/product/<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>" class="img-thumbnail" style="max-height:80px;">
                        <?php endif; ?>
                      </td>
                      <td>฿<?= number_format($item['price'], 2) ?></td>
                      <td><?= $item['quantity'] ?></td>
                      <td>฿<?= number_format($sum, 2) ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <tr>
                      <td colspan="4" class="text-end fw-bold">ระบบคำนวณยอดรวมสุทธิ</td>
                      <td class="fw-bold text-danger">฿<?= number_format($total, 2) ?></td>
                    </tr>
                  </tbody>
                </table>
                <hr>

                <form method="post">
                  <input type="hidden" name="orde_id" value="<?= $row['id'] ?>">
                  <div class="mb-3">
                    <label class="fw-bold">ควบคุมสถานะ</label>
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
                  <button type="submit" name="update_order" class="btn btn-success w-100 fw-bold">บันทึกการปรับปรุงสถานะ</button>
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
