<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/session.php';

if (!isAdmin()) {
    header('Location: /ecommerce-php/login.php');
    exit;
}

$conn = getConnection();

// ====== AJOUT PRODUIT ======
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product'])) {
    $nom = $_POST['nom'];
    $description = $_POST['description'];
    $prix = $_POST['prix'];
    $prix_promo = $_POST['prix_promo'] ?: null;
    $stock = $_POST['stock'];
    $featured = isset($_POST['featured']) ? 1 : 0;

    if (!empty($_POST['new_category'])) {
        $newCat = trim($_POST['new_category']);
        $stmtCheck = $conn->prepare("SELECT id FROM categories WHERE nom = ?");
        $stmtCheck->execute([$newCat]);
        $existing = $stmtCheck->fetch();
        if ($existing) {
            $category_id = $existing['id'];
        } else {
            $stmt = $conn->prepare("INSERT INTO categories (nom) VALUES (?)");
            $stmt->execute([$newCat]);
            $category_id = $conn->lastInsertId();
        }
    } else {
        $category_id = $_POST['category_id'] ?: null;
    }

    $image = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
        if (!in_array($_FILES['image']['type'], $allowedTypes)) die("Type de fichier non autorisé");
        if ($_FILES['image']['size'] > 2*1024*1024) die("Image trop volumineuse");

        $uploadDir = __DIR__ . '/../uploads/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

        $fileName = time() . '_' . basename($_FILES['image']['name']);
        $targetPath = $uploadDir . $fileName;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
            $image = 'uploads/' . $fileName;
        }
    }

    $status = ($stock <= 0) ? 'epuise' : 'disponible';

    $stmt = $conn->prepare("INSERT INTO produits (nom, description, prix, prix_promo, stock, category_id, image, featured, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$nom, $description, $prix, $prix_promo, $stock, $category_id, $image, $featured, $status]);

    $_SESSION['success'] = "Produit ajouté avec succès !";
    header('Location: products.php');
    exit;
}

// ====== RAVITAILLEMENT PRODUIT ======
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['restock_product'])) {
    $productId = intval($_POST['product_id']);
    $quantite = intval($_POST['ravitaillement']);

    if ($productId > 0 && $quantite > 0) {
        $date_now = date('Y-m-d H:i:s');

        $stmt = $conn->prepare("UPDATE produits SET stock = stock + ?, status = 'disponible', last_restock = ? WHERE id = ?");
        $stmt->execute([$quantite, $date_now, $productId]);

        $_SESSION['success'] = "Vous avez ravitaillé ce produit le " . date('d/m/Y H:i', strtotime($date_now));
    } else {
        $_SESSION['error'] = "Quantité ou produit invalide.";
    }

    header('Location: products.php');
    exit;
}

// ====== SUPPRESSION PRODUIT ======
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_product'])) {
    $productId = intval($_POST['product_id']);
    if ($productId > 0) {
        $stmt = $conn->prepare("SELECT COUNT(*) FROM ventes WHERE produit_id = ?");
        $stmt->execute([$productId]);
        $count = $stmt->fetchColumn();

        if ($count == 0) {
            $stmt = $conn->prepare("SELECT image FROM produits WHERE id = ?");
            $stmt->execute([$productId]);
            $product = $stmt->fetch();
            if ($product && $product['image'] && file_exists(__DIR__ . '/../' . $product['image'])) {
                unlink(__DIR__ . '/../' . $product['image']);
            }

            $stmt = $conn->prepare("DELETE FROM produits WHERE id = ?");
            $stmt->execute([$productId]);

            $_SESSION['success'] = "Produit supprimé avec succès !";
        } else {
            $_SESSION['error'] = "Impossible de supprimer ce produit : il a des ventes enregistrées.";
        }
    }
    header('Location: products.php');
    exit;
}

