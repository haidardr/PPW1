<?php
$host = "localhost";
$user = "root"; 
$password = "Megamode#12";    
$database = "FINPRO";

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

$conn->set_charset("utf8");

function dd($data) {
    echo '<pre>';
    var_dump($data);
    echo '</pre>';
    die();
}


try {
    $result_clothing = $conn->query("SELECT * FROM products WHERE type = 'clothing' LIMIT 4");
    if ($result_clothing) {
        $clothing_only_4 = $result_clothing->fetch_all(MYSQLI_ASSOC);
    } else {
        echo "Error query clothing: " . $conn->error;
        $clothing_only_4 = [];
    }

    $result_accessory = $conn->query("SELECT * FROM products WHERE type = 'accessory' LIMIT 4");
    if ($result_accessory) {
        $accessory_only_4 = $result_accessory->fetch_all(MYSQLI_ASSOC);
    } else {
        echo "Error query accessory: " . $conn->error;
        $accessory_only_4 = [];
    }

    $result_all = $conn->query("SELECT * FROM products");
    if ($result_all) {
        $all_products = $result_all->fetch_all(MYSQLI_ASSOC);
    } else {
        echo "Error query accessory: " . $conn->error;
        $all_products = [];
    }
    // dd($all_products);

    $clothing_all = $conn->query("SELECT * FROM products WHERE type = 'clothing'");
    if ($clothing_all) {
        $clothing_all = $clothing_all->fetch_all(MYSQLI_ASSOC);
    } else {
        echo "Error query clothing: " . $conn->error;
        $clothing_all = [];
    }

    // dd($clothing_all);
    $accessory_all = $conn->query("SELECT * FROM products WHERE type = 'accessory'");
    if ($accessory_all) {
        $accessory_all = $accessory_all->fetch_all(MYSQLI_ASSOC);
    } else {
        echo "Error query clothing: " . $conn->error;
        $accessory_all = [];
    }

    // dd($accessory_all);



    
} catch (Exception $e) {
    echo "Database error: " . $e->getMessage();
    $clothing_only_4 = [];
    $accessory_only_4 = [];
}
?>