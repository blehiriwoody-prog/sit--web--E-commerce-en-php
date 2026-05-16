<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../config/database.php';

if (!isAdmin()) {
    header('Location: /ecommerce-php/login.php');
    exit;
}

$conn = getConnection();

/* ==============================
   VENTES DU MOIS ACTUEL
============================== */

$stmt = $conn->prepare("
    SELECT 
        v.id, 
        v.quantite, 
        v.date_vente,
        p.nom AS produit_nom,
        COALESCE(p.prix_promo, p.prix) AS prix_unitaire
    FROM ventes v
    INNER JOIN produits p ON v.produit_id = p.id
    WHERE MONTH(v.date_vente) = MONTH(CURRENT_DATE())
    AND YEAR(v.date_vente) = YEAR(CURRENT_DATE())
    ORDER BY v.date_vente DESC
");

$stmt->execute();
$ventes = $stmt->fetchAll(PDO::FETCH_ASSOC);

$totalMois = 0;
foreach ($ventes as $vente) {
    $totalMois += $vente['prix_unitaire'] * $vente['quantite'];
}

$pageTitle = 'Ventes du mois';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="admin-content">
    <h1>Ventes du mois (<?= date('m/Y') ?>)</h1>

    <?php if(empty($ventes)): ?>
        <p>Aucune vente enregistrée pour ce mois.</p>
    <?php else: ?>

        <table border="1" cellpadding="10" cellspacing="0" width="100%">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Produit</th>
                    <th>Quantité</th>
                    <th>Prix unitaire</th>
                    <th>Total</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($ventes as $v): ?>
                <tr>
                    <td>#<?= $v['id'] ?></td>
                    <td><?= htmlspecialchars($v['produit_nom']) ?></td>
                    <td><?= $v['quantite'] ?></td>
                    <td><?= number_format($v['prix_unitaire'], 0, ',', ' ') ?> FCFA</td>
                    <td><?= number_format($v['prix_unitaire'] * $v['quantite'], 0, ',', ' ') ?> FCFA</td>
                    <td><?= date('d/m/Y H:i', strtotime($v['date_vente'])) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <h3 style="margin-top:20px;">
            Total du mois : 
            <?= number_format($totalMois, 0, ',', ' ') ?> FCFA
        </h3>

    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
<style>

/* Fond général */
.admin-content {
    padding: 40px;
    background: #f8f5f2;
    min-height: 100vh;
    font-family: 'Segoe UI', sans-serif;
}

/* Titre */
.admin-content h1 {
    color: #5c3a21;
    margin-bottom: 25px;
    font-weight: 700;
}

/* Tableau style carte */
.admin-content table {
    width: 100%;
    border-collapse: collapse;
    background: #ffffff;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 6px 18px rgba(92, 58, 33, 0.15);
}

/* Header */
.admin-content thead {
    background: linear-gradient(90deg, #5c3a21, #7b4b2a);
    color: #ffffff;
}

.admin-content th {
    padding: 15px;
    text-align: left;
    font-weight: 600;
    font-size: 14px;
    letter-spacing: 0.5px;
}

/* Cellules */
.admin-content td {
    padding: 14px 15px;
    border-bottom: 1px solid #f0e6dc;
    font-size: 14px;
    color: #4a2e1b;
}

/* Lignes alternées */
.admin-content tbody tr:nth-child(even) {
    background: #faf7f4;
}

/* Effet hover */
.admin-content tbody tr {
    transition: 0.3s ease;
}

.admin-content tbody tr:hover {
    background: #f1e6dd;
    transform: scale(1.005);
}

/* Prix en couleur marron fort */
.admin-content td:nth-child(4),
.admin-content td:nth-child(5) {
    font-weight: 600;
    color: #7b4b2a;
}

/* Total du mois */
.admin-content h3 {
    margin-top: 25px;
    padding: 15px;
    background: #ffffff;
    border-left: 6px solid #5c3a21;
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    border-radius: 8px;
    font-size: 18px;
    color: #5c3a21;
}

/* Message vide */
.admin-content p {
    background: #ffffff;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    color: #7b4b2a;
    font-weight: 500;
}

</style>

