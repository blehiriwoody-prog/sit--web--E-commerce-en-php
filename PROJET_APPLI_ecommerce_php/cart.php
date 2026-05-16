<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/session.php';

$pageTitle = 'Mon Panier - Boutique Élégance';
require_once __DIR__ . '/includes/header.php';

$cart = getCart();
$conn = getConnection();

// Récupérer les détails des produits dans le panier
$cartItems = [];
$total = 0;

foreach ($cart as $productId => $quantity) {
    $stmt = $conn->prepare("SELECT * FROM produits WHERE id = ?");
    $stmt->execute([$productId]);
    $product = $stmt->fetch();
    
    if ($product) {
        $prix = $product['prix_promo'] ?? $product['prix'];
        $cartItems[] = [
            'product' => $product,
            'quantity' => $quantity,
            'prix' => $prix,
            'subtotal' => $prix * $quantity
        ];
        $total += $prix * $quantity;
    }
}
?>

<div class="page-header">
    <div class="container">
        <h1>Mon Panier</h1>
        <p><?php echo count($cartItems); ?> article(s)</p>
    </div>
</div>

<div class="cart-page">
    <div class="container">
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <?php 
                echo htmlspecialchars($_SESSION['success']); 
                unset($_SESSION['success']);
                ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-error">
                <?php 
                echo htmlspecialchars($_SESSION['error']); 
                unset($_SESSION['error']);
                ?>
            </div>
        <?php endif; ?>
        
        <?php if (empty($cartItems)): ?>
            <div class="empty-cart">
                <div class="empty-cart-icon">🛒</div>
                <h2>Votre panier est vide</h2>
                <p>Découvrez nos produits et ajoutez-les à votre panier</p>
                <a href="/ecommerce-php/products.php" class="btn-primary">Continuer mes achats</a>
            </div>
        <?php else: ?>
            <div class="cart-layout">
                <div class="cart-items">
                    <h2>Articles</h2>
                    
                    <?php foreach ($cartItems as $item): ?>
                    <div class="cart-item">
                        <div class="cart-item-image">
                            <?php if ($item['product']['image']): ?>
                                <img src="<?php echo htmlspecialchars($item['product']['image']); ?>" 
                                     alt="<?php echo htmlspecialchars($item['product']['nom']); ?>">
                            <?php else: ?>
                                <div class="cart-placeholder">📦</div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="cart-item-details">
                            <h3>
                                <a href="/ecommerce-php/product-detail.php?id=<?php echo $item['product']['id']; ?>">
                                    <?php echo htmlspecialchars($item['product']['nom']); ?>
                                </a>
                            </h3>
                            <p class="cart-item-price">
                                <?php echo number_format($item['prix'], 0, ',', ' '); ?> FCFA
                            </p>
                        </div>
                        
                        <div class="cart-item-actions">
                            <form method="POST" action="/ecommerce-php/update-cart.php" class="quantity-form">
                                <input type="hidden" name="product_id" value="<?php echo $item['product']['id']; ?>">
                                <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" 
                                       min="1" max="<?php echo $item['product']['stock']; ?>">
                                <button type="submit" class="btn-update">Mettre à jour</button>
                            </form>
                            
                            <form method="POST" action="/ecommerce-php/remove-from-cart.php" class="remove-form">
                                <input type="hidden" name="product_id" value="<?php echo $item['product']['id']; ?>">
                                <button type="submit" class="btn-remove">Supprimer</button>
                            </form>
                        </div>
                        
                        <div class="cart-item-subtotal">
                            <?php echo number_format($item['subtotal'], 0, ',', ' '); ?> FCFA
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="cart-summary">
                    <h2>Résumé</h2>
                    
                    <div class="summary-line">
                        <span>Sous-total</span>
                        <span><?php echo number_format($total, 0, ',', ' '); ?> FCFA</span>
                    </div>
                    
                    <div class="summary-line">
                        <span>Livraison</span>
                        <span>Gratuite</span>
                    </div>
                    
                    <div class="summary-total">
                        <span>Total</span>
                        <span><?php echo number_format($total, 0, ',', ' '); ?> FCFA</span>
                    </div>
                    
                    <?php if (isLoggedIn()): ?>
                        <a href="/ecommerce-php/checkout.php" class="btn-primary btn-full">Passer la commande</a>
                    <?php else: ?>
                        <a href="/ecommerce-php/login.php?redirect=checkout" class="btn-primary btn-full">
                            Se connecter pour commander
                        </a>
                    <?php endif; ?>
                    
                    <a href="/ecommerce-php/products.php" class="btn-continue">Continuer mes achats</a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.empty-cart {
    text-align: center;
    padding: 80px 20px;
}

