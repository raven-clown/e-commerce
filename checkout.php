<?php
session_start();
include('./component/connectdatabase.php');

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_checkout'])) {
    $result = $order_obj->placeOrder($user_id, $cart_obj);

    if (!empty($result['success'])) {
        header('Location: order.php?placed=1');
        exit;
    }

    $message = urlencode($result['message'] ?? 'Checkout failed');
    header('Location: cart.php?error=' . $message);
    exit;
}

header('Location: cart.php');
exit;
