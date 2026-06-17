<?php
include 'koneksi.php'; 

error_reporting(0); 
ini_set('display_errors', 0); 

header('Content-Type: application/json'); 

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die(json_encode(["success" => false, "message" => "Koneksi gagal: " . $conn->connect_error])); 
}

$data = json_decode(file_get_contents("php://input"), true);

if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
    die(json_encode(["success" => false, "message" => "Invalid JSON input."]));
}

$productId = $data['productId'] ?? null; 
$quantity = isset($data['quantity']) ? (int)$data['quantity'] : 0;
$price = isset($data['price']) ? (int)$data['price'] : 0; 
$userId = $data['userId'] ?? null;

if ($userId === null || $productId === null || $quantity <= 0 || $price <= 0) {
    $missingFields = [];
    if ($userId === null) $missingFields[] = 'userId';
    if ($productId === null) $missingFields[] = 'productId';
    if ($quantity <= 0) $missingFields[] = 'quantity';
    if ($price <= 0) $missingFields[] = 'price'; 

    die(json_encode([
        "success" => false,
        "message" => "Data tidak lengkap atau tidak valid. Missing/Invalid: " . implode(', ', $missingFields)
    ]));
}


$stmt = $conn->prepare("INSERT INTO orders (user_id, total_price) VALUES (?, ?)");

if ($stmt === false) {
    die(json_encode(["success" => false, "message" => "Gagal menyiapkan statement SQL: " . $conn->error]));
}

$bindSuccess = $stmt->bind_param("ii", $userId, $price); 

if ($bindSuccess === false) {
    die(json_encode(["success" => false, "message" => "Gagal mengikat parameter: " . $stmt->error]));
}

if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Pesanan berhasil disimpan!"]);
} else {
    echo json_encode(["success" => false, "message" => "Gagal menyimpan pesanan: " . $stmt->error]);
}

$stmt->close();
$conn->close();
?>