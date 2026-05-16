<?php
session_start();

// Vérifier si l'utilisateur est connecté
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Vérifier si l'utilisateur est admin
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

// Obtenir l'ID de l'utilisateur connecté
function getUserId() {
    return $_SESSION['user_id'] ?? null;
}

// Obtenir les informations de l'utilisateur
function getUserInfo() {
    if (!isLoggedIn()) return null;
    
    require_once __DIR__ . '/../config/database.php';
    $conn = getConnection();
    
    $stmt = $conn->prepare("SELECT id, nom, prenom, email, role FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    
    return $stmt->fetch();
}

// Gestion du panier
function getCart() {
    return $_SESSION['cart'] ?? [];
}

function addToCart($productId, $quantity = 1) {
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    
    if (isset($_SESSION['cart'][$productId])) {
        $_SESSION['cart'][$productId] += $quantity;
    } else {
        $_SESSION['cart'][$productId] = $quantity;
    }
}

function removeFromCart($productId) {
    if (isset($_SESSION['cart'][$productId])) {
        unset($_SESSION['cart'][$productId]);
    }
}

function updateCartQuantity($productId, $quantity) {
    if ($quantity <= 0) {
        removeFromCart($productId);
    } else {
        $_SESSION['cart'][$productId] = $quantity;
    }
}

function getCartCount() {
    $cart = getCart();
    return array_sum($cart);
}

function clearCart() {
    $_SESSION['cart'] = [];
}

function getCartTotal() {
    require_once __DIR__ . '/../config/database.php';
    $conn = getConnection();
    $cart = getCart();
    $total = 0;
    
    foreach ($cart as $productId => $quantity) {
        $stmt = $conn->prepare("SELECT prix, prix_promo FROM produits WHERE id = ?");
        $stmt->execute([$productId]);
        $product = $stmt->fetch();
        
        if ($product) {
            $prix = $product['prix_promo'] ?? $product['prix'];
            $total += $prix * $quantity;
        }
    }
    
    return $total;
}
?>
