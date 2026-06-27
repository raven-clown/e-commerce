<?php
session_start();
include("./component/connectdatabase.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $orde_id      = $_POST['orde_id'];
    $user_id      = $_SESSION['user_id'];

    $user_fname   = $_POST['user_fname'];
    $user_lname   = $_POST['user_lname'];
    $user_tel     = $_POST['user_tel'];
    $user_address = $_POST['user_address'];

    $bank_name    = $_POST['bank_name'];
    $transfer_date= $_POST['transfer_date'];
    $transfer_time= $_POST['transfer_time'];
    $slip_file    = $_FILES['orde_slip'];

    $current_user = $user_obj->getById($user_id);
    if ($current_user) {
        $user_obj->updateProfile($user_id, $user_fname, $user_lname, $current_user['email'], $user_tel, $user_address);
    }

    $payment_result = $order_obj->confirmPayment($orde_id, $user_id, $bank_name, $transfer_date, $transfer_time, $slip_file);

    if ($payment_result === true) {
        header("Location: order.php?success=1");
        exit();
    } else {
        header("Location: order.php?error=" . urlencode($payment_result));
        exit();
    }
} else {
    header("Location: order.php");
    exit();
}
