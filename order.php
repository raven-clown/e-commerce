<?php
session_start();
include("./component/connectdatabase.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$status_labels = [
    '0' => 'รอชำระเงิน',
    '1' => 'ตรวจสอบ',
    '2' => 'ชำระเงินแล้ว',
    '3' => 'กำลังผลิตสินค้า',
    '4' => 'ส่งสินค้า',
    '5' => 'ยกเลิก'
];
$badge_colors = [
    '0' => 'info',
    '1' => 'warning',
    '2' => 'success',
    '3' => 'primary',
    '4' => 'secondary',
    '5' => 'danger'
];

$orders = $order_obj->getUserOrders($user_id);
?>

<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<title>รายการสั่งซื้อของฉัน</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Prompt:wght@400;600&display=swap" rel="stylesheet">
<link href="./assets/css/style.css" rel="stylesheet" />
<style>
.modal-dialog { max-width: 700px; margin: 1.75rem auto; }
</style>
</head>
<body>

<?php include('./component/navbar.php'); ?>

<div class="container mt-5 py-5">
  <h3 class="my-4 fw-bold">รายการสั่งซื้อของฉัน</h3>

  <?php if (count($orders) > 0): ?>
    <?php foreach ($orders as $order): ?>
      <div class="card mb-4 shadow-sm border-0">
        <div class="card-header bg-dark text-white d-flex justify-content-between">
          <span>รหัสออเดอร์ #<?= $order['id'] ?></span>
          <span>วันที่: <?= $order['created_at'] ?></span>
        </div>
        <div class="card-body">
          <table class="table align-middle">
            <thead>
              <tr>
                <th>สินค้า</th>
                <th width="120">ราคา</th>
                <th width="100">จำนวน</th>
                <th width="120">รวม</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($order['items'] as $item): ?>
                <tr>
                  <td class="d-flex align-items-center">
                    <img src="assets/image/product/<?= htmlspecialchars($item['image']) ?>"
                          width="80" height="80" class="rounded border me-3" style="object-fit: contain;">
                    <?= htmlspecialchars($item['name']) ?>
                  </td>
                  <td>฿<?= number_format($item['price'], 2) ?></td>
                  <td><?= $item['quantity'] ?></td>
                  <td>฿<?= number_format($item['price'] * $item['quantity'], 2) ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
          <div class="d-flex justify-content-between align-items-center">
            <b>ยอดรวม: ฿<?= number_format($order['total_amount'], 2) ?></b>
            <span class="badge bg-<?= $badge_colors[$order['status']] ?> fs-6"><?= $status_labels[$order['status']] ?></span>
          </div>

