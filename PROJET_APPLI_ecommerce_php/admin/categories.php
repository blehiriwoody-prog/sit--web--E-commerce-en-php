<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../config/database.php';

// Vérifie si l'utilisateur est admin
if (!isAdmin()) {
    header('Location: /ecommerce-php/login.php');
    exit;
}

$conn = getConnection();

// ===== SUPPRESSION CATEGORIE =====
if (isset($_POST['delete_category'])) {
    $catId = intval($_POST['category_id']);
    if ($catId > 0) {
        // Optionnel : vérifier si des produits existent dans cette catégorie avant suppression
        $stmtCheck = $conn->prepare("SELECT COUNT(*) FROM produits WHERE category_id = ?");
        $stmtCheck->execute([$catId]);
        if ($stmtCheck->fetchColumn() == 0) {
            $stmtDel = $conn->prepare("DELETE FROM categories WHERE id = ?");
            $stmtDel->execute([$catId]);
            $_SESSION['success'] = "Catégorie supprimée avec succès !";
        } else {
            $_SESSION['error'] = "Impossible de supprimer : des produits existent dans cette catégorie.";
        }
        header('Location: categorie.php');
        exit;
    }
}

// ===== FILTRAGE PAR CATEGORIE =====
$selectedCategory = $_GET['category_id'] ?? '';
$selectedCategory = intval($selectedCategory);

// Récupère toutes les catégories pour le filtre et la suppression
$categories = $conn->query("SELECT id, nom FROM categories ORDER BY nom ASC")->fetchAll();

// Récupère les produits selon la catégorie sélectionnée
if ($selectedCategory > 0) {
    $stmt = $conn->prepare("
        SELECT p.*, c.nom AS category_name
        FROM produits p
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE p.category_id = ?
        ORDER BY p.id DESC
    ");
    $stmt->execute([$selectedCategory]);
    $products = $stmt->fetchAll();
} else {
    $stmt = $conn->query("
        SELECT p.*, c.nom AS category_name
        FROM produits p
        LEFT JOIN categories c ON p.category_id = c.id
        ORDER BY c.nom ASC, p.id DESC
    ");
    $products = $stmt->fetchAll();
}

$pageTitle = 'Produits par catégorie';
require_once __DIR__ . '/../includes/header.php';
?>

<style>
    .admin-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
        font-family: 'Montserrat', sans-serif;
    }
    .admin-table th, .admin-table td {
        padding: 12px 15px;
        border: 1px solid #D2B48C;
        text-align: left;
    }
    .admin-table th {
        background-color: #8B4513;
        color: #FFF8F0;
    }
    .admin-table tr:nth-child(even) {
        background-color: #FFF8F0;
    }
    .admin-table tr:hover {
        background-color: #A0522D;
        color: #FFF8F0;
        transition: 0.3s;
    }
    select, button {
        padding: 8px 12px;
        margin-right: 10px;
        font-size: 0.9rem;
        border: 1px solid #D2B48C;
        border-radius: 4px;
        background-color: #FFF8F0;
        cursor: pointer;
        transition: all 0.3s;
    }
    button:hover, select:hover {
        background-color: #A0522D;
        color: #FFF8F0;
    }
    .error { color:red; font-weight:bold; }
    .success { color:green; font-weight:bold; }
</style>

<div class="container">
    <h1>Produits par catégorie</h1>

    <?php if(isset($_SESSION['success'])): ?>
        <p class="success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></p>
    <?php endif; ?>
    <?php if(isset($_SESSION['error'])): ?>
        <p class="error"><?= $_SESSION['error']; unset($_SESSION['error']); ?></p>
    <?php endif; ?>

    <!-- ===== FILTRE PAR CATEGORIE ===== -->
    <form method="GET" style="margin-bottom: 20px;">
        <select name="category_id">
            <option value="">-- Toutes les catégories --</option>
            <?php foreach ($categories as $cat): ?>
                <option value="<?= $cat['id'] ?>" <?= ($selectedCategory == $cat['id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($cat['nom']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit">Filtrer</button>
    </form>

    <!-- ===== TABLEAU CATEGORIES AVEC SUPPRESSION ===== -->
    <h2>Catégories</h2>
    <table class="admin-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nom</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($categories as $cat): ?>
                <tr>
                    <td><?= $cat['id'] ?></td>
                    <td><?= htmlspecialchars($cat['nom']) ?></td>
                    <td>
                        <form method="POST" onsubmit="return confirm('Supprimer cette catégorie ?')">
                            <input type="hidden" name="category_id" value="<?= $cat['id'] ?>">
                            <button type="submit" name="delete_category">Supprimer</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- ===== TABLEAU PRODUITS ===== -->
    <h2>Produits</h2>
    <table class="admin-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nom</th>
                <th>Catégorie</th>
                <th>Stock</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php if($products): ?>
                <?php foreach($products as $prod): ?>
                    <tr>
                        <td><?= $prod['id'] ?></td>
                        <td><?= htmlspecialchars($prod['nom']) ?></td>
                        <td><?= htmlspecialchars($prod['category_name'] ?? 'Aucune') ?></td>
                        <td><?= $prod['stock'] ?></td>
                        <td>
                            <?= ($prod['stock'] <= 0) 
                                ? '<span style="color:red;font-weight:bold;">Épuisé</span>' 
                                : '<span style="color:green;font-weight:bold;">Disponible</span>' ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="5" style="text-align:center;">Aucun produit trouvé.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
