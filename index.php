<?php
session_start();
include('./component/connectdatabase.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Model Shop</title>
    <link rel="icon" href="./assets/image/icon.png" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@400;600&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link href="./assets/css/style.css" rel="stylesheet" />
</head>
<body>
    <?php include('./component/navbar.php'); ?>

    <div class="container py-5">
        <div class="row">
            <div class="col-md-12 text-center">
                <div id="productCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="2000">
                    <div class="carousel-inner">
                        <?php
                        $carouselProducts = $product_obj->getForCarousel();
                        $active = 'active';
                        if (count($carouselProducts) > 0) {
                            foreach ($carouselProducts as $row) {
                        ?>
                        <div class="carousel-item <?= $active; ?>">
                            <img src="./assets/image/product/<?= htmlspecialchars($row['image']); ?>"
                                 class="d-block w-100"
                                 alt="Product Image"
                                 style="height: 500px; object-fit: cover;">
                        </div>
                        <?php
                            $active = '';
                            }
                        }
                        ?>
                    </div>
                    <button class="carousel-control-prev" type="button" data-bs-target="#productCarousel" data-bs-slide="prev">
                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Previous</span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#productCarousel" data-bs-slide="next">
                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Next</span>
                    </button>
                </div>

                <div class="row mt-5">
                    <div class="col-md-12">
                        <h3 class="pb-2 border-bottom">Top 10 Products</h3>
                        <div class="row">
                            <?php
                            $topProducts = $product_obj->getTopSelling(10);
                            foreach ($topProducts as $row) {
                            ?>
                            <div class="col-12 col-sm-6 col-md-4 col-lg-2 mb-4">
                                <div class="card product-card h-100">
                                    <img src="./assets/image/product/<?= htmlspecialchars($row['image']); ?>"
                                         class="card-img-top"
                                         alt="Product"
                                         style="width: 100%; height: 200px; object-fit: contain; background: #fff; padding: 10px;">
                                    <div class="card-body text-center">
                                        <h6 class="card-title"><?= htmlspecialchars($row['name']); ?></h6>
                                        <span style="font-size: 12px; display:block; max-height:40px; overflow:hidden;"><?= htmlspecialchars($row['description']); ?></span><br>
                                        <span class="text-danger">฿<?= number_format($row['price']); ?></span><br>
                                        <button class="btn btn-primary mt-3" onclick="window.location.href='product_detail.php?prod_id=<?= $row['id'] ?>'">View details</button>
                                    </div>
                                </div>
                            </div>
                            <?php } ?>
                        </div>
                    </div>
                </div>

                <div class="row mt-5">
                    <div class="col-md-12">
                        <h3 class="pb-2 border-bottom">New Products</h3>
                        <div class="row">
                            <?php
                            $newProducts = $product_obj->getNewest(10);
                            foreach ($newProducts as $row) {
                            ?>
                            <div class="col-12 col-sm-6 col-md-4 col-lg-2 mb-4">
                                <div class="card product-card h-100">
                                    <img src="./assets/image/product/<?= htmlspecialchars($row['image']); ?>"
                                         class="card-img-top"
                                         alt="Product"
                                         style="width: 100%; height: 200px; object-fit: contain; background: #fff; padding: 10px;">
                                    <div class="card-body text-center">
                                        <h6 class="card-title"><?= htmlspecialchars($row['name']); ?></h6>
                                        <span style="font-size: 12px; display:block; max-height:40px; overflow:hidden;"><?= htmlspecialchars($row['description']); ?></span><br>
                                        <span class="text-danger">฿<?= number_format($row['price']); ?></span><br>
                                        <button class="btn btn-primary mt-3" onclick="window.location.href='product_detail.php?prod_id=<?= $row['id'] ?>'">View details</button>
                                    </div>
                                </div>
                            </div>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