<?php if($order['status'] == '0'): ?>
  <button class="btn btn-primary mt-3 fw-bold" data-bs-toggle="modal" data-bs-target="#paymentModal<?= $order['id'] ?>">
    <i class="fas fa-money-bill-wave"></i> แจ้งชำระเงิน
  </button>

  <div class="modal fade" id="paymentModal<?= $order['id'] ?>" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title">แจ้งชำระเงินสำหรับออเดอร์ #<?= $order['id'] ?></h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <?php
            $user_info = $user_obj->getById($user_id);
          ?>
          <form method="post" action="payment_confirm.php" enctype="multipart/form-data">
            <input type="hidden" name="orde_id" value="<?= $order['id'] ?>">
            <div class="row mb-2">
              <div class="col-md-6">
                <label class="form-label">ชื่อ</label>
                <input type="text" class="form-control" name="user_fname" value="<?= htmlspecialchars($user_info['first_name']) ?>" required>
              </div>
              <div class="col-md-6">
                <label class="form-label">นามสกุล</label>
                <input type="text" class="form-control" name="user_lname" value="<?= htmlspecialchars($user_info['last_name']) ?>" required>
              </div>
            </div>
            <div class="mb-2">
              <label class="form-label">อีเมล</label>
              <input type="email" class="form-control" value="<?= htmlspecialchars($user_info['email']) ?>" readonly>
            </div>
            <div class="mb-2">
              <label class="form-label">เบอร์โทรศัพท์</label>
              <input type="text" class="form-control" name="user_tel" value="<?= htmlspecialchars($user_info['phone']) ?>" required>
            </div>
            <div class="mb-2">
              <label class="form-label">ที่อยู่</label>
              <textarea class="form-control" name="user_address" rows="2" required><?= htmlspecialchars($user_info['address']) ?></textarea>
            </div>

            <h6 class="mt-4 mb-2 fw-bold text-secondary">ออเดอร์สรุปยอด</h6>
            <div class="alert alert-secondary text-center fs-5">
              <b>ยอดเงินที่ต้องชำระ: ฿<?= number_format($order['total_amount'], 2) ?></b>
            </div>

            <div class="text-center mb-4 p-3 border rounded bg-light">
              <h5 class="fw-bold mb-3 text-primary">สแกนเพื่อชำระเงิน</h5>
              <img src="silppay.jpg" alt="QR ชำระเงิน" class="img-fluid rounded border mb-3" style="max-height:200px; obj-fit:contain;">
              <p class="mb-1"><b>เลขบัญชี:</b> 4250641812</p>
              <p class="mb-1"><b>ธนาคาร:</b> ไทยพาณิชย์ (SCB)</p>
              <p class="mb-0"><b>ชื่อบัญชี:</b> สุรศักดิ์ จำนงค์ภักดิ์</p>
            </div>

            <div class="mb-3">
              <label class="form-label fw-bold">อัปโหลดหลักฐานการโอน <span class="text-danger">*</span></label>
              <input type="file" class="form-control slipInput" name="orde_slip" accept="image/jpeg, image/png, image/jpg" required>
              <small class="text-muted">รองรับไฟล์ JPG, PNG ขนาดไม่เกิน 5MB</small>
              <div class="d-flex justify-content-center">
                <img class="img-fluid rounded border slipPreview d-none mt-2 shadow-sm" src="#" alt="Preview" style="max-height:250px;">
              </div>
            </div>
            <div class="row mb-3">
              <div class="col-md-6">
                <label class="form-label fw-bold">ธนาคารที่โอนเข้า <span class="text-danger">*</span></label>
                <select class="form-select" name="bank_name" required>
                    <option value="" disabled selected>เลือกธนาคาร...</option>
                    <option value="SCB">ไทยพาณิชย์ (SCB)</option>
                    <option value="KBANK">กสิกรไทย (KBANK)</option>
                    <option value="BBL">กรุงเทพ (BBL)</option>
                    <option value="KTB">กรุงไทย (KTB)</option>
                    <option value="BAY">กรุงศรีอยุธยา (BAY)</option>
                    <option value="PromptPay">พร้อมเพย์ (PromptPay)</option>
                </select>
              </div>
              <div class="col-md-6">
                <input type="hidden" name="amount" value="<?= $order['total_amount'] ?>">
              </div>
            </div>
            <div class="row mb-3">
              <div class="col-md-6">
                <label class="form-label fw-bold">วันที่โอน <span class="text-danger">*</span></label>
                <input type="date" class="form-control" name="transfer_date" required>
              </div>
              <div class="col-md-6">
                <label class="form-label fw-bold">เวลาที่โอน <span class="text-danger">*</span></label>
                <input type="time" class="form-control" name="transfer_time" required>
              </div>
            </div>
            <div class="mt-4">
              <button type="submit" class="btn btn-success w-100 btn-lg fw-bold"><i class="fas fa-check-circle"></i> ยืนยันข้อมูลการชำระเงิน</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

