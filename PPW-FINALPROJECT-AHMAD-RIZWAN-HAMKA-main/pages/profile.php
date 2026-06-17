<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ?page=login");
    exit();
}

include 'koneksi.php';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$database", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($_POST['action'] ?? '' === 'update_profile') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone_number']);
    
    $updateStmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, phone_number = ? WHERE id = ?");
    
    try {
        $updateStmt->execute([$name, $email, $phone, $user_id]);
        $_SESSION['success_message'] = "Profil berhasil diperbarui!";
        
        $stmt->execute([$user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        $_SESSION['error_message'] = "Error: Email atau nomor telepon sudah digunakan!";
    }
}

if ($_GET['action'] ?? '' === 'logout') {
    session_destroy();
    header("Location: ?page=home");
    exit();
}

$orderStmt = $pdo->prepare("
    SELECT 
        o.id as order_id,
        o.order_date,
        o.total_price,
        o.status,
        p.name as product_name,
        p.image_url,
        p.price,
        oi.quantity,
        oi.item_price,
        pc.name as category_name
    FROM orders o
    JOIN order_items oi ON o.id = oi.order_id
    JOIN products p ON oi.product_id = p.id
    LEFT JOIN product_categories pc ON p.category_id = pc.id
    WHERE o.user_id = ?
    ORDER BY o.order_date DESC
");
$orderStmt->execute([$user_id]);
$orders = $orderStmt->fetchAll(PDO::FETCH_ASSOC);

$groupedOrders = [];
foreach ($orders as $order) {
    $groupedOrders[$order['order_id']]['info'] = [
        'order_date' => $order['order_date'],
        'total_price' => $order['total_price'],
        'status' => $order['status']
    ];
    $groupedOrders[$order['order_id']]['items'][] = $order;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - <?= htmlspecialchars($user['name']) ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="icon" href="assets/images/favicon.png" type="image/png">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style-dashboard.css">
</head>
<body>
    <?php
    if (isset($_SESSION['user_id'])) {
        include $_SESSION['user_role'] === 'user' 
            ? 'components/header_login.php' 
            : 'components/header_admin.php';
    } else {
        include 'components/header.php';
    }
    ?>

    <div class="dashboard-banner">
        <div class="banner-title">
            <h1>Welcome, <?= htmlspecialchars($user['name']) ?>!</h1>
            <p><?= htmlspecialchars($user['email']) ?></p>
            <?php if ($user['phone_number']): ?>
                <p><?= htmlspecialchars($user['phone_number']) ?></p>
            <?php endif; ?>
        </div>
        <div class="dashboard-actions">
            <button class="dashboard-btn" onclick="openEditModal()">Edit Profile</button>
            <a href="?page=logout" class="dashboard-btn btn-delete" onclick="return confirm('Yakin ingin logout?')">Logout</a>
        </div>
    </div>

    <div class="orders-section">
        <div class="orders-container">
            <!-- Alert Messages -->
            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="alert alert-success">
                    <?= $_SESSION['success_message'] ?>
                </div>
                <?php unset($_SESSION['success_message']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="alert alert-error">
                    <?= $_SESSION['error_message'] ?>
                </div>
                <?php unset($_SESSION['error_message']); ?>
            <?php endif; ?>

            <div class="orders-header">
                <div class="orders-title">Order History</div>
            </div>

            <?php if (empty($groupedOrders)): ?>
                <div class="no-orders">
                    <h3>No Orders Yet</h3>
                    <p>You haven't placed any orders. Start shopping to see your orders here!</p>
                </div>
            <?php else: ?>
                <div class="order-card-container">
                    <?php foreach ($groupedOrders as $orderId => $orderData): ?>
                        <div class="order-card">
                            <div class="order-header">
                                <div class="order-info">
                                    <h4>Order #<?= $orderId ?></h4>
                                    <div class="order-date">
                                        <?= date('d M Y, H:i', strtotime($orderData['info']['order_date'])) ?>
                                    </div>
                                </div>
                                <div class="order-status">
                                    <span class="status-badge status-<?= $orderData['info']['status'] ?>">
                                        <?= ucfirst($orderData['info']['status']) ?>
                                    </span>
                                    <div class="order-total">
                                        Rp <?= number_format($orderData['info']['total_price'], 0, ',', '.') ?>
                                    </div>
                                    
                                    <?php if ($orderData['info']['status'] === 'pending'): ?>
                                        <a href="?page=payment-gateway&order_id=<?= $orderId ?>" class="dashboard-btn" style="margin-top: 10px; padding: 5px 10px; font-size: 14px; text-align: center;">Pay Now</a>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="order-items">
                                <?php foreach ($orderData['items'] as $item): ?>
                                    <div class="order-item">
                                        <?php if ($item['image_url']): ?>
                                            <img src="<?= htmlspecialchars($item['image_url']) ?>" 
                                                    alt="<?= htmlspecialchars($item['product_name']) ?>" 
                                                    class="item-image">
                                        <?php else: ?>
                                            <div class="item-image" style="background-color: #f0f0f0; display: flex; align-items: center; justify-content: center; color: #666;">
                                                No Image
                                            </div>
                                        <?php endif; ?>
                                        <div class="item-details">
                                            <div class="item-name"><?= htmlspecialchars($item['product_name']) ?></div>
                                            <div class="item-category"><?= htmlspecialchars($item['category_name']) ?></div>
                                            <div class="item-quantity">Qty: <?= $item['quantity'] ?></div>
                                        </div>
                                        <div class="item-price">
                                            <div>Rp <?= number_format($item['item_price'], 0, ',', '.') ?></div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Edit Profile Modal -->
    <div class="modal" id="editProfileModal">
        <div class="modal-content">
            <span class="close-button" onclick="closeEditModal()">&times;</span>
            <h2 class="modal-title">Edit Profile</h2>
            <form method="POST">
                <input type="hidden" name="action" value="update_profile">
                
                <div class="form-group">
                    <label for="name">Full Name</label>
                    <input type="text" class="form-control" id="name" name="name" 
                            value="<?= htmlspecialchars($user['name']) ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" class="form-control" id="email" name="email" 
                            value="<?= htmlspecialchars($user['email']) ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="phone_number">Phone Number</label>
                    <input type="text" class="form-control" id="phone_number" name="phone_number" 
                            value="<?= htmlspecialchars($user['phone_number'] ?? '') ?>">
                </div>

                <div class="modal-actions">
                    <button type="button" class="btn-cancel" onclick="closeEditModal()">Cancel</button>
                    <button type="submit" class="btn-save">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <?php include 'components/footer.php'; ?>

    <script>
        function openEditModal() {
            document.getElementById('editProfileModal').style.display = 'block';
        }

        function closeEditModal() {
            document.getElementById('editProfileModal').style.display = 'none';
        }

        window.onclick = function(event) {
            const modal = document.getElementById('editProfileModal');
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }

        // Auto hide alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                alert.style.display = 'none';
            });
        }, 5000);
    </script>
</body>
</html>