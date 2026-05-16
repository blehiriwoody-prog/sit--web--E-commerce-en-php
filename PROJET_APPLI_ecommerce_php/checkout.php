<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/session.php';

if (!isLoggedIn()) {
    header('Location: /ecommerce-php/login.php?redirect=checkout');
    exit;
}

$cart = getCart();
if (empty($cart)) {
    header('Location: /ecommerce-php/cart.php');
    exit;
}

$error = '';
$success = '';
$userInfo = getUserInfo();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $adresse = trim($_POST['adresse'] ?? '');
    $ville = trim($_POST['ville'] ?? '');
    $telephone = trim($_POST['telephone'] ?? '');
    
    if (empty($adresse) || empty($ville) || empty($telephone)) {
        $error = 'Veuillez remplir tous les champs';
    } else {
        $conn = getConnection();
        $total = getCartTotal();
        $adresseLivraison = $adresse . ', ' . $ville . ' - Tél: ' . $telephone;
        
        try {
            $conn->beginTransaction();
            
            // Créer la commande
            $stmt = $conn->prepare("INSERT INTO commandes (user_id, total, adresse_livraison) VALUES (?, ?, ?)");
            $stmt->execute([getUserId(), $total, $adresseLivraison]);
            $commandeId = $conn->lastInsertId();
            
            // Ajouter les détails de la commande
            foreach ($cart as $productId => $quantity) {
                $stmt = $conn->prepare("SELECT prix, prix_promo, stock FROM produits WHERE id = ?");
                $stmt->execute([$productId]);
                $product = $stmt->fetch();
                
                if ($product && $product['stock'] >= $quantity) {
                    $prix = $product['prix_promo'] ?? $product['prix'];
                    
                    // Ajouter le détail
                    $stmt = $conn->prepare("INSERT INTO commande_details (commande_id, produit_id, quantite, prix_unitaire) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$commandeId, $productId, $quantity, $prix]);
                    
                    // Mettre à jour le stock
                    $stmt = $conn->prepare("UPDATE produits SET stock = stock - ? WHERE id = ?");
                    $stmt->execute([$quantity, $productId]);
                }
            }
            
            $conn->commit();
            clearCart();
            
            $_SESSION['success'] = 'Commande passée avec succès !';
            header('Location: /ecommerce-php/order-success.php?id=' . $commandeId);
            exit;
            
        } catch (Exception $e) {
            $conn->rollBack();
            $error = 'Erreur lors de la commande : ' . $e->getMessage();
        }
    }
}

$pageTitle = 'Finaliser ma commande - GOLABELSANDALE';
require_once __DIR__ . '/includes/header.php';

$total = getCartTotal();
?>

<div class="page-header">
    <div class="container">
        <h1>Finaliser ma commande</h1>
    </div>
</div>

