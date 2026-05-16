<?php
require_once __DIR__ . '/includes/session.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /ecommerce-php/cart.php');
    exit;
}

$productId = intval($_POST['product_id'] ?? 0);
$quantity = intval($_POST['quantity'] ?? 0);

if ($productId > 0) {
    updateCartQuantity($productId, $quantity);
    $_SESSION['success'] = 'Panier mis à jour';
}

header('Location: /ecommerce-php/cart.php');
exit;
?>
