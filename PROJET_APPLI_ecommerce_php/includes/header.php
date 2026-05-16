

<?php 
require_once __DIR__ . '/session.php';
require_once __DIR__ . '/session.php';

$userInfo = getUserInfo();
$cartCount = getCartCount();
?>


<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'Boutique golabelsandale'; ?></title>
    <link rel="stylesheet" href="/ecommerce-php/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Montserrat:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

<body>
    <header class="main-header">
        <div class="header-top">
            <div class="container">
                <div class="header-contact">
                    <span>📞 +225 07 79 67 42 03</span>
                    <span>✉️ contact@golabelsandale.ci</span>
                </div>
                <div class="header-links">
                    <?php if (isLoggedIn()): ?>
                        <a href="/ecommerce-php/account.php">Bonjour, <?php echo htmlspecialchars($userInfo['prenom']); ?></a>
                        <?php if (isAdmin()): ?>
                            <a href="/ecommerce-php/admin/">GOLABELSANDALE</a>
                        <?php endif; ?>
                        <a href="/ecommerce-php/logout.php">Déconnexion</a>
                    <?php else: ?>
                        <a href="/ecommerce-php/login.php">Connexion</a>
                        <a href="/ecommerce-php/register.php">Inscription</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <nav class="main-nav">
            <div class="container">
                <a href="/ecommerce-php/index.php" class="logo">
                    <span class="logo-elegant">golabelesandale</span>
                    <span class="logo-tagline">Votre style, notre passion</span>
                </a>
                
                <div class="nav-menu">
                    <a href="/ecommerce-php/index.php">Accueil</a>
                    <a href="/ecommerce-php/products.php">Produits</a>
                    <a href="/ecommerce-php/about.php">À propos</a>
                    <a href="/ecommerce-php/contact.php">Contact</a>
                </div>
                
                <div class="nav-actions">
                    <a href="/ecommerce-php/search.php" class="icon-btn" title="Rechercher">
                        🔍
                    </a>
                    <a href="/ecommerce-php/cart.php" class="icon-btn cart-btn" title="Panier">
                        🛒
                        <?php if ($cartCount > 0): ?>
                            <span class="cart-badge"><?php echo $cartCount; ?></span>
                        <?php endif; ?>
                    </a>
                </div>
            </div>
        </nav>
    </header>
    
    <main class="main-content">
