<?php
require_once __DIR__ . '/includes/session.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /ecommerce-php/cart.php');
    exit;
}

$productId = intval($_POST['product_id'] ?? 0);

if ($productId > 0) {
    removeFromCart($productId);
    $_SESSION['success'] = 'Produit retiré du panier';
}

header('Location: /ecommerce-php/cart.php');
exit;
?>