.empty-cart-icon {
    font-size: 6rem;
    margin-bottom: 30px;
    opacity: 0.3;
}

.empty-cart h2 {
    font-family: var(--font-display);
    font-size: 2rem;
    color: var(--color-primary);
    margin-bottom: 15px;
}

.empty-cart p {
    color: var(--color-text-light);
    margin-bottom: 30px;
}

.cart-layout {
    display: grid;
    grid-template-columns: 1fr 400px;
    gap: 40px;
}

.cart-items h2,
.cart-summary h2 {
    font-family: var(--font-display);
    font-size: 1.8rem;
    margin-bottom: 30px;
    color: var(--color-primary);
}

.cart-item {
    display: grid;
    grid-template-columns: 100px 1fr auto auto;
    gap: 20px;
    padding: 25px;
    background: var(--color-white);
    margin-bottom: 20px;
    align-items: center;
}

.cart-item-image {
    width: 100px;
    height: 100px;
}

.cart-item-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.cart-placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--color-light);
    font-size: 3rem;
}

.cart-item-details h3 {
    font-size: 1.1rem;
    margin-bottom: 10px;
}

.cart-item-details h3 a {
    color: var(--color-primary);
    text-decoration: none;
    transition: var(--transition);
}

.cart-item-details h3 a:hover {
    color: var(--color-secondary);
}

.cart-item-price {
    color: var(--color-text-light);
    font-weight: 600;
}

.cart-item-actions {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.quantity-form {
    display: flex;
    gap: 10px;
}

.quantity-form input {
    width: 60px;
    padding: 8px;
    border: 1px solid var(--color-border);
    text-align: center;
}

.btn-update,
.btn-remove {
    padding: 8px 15px;
    border: none;
    cursor: pointer;
    font-size: 0.85rem;
    transition: var(--transition);
}

.btn-update {
    background: var(--color-secondary);
    color: var(--color-white);
}

.btn-update:hover {
    background: var(--color-accent);
}

.btn-remove {
    background: transparent;
    color: var(--color-error);
    text-decoration: underline;
}

.btn-remove:hover {
    color: #c0392b;
}

.cart-item-subtotal {
    font-size: 1.3rem;
    font-weight: 700;
    color: var(--color-primary);
}

.cart-summary {
    background: var(--color-white);
    padding: 30px;
    height: fit-content;
    position: sticky;
    top: 100px;
}

.summary-line {
    display: flex;
    justify-content: space-between;
    margin-bottom: 15px;
    padding-bottom: 15px;
    border-bottom: 1px solid var(--color-border);
}

.summary-total {
    display: flex;
    justify-content: space-between;
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--color-primary);
    margin: 25px 0;
    padding: 25px 0;
    border-top: 2px solid var(--color-primary);
    border-bottom: 2px solid var(--color-primary);
}

.btn-continue {
    display: block;
    text-align: center;
    margin-top: 15px;
    color: var(--color-secondary);
    text-decoration: none;
    font-weight: 600;
}

.btn-continue:hover {
    text-decoration: underline;
}

@media (max-width: 968px) {
    .cart-layout {
        grid-template-columns: 1fr;
    }
    
    .cart-item {
        grid-template-columns: 80px 1fr;
        gap: 15px;
    }
    
    .cart-item-actions,
    .cart-item-subtotal {
        grid-column: 2;
    }
    
    .cart-summary {
        position: static;
    }
}
</style>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
