<?php
$currentPage = $_GET['page'] ?? 'home';
?>
<header>
    <a href="?page=home" class="logo">
        <img src="assets/images/logo.png" style="height: 18px;" alt="Logo">
    </a>
    <nav>
        <a href="?page=home" <?php if ($currentPage == 'home') echo 'style="font-weight:700"'; ?>>Home</a>
        <a href="?page=shop" <?php if (in_array($currentPage, ['shop', 'shop-clothing', 'shop-accesory'])) echo 'style="font-weight:700"'; ?>>Shop</a>
        <a href="?page=dashboard" <?php if ($currentPage == 'dashboard') echo 'style="font-weight:700"'; ?>>Dashboard</a>
    </nav>
    <a href="?page=dashboard" class="login">
        <span class="dot"></span>Hi, <?php echo explode(' ', $_SESSION['user_name'])[0]; ?>!
    </a>
</header>