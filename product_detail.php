<?php
session_start();
include('./component/connectdatabase.php');

if (!isset($_GET['prod_id']) || empty($_GET['prod_id'])) {
    echo "ไม่พบสินค้านี้";
    exit;
}

$prod_id = $_GET['prod_id'];

$product = $product_obj->getById($prod_id);

if (!$product) {
    echo "ไม่พบสินค้ารายการนี้";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'addcart') {
    header('Content-Type: application/json');
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(["status" => "error", "message" => "not_logged_in"]);
        exit;
    }

    $user_id = $_SESSION['user_id'];
    $product_id = $_POST['prod_id'];
    $quantity = $_POST['prod_qty'];

    $result = $cart_obj->addItem($user_id, $product_id, $quantity);

    if ($result === "success") {
        echo json_encode(["status" => "success", "message" => "เพิ่มสินค้าลงตะกร้าสำเร็จ"]);
    } elseif ($result === "stock_error") {
        echo json_encode(["status" => "error", "message" => "สินค้าในสต๊อกไม่เพียงพอ"]);
    } else {
        echo json_encode(["status" => "error", "message" => "product_not_found"]);
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รายละเอียดสินค้า - <?= htmlspecialchars($product['name']); ?></title>
    <link rel="icon" href="./assets/image/icon.png" type="image/png">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src='https://code.jquery.com/jquery-3.7.1.js' integrity='sha256-eKhayi8LEQwp4NKxN+CfCh+3qOVUtJn3QNZ0TciWLP4=' crossorigin='anonymous'></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@400;600&display=swap" rel="stylesheet">
    <link href="./assets/css/style.css" rel="stylesheet" />
    <style>
        .product-image {
            width: 100%;
            height: auto;
            max-height: 500px;
            object-fit: contain;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <?php include('./component/navbar.php'); ?>

    <div class="container py-5 mt-5">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">หน้าแรก</a></li>
                <li class="breadcrumb-item"><a href="product.php">สินค้าทั้งหมด</a></li>
                <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($product['name']); ?></li>
            </ol>
        </nav>

        <div class="row">
            <div class="col-md-6 mb-4">
                <img src="./assets/image/product/<?= htmlspecialchars($product['image']); ?>" alt="Product Image" class="product-image">
            </div>

            <div class="col-md-6">
                <h2 class="fw-bold mb-3"><?= htmlspecialchars($product['name']); ?></h2>
                <h4 class="text-danger mb-4">ราคา <?= number_format($product['price']); ?> บาท</h4>

                <h5 class="fw-bold">รายละเอียดสินค้า</h5>
                <p class="text-muted" style="white-space: pre-wrap;"><?= htmlspecialchars($product['description']); ?></p>

                <div class="mb-4">
                    <span class="fw-bold">สถานะ:</span>
                    <?php if ($product['stock'] > 0): ?>
                        <span class="text-success"><i class="fas fa-check-circle"></i> มีสินค้า (เหลือ <?= $product['stock']; ?> ชิ้น)</span>
                    <?php else: ?>
                        <span class="text-danger"><i class="fas fa-times-circle"></i> สินค้าหมด</span>
                    <?php endif; ?>
                </div>

                <div class="d-flex align-items-center mb-4">
                    <label for="quantity" class="me-3 fw-bold">จำนวน:</label>
                    <div class="input-group" style="width: 130px;">
                        <button class="btn btn-outline-secondary" type="button" id="btn-minus">-</button>
                        <input type="text" class="form-control text-center" id="quantity" value="1" readonly>
                        <button class="btn btn-outline-secondary" type="button" id="btn-plus">+</button>
                    </div>
                </div>

                <button class="btn btn-primary btn-lg w-100" id="btn-add-cart" <?= $product['stock'] <= 0 ? 'disabled' : '' ?>>
                    <i class="fas fa-cart-plus me-2"></i> เพิ่มลงตะกร้า
                </button>
            </div>
        </div>
    </div>

    <script>
    $(document).ready(function() {
        const stock = <?= $product['stock']; ?>;

        $('#btn-plus').click(function() {
            let qty = parseInt($('#quantity').val());
            if (qty < stock) {
                $('#quantity').val(qty + 1);
            }
        });

        $('#btn-minus').click(function() {
            let qty = parseInt($('#quantity').val());
            if (qty > 1) {
                $('#quantity').val(qty - 1);
            }
        });

        $('#btn-add-cart').click(function() {
            let qty = parseInt($('#quantity').val());
            let prod_id = <?= $product['id']; ?>;

            $.ajax({
                url: 'product_detail.php?prod_id=' + prod_id,
                type: 'POST',
                data: {
                    action: 'addcart',
                    prod_id: prod_id,
                    prod_qty: qty
                },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'เพิ่มลงตะกร้าสำเร็จ!',
                            text: response.message,
                            showConfirmButton: false,
                            timer: 1500
                        }).then(() => {
                            location.reload();
                        });
                    } else if (response.message === 'not_logged_in') {
                        Swal.fire({
                            icon: 'warning',
                            title: 'กรุณาเข้าสู่ระบบ',
                            text: 'คุณต้องเข้าสู่ระบบก่อนเพิ่มสินค้าลงตะกร้า',
                            confirmButtonText: 'ไปหน้าเข้าสู่ระบบ'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                window.location.href = 'login.php';
                            }
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'ทำรายการไม่สำเร็จ',
                            text: response.message
                        });
                    }
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'ข้อผิดพลาดเครือข่าย',
                        text: 'ไม่สามารถติดต่อเซิร์ฟเวอร์ได้'
                    });
                }
            });
        });
    });
    </script>
</body>
</html>
