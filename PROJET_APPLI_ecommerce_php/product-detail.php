<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/session.php';

$productId = $_GET['id'] ?? 0;

$conn = getConnection();
$stmt = $conn->prepare("SELECT p.*, c.nom as categorie_nom 
                        FROM produits p 
                        LEFT JOIN categories c ON p.category_id = c.id 
                        WHERE p.id = ?");
$stmt->execute([$productId]);
$product = $stmt->fetch();

if (!$product) {
    header('Location: /ecommerce-php/products.php');
    exit;
}

$pageTitle = htmlspecialchars($product['nom']) . ' - Boutique Élégance';
require_once __DIR__ . '/includes/header.php';

// Produits similaires
$relatedStmt = $conn->prepare("SELECT * FROM produits 
                                WHERE category_id = ? AND id != ? 
                                LIMIT 4");
$relatedStmt->execute([$product['category_id'], $productId]);
$relatedProducts = $relatedStmt->fetchAll();
?>

<div class="product-detail-page">
    <div class="container">
        <nav class="breadcrumb">
            <a href="/ecommerce-php/index.php">Accueil</a>
            <span>/</span>
            <a href="/ecommerce-php/products.php">Produits</a>
            <?php if ($product['categorie_nom']): ?>
                <span>/</span>
                <a href="/ecommerce-php/products.php?category=<?php echo $product['category_id']; ?>">
                    <?php echo htmlspecialchars($product['categorie_nom']); ?>
                </a>
            <?php endif; ?>
            <span>/</span>
            <span><?php echo htmlspecialchars($product['nom']); ?></span>
        </nav>
        
        <div class="product-detail-grid">
            <div class="product-detail-image">
                <?php if ($product['image']): ?>
                    <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['nom']); ?>">
                <?php else: ?>
                    <div class="product-placeholder-large">📦</div>
                <?php endif; ?>
            </div>
            
            <div class="product-detail-info">
                <?php if ($product['categorie_nom']): ?>
                    <span class="product-category"><?php echo htmlspecialchars($product['categorie_nom']); ?></span>
                <?php endif; ?>
                
                <h1 class="product-detail-title"><?php echo htmlspecialchars($product['nom']); ?></h1>
                
                <div class="product-detail-price">
                    <?php if ($product['prix_promo']): ?>
                        <span class="price-old"><?php echo number_format($product['prix'], 0, ',', ' '); ?> FCFA</span>
                        <span class="price-new"><?php echo number_format($product['prix_promo'], 0, ',', ' '); ?> FCFA</span>
                        <span class="price-save">
                            Économisez <?php echo number_format($product['prix'] - $product['prix_promo'], 0, ',', ' '); ?> FCFA
                        </span>
                    <?php else: ?>
                        <span class="price-current"><?php echo number_format($product['prix'], 0, ',', ' '); ?> FCFA</span>
                    <?php endif; ?>
                </div>
                
                <?php if ($product['description']): ?>
                    <div class="product-description">
                        <h3>Description</h3>
                        <p><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
                    </div>
                <?php endif; ?>
                
                <div class="product-stock">
                    <?php if ($product['stock'] > 0): ?>
                        <span class="stock-available">✓ En stock (<?php echo $product['stock']; ?> disponible(s))</span>
                    <?php else: ?>
                        <span class="stock-unavailable">✗ Rupture de stock</span>
                    <?php endif; ?>
                </div>
                
                <?php if ($product['stock'] > 0): ?>
                    <form method="POST" action="/ecommerce-php/add-to-cart.php" class="product-detail-form">
                        <div class="quantity-selector">
                            <label for="quantity">Quantité :</label>
                            <input type="number" id="quantity" name="quantity" value="1" min="1" max="<?php echo $product['stock']; ?>">
                        </div>
                        
                        <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                        <button type="submit" class="btn-primary btn-large">Ajouter au panier</button>
                    </form>
                <?php endif; ?>
                
                <div class="product-features">
                    <div class="feature">
                        <span>🚚</span>
                        <div>
                            <strong>Livraison rapide</strong>
                            <p>Livraison en 24-48h sur Abidjan</p>
                        </div>
                    </div>
                    <div class="feature">
                        <span>↩️</span>
                        <div>
                            <strong>Retours gratuits</strong>
                            <p>14 jours pour changer d'avis</p>
                        </div>
                    </div>
                    <div class="feature">
                        <span>💳</span>
                        <div>
                            <strong>Paiement sécurisé</strong>
                            <p>Vos données sont protégées</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if (!empty($relatedProducts)): ?>
<section class="related-products">
    <div class="container">
        <h2 class="section-title">Produits similaires</h2>
        <div class="products-grid">
            <?php foreach ($relatedProducts as $related): ?>
            <article class="product-card">
                <a href="/ecommerce-php/product-detail.php?id=<?php echo $related['id']; ?>" class="product-image">
                    <?php if ($related['image']): ?>
                        <img src="<?php echo htmlspecialchars($related['image']); ?>" alt="<?php echo htmlspecialchars($related['nom']); ?>">
                    <?php else: ?>
                        <div class="product-placeholder">📦</div>
                    <?php endif; ?>
                </a>
                
                <div class="product-info">
                    <h3 class="product-name">
                        <a href="/ecommerce-php/product-detail.php?id=<?php echo $related['id']; ?>">
                            <?php echo htmlspecialchars($related['nom']); ?>
                        </a>
                    </h3>
                    
                    <div class="product-price">
                        <?php if ($related['prix_promo']): ?>
                            <span class="price-old"><?php echo number_format($related['prix'], 0, ',', ' '); ?> FCFA</span>
                            <span class="price-new"><?php echo number_format($related['prix_promo'], 0, ',', ' '); ?> FCFA</span>
                        <?php else: ?>
                            <span class="price-current"><?php echo number_format($related['prix'], 0, ',', ' '); ?> FCFA</span>
                        <?php endif; ?>
                    </div>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<style>
.product-detail-page {
    padding: 60px 0;
}

.breadcrumb {
    margin-bottom: 40px;
    font-size: 0.9rem;
}

.breadcrumb a {
    color: var(--color-text);
    text-decoration: none;
    transition: var(--transition);
}

.breadcrumb a:hover {
    color: var(--color-secondary);
}

.breadcrumb span {
    margin: 0 10px;
    color: var(--color-text-light);
}

.product-detail-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 60px;
    margin-bottom: 80px;
}

