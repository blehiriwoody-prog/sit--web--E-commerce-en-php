<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../config/database.php';

if (!isAdmin()) {
    header('Location: /ecommerce-php/login.php');
    exit;
}

$conn = getConnection();

/* =========================
   CHANGER STATUT + ENREGISTRER VENTE
========================= */
if (isset($_POST['change_status'])) {

    $newStatus = $_POST['statut'];
    $orderId = (int)$_POST['order_id'];

    try {

        $conn->beginTransaction();

        // Vérifier ancien statut
        $check = $conn->prepare("SELECT statut FROM commandes WHERE id = ?");
        $check->execute([$orderId]);
        $oldStatus = $check->fetchColumn();

        // Mettre à jour le statut
        $update = $conn->prepare("UPDATE commandes SET statut = ? WHERE id = ?");
        $update->execute([$newStatus, $orderId]);

        // 🎯 Si devient livrée et n'était pas déjà livrée
        if ($newStatus === 'livree' && $oldStatus !== 'livree') {

            // Récupérer produits de la commande
            $details = $conn->prepare("
                SELECT produit_id, quantite
                FROM commande_details
                WHERE commande_id = ?
            ");
            $details->execute([$orderId]);
            $produits = $details->fetchAll(PDO::FETCH_ASSOC);

            foreach ($produits as $prod) {

                // Insérer dans ventes
                $insert = $conn->prepare("
                    INSERT INTO ventes (produit_id, quantite, date_vente)
                    VALUES (?, ?, NOW())
                ");
                $insert->execute([
                    $prod['produit_id'],
                    $prod['quantite']
                ]);
            }
        }

        $conn->commit();

    } catch (Exception $e) {
        $conn->rollBack();
        die("Erreur : " . $e->getMessage());
    }

    header("Location: orders.php");
    exit;
}

/* =========================
   PAGINATION
========================= */
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;

$offset = ($page - 1) * $limit;

/* =========================
   FILTRE PAR STATUT
========================= */
$where = "";
$params = [];

if (!empty($_GET['statut'])) {
    $where = "WHERE c.statut = ?";
    $params[] = $_GET['statut'];
}

/* =========================
   TOTAL POUR PAGINATION
========================= */
$countStmt = $conn->prepare("
    SELECT COUNT(*) as total
    FROM commandes c
    $where
");
$countStmt->execute($params);
$totalOrders = $countStmt->fetch()['total'];
$totalPages = ceil($totalOrders / $limit);

/* =========================
   RECUPERATION COMMANDES
========================= */
$sql = "
SELECT c.*, u.nom, u.prenom, u.email
FROM commandes c
JOIN users u ON c.user_id = u.id
$where
ORDER BY c.created_at DESC
LIMIT $limit OFFSET $offset
";

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = "Gestion des commandes";
require_once __DIR__ . '/../includes/header.php';
?>

<div class="admin-wrapper">
    <h1>Gestion des commandes</h1>

    <form method="GET" class="filter-bar">
        <select name="statut">
            <option value="">Tous les statuts</option>
            <option value="en_attente">En attente</option>
            <option value="confirmee">Confirmée</option>
            <option value="expediee">Expédiée</option>
            <option value="livree">Livrée</option>
            <option value="annulee">Annulée</option>
        </select>
        <button type="submit">Filtrer</button>
    </form>

    <div class="table-container">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Client</th>
                    <th>Email</th>
                    <th>Total</th>
                    <th>Statut</th>
                    <th>Date</th>
                    <th>Modifier</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order): ?>
                <tr>
                    <td>#<?= $order['id']; ?></td>
                    <td><?= htmlspecialchars($order['prenom'].' '.$order['nom']); ?></td>
                    <td><?= htmlspecialchars($order['email']); ?></td>
                    <td><?= number_format($order['total'], 0, ',', ' '); ?> FCFA</td>
                    <td>
                        <span class="status-badge status-<?= $order['statut']; ?>">
                            <?= ucfirst($order['statut']); ?>
                        </span>
                    </td>
                    <td><?= date('d/m/Y H:i', strtotime($order['created_at'])); ?></td>
                    <td>
                        <form method="POST">
                            <input type="hidden" name="order_id" value="<?= $order['id']; ?>">
                            <select name="statut">
                                <option value="en_attente">En attente</option>
                                <option value="confirmee">Confirmée</option>
                                <option value="expediee">Expédiée</option>
                                <option value="livree">Livrée</option>
                                <option value="annulee">Annulée</option>
                            </select>
                            <button type="submit" name="change_status">✔</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="pagination">
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a class="<?= ($i == $page) ? 'active' : ''; ?>"
               href="?page=<?= $i; ?><?= isset($_GET['statut']) ? '&statut='.$_GET['statut'] : ''; ?>">
                <?= $i; ?>
            </a>
        <?php endfor; ?>
    </div>
</div>
<style>

/* Wrapper */
.admin-wrapper {
    padding: 30px;
    background: #f4f6f9;
    min-height: 100vh;
    font-family: 'Segoe UI', sans-serif;
}

.admin-wrapper h1 {
    margin-bottom: 25px;
    font-size: 26px;
    font-weight: 600;
    color: #2c3e50;
}

/* Filter */
.filter-bar {
    margin-bottom: 20px;
    display: flex;
    gap: 10px;
}

.filter-bar select,
.filter-bar button {
    padding: 8px 12px;
    border-radius: 6px;
    border: 1px solid #ddd;
    font-size: 14px;
}

.filter-bar button {
    background: #b82e05;
    color: white;
    border: none;
    cursor: pointer;
    transition: 0.3s;
}

.filter-bar button:hover {
    background: #a74504;
}

/* Table container */
.table-container {
    background: #ffffff;
    border-radius: 12px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.05);
    overflow: hidden;
}

/* Table */
.admin-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 14px;
}

