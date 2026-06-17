<header>
    <a href="?page=home" class="logo">
        <img src="assets/images/logo.png" style="height: 18px;" alt="">
    </a>
    <nav>
        <a href="?page=home" <?php if ($_GET['page'] == 'home') echo 'style="font-weight:700"'; ?>>Home</a>
        <a href="?page=shop" <?php if (in_array($_GET['page'], ['shop', 'shop-clothing', 'shop-accesory'])
) echo 'style="font-weight:700"'; ?>>Shop</a>
        <a href="?page=cart" <?php if ($_GET['page'] == 'cart') echo 'style="font-weight:700"'; ?>>Cart</a>
        <a href="?page=profile" <?php if ($_GET['page'] == 'profile') echo 'style="font-weight:700"'; ?>>Profile</a>
    </nav>
    <a href="?page=profile" class="login" <?php if (($_GET['page'] ?? '') == 'login') echo 'style="font-weight:700"'; ?>>
        <span class="dot"></span>Hi, <?php echo explode(' ', $_SESSION['user_name'])[0]; ?>!
    </a>

</header>