.product-detail-image img {
    width: 100%;
    height: auto;
    box-shadow: var(--shadow-lg);
}

.product-placeholder-large {
    width: 100%;
    aspect-ratio: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--color-light);
    font-size: 8rem;
}

.product-detail-title {
    font-family: var(--font-display);
    font-size: 2.5rem;
    color: var(--color-primary);
    margin-bottom: 20px;
}

.product-detail-price {
    margin-bottom: 30px;
    padding-bottom: 30px;
    border-bottom: 1px solid var(--color-border);
}

.price-save {
    display: block;
    color: var(--color-success);
    font-weight: 600;
    margin-top: 10px;
}

.product-description {
    margin-bottom: 30px;
}

.product-description h3 {
    font-family: var(--font-display);
    font-size: 1.5rem;
    margin-bottom: 15px;
    color: var(--color-primary);
}

.product-description p {
    color: var(--color-text);
    line-height: 1.8;
}

.product-stock {
    margin-bottom: 30px;
}

.stock-available {
    color: var(--color-success);
    font-weight: 600;
}

.stock-unavailable {
    color: var(--color-error);
    font-weight: 600;
}

.product-detail-form {
    margin-bottom: 40px;
}

.quantity-selector {
    display: flex;
    align-items: center;
    gap: 15px;
    margin-bottom: 20px;
}

.quantity-selector label {
    font-weight: 600;
}

.quantity-selector input {
    width: 80px;
    padding: 10px;
    border: 1px solid var(--color-border);
    text-align: center;
    font-size: 1rem;
}

.btn-large {
    padding: 18px 50px;
    font-size: 1.1rem;
}

.product-features {
    background: var(--color-light);
    padding: 30px;
}

.product-features .feature {
    display: flex;
    align-items: start;
    gap: 15px;
    margin-bottom: 20px;
}

.product-features .feature:last-child {
    margin-bottom: 0;
}

.product-features .feature span {
    font-size: 2rem;
}

.product-features .feature strong {
    display: block;
    margin-bottom: 5px;
    color: var(--color-primary);
}

.product-features .feature p {
    font-size: 0.9rem;
    color: var(--color-text-light);
}

.related-products {
    padding: 60px 0;
    background: var(--color-white);
}

@media (max-width: 968px) {
    .product-detail-grid {
        grid-template-columns: 1fr;
        gap: 40px;
    }
}
</style>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
