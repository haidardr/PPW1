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

 
$userStmt = $pdo->prepare("SELECT name, email, phone_number FROM users WHERE id = ?");
$userStmt->execute([$user_id]);
$userInfo = $userStmt->fetch(PDO::FETCH_ASSOC);

 
$cartStmt = $pdo->prepare("
    SELECT 
        ci.product_id,
        ci.quantity,
        p.name as product_name,
        p.price as product_price,
        p.image_url
    FROM cart_items ci
    JOIN products p ON ci.product_id = p.id
    WHERE ci.user_id = ?
");
$cartStmt->execute([$user_id]);
$cartItems = $cartStmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($cartItems)) {
    $_SESSION['error_message'] = "Your cart is empty. Please add items to your cart before proceeding to checkout.";
    header("Location: ?page=cart");
    exit();
}

$totalPrice = 0;
foreach ($cartItems as $item) {
    $totalPrice += $item['product_price'] * $item['quantity'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
    $payment_method = $_POST['payment_method'] ?? null;

    if (empty($payment_method)) {
        $_SESSION['error_message'] = "Please select a payment method.";
    } else {
        try {
            $pdo->beginTransaction();

            $orderStmt = $pdo->prepare("INSERT INTO orders (user_id, total_price, status) VALUES (?, ?, 'pending')");
            $orderStmt->execute([$user_id, $totalPrice]);
            $order_id = $pdo->lastInsertId();

            $orderItemStmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, item_price) VALUES (?, ?, ?, ?)");
            foreach ($cartItems as $item) {
                $orderItemStmt->execute([$order_id, $item['product_id'], $item['quantity'], $item['product_price']]);
            }

            $paymentStmt = $pdo->prepare("INSERT INTO payments (order_id, payment_method, payment_status) VALUES (?, ?, 'pending')");
            $paymentStmt->execute([$order_id, $payment_method]);

            $clearCartStmt = $pdo->prepare("DELETE FROM cart_items WHERE user_id = ?");
            $clearCartStmt->execute([$user_id]);

            $pdo->commit();
            $_SESSION['success_message'] = "Order placed successfully! Your Order ID is #{$order_id}. Please proceed with the payment.";

            header("Location: ?page=profile"); 
            exit();

        } catch (PDOException $e) {
            $pdo->rollBack();
            $_SESSION['error_message'] = "Failed to place order. Please try again. Error: " . $e->getMessage();
        }
    }
    if(isset($_SESSION['error_message'])){
        header("Location: ?page=checkout-orders"); 
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - <?= htmlspecialchars($userInfo['name'] ?? 'User') ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="icon" href="assets/images/favicon.png" type="image/png">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style-dashboard.css">
    <style>
        .checkout-container {
            display: flex;
            gap: 30px;
            flex-wrap: wrap; /* Allow wrapping on smaller screens */
        }

        .checkout-summary, .checkout-details {
            background-color: #f9f9f9;
            padding: 25px;
            border: 1px solid #eee;
            border-radius: 8px; /* Softer corners */
        }

        .checkout-summary {
            flex: 2; /* Takes more space */
            min-width: 300px; /* Minimum width before wrapping */
        }

        .checkout-details {
            flex: 1;
            min-width: 280px;
        }
        
        .checkout-summary h4, .checkout-details h4 {
            font-size: 18px;
            font-weight: 600;
            color: #333;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }

        .order-item-checkout {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 10px 0;
            border-bottom: 1px solid #e0e0e0;
        }
        .order-item-checkout:last-child {
            border-bottom: none;
        }
        .item-image-checkout {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 6px;
            border: 1px solid #ddd;
        }
        .item-details-checkout {
            flex-grow: 1;
        }
        .item-name-checkout {
            font-size: 15px;
            font-weight: 500;
        }
        .item-qty-price-checkout {
            font-size: 13px;
            color: #555;
        }
        .item-subtotal-checkout {
            font-size: 14px;
            font-weight: 500;
            text-align: right;
        }

        .total-section {
            margin-top: 20px;
            padding-top: 15px;
            border-top: 2px solid #333;
            text-align: right;
        }
        .total-section .total-amount {
            font-size: 20px;
            font-weight: 700;
            color: #1b1b1b;
        }

        .user-info-group, .payment-method-group {
            margin-bottom: 15px;
        }
        .user-info-group label, .payment-method-group label {
            display: block;
            font-size: 13px;
            font-weight: 500;
            color: #555;
            margin-bottom: 3px;
        }
        .user-info-group p, .payment-method-group .form-control {
            font-size: 15px;
            color: #333;
            margin: 0;
            padding: 8px;
            background-color: #fff;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        
        .payment-method-group .form-control { /* Select specific style */
            width: 100%;
            box-sizing: border-box;
        }

        .place-order-btn {
            background-color: #1b1b1b;
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 500;
            font-family: 'Poppins', sans-serif;
            transition: background-color 0.3s ease;
            width: 100%;
            margin-top: 15px;
        }
        .place-order-btn:hover {
            background-color: #333;
        }

        .alert { 
            padding: 15px;
            margin: 20px auto;
            border-radius: 0;
            font-weight: 400;
            font-family: 'Poppins', sans-serif;
            width: 100%; 
            box-sizing: border-box;
        }
        .alert-success {
            background-color: #f8f9fa; color: #000; border: 1px solid #000;
        }
        .alert-error {
            background-color: #f8f9fa; color: #000; border: 1px solid #000;
        }
    </style>
</head>
<body>
    <?php
    if (isset($_SESSION['user_id'])) {
        include 'components/header_login.php';
    } else {
        include 'components/header.php';  
    }
    ?>
    
    <div class="orders-section">
        <div class="orders-container">
            <div class="orders-header">
                <div class="orders-title">Checkout</div>
            </div>

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

            <form method="POST" action="?page=checkout-orders">
                <div class="checkout-container">
                    <div class="checkout-summary">
                        <h4>Order Summary</h4>
                        <?php foreach ($cartItems as $item): ?>
                            <div class="order-item-checkout">
                                <?php if ($item['image_url']): ?>
                                    <img src="<?= htmlspecialchars($item['image_url']) ?>" 
                                            alt="<?= htmlspecialchars($item['product_name']) ?>" 
                                            class="item-image-checkout">
                                <?php else: ?>
                                    <div class="item-image-checkout" style="background-color: #f0f0f0; display: flex; align-items: center; justify-content: center; color: #666; font-size:10px; text-align:center;">
                                        No Image
                                    </div>
                                <?php endif; ?>
                                <div class="item-details-checkout">
                                    <div class="item-name-checkout"><?= htmlspecialchars($item['product_name']) ?></div>
                                    <div class="item-qty-price-checkout">
                                        Qty: <?= $item['quantity'] ?> &times; Rp <?= number_format($item['product_price'], 0, ',', '.') ?>
                                    </div>
                                </div>
                                <div class="item-subtotal-checkout">
                                    Rp <?= number_format($item['product_price'] * $item['quantity'], 0, ',', '.') ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        <div class="total-section">
                            Total: <span class="total-amount">Rp <?= number_format($totalPrice, 0, ',', '.') ?></span>
                        </div>
                    </div>

                    <div class="checkout-details">
                        <h4>Your Details & Payment</h4>
                        <div class="user-info-group">
                            <label>Name:</label>
                            <p><?= htmlspecialchars($userInfo['name']) ?></p>
                        </div>
                        <div class="user-info-group">
                            <label>Email:</label>
                            <p><?= htmlspecialchars($userInfo['email']) ?></p>
                        </div>
                        <div class="user-info-group">
                            <label>Phone Number:</label>
                            <p><?= htmlspecialchars($userInfo['phone_number'] ?: 'Not provided') ?></p>
                        </div>
                        
                        <div class="payment-method-group">
                            <label for="payment_method">Payment Method:</label>
                            <select name="payment_method" id="payment_method" class="form-control" required>
                                <option value="" disabled selected>Select Payment Method</option>
                                <option value="Bank Transfer BCA">Bank Transfer BCA</option>
                                <option value="Bank Transfer Mandiri">Bank Transfer Mandiri</option>
                                <option value="GoPay">GoPay</option>
                                <option value="OVO">OVO</option>
                                <option value="COD">Cash On Delivery (COD)</option>
                            </select>
                        </div>
                        <button type="submit" name="place_order" class="place-order-btn">Place Order</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <?php include 'components/footer.php'; ?>


</body>
</html>