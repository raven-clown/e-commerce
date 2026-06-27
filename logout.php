<?php
session_start();
session_unset();
session_destroy();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Logging out...</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
<script>
    Swal.fire({
        title: 'Logged out',
        text: 'You have been signed out successfully.',
        icon: 'success',
        confirmButtonText: 'OK'
    }).then(function() {
        window.location.href = 'index.php';
    });
</script>
</body>
</html>