<div class="checkout-page">
    <div class="container">
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <div class="checkout-layout">
            <div class="checkout-form-section">
                <h2>Informations de livraison</h2>
                
                <form method="POST" action="" class="checkout-form">
                    <div class="form-group">
                        <label for="nom">Nom complet</label>
                        <input type="text" id="nom" value="<?php echo htmlspecialchars($userInfo['prenom'] . ' ' . $userInfo['nom']); ?>" disabled>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" value="<?php echo htmlspecialchars($userInfo['email']); ?>" disabled>
                    </div>
                    
                    <div class="form-group">
                        <label for="telephone">Téléphone *</label>
                        <input type="tel" id="telephone" name="telephone" required 
                               placeholder="+225 XX XX XX XX XX">
                    </div>
                    
                    <div class="form-group">
                        <label for="adresse">Adresse de livraison *</label>
                        <textarea id="adresse" name="adresse" rows="3" required 
                                  placeholder="Numéro, rue, quartier..."></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="ville">Ville *</label>
                        <input type="text" id="ville" name="ville" required placeholder="Abidjan">
                    </div>
                    
                    <button type="submit" class="btn-primary btn-full btn-large">
                        Confirmer la commande (<?php echo number_format($total, 0, ',', ' '); ?> FCFA)
                    </button>
                </form>
            </div>
            
            <div class="checkout-summary">
                <h2>Récapitulatif</h2>
                
                <?php
                $conn = getConnection();
                foreach ($cart as $productId => $quantity):
                    $stmt = $conn->prepare("SELECT * FROM produits WHERE id = ?");
                    $stmt->execute([$productId]);
                    $product = $stmt->fetch();
                    if ($product):
                        $prix = $product['prix_promo'] ?? $product['prix'];
                ?>
                <div class="summary-item">
                    <div class="summary-item-info">
                        <strong><?php echo htmlspecialchars($product['nom']); ?></strong>
                        <span>x <?php echo $quantity; ?></span>
                    </div>
                    <span class="summary-item-price">
                        <?php echo number_format($prix * $quantity, 0, ',', ' '); ?> FCFA
                    </span>
                </div>
                <?php 
                    endif;
                endforeach; 
                ?>
                
                <div class="summary-divider"></div>
                
                <div class="summary-line">
                    <span>Sous-total</span>
                    <span><?php echo number_format($total, 0, ',', ' '); ?> FCFA</span>
                </div>
                
                <div class="summary-line">
                    <span>Livraison</span>
                    <span class="text-success">Gratuite</span>
                </div>
                
                <div class="summary-total">
                    <strong>Total</strong>
                    <strong><?php echo number_format($total, 0, ',', ' '); ?> FCFA</strong>
                </div>
                
                <div class="form-group">
    <h2>Mode de paiement *</h2>
    <label>
        <input type="radio" name="payment_method" value="wave" required> Wave
    </label><br>
    <label>
        <input type="radio" name="payment_method" value="orange_money"> Orange Money
    </label>
</div>

<div class="form-group">
    <label for="phone_number">Numéro de téléphone pour le paiement *</label>
    <input type="tel" id="phone_number" name="phone_number" required placeholder="+225 XX XX XX XX XX">
</div>

            </div>
        </div>
    </div>
</div>

<style>
.checkout-page {
    padding: 60px 0;
}

.checkout-layout {
    display: grid;
    grid-template-columns: 1fr 400px;
    gap: 50px;
}

.checkout-form-section h2,
.checkout-summary h2 {
    font-family: var(--font-display);
    font-size: 1.8rem;
    color: var(--color-primary);
    margin-bottom: 30px;
}

.checkout-form {
    background: var(--color-white);
    padding: 40px;
}

.checkout-summary {
    background: var(--color-white);
    padding: 30px;
    height: fit-content;
    position: sticky;
    top: 100px;
}

.summary-item {
    display: flex;
    justify-content: space-between;
    align-items: start;
    margin-bottom: 20px;
    padding-bottom: 20px;
    border-bottom: 1px solid var(--color-border);
}

.summary-item-info {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.summary-item-info strong {
    color: var(--color-primary);
}

.summary-item-info span {
    font-size: 0.9rem;
    color: var(--color-text-light);
}

.summary-item-price {
    font-weight: 600;
    color: var(--color-primary);
}

.summary-divider {
    height: 2px;
    background: var(--color-border);
    margin: 25px 0;
}

.summary-line {
    display: flex;
    justify-content: space-between;
    margin-bottom: 15px;
    color: var(--color-text);
}

.summary-total {
    display: flex;
    justify-content: space-between;
    font-size: 1.4rem;
    padding: 25px 0;
    margin: 20px 0;
    border-top: 2px solid var(--color-primary);
    border-bottom: 2px solid var(--color-primary);
    color: var(--color-primary);
}

.payment-info {
    background: var(--color-light);
    padding: 20px;
    margin-top: 25px;
}

.payment-info p {
    margin-bottom: 10px;
}

.payment-info .small {
    font-size: 0.85rem;
    color: var(--color-text-light);
}

.text-success {
    color: var(--color-success);
    font-weight: 600;
}

@media (max-width: 968px) {
    .checkout-layout {
        grid-template-columns: 1fr;
    }
    
    .checkout-summary {
        position: static;
    }
}
</style>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
