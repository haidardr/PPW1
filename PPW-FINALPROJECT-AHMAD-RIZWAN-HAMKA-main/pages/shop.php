<?php
include 'koneksi.php'; 
try {
    $result_all = $conn->query("SELECT * FROM products ORDER BY RAND()");
    if ($result_all) {
        $all_products = $result_all->fetch_all(MYSQLI_ASSOC);
    } else {
        echo "Error query accessory: " . $conn->error;
        $all_products = [];
    }
    // dd($all_products);

} catch (Exception $e) {
    echo "Database error: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop - Our Products</title>
    <link rel="icon" href="assets/images/favicon.png" type="image/png">
    <link rel="stylesheet" href="assets/css/shop-style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;900&display=swap" rel="stylesheet">
</head>
<body>
    <?php
    session_start();
    if (isset($_SESSION['user_id'])) {
        include $_SESSION['user_role'] === 'user' 
            ? 'components/header_login.php' 
            : 'components/header_admin.php';
    } else {
        include 'components/header.php';
    }
    ?>

    <div class="Product-section">
        <div class="product-container">
            <div class="product-navigation">
                <div class="category">All Product</div>
                <?php
                    include 'components/navigation.php'
                ?>
            </div>
            <div class="card-container" id="product-cards">
                <?php
                // dd($all_products);
                if (isset($all_products) && !empty($all_products)) {
                    foreach ($all_products as $row) {
                        $category = strtolower($row["category"] ?? 'clothing');
                        echo '<div class="card" data-category="' . htmlspecialchars($row["type"]) . '">';
                        echo '  <div class="card-image">';
                        echo '      <img src="' . htmlspecialchars($row["image_url"] ?? 'assets/images/default.jpg') . '" alt="' . htmlspecialchars($row["name"] ?? 'Product') . '" loading="lazy">';
                        echo '  </div>';
                        echo '  <div class="card-title">'; 
                        echo '      <h2 class="carde-id" hidden>' . $row["id"]. '</h2>';
                        echo '      <h4 class="carde-title" hidden>' . $row["name"]. '</h4>';
                        echo '      <h3 class="carde-title">' . htmlspecialchars(implode(' ', array_slice(explode(' ', $row["name"] ?? 'Unnamed Product'), 0, 2))) . '</h3>';
                        echo '      <p class="card-price">Rp ' . number_format($row["price"] ?? 0, 0, ',', '.') . '</p>';
                        echo '  </div>';
                        echo '</div>';
                    }
                } else {
                    echo '<div class="no-products">';
                    echo '  <div class="no-products-content">';
                    echo '      <h3>No Products Available</h3>';
                    echo '      <p>Products are currently being updated. Please check back later.</p>';
                    echo '  </div>';
                    echo '</div>';
                }
                ?>
            </div>
            
            <!-- <?php if (isset($all_products) && !empty($all_products)): ?>
            <div class="pagination-container">
                <div class="pagination">
                    <a href="#" class="arrow disabled" id="prev-page">&laquo; Previous</a>
                    <span class="active">1</span>
                    <a href="#" class="arrow disabled" id="next-page">Next &raquo;</a>
                </div>
            </div>
            <?php endif; ?> -->
        </div>
    </div>

    <?php
    include 'components/footer.php';
    include 'components/modal.php';
    ?>

</body>
</html>