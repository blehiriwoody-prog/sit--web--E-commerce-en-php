<?php
require_once __DIR__ . '/config/database.php';
$pageTitle = 'Nos Produits - Boutique Élégance';
require_once __DIR__ . '/includes/header.php';

$conn = getConnection();

// Filtres
$categoryId = $_GET['category'] ?? null;
$search = $_GET['search'] ?? '';

// Requête
$sql = "SELECT p.*, c.nom as categorie_nom 
        FROM produits p 
        LEFT JOIN categories c ON p.category_id = c.id 
        WHERE 1=1";
$params = [];

if ($categoryId) {
    $sql .= " AND p.category_id = ?";
    $params[] = $categoryId;
}

if ($search) {
    $sql .= " AND (p.nom LIKE ? OR p.description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$sql .= " ORDER BY (p.stock <= 0), p.created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();
?>

<div class="page-header">
    <div class="container">
        <h1>Nos Produits</h1>
        <p>Découvrez notre collection exclusive</p>
    </div>
</div>

<div class="products-page">
    <div class="container">
        <div class="products-layout">

            <!-- SIDEBAR -->
            <aside class="sidebar">

                <!-- BOUTONS CATÉGORIES -->
                <div class="filter-box">
                    <h3>Catégories</h3>

                    <div class="category-buttons">

                        <a href="/ecommerce-php/products.php"
                           class="cat-btn <?php echo !$categoryId ? 'active' : ''; ?>">
                            Tous les produits
                        </a>

                        <!-- ⚠️ CHANGE 1 SI TON ID SANDALE EST DIFFERENT -->
                        <a href="/ecommerce-php/products.php?category=1"
                           class="cat-btn <?php echo $categoryId == 1 ? 'active' : ''; ?>">
                            👡 Sandale
                        </a>

                    </div>
                </div>

                <!-- RECHERCHE -->
                <div class="filter-box">
                    <h3>Recherche</h3>
                    <form method="GET">
                        <?php if ($categoryId): ?>
                            <input type="hidden" name="category" value="<?php echo $categoryId; ?>">
                        <?php endif; ?>
                        <input type="text" name="search" placeholder="Rechercher..." 
                               value="<?php echo htmlspecialchars($search); ?>" class="search-input">
                        <button type="submit" class="btn-primary btn-full">Rechercher</button>
                    </form>
                </div>

            </aside>

            <!-- PRODUITS -->
            <div class="products-main">

                <?php if (empty($products)): ?>
                    <div class="no-products">
                        <p>Aucun produit trouvé</p>
                    </div>
                <?php else: ?>

                    <div class="products-count">
                        <?php echo count($products); ?> produit(s) trouvé(s)
                    </div>

                    <div class="products-grid">

                        <?php foreach ($products as $product): ?>
                        <article class="product-card <?php echo ($product['stock'] <= 0) ? 'out-card' : ''; ?>">

                            <?php if ($product['stock'] > 0): ?>
                                <a href="/ecommerce-php/product-detail.php?id=<?php echo $product['id']; ?>" class="product-image">
                            <?php else: ?>
                                <div class="product-image disabled-link">
                            <?php endif; ?>

                                <?php if ($product['image']): ?>
                                    <img src="<?php echo htmlspecialchars($product['image']); ?>" 
                                         alt="<?php echo htmlspecialchars($product['nom']); ?>">
                                <?php else: ?>
                                    <div class="product-placeholder">📦</div>
                                <?php endif; ?>

                                <?php if ($product['prix_promo']): ?>
                                    <span class="product-badge">PROMO</span>
                                <?php endif; ?>

                                <?php if ($product['stock'] <= 0): ?>
                                    <span class="product-badge-stock">RUPTURE DE STOCK</span>
                                <?php endif; ?>

                            <?php if ($product['stock'] > 0): ?>
                                </a>
                            <?php else: ?>
                                </div>
                            <?php endif; ?>

                            <div class="product-info">

                                <h3 class="product-name">
                                    <?php if ($product['stock'] > 0): ?>
                                        <a href="/ecommerce-php/product-detail.php?id=<?php echo $product['id']; ?>">
                                            <?php echo htmlspecialchars($product['nom']); ?>
                                        </a>
                                    <?php else: ?>
                                        <?php echo htmlspecialchars($product['nom']); ?>
                                    <?php endif; ?>
                                </h3>

                                <div class="product-price">
                                    <?php if ($product['prix_promo']): ?>
                                        <span class="price-old">
                                            <?php echo number_format($product['prix'], 0, ',', ' '); ?> FCFA
                                        </span>
                                        <span class="price-new">
                                            <?php echo number_format($product['prix_promo'], 0, ',', ' '); ?> FCFA
                                        </span>
                                    <?php else: ?>
                                        <span class="price-current">
                                            <?php echo number_format($product['prix'], 0, ',', ' '); ?> FCFA
                                        </span>
                                    <?php endif; ?>
                                </div>

                                <?php if ($product['stock'] <= 0): ?>
                                    <button class="btn-add-cart disabled" disabled>
                                        Indisponible
                                    </button>
                                <?php else: ?>
                                    <form method="POST" action="/ecommerce-php/add-to-cart.php">
                                        <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                        <button type="submit" class="btn-add-cart">
                                            Ajouter au panier
                                        </button>
                                    </form>
                                <?php endif; ?>

                            </div>
                        </article>
                        <?php endforeach; ?>

                    </div>
                <?php endif; ?>
            </div>

        </div>
    </div>
</div>

<style>

/* ===== CATÉGORIES BOUTONS ===== */

.category-buttons {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.cat-btn {
    display: block;
    padding: 14px 18px;
    text-align: center;
    text-decoration: none;
    font-weight: 600;
    border-radius: 8px;
    background: #f4f4f4;
    color: #333;
    transition: 0.3s ease;
}

.cat-btn:hover {
    background: #b10101;
    color: #fff;
    transform: translateY(-3px);
}

.cat-btn.active {
    background: linear-gradient(45deg, #a70101, #a70303);
    color: #fff;
}

/* ===== PRODUITS ===== */

.product-image {
    position: relative;
    display: block;
}

.out-card {
    opacity: 0.85;
}

.out-card img {
    filter: grayscale(100%);
}

.product-badge-stock {
    position: absolute;
    top: 12px;
    left: 12px;
    background: linear-gradient(45deg, #8b0000, #c0392b);
    color: #fff;
    padding: 8px 12px;
    font-size: 0.75rem;
    font-weight: bold;
    border-radius: 4px;
}

.btn-add-cart.disabled {
    background: #999;
    cursor: not-allowed;
    opacity: 0.7;
}

.disabled-link {
    pointer-events: none;
}

</style>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
