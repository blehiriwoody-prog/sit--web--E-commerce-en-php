<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/session.php';

// Vérifier si l'utilisateur est connecté
if (!isLoggedIn()) {
    header('Location: /ecommerce-php/login.php');
    exit;
}

// Récupérer l'ID de la commande depuis l'URL
$commandeId = intval($_GET['id'] ?? 0);

if ($commandeId <= 0) {
    header('Location: /ecommerce-php/index.php');
    exit;
}

$conn = getConnection();

// Récupérer les détails de la commande
$stmt = $conn->prepare("
    SELECT c.*, u.prenom, u.nom 
    FROM commandes c 
    JOIN users u ON c.user_id = u.id 
    WHERE c.id = ? AND c.user_id = ?
");
$stmt->execute([$commandeId, getUserId()]);
$commande = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$commande) {
    header('Location: /ecommerce-php/index.php');
    exit;
}

// Récupérer les produits de la commande
$stmt = $conn->prepare("
    SELECT cd.quantite, cd.prix_unitaire, p.nom 
    FROM commande_details cd
    JOIN produits p ON cd.produit_id = p.id
    WHERE cd.commande_id = ?
");
$stmt->execute([$commandeId]);
$produits = $stmt->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = "Commande réussie - Boutique GOLABELSANDALE";
require_once __DIR__ . '/includes/header.php';
?>

<div class="container" style="max-width:800px;margin:40px auto;">
    
    <h1>Merci <?= htmlspecialchars($commande['prenom'] . ' ' . $commande['nom']); ?> ! </h1>
    <p>Votre commande <strong>#<?= htmlspecialchars($commande['id']); ?></strong> a été passée avec succès.</p>

    <hr>

    <h2>Détails de la commande :</h2>

    <?php if (!empty($produits)): ?>
        <ul style="list-style:none;padding:0;">
            <?php foreach ($produits as $prod): ?>
                <li style="padding:10px 0;border-bottom:1px solid #eee;">
                    <strong><?= htmlspecialchars($prod['nom']); ?></strong><br>
                    Quantité : <?= intval($prod['quantite']); ?><br>
                    Total : 
                    <?= number_format($prod['prix_unitaire'] * $prod['quantite'], 0, ',', ' '); ?> FCFA
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p>Aucun produit trouvé pour cette commande.</p>
    <?php endif; ?>

    <hr>

    <p><strong>Total général :</strong> 
        <?= number_format($commande['total'], 0, ',', ' '); ?> FCFA
    </p>

    <p><strong>Adresse de livraison :</strong><br>
        <?= nl2br(htmlspecialchars($commande['adresse_livraison'])); ?>
    </p>

    <br>

    <a href="/ecommerce-php/index.php" class="btn-primary" 
       style="display:inline-block;padding:10px 20px;text-decoration:none;">
        Retour à la boutique
    </a>

</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
