<?php
session_start();
include('./component/connectdatabase.php');

$alert = '';

if (isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    $auth_user = $user_obj->login($username, $password);

    if ($auth_user) {
        $_SESSION['user_id'] = $auth_user['id'];
        $_SESSION['user_username'] = $auth_user['username'];
        $_SESSION['user_role'] = $auth_user['role'];

        if ($auth_user['role'] === 'admin') {
            $redirect = 'dashboard/user.php';
        } elseif ($auth_user['role'] === 'factory') {
            $redirect = 'dashboard/order_fa.php';
        } else {
            $redirect = 'index.php';
        }

        $alert = "Swal.fire({
            title: 'Login successful',
            text: 'Welcome {$auth_user['username']}!',
            icon: 'success'
        }).then(() => { window.location.href='{$redirect}'; });";
    } else {
        $alert = "Swal.fire('Invalid credentials','Username or password is incorrect','error');";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="icon" href="./assets/image/icon.png" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@400;600&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body style="font-family: 'Prompt', sans-serif;">
    <div class="container d-flex justify-content-center align-items-center vh-100">
        <div class="card p-4 shadow" style="width: 400px; border-radius: 16px;">
            <div class="card-header text-center bg-white border-0">
                <h3>Login</h3>
            </div>
            <div class="card-body">
                <form method="POST" action="">
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="username" name="username" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <button type="submit" name="login" class="btn btn-success w-100">Sign in</button>
                </form>
                <div class="mt-3 text-center">
                    <a href="register.php">Don't have an account? Register</a>
                </div>
            </div>
        </div>
    </div>

    <?php if ($alert): ?>
    <script><?= $alert ?></script>
    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
