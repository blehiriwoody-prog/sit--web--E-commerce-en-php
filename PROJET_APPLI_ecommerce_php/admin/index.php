<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../config/database.php';

if (!isAdmin()) {
    header('Location: /ecommerce-php/login.php');
    exit;
}

$conn = getConnection();

// Statistiques
$statsProducts = $conn->query("SELECT COUNT(*) as count FROM produits")->fetch()['count'];
$statsCategories = $conn->query("SELECT COUNT(*) as count FROM categories")->fetch()['count'];
$statsOrders = $conn->query("SELECT COUNT(*) as count FROM commandes")->fetch()['count'];
$statsUsers = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'client'")->fetch()['count'];

// Dernières commandes
$recentOrders = $conn->query("SELECT c.*, u.nom, u.prenom 
                              FROM commandes c 
                              JOIN users u ON c.user_id = u.id 
                              ORDER BY c.created_at DESC 
                              LIMIT 5")->fetchAll();

$pageTitle = 'Administration - GOLABELSANDALE';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="admin-layout">
    <aside class="admin-sidebar">
        <h2>Administration</h2>
        <nav class="admin-nav">
            <a href="/ecommerce-php/admin/index.php" class="active"> Tableau de bord</a>
            <a href="/ecommerce-php/admin/products.php"> Produits</a>
            <a href="/ecommerce-php/admin/categories.php"> Catégories</a>
            <a href="/ecommerce-php/admin/orders.php"> Commandes</a>
            <a href="/ecommerce-php/admin/users.php"> Utilisateurs</a>
            <a href="/ecommerce-php/admin/ventes.php"> Ventes</a>
            <a href="/ecommerce-php/index.php">← Retour au site</a>
        </nav>
    </aside>
    
    <main class="admin-content">
        <div class="admin-header">
            <h1>Tableau de bord</h1>
        </div>
        
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">📦</div>
                <div class="stat-info">
                    <h3><?php echo $statsProducts; ?></h3>
                    <p>Produits</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">📑</div>
                <div class="stat-info">
                    <h3><?php echo $statsCategories; ?></h3>
                    <p>Catégories</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">🛒</div>
                <div class="stat-info">
                    <h3><?php echo $statsOrders; ?></h3>
                    <p>Commandes</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">👥</div>
                <div class="stat-info">
                    <h3><?php echo $statsUsers; ?></h3>
                    <p>Clients</p>
                </div>
            </div>
        </div>
        
        <div class="admin-section">
            <h2>Dernières commandes</h2>
            
            <?php if (empty($recentOrders)): ?>
                <p>Aucune commande pour le moment</p>
            <?php else: ?>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Client</th>
                            <th>Total</th>
                            <th>Statut</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentOrders as $order): ?>
                        <tr>
                            <td>#<?php echo $order['id']; ?></td>
                            <td><?php echo htmlspecialchars($order['prenom'] . ' ' . $order['nom']); ?></td>
                            <td><?php echo number_format($order['total'], 0, ',', ' '); ?> FCFA</td>
                            <td><span class="status-badge status-<?php echo $order['statut']; ?>"><?php echo $order['statut']; ?></span></td>
                            <td><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></td>
                            <td>
                                <a href="/ecommerce-php/admin/order-detail.php?id=<?php echo $order['id']; ?>" class="btn-small">Voir</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </main>
</div>

<style>
.admin-layout {
    display: grid;
    grid-template-columns: 250px 1fr;
    min-height: calc(100vh - 200px);
}

.admin-sidebar {
    background: var(--color-primary);
    color: var(--color-white);
    padding: 30px 0;
}

.admin-sidebar h2 {
    font-family: var(--font-display);
    padding: 0 25px;
    margin-bottom: 30px;
    font-size: 1.5rem;
}

.admin-nav a {
    display: block;
    padding: 15px 25px;
    color: var(--color-white);
    text-decoration: none;
    transition: var(--transition);
    border-left: 3px solid transparent;
}

.admin-nav a:hover,
.admin-nav a.active {
    background: rgba(255,255,255,0.1);
    border-left-color: var(--color-secondary);
}

.admin-content {
    padding: 40px;
    background: var(--color-light);
}

.admin-header {
    margin-bottom: 40px;
}

.admin-header h1 {
    font-family: var(--font-display);
    font-size: 2.5rem;
    color: var(--color-primary);
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 25px;
    margin-bottom: 50px;
}

.stat-card {
    background: var(--color-white);
    padding: 25px;
    display: flex;
    align-items: center;
    gap: 20px;
    box-shadow: var(--shadow-sm);
}

.stat-icon {
    font-size: 3rem;
}

.stat-info h3 {
    font-size: 2rem;
    color: var(--color-primary);
    margin-bottom: 5px;
}

.stat-info p {
    color: var(--color-text-light);
}

.admin-section {
    background: var(--color-white);
    padding: 30px;
    box-shadow: var(--shadow-sm);
    margin-bottom: 30px;
}

.admin-section h2 {
    font-family: var(--font-display);
    font-size: 1.8rem;
    color: var(--color-primary);
    margin-bottom: 25px;
}

.admin-table {
    width: 100%;
    border-collapse: collapse;
}

.admin-table th,
.admin-table td {
    padding: 15px;
    text-align: left;
    border-bottom: 1px solid var(--color-border);
}

.admin-table th {
    background: var(--color-light);
    font-weight: 600;
    color: var(--color-primary);
}

.admin-table tr:hover {
    background: var(--color-light);
}

.status-badge {
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 600;
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

.btn-small {
    padding: 6px 15px;
    background: var(--color-secondary);
    color: var(--color-white);
    text-decoration: none;
    font-size: 0.85rem;
    border-radius: 3px;
    transition: var(--transition);
}

.btn-small:hover {
    background: var(--color-accent);
}
</style>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
