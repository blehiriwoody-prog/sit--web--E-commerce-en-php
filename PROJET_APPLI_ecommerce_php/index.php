<?php
require_once __DIR__ . '/config/database.php';
initDatabase();

$pageTitle = 'Accueil - Boutique golabel sandale';
require_once __DIR__ . '/includes/header.php';

// Récupérer les produits mis en avant
$conn = getConnection();
$stmt = $conn->query("SELECT p.*, c.nom as categorie_nom 
                      FROM produits p 
                      LEFT JOIN categories c ON p.category_id = c.id 
                      WHERE p.featured = 1 
                      LIMIT 6");
$featuredProducts = $stmt->fetchAll();

// Récupérer les catégories
$categories = $conn->query("SELECT * FROM categories LIMIT 4")->fetchAll();
?>

<div class="hero-section">
    <div class="hero-overlay"></div>
    <div class="hero-content">
        <h1 class="hero-title">
            <span class="hero-subtitle">Collection 2025</span>
            Bienvenue chez Golabele Sandale
        </h1>
        <p class="hero-description">Des produits soigneusement sélectionnés pour sublimer votre quotidien</p>
        <a href="/ecommerce-php/products.php" class="btn-primary">Découvrir la collection</a>
    </div>
    <div class="hero-decoration"></div>
</div>

<?php if (!empty($categories)): ?>
<section class="categories-section">
    <div class="container">
        <h2 class="section-title">Nos Catégories</h2>
        <div class="categories-grid">
            <?php foreach ($categories as $cat): ?>
            <a href="/ecommerce-php/products.php?category=<?php echo $cat['id']; ?>" class="category-card">
                <div class="category-image">
                    <?php if ($cat['image']): ?>
                        <img src="<?php echo htmlspecialchars($cat['image']); ?>" alt="<?php echo htmlspecialchars($cat['nom']); ?>">
                    <?php else: ?>
                        <div class="category-placeholder"></div>
                    <?php endif; ?>
                </div>
                <h3><?php echo htmlspecialchars($cat['nom']); ?></h3>
                <p><?php echo htmlspecialchars($cat['description'] ?? ''); ?></p>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php if (!empty($featuredProducts)): ?>
<section class="featured-section">
    <div class="container">
        <h2 class="section-title">Produits Vedettes</h2>
        <div class="products-grid">
            <?php foreach ($featuredProducts as $product): ?>
            <article class="product-card">
                <a href="/ecommerce-php/product-detail.php?id=<?php echo $product['id']; ?>" class="product-image">
                    <?php if ($product['image']): ?>
                        <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['nom']); ?>">
                    <?php else: ?>
                        <div class="product-placeholder"></div>
                    <?php endif; ?>
                    
                    <?php if ($product['prix_promo']): ?>
                        <span class="product-badge">PROMO</span>
                    <?php endif; ?>
                </a>
                
                <div class="product-info">
                    <?php if ($product['categorie_nom']): ?>
                        <span class="product-category"><?php echo htmlspecialchars($product['categorie_nom']); ?></span>
                    <?php endif; ?>
                    
                    <h3 class="product-name">
                        <a href="/ecommerce-php/product-detail.php?id=<?php echo $product['id']; ?>">
                            <?php echo htmlspecialchars($product['nom']); ?>
                        </a>
                    </h3>
                    
                    <div class="product-price">
                        <?php if ($product['prix_promo']): ?>
                            <span class="price-old"><?php echo number_format($product['prix'], 0, ',', ' '); ?> FCFA</span>
                            <span class="price-new"><?php echo number_format($product['prix_promo'], 0, ',', ' '); ?> FCFA</span>
                        <?php else: ?>
                            <span class="price-current"><?php echo number_format($product['prix'], 0, ',', ' '); ?> FCFA</span>
                        <?php endif; ?>
                    </div>
                    
                    <form method="POST" action="/ecommerce-php/add-to-cart.php" class="add-to-cart-form">
                        <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                        <button type="submit" class="btn-add-cart">Ajouter au panier</button>
                    </form>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<section class="features-section">
    <div class="container">
        <div class="features-grid">
            <div class="feature-item">
                <div class="feature-icon"></div>
                <h3>Livraison Rapide</h3>
                <p>Livraison en 24-48h sur Abidjan</p>
            </div>
            <div class="feature-item">
                <div class="feature-icon"></div>
                <h3>Paiement Sécurisé</h3>
                <p>Transactions 100% sécurisées</p>
            </div>
            <div class="feature-item">
                <div class="feature-icon"></div>
                <h3>Retours Gratuits</h3>
                <p>Satisfait ou remboursé sous 14 jours</p>
            </div>
            <div class="feature-item">
                <div class="feature-icon"></div>
                <h3>Offres Exclusives</h3>
                <p>Promotions régulières pour nos membres</p>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
