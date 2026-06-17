<?php
session_start();


if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'user') {
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

if ($_POST) {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'update_quantity':
                $cart_id = (int)$_POST['cart_id'];
                $quantity = (int)$_POST['quantity'];
                
                if ($quantity > 0) {
                    $updateStmt = $pdo->prepare("UPDATE cart_items SET quantity = ? WHERE id = ? AND user_id = ?");
                    $updateStmt->execute([$quantity, $cart_id, $user_id]);
                    $_SESSION['success_message'] = "Quantity updated successfully!";
                } else {
                    $_SESSION['error_message'] = "Quantity must be greater than 0.";
                }
                break;
                
            case 'delete_item':
                $cart_id = (int)$_POST['cart_id'];
                
                $deleteStmt = $pdo->prepare("DELETE FROM cart_items WHERE id = ? AND user_id = ?");
                $deleteStmt->execute([$cart_id, $user_id]);
                $_SESSION['success_message'] = "Item removed from cart!";
                break;
                
            case 'update_profile':  
                $name = $_POST['name'];
                $email = $_POST['email'];
                $phone_number = $_POST['phone_number'];
                
                $updateUserStmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, phone_number = ? WHERE id = ?");
                $updateUserStmt->execute([$name, $email, $phone_number, $user_id]);
                $_SESSION['success_message'] = "Profile updated successfully!";
                break;
        }
        
        header("Location: ?page=cart");
        exit();
    }
}

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

 
$cartStmt = $pdo->prepare("
    SELECT 
        ci.id as cart_id,
        ci.quantity,
        ci.added_at,
        p.id as product_id,
        p.name as product_name,
        p.image_url,
        p.price,
        pc.name as category_name
    FROM cart_items ci
    JOIN products p ON ci.product_id = p.id
    LEFT JOIN product_categories pc ON p.category_id = pc.id
    WHERE ci.user_id = ?
    ORDER BY ci.added_at DESC
");
$cartStmt->execute([$user_id]);
$cartItems = $cartStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Cart - <?= htmlspecialchars($user['name'] ?? 'User') ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="icon" href="assets/images/favicon.png" type="image/png">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style-dashboard.css">
    <link rel="stylesheet" href="assets/css/style-cart.css">
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

    <div class="page-content-wrapper">
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success">
                <?= htmlspecialchars($_SESSION['success_message']) ?>
            </div>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-error">
                <?= htmlspecialchars($_SESSION['error_message']) ?>
            </div>
            <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>
        
        <div class="orders-section">
            <div class="orders-container">
                <div class="orders-header">
                    <div class="orders-title">Your Cart</div>
                </div>

                <?php if (empty($cartItems)): ?>
                    <div class="no-orders">
                        <h3>Cart is Empty</h3>
                        <p>You haven't added any items to your cart.</p>
                    </div>
                <?php else: ?>
                    <div class="order-card-container">
                        <div class="order-card">
                            <div class="order-header">
                                <div class="order-info">
                                    <h4>Pending Checkout</h4>
                                    <div class="order-date"><?= date('d M Y, H:i') ?> (Cart View)</div>
                                </div>
                                <div class="order-status">
                                    <span class="status-badge status-cart">Cart</span>
                                    <div class="order-total">
                                        Rp <?= number_format(array_sum(array_map(function($item) {
                                            return $item['price'] * $item['quantity'];
                                        }, $cartItems)), 0, ',', '.') ?>
                                    </div>
                                </div>
                            </div>
                            <div class="order-items">
                                <?php foreach ($cartItems as $item): ?>
                                    <div class="order-item">
                                        <?php if ($item['image_url']): ?>
                                            <img src="<?= htmlspecialchars($item['image_url']) ?>" 
                                                    alt="<?= htmlspecialchars($item['product_name']) ?>" 
                                                    class="item-image">
                                        <?php else: ?>
                                            <div class="item-image" style="background-color: #f0f0f0; display: flex; align-items: center; justify-content: center; color: #666; font-size:10px; text-align:center;">
                                                No Image
                                            </div>
                                        <?php endif; ?>
                                        <div class="item-details">
                                            <div class="item-name"><?= htmlspecialchars($item['product_name']) ?></div>
                                            <div class="item-category"><?= htmlspecialchars($item['category_name']) ?></div>
                                            <div class="item-quantity">Current Qty: <?= $item['quantity'] ?></div>
                                            
                                            <div class="item-actions">
                                                <button type="button" class="edit-qty-btn" onclick="openUpdateModal(<?= $item['cart_id'] ?>, <?= $item['quantity'] ?>, '<?= htmlspecialchars(addslashes($item['product_name'])) ?>')">Edit Qty</button>
                                                
                                                <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to remove this item from your cart?');">
                                                    <input type="hidden" name="action" value="delete_item">
                                                    <input type="hidden" name="cart_id" value="<?= $item['cart_id'] ?>">
                                                    <button type="submit" class="delete-btn">Remove</button>
                                                </form>
                                            </div>
                                        </div>
                                        <div class="item-price">
                                            <div>Rp <?= number_format($item['price'] * $item['quantity'], 0, ',', '.') ?></div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                                <div class="checkout-button-container">
                                    <a class="checkout-btn" href="?page=checkout-orders">Checkout Orders</a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div> <div id="updateQuantityModal" class="modal">
        <div class="modal-content">
            <span class="close-button" onclick="closeModal('updateQuantityModal')">&times;</span>
            <h3 id="updateModalTitle" class="modal-title">Update Quantity</h3>
            <form method="POST" id="updateQuantityForm">
                <input type="hidden" name="action" value="update_quantity">
                <input type="hidden" name="cart_id" id="modal_cart_id">
                
                <div class="form-group">
                    <label for="modal_quantity">Quantity:</label>
                    <input type="number" name="quantity" id="modal_quantity" class="form-control" min="1" required>
                </div>
                
                <div class="modal-actions">
                    <button type="button" class="btn-cancel" onclick="closeModal('updateQuantityModal')">Cancel</button>
                    <button type="submit" class="btn-save">Update Quantity</button>
                </div>
            </form>
        </div>
    </div>

    <?php include 'components/footer.php'; 
    ?>

    <script>
        const updateQuantityModal = document.getElementById('updateQuantityModal');
        const modalCartIdInput = document.getElementById('modal_cart_id');
        const modalQuantityInput = document.getElementById('modal_quantity');
        const updateModalTitle = document.getElementById('updateModalTitle');

        function openUpdateModal(cartId, currentQuantity, productName) {
            modalCartIdInput.value = cartId;
            modalQuantityInput.value = currentQuantity;
            updateModalTitle.textContent = 'Update Quantity for: ' + productName;
            updateQuantityModal.style.display = 'block';
        }

        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }

        window.onclick = function(event) {
            if (event.target == updateQuantityModal) {
                updateQuantityModal.style.display = 'none';
            }
        }

        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                alert.style.display = 'none';
            });
        }, 5000);
    </script>
</body>
</html>