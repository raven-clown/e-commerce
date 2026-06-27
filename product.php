<?php
session_start();
include('./component/connectdatabase.php');

$search = isset($_GET['q']) ? trim($_GET['q']) : '';
$products = $search !== '' ? $product_obj->search($search) : $product_obj->getAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products</title>
    <link rel="icon" href="./assets/image/icon.png" type="image/png">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@400;600&display=swap" rel="stylesheet">
    <link href="./assets/css/style.css" rel="stylesheet" />
</head>
<body>
    <?php include('./component/navbar.php'); ?>

    <div class="container py-5">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb bg-transparent mb-4 px-0">
                <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                <li class="breadcrumb-item active" aria-current="page">Products</li>
            </ol>
        </nav>

        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
            <h1 class="mb-0">Products</h1>
            <form method="get" class="d-flex gap-2" style="min-width: 280px;">
                <input type="text" name="q" class="form-control" placeholder="Search products..." value="<?= htmlspecialchars($search) ?>">
                <button class="btn btn-primary" type="submit">Search</button>
            </form>
        </div>

        <div class="row">
            <?php if (empty($products)): ?>
                <div class="col-12 text-center text-muted py-5">No products found.</div>
            <?php endif; ?>
            <?php foreach ($products as $product) { ?>
                <div class="col-md-3 mb-4">
                    <div class="card h-100 shadow-sm border-0 rounded-3">
                        <img src="./assets/image/product/<?= htmlspecialchars($product['image']); ?>"
                             class="card-img-top"
                             alt="Product"
                             style="width: 100%; height: 220px; object-fit: contain; background: #fff; padding: 10px; border-bottom: 1px solid #eee;">
                        <div class="card-body text-center">
                            <h5 class="fw-bold mb-2"><?= htmlspecialchars($product['name']); ?></h5>
                            <p class="text-success fw-semibold mb-1">฿<?= number_format($product['price']) ?></p>
                            <p class="text-muted small mb-3">In stock: <?= (int)$product['stock'] ?></p>
                            <a href="product_detail.php?prod_id=<?= $product['id'] ?>" class="btn btn-primary w-100">View details</a>
                        </div>
                    </div>
                </div>
            <?php } ?>
        </div>
    </div>
</body>
</html>