// ====== MODIFICATION PRODUIT ======
$editingProduct = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_product'])) {
    $editId = intval($_POST['edit_product_id']);
    $stmt = $conn->prepare("SELECT * FROM produits WHERE id = ?");
    $stmt->execute([$editId]);
    $editingProduct = $stmt->fetch();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_product'])) {
    $id = intval($_POST['update_product_id']);
    $nom = $_POST['nom'];
    $description = $_POST['description'];
    $prix = $_POST['prix'];
    $prix_promo = $_POST['prix_promo'] ?: null;
    $stock = $_POST['stock'];
    $category_id = $_POST['category_id'] ?: null;
    $featured = isset($_POST['featured']) ? 1 : 0;

    $image = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
        if (!in_array($_FILES['image']['type'], $allowedTypes)) die("Type de fichier non autorisé");
        if ($_FILES['image']['size'] > 2*1024*1024) die("Image trop volumineuse");

        $uploadDir = __DIR__ . '/../uploads/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

        $fileName = time() . '_' . basename($_FILES['image']['name']);
        $targetPath = $uploadDir . $fileName;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
            $image = 'uploads/' . $fileName;
        }
    }

    if ($image) {
        $stmt = $conn->prepare("UPDATE produits SET nom=?, description=?, prix=?, prix_promo=?, stock=?, category_id=?, image=?, featured=? WHERE id=?");
        $stmt->execute([$nom, $description, $prix, $prix_promo, $stock, $category_id, $image, $featured, $id]);
    } else {
        $stmt = $conn->prepare("UPDATE produits SET nom=?, description=?, prix=?, prix_promo=?, stock=?, category_id=?, featured=? WHERE id=?");
        $stmt->execute([$nom, $description, $prix, $prix_promo, $stock, $category_id, $featured, $id]);
    }

    $_SESSION['success'] = "Produit mis à jour avec succès !";
    header('Location: products.php');
    exit;
}

$categories = $conn->query("SELECT * FROM categories ORDER BY nom ASC")->fetchAll();

