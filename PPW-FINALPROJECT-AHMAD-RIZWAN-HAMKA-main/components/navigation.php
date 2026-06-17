<?php $page = $_GET['page'] ?? 'shop'; ?>

<div class="navigation">
    <a href="?page=shop" class="clothing-button <?php if ($page == 'shop') echo 'selected'; ?>">Show All</a>
    <a href="?page=shop-clothing" class="clothing-button <?php if ($page == 'shop-clothing') echo 'selected'; ?>">Clothing</a>
    <a href="?page=shop-accesory" class="clothing-button <?php if ($page == 'shop-accesory') echo 'selected'; ?>">Accessory</a>
</div>
