<?php
require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /ecommerce-php/products.php');
    exit;
}

$productId = intval($_POST['product_id'] ?? 0);
$quantity = intval($_POST['quantity'] ?? 1);

if ($productId <= 0 || $quantity <= 0) {
    header('Location: /ecommerce-php/products.php');
    exit;
}

// Vérifier que le produit existe et est en stock
$conn = getConnection();
$stmt = $conn->prepare("SELECT stock FROM produits WHERE id = ?");
$stmt->execute([$productId]); 
$product = $stmt->fetch();

if (!$product || $product['stock'] < $quantity) {
    $_SESSION['error'] = 'Produit non disponible en quantité suffisante';
    header('Location: /ecommerce-php/product-detail.php?id=' . $productId);
    exit;
}

addToCart($productId, $quantity);

$_SESSION['success'] = 'Produit ajouté au panier avec succès !';
header('Location: /ecommerce-php/cart.php');
exit;
?>
