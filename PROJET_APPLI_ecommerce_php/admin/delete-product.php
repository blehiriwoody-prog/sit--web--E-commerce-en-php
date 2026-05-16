<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/session.php';

if (!isAdmin()) {
    header('Location: /ecommerce-php/login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = "Méthode invalide";
    header('Location: products.php');
    exit;
}

$conn = getConnection();

$productId = intval($_POST['product_id'] ?? 0);
$ravitaillement = intval($_POST['stock'] ?? 0); // Nouvelle quantité à ajouter

if ($productId <= 0 || $ravitaillement <= 0) {
    $_SESSION['error'] = "Produit ou quantité invalide";
    header('Location: products.php');
    exit;
}

// Vérifier que le produit existe
$stmt = $conn->prepare("SELECT stock FROM produits WHERE id = ?");
$stmt->execute([$productId]);
$product = $stmt->fetch();

if ($product) {
    // Ajouter la quantité au stock existant
    $stmt = $conn->prepare("UPDATE produits SET stock = stock + ? WHERE id = ?");
    $stmt->execute([$ravitaillement, $productId]);

    $_SESSION['success'] = "Produit ravitaillé avec succès !";
} else {
    $_SESSION['error'] = "Produit introuvable";
}

header('Location: products.php');
exit;
?>
