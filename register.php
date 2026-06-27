<?php
session_start();
include('./component/connectdatabase.php');

$popup_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $first_name = $_POST['user_fname'] ?? '';
    $last_name  = $_POST['user_lname'] ?? '';
    $username   = $_POST['user_username'] ?? '';
    $password   = $_POST['user_password'] ?? '';
    $confirm    = $_POST['confirm_password'] ?? '';
    $email      = $_POST['user_email'] ?? '';
    $phone      = $_POST['user_tel'] ?? '';
    $address    = $_POST['user_address'] ?? '';

    if ($first_name && $last_name && $username && $password && $confirm && $email && $phone && $address) {
        if ($password !== $confirm) {
            $popup_message = "Swal.fire({title:'Warning',text:'Passwords do not match',icon:'warning'});";
        } else {
            $result = $user_obj->register($first_name, $last_name, $username, $password, $email, $phone, $address);
            if ($result === 'username_exists') {
                $popup_message = "Swal.fire({title:'Warning',text:'Username already taken',icon:'warning'});";
            } elseif ($result === true) {
                $popup_message = "Swal.fire({title:'Success',text:'Account created',icon:'success'}).then(()=>{window.location.href='login.php';});";
            } else {
                $popup_message = "Swal.fire({title:'Error',text:'Registration failed',icon:'error'});";
            }
        }
    } else {
        $popup_message = "Swal.fire({title:'Warning',text:'Please fill in all fields',icon:'warning'});";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="icon" href="./assets/image/icon.png" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@400;600&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body style="font-family: 'Prompt', sans-serif;">
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-5">
            <div class="card p-4 shadow-lg rounded-4">
                <h3 class="text-center mb-4">Create account</h3>
                <form method="post">
                    <div class="mb-3">
                        <label class="form-label">First name</label>
                        <input type="text" class="form-control" name="user_fname" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Last name</label>
                        <input type="text" class="form-control" name="user_lname" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" class="form-control" name="user_username" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" class="form-control" name="user_password" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Confirm password</label>
                        <input type="password" class="form-control" name="confirm_password" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" name="user_email" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Phone</label>
                        <input type="tel" class="form-control" name="user_tel" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Address</label>
                        <textarea class="form-control" name="user_address" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Register</button>
                </form>
                <div class="mt-3 text-center">
                    <a href="login.php">Already have an account? Sign in</a>
                </div>
            </div>
        </div>
    </div>
</div>
<?php if ($popup_message): ?><script><?= $popup_message ?></script><?php endif; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
