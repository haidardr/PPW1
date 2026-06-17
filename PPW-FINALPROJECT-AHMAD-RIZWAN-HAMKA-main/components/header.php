<header>
    <a href="?page=home" class="logo">
        <img src="assets/images/logo.png" style="height: 18px;" alt="">
    </a>
    <nav>
        <a href="?page=home" <?php if (($_GET['page'] ?? 'home') == 'home') echo 'style="font-weight:700"'; ?>>Home</a>
        <a href="?page=shop" <?php if (in_array($_GET['page'] ?? '', ['shop', 'shop-clothing', 'shop-accesory'])) echo 'style="font-weight:700"'; ?>>Shop</a>
    </nav>
    <a href="?page=login" class="login" <?php if (($_GET['page'] ?? '') == 'login') echo 'style="font-weight:700"'; ?>>
        log in <span class="dot"></span>
    </a>
</header>