$products = $conn->query("
    SELECT p.*, 
           c.nom AS category_name, 
           COALESCE(SUM(v.quantite), 0) AS total_vendu
    FROM produits p
    LEFT JOIN categories c ON p.category_id = c.id
    LEFT JOIN ventes v ON p.id = v.produit_id
    GROUP BY p.id
    ORDER BY p.id DESC
")->fetchAll();
?>

<?php include __DIR__ . '/../includes/header.php'; ?>

<div class="container">
    <h1>Gérer les produits</h1>

    <?php if (isset($_SESSION['success'])): ?>
        <p style="color:green;"><?= $_SESSION['success']; unset($_SESSION['success']); ?></p>
    <?php endif; ?>
    <?php if (isset($_SESSION['error'])): ?>
        <p style="color:red;"><?= $_SESSION['error']; unset($_SESSION['error']); ?></p>
    <?php endif; ?>

    <h2>Ajouter un produit</h2>
    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="add_product" value="1">
        <label>Nom</label><br>
        <input type="text" name="nom" required><br><br>

        <label>Description</label><br>
        <textarea name="description" required></textarea><br><br>

        <label>Prix</label><br>
        <input type="number" name="prix" step="0.01" required><br><br>

        <label>Prix promo</label><br>
        <input type="number" name="prix_promo" step="0.01"><br><br>

        <label>Stock initial</label><br>
        <input type="number" name="stock" required><br><br>

        <label>Catégorie existante</label><br>
        <select name="category_id">
            <option value="">-- Aucune --</option>
            <?php foreach ($categories as $cat): ?>
                <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['nom']) ?></option>
            <?php endforeach; ?>
        </select><br><br>

        <label>Ou créer une nouvelle catégorie</label><br>
        <input type="text" name="new_category" placeholder="Ex: Sandales"><br><br>

        <label>Image du produit</label><br>
        <input type="file" name="image" accept="image/*" required><br><br>

        <label>Produit en avant</label>
        <input type="checkbox" name="featured"><br><br>

        <button type="submit" class="btn-primary">Ajouter</button>
    </form>

    <?php if ($editingProduct): ?>
    <h2>Modifier le produit #<?= $editingProduct['id'] ?></h2>
    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="update_product_id" value="<?= $editingProduct['id'] ?>">

        <label>Nom</label><br>
        <input type="text" name="nom" value="<?= htmlspecialchars($editingProduct['nom']) ?>" required><br><br>

        <label>Description</label><br>
        <textarea name="description" required><?= htmlspecialchars($editingProduct['description']) ?></textarea><br><br>

        <label>Prix</label><br>
        <input type="number" name="prix" step="0.01" value="<?= $editingProduct['prix'] ?>" required><br><br>

        <label>Prix promo</label><br>
        <input type="number" name="prix_promo" step="0.01" value="<?= $editingProduct['prix_promo'] ?>"><br><br>

        <label>Stock</label><br>
        <input type="number" name="stock" value="<?= $editingProduct['stock'] ?>" required><br><br>

        <label>Catégorie</label><br>
        <select name="category_id">
            <option value="">-- Aucune --</option>
            <?php foreach ($categories as $cat): ?>
                <option value="<?= $cat['id'] ?>" <?= ($editingProduct['category_id']==$cat['id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($cat['nom']) ?>
                </option>
            <?php endforeach; ?>
        </select><br><br>

        <label>Image (laisser vide pour conserver)</label><br>
        <input type="file" name="image" accept="image/*"><br><br>

        <label>Produit en avant</label>
        <input type="checkbox" name="featured" <?= $editingProduct['featured'] ? 'checked' : '' ?>><br><br>

        <button type="submit" name="update_product" class="btn-primary">Mettre à jour</button>
    </form>
    <?php endif; ?>

    <h2>Produits existants</h2>
    <table class="admin-table" border="1" cellpadding="10" cellspacing="0">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nom</th>
                <th>Catégorie</th>
                <th>Stock restant</th>
                <th>Status</th>
                <th>Dernier ravitaillement</th>
                <th>Vendus</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($products as $prod): 
                $stock_restant = max(0, $prod['stock']); 
            ?>
            <tr>
                <td><?= $prod['id'] ?></td>
                <td><?= htmlspecialchars($prod['nom']) ?></td>
                <td><?= htmlspecialchars($prod['category_name'] ?? 'Aucune') ?></td>
                <td>
                    <?php if ($stock_restant <= 0): ?>
                        <span style="color:red; font-weight:bold;">0</span>
                    <?php else: ?>
                        <span style="color:green; font-weight:bold;"><?= $stock_restant ?></span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if ($stock_restant <= 0): ?>
                        <span style="color:red; font-weight:bold;">Épuisé</span>
                    <?php else: ?>
                        <span style="color:green; font-weight:bold;">Disponible</span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if (!empty($prod['last_restock'])): ?>
                        <?= date('d/m/Y H:i', strtotime($prod['last_restock'])) ?>
                    <?php else: ?>
                        --
                    <?php endif; ?>
                </td>
                <td><?= $prod['total_vendu'] ?></td>
                <td>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="product_id" value="<?= $prod['id'] ?>">
                        <input type="number" name="ravitaillement" min="1" placeholder="Quantité" required style="width:80px;">
                        <button type="submit" name="restock_product" class="btn-primary">Ravitailler</button>
                    </form>

                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="edit_product_id" value="<?= $prod['id'] ?>">
                        <button type="submit" name="edit_product" class="btn-primary">Modifier</button>
                    </form>

                    <?php if ($prod['total_vendu'] == 0): ?>
                        <form method="POST" style="display:inline;" onsubmit="return confirm('Voulez-vous vraiment supprimer ce produit ?');">
                            <input type="hidden" name="product_id" value="<?= $prod['id'] ?>">
                            <input type="hidden" name="delete_product" value="1">
                            <button type="submit" class="btn-danger">Supprimer</button>
                        </form>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>

<style>
.container { max-width:1200px; margin:40px auto; padding:0 20px; font-family:Arial,sans-serif; }
h1,h2 { margin-bottom:20px; }
form input[type="text"], form input[type="number"], form textarea, form select, form input[type="file"] { width:100%; padding:6px 8px; margin:5px 0 15px 0; border:1px solid #ccc; border-radius:4px; box-sizing:border-box; }
form button { padding:6px 12px; border:none; border-radius:4px; cursor:pointer; }
.admin-table { width:100%; border-collapse:collapse; margin-top:30px; background:#fff; }
.admin-table th,.admin-table td { padding:10px 12px; border-bottom:1px solid #ddd; text-align:left; }
.admin-table tbody tr:nth-child(even){ background:#f9f9f9; }
.admin-table tbody tr:hover { background:#f1f1f1; }
.btn-danger { padding:4px 8px; border-radius:4px; cursor:pointer; }
.btn-primary { padding:4px 8px; border-radius:4px; cursor:pointer; background-color:#007bff; color:#fff; border:none; }
</style>