.admin-table thead {
    background: linear-gradient(90deg, #ae2c01, #c41902);
    color: white;
}

.admin-table th {
    padding: 14px;
    text-align: left;
    font-weight: 600;
    letter-spacing: 0.5px;
}

.admin-table td {
    padding: 14px;
    border-bottom: 1px solid #f0f0f0;
}

.admin-table tbody tr {
    transition: 0.2s;
}

.admin-table tbody tr:hover {
    background: #f8f9fc;
    transform: scale(1.005);
}

/* Montant */
.amount {
    font-weight: 600;
    color: #1cc88a;
}

/* Status badge */
.status-badge {
    padding: 6px 10px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    display: inline-block;
}

.status-en_attente {
    background: #fff3cd;
    color: #856404;
}

.status-confirmee {
    background: #d1ecf1;
    color: #0c5460;
}

.status-expediee {
    background: #d4edda;
    color: #155724;
}

.status-livree {
    background: #28a745;
    color: white;
}

.status-annulee {
    background: #f8d7da;
    color: #721c24;
}

/* Select modifier */
.status-form select {
    padding: 6px;
    border-radius: 6px;
    border: 1px solid #ccc;
    font-size: 13px;
}

.status-form button {
    padding: 6px 10px;
    border: none;
    border-radius: 6px;
    background: #c03f03;
    color: white;
    cursor: pointer;
    transition: 0.3s;
}

.status-form button:hover {
    background: #d22903;
}

/* Pagination */
.pagination {
    margin-top: 20px;
    display: flex;
    gap: 6px;
}

.pagination a {
    padding: 8px 14px;
    border-radius: 6px;
    text-decoration: none;
    font-size: 14px;
    background: #ffffff;
    border: 1px solid #ddd;
    color: #555;
    transition: 0.3s;
}

.pagination a:hover {
    background: #b93c02;
    color: white;
    border-color: #ba1304;
}

.pagination a.active {
    background: #af1b01;
    color: white;
    border-color: #cf2903;
    font-weight: bold;
}

</style>