<?php else: ?>
  <button class="btn btn-secondary mt-3 fw-bold" data-bs-toggle="modal" data-bs-target="#detailModal<?= $order['id'] ?>">ดูรายละเอียด</button>

  <div class="modal fade" id="detailModal<?= $order['id'] ?>" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header bg-secondary text-white">
          <h5 class="modal-title">รายละเอียดการชำระเงิน #<?= $order['id'] ?></h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <?php
            $user_info = $user_obj->getById($user_id);
          ?>
          <h6 class="fw-bold">ข้อมูลผู้ใช้และที่อยู่จัดส่ง</h6>
          <p class="mb-1"><b>Name:</b> <?= htmlspecialchars($user_info['first_name']." ".$user_info['last_name']) ?></p>
          <p class="mb-1"><b>โทร:</b> <?= htmlspecialchars($user_info['phone']) ?></p>
          <p class="mb-3"><b>ที่อยู่รันคำสั่งซื้อ:</b> <br><span class="text-muted"><?= nl2br(htmlspecialchars($user_info['address'])) ?></span></p>

          <h6 class="mt-3 fw-bold">สินค้าในออเดอร์</h6>
          <table class="table table-bordered align-middle">
            <thead class="table-light">
              <tr>
                <th>สินค้า</th>
                <th width="80">จำนวน</th>
                <th width="120">ราคา</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($order['items'] as $item): ?>
              <tr>
                <td class="d-flex align-items-center">
                  <img src="assets/image/product/<?= htmlspecialchars($item['image']) ?>"
                       width="60" height="60" class="rounded border me-2" style="object-fit: contain;">
                  <?= htmlspecialchars($item['name']) ?>
                </td>
                <td><?= $item['quantity'] ?></td>
                <td>฿<?= number_format($item['price']*$item['quantity'],2) ?></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
          <div class="alert alert-info fs-5 text-center">
            <b>ยอดรวม: ฿<?= number_format($order['total_amount'],2) ?></b>
          </div>

          <?php if(!empty($order['slip_image'])): ?>
            <h6 class="fw-bold mt-4 border-bottom pb-2">รายละเอียดสลิปโอนเงิน</h6>
            <div class="text-center mb-3 p-3 bg-light rounded">
              <img src="assets/image/slip/<?= htmlspecialchars($order['slip_image']) ?>"
                   class="img-fluid rounded border shadow-sm mb-3" style="max-height:300px;">
              <p class="mb-1"><b>ธนาคาร:</b> <?= htmlspecialchars($order['bank_name']) ?></p>
              <p class="mb-1"><b>วันที่โอน:</b> <?= htmlspecialchars($order['transfer_date']) ?></p>
              <p class="mb-0"><b>เวลาโอน:</b> <?= htmlspecialchars($order['transfer_time']) ?></p>
            </div>
          <?php else: ?>
            <p class="text-center text-muted">ยังไม่มีหลักฐานการโอน หรืออัปโหลดไว้ก่อนการปรับปรุงระบบ</p>
          <?php endif; ?>

        </div>
      </div>
    </div>
  </div>
<?php endif; ?>

        </div>
      </div>
    <?php endforeach; ?>
  <?php else: ?>
    <div class="text-center py-5">
        <i class="fas fa-box-open fa-4x text-muted mb-3"></i>
        <h4 class="text-muted">คุณยังไม่มีรายการสั่งซื้อ</h4>
        <a href="product.php" class="btn btn-primary mt-3">เริ่มต้นช้อปปิ้งออนไลน์เลย</a>
    </div>
  <?php endif; ?>
</div>

<script>
document.addEventListener('change', function(e){
  if(e.target.classList.contains('slipInput')){
    const file = e.target.files[0];
    const preview = e.target.closest('.modal-body').querySelector('.slipPreview');
    if(file){
      const reader = new FileReader();
      reader.onload = function(ev){
        preview.src = ev.target.result;
        preview.classList.remove('d-none');
      }
      reader.readAsDataURL(file);
    } else {
      preview.src = '#';
      preview.classList.add('d-none');
    }
  }
});
</script>
<?php if (isset($_GET['placed']) && $_GET['placed'] == 1): ?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
Swal.fire({
    icon: 'success',
    title: 'Order placed',
    text: 'Your order was created. Please upload payment proof when ready.',
    confirmButtonText: 'OK'
}).then(() => {
    window.location.href = window.location.pathname;
});
</script>
<?php endif; ?>

<?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
Swal.fire({
    icon: 'success',
    title: 'ชำระเงินเรียบร้อย',
    text: 'ระบบได้รับข้อมูลการชำระเงินของคุณเรียบร้อยแล้ว กรุณารอการตรวจสอบสักครู่',
    confirmButtonText: 'ตกลง'
}).then(() => {
    window.location.href = window.location.pathname;
});
</script>
<?php endif; ?>

<?php if (isset($_GET['error'])): ?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
Swal.fire({
    icon: 'error',
    title: 'ผิดพลาด',
    text: '<?= htmlspecialchars($_GET['error']) ?>',
    confirmButtonText: 'ตกลง'
}).then(() => {
    window.location.href = window.location.pathname;
});
</script>
<?php endif; ?>

</body>
</html>
