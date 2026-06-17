<?php
include 'koneksi.php'; 

try {
    $result_clothing = $conn->query("SELECT * FROM products WHERE type = 'clothing' ORDER BY RAND()  LIMIT 4");
    if ($result_clothing) {
        $clothing_only_4 = $result_clothing->fetch_all(MYSQLI_ASSOC);
    } else {
        echo "Error query clothing: " . $conn->error;
        $clothing_only_4 = [];
    }

    $result_accessory = $conn->query("SELECT * FROM products WHERE type = 'accessory'ORDER BY RAND()  LIMIT 4   ");
    if ($result_accessory) {
        $accessory_only_4 = $result_accessory->fetch_all(MYSQLI_ASSOC);
    } else {
        echo "Error query accessory: " . $conn->error;
        $accessory_only_4 = [];
    }

    
} catch (Exception $e) {
    echo "Database error: " . $e->getMessage();
    $clothing_only_4 = [];
    $accessory_only_4 = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home - Zwnzs Store</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="icon" href="assets/images/favicon.png" type="image/png">
    <link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet">
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
    
    <div class="Banner">
        <div class="text-area">
            <div class="banner-title">
                <h1>NOT JUST MERCH. <br> IT'S IDENTITY</h1>
                <a href="?page=shop">explore now</a>
            </div>
        </div>
        <div class="image-area">
            <img src="assets/images/shirt.png" alt="">
        </div>
    </div>
    
    <div class="Product-section">
        <div class="product-container">
            <div class="product-navigation">
                <div class="category">Apparel</div>
                <div class="pagination-container">
                    <a href="?page=shop">Show All</a>
                </div>
            </div>

            <div class="card-container" id="product-cards">
                <?php
                if (isset($clothing_only_4)) {
                    echo "<!-- Debug: clothing_only_4 tersedia, jumlah: " . count($clothing_only_4) . " -->";
                    
                    if (!empty($clothing_only_4)) {
                        foreach ($clothing_only_4 as $row) {
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
                        echo '<p>Tidak ada produk clothing ditemukan.</p>';
                    }
                } else {
                    echo '<p>Error: Data clothing tidak tersedia. Periksa koneksi database.</p>';
                    echo '<!-- Debug: $clothing_only_4 tidak tersedia -->';
                }
                ?>
            </div>
        </div>
    </div>

    <div class="Banner" style="height: 400px; background-color: white; color: #1b1b1b;">
        <h1>CARRY<br>THE<br>ESSENCE.</h1>
    </div>

    <div class="Product-section">
        <div class="product-container">
            <div class="product-navigation">
                <div class="category">Accessories</div>
                <a href="?page=shop">Show All</a>
            </div>
            <div class="card-container" id="product-cards">
                <?php
                if (isset($accessory_only_4)) {
                    echo "<!-- Debug: accessory_only_4 tersedia, jumlah: " . count($accessory_only_4) . " -->";
                    
                    if (!empty($accessory_only_4)) {
                        foreach ($accessory_only_4 as $row) {
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
                        echo '<p>Tidak ada produk accessories ditemukan.</p>';
                    }
                } else {
                    echo '<p>Error: Data accessories tidak tersedia. Periksa koneksi database.</p>';
                    echo '<!-- Debug: $accessory_only_4 tidak tersedia -->';
                }
                ?>
            </div>
        </div>
    </div>

    <div class="Banner" style="height: 600px;">
        <h1>NOT <br> JUST <br>MERCH.</h1>
    </div>

    <?php
    include 'components/modal.php';
    include 'components/footer.php';
    ?>

</body>
</html>