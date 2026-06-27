<?php
session_start();
include('./component/connectdatabase.php');

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');

    if ($_POST['action'] === 'remove_item') {
        $cart_id = $_POST['cart_id'];
        if ($cart_obj->removeItem($user_id, $cart_id)) {
            echo json_encode(['status' => 'success', 'message' => 'ลบสินค้าเรียบร้อย']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'ลบสินค้าล้มเหลว']);
        }
        exit;
    }

    if ($_POST['action'] === 'update_qty') {
        $cart_id = $_POST['cart_id'];
        $qty = (int)$_POST['qty'];

        if ($qty < 1) {
            echo json_encode(['status' => 'error', 'message' => 'จำนวนต้องไม่น้อยกว่า 1']);
            exit;
        }

        if ($cart_obj->updateQty($user_id, $cart_id, $qty)) {
            echo json_encode(['status' => 'success', 'message' => 'อัปเดตจำนวนเรียบร้อย']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Insufficient stock or update failed']);
        }
        exit;
    }
}

$cart_items = $cart_obj->getByUser($user_id);
$total_price = 0;
$checkout_error = isset($_GET['error']) ? $_GET['error'] : '';
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ตะกร้าสินค้า</title>
    <link rel="icon" href="./assets/image/icon.png" type="image/png">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src='https://code.jquery.com/jquery-3.7.1.js' integrity='sha256-eKhayi8LEQwp4NKxN+CfCh+3qOVUtJn3QNZ0TciWLP4=' crossorigin='anonymous'></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@400;600&display=swap" rel="stylesheet">
    <link href="./assets/css/style.css" rel="stylesheet" />
</head>
<body>
    <?php include('./component/navbar.php'); ?>

    <div class="container py-5 mt-5">
        <h2 class="fw-bold mb-4"><i class="fas fa-shopping-cart text-primary"></i> Your cart</h2>

        <?php if ($checkout_error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($checkout_error) ?></div>
        <?php endif; ?>

        <?php if (count($cart_items) > 0): ?>
            <div class="row">
                <div class="col-lg-8 mb-4">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>สินค้า</th>
                                            <th class="text-center">ราคา</th>
                                            <th class="text-center">จำนวน</th>
                                            <th class="text-center">รวม</th>
                                            <th class="text-center">ลบ</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        foreach($cart_items as $row) {
                                            $subtotal = $row['price'] * $row['quantity'];
                                            $total_price += $subtotal;
                                        ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <img src="./assets/image/product/<?= htmlspecialchars($row['image']); ?>" alt="Product" style="width: 60px; height: 60px; object-fit: contain;" class="rounded border me-3">
                                                    <div>
                                                        <h6 class="mb-0 fw-bold"><?= htmlspecialchars($row['name']); ?></h6>
                                                        <small class="text-muted">สต๊อก: <?= $row['stock']; ?> ชิ้น</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center">฿<?= number_format($row['price']); ?></td>
                                            <td class="text-center">
                                                <div class="input-group input-group-sm mx-auto" style="width: 100px;">
                                                    <button class="btn btn-outline-secondary btn-minus" type="button" data-id="<?= $row['cart_id']; ?>" data-stock="<?= $row['stock']; ?>">-</button>
                                                    <input type="text" class="form-control text-center input-qty" value="<?= $row['quantity']; ?>" readonly>
                                                    <button class="btn btn-outline-secondary btn-plus" type="button" data-id="<?= $row['cart_id']; ?>" data-stock="<?= $row['stock']; ?>">+</button>
                                                </div>
                                            </td>
                                            <td class="text-center text-danger fw-bold item-total">฿<?= number_format($subtotal); ?></td>
                                            <td class="text-center">
                                                <button class="btn btn-danger btn-sm btn-delete" data-id="<?= $row['cart_id']; ?>">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card shadow-sm border-primary">
                        <div class="card-header bg-primary text-white fw-bold">
                            สรุปคำสั่งซื้อ
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-3">
                                <span>ยอดรวมสินค้า</span>
                                <span class="fw-bold" id="cart-total">฿<?= number_format($total_price); ?></span>
                            </div>
                            <hr>
                            <form method="post" action="checkout.php" class="w-100">
                                <input type="hidden" name="confirm_checkout" value="1">
                                <button type="submit" class="btn btn-success w-100 fw-bold py-2">
                                    <i class="fas fa-check-circle me-1"></i> Place order
                                </button>
                            </form>
                            <a href="index.php" class="btn btn-outline-secondary w-100 mt-2">
                                เลือกซื้อสินค้าต่อ
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="alert alert-warning text-center p-5">
                <h4><i class="fas fa-box-open fa-2x mb-3"></i></h4>
                <p>ตะกร้าสินค้าของคุณว่างเปล่า</p>
                <a href="index.php" class="btn btn-primary mt-2">กลับไปเลือกซื้อสินค้า</a>
            </div>
        <?php endif; ?>
    </div>

    <script>
    $(document).ready(function() {

        function updateQuantity(cartId, newQty) {
            $.ajax({
                url: 'cart.php',
                type: 'POST',
                data: {
                    action: 'update_qty',
                    cart_id: cartId,
                    qty: newQty
                },
                dataType: 'json',
                success: function(response) {
                    if(response.status === 'success') {
                        location.reload();
                    } else {
                        Swal.fire('ข้อผิดพลาด', response.message, 'error');
                        location.reload();
                    }
                },
                error: function() {
                    Swal.fire('ผิดพลาด', 'อัปเดตข้อมูลล้มเหลว', 'error');
                }
            });
        }

        $('.btn-plus').click(function() {
            let input = $(this).siblings('.input-qty');
            let currentQty = parseInt(input.val());
            let stock = parseInt($(this).data('stock'));
            let cartId = $(this).data('id');

            if(currentQty < stock) {
                let newQty = currentQty + 1;
                input.val(newQty);
                updateQuantity(cartId, newQty);
            } else {
                Swal.fire('ข้อมูลสต๊อก', 'คุณไม่สามารถเพิ่มจำนวนเกินกว่าที่มีอยู่ในสต๊อกได้', 'info');
            }
        });

        $('.btn-minus').click(function() {
            let input = $(this).siblings('.input-qty');
            let currentQty = parseInt(input.val());
            let cartId = $(this).data('id');

            if(currentQty > 1) {
                let newQty = currentQty - 1;
                input.val(newQty);
                updateQuantity(cartId, newQty);
            } else {
                promptDelete(cartId);
            }
        });

        function promptDelete(cartId) {
            Swal.fire({
                title: 'ยืนยันการลบ?',
                text: "คุณต้องการลบสินค้านี้ออกจากตะกร้าใช่หรือไม่?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'ใช่, ลบเลย!',
                cancelButtonText: 'ยกเลิก'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: 'cart.php',
                        type: 'POST',
                        data: {
                            action: 'remove_item',
                            cart_id: cartId
                        },
                        dataType: 'json',
                        success: function(response) {
                            if(response.status === 'success') {
                                location.reload();
                            } else {
                                Swal.fire('ล้มเหลว', response.message, 'error');
                            }
                        }
                    });
                }
            });
        }

        $('.btn-delete').click(function() {
            let cartId = $(this).data('id');
            promptDelete(cartId);
        });

    });
    </script>
</body>
</html>
