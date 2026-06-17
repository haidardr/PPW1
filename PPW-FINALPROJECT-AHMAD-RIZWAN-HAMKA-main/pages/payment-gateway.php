<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ?page=login");
    exit();
}

$order_id = filter_input(INPUT_GET, 'order_id', FILTER_VALIDATE_INT);
if (!$order_id) {
    $_SESSION['error_message'] = "Invalid Order ID specified.";
    header("Location: ?page=profile");
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

$stmt = $pdo->prepare("
    SELECT o.id, o.total_price, o.status, p.payment_method, p.payment_status
    FROM orders o
    LEFT JOIN payments p ON o.id = p.order_id
    WHERE o.id = ? AND o.user_id = ?
");
$stmt->execute([$order_id, $user_id]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    $_SESSION['error_message'] = "Order not found or you do not have permission to access it.";
    header("Location: ?page=profile");
    exit();
}

if ($order['status'] !== 'pending') {
    $_SESSION['error_message'] = "This order has already been processed and cannot be paid again.";
    header("Location: ?page=profile");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_payment'])) {
    try {
        $pdo->beginTransaction();

        $orderItemsStmt = $pdo->prepare(
            "SELECT product_id, quantity FROM order_items WHERE order_id = ?"
        );
        $orderItemsStmt->execute([$order_id]);
        $items = $orderItemsStmt->fetchAll(PDO::FETCH_ASSOC);

        $updateStockStmt = $pdo->prepare(
            "UPDATE products SET stock = stock - ? WHERE id = ?"
        );

        foreach ($items as $item) {
            $stockCheckStmt = $pdo->prepare("SELECT stock FROM products WHERE id = ? FOR UPDATE");
            $stockCheckStmt->execute([$item['product_id']]);
            $currentStock = $stockCheckStmt->fetchColumn();

            if ($currentStock === false || $currentStock < $item['quantity']) {
                throw new Exception("Maaf, stok untuk salah satu produk dalam pesanan Anda tidak mencukupi. Pembelian tidak dapat dilanjutkan.");
            }

            $updateStockStmt->execute([$item['quantity'], $item['product_id']]);
        }

        $updateOrderStmt = $pdo->prepare("UPDATE orders SET status = 'paid' WHERE id = ?");
        $updateOrderStmt->execute([$order_id]);

        $updatePaymentStmt = $pdo->prepare("UPDATE payments SET payment_status = 'confirmed' WHERE order_id = ?");
        $updatePaymentStmt->execute([$order_id]);

        $pdo->commit();

        $_SESSION['success_message'] = "Payment for Order #{$order_id} was successful!";
        header("Location: ?page=profile");
        exit();

    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error_message'] = "Payment simulation failed. Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Process Payment - Order #<?= htmlspecialchars($order['id']) ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="icon" href="assets/images/favicon.png" type="image/png">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style-dashboard.css">
</head>
<body>
    <?php
    if (isset($_SESSION['user_id'])) {
        include 'components/header_login.php';
    } else {
        include 'components/header.php';
    }
    ?>

    <div class="page-content-wrapper">
        <div class="payment-container" style="max-width: 600px; margin: 50px auto; padding: 35px; background-color: #f9f9f9; border-radius: 8px;">
            <div class="payment-header" style="text-align: center; margin-bottom: 30px;">
                <h1 class="payment-title" style="font-size: 26px; font-weight: 600;">Payment Simulation</h1>
                <p class="payment-subtitle" style="font-size: 16px; color: #777;">Complete your purchase for Order #<?= htmlspecialchars($order['id']) ?></p>
            </div>

            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="alert alert-error" style="padding: 15px; background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; border-radius: 4px; margin-bottom: 20px;">
                    <?= htmlspecialchars($_SESSION['error_message']) ?>
                </div>
                <?php unset($_SESSION['error_message']); ?>
            <?php endif; ?>

            <div class="payment-details">
                <div class="detail-item" style="display: flex; justify-content: space-between; padding: 15px 0; border-bottom: 1px solid #e5e5e5;">
                    <span class="detail-label" style="font-weight: 500;">Payment Method:</span>
                    <span class="detail-value" style="font-weight: 600;"><?= htmlspecialchars($order['payment_method']) ?></span>
                </div>
                <div class="detail-item" style="display: flex; justify-content: space-between; padding: 15px 0; border-bottom: 1px solid #e5e5e5;">
                    <span class="detail-label" style="font-weight: 500;">Current Status:</span>
                    <span class="detail-value status-badge status-<?= htmlspecialchars($order['status']) ?>"><?= ucfirst(htmlspecialchars($order['status'])) ?></span>
                </div>
                <div class="detail-item" style="display: flex; justify-content: space-between; padding: 15px 0;">
                    <span class="detail-label" style="font-weight: 500;">Amount to Pay:</span>
                    <span class="detail-value total" style="font-size: 22px; font-weight: 700;">Rp <?= number_format($order['total_price'], 0, ',', '.') ?></span>
                </div>
            </div>

            <form class="payment-form" method="POST" style="text-align: center; margin-top: 30px;">
                <p class="dummy-info" style="font-size: 14px; color: #888; margin-bottom: 20px;">This is a simulated payment environment</p>
                <input type="hidden" name="order_id" value="<?= htmlspecialchars($order['id']) ?>">
                <button type="submit" name="confirm_payment" class="confirm-payment-btn" style="background-color: #1b1b1b; color: white; border: none; padding: 14px 35px; border-radius: 6px; cursor: pointer; font-size: 16px; width: 100%;">Confirm Payment</button>
            </form>
        </div>
    </div>

    <?php include 'components/footer.php'; ?>
</body>
</html>