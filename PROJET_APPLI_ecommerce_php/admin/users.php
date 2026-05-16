<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../config/database.php';

if (!isAdmin()) {
    header('Location: /ecommerce-php/login.php');
    exit;
}

$conn = getConnection();

$clients = $conn->query("
    SELECT id, nom, prenom, email, created_at
    FROM users
    WHERE role = 'client'
    ORDER BY created_at DESC
")->fetchAll();

$pageTitle = 'Clients - Administration';
require_once __DIR__ . '/../includes/header.php';
?>

<h1>Liste des clients</h1>

<table class="admin-table">
    <thead>
        <tr>
            <th>ID</th>
            <th>Nom complet</th>
            <th>Email</th>
            <th>Date d'inscription</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($clients as $client): ?>
        <tr>
            <td><?php echo $client['id']; ?></td>
            <td><?php echo htmlspecialchars($client['prenom'].' '.$client['nom']); ?></td>
            <td><?php echo htmlspecialchars($client['email']); ?></td>
            <td><?php echo date('d/m/Y H:i', strtotime($client['created_at'])); ?></td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($clients)): ?>
        <tr>
            <td colspan="4">Aucun client trouvé.</td>
        </tr>
        <?php endif; ?>
    </tbody>
</table>

<style>
/* ===== TABLEAU STYLÉ ===== */
.admin-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 30px;
    font-family: 'Montserrat', sans-serif;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.admin-table thead {
    background-color: #A0522D; /* Marron moyen pour le header */
    color: #FFF8F0;           /* Blanc cassé */
}

.admin-table th,
.admin-table td {
    padding: 12px 15px;
    text-align: left;
    border-bottom: 1px solid #D2B48C; /* bordure douce */
}

.admin-table tbody tr {
    background-color: #FFF8F0; /* ligne claire */
    transition: background 0.3s, transform 0.2s;
}

.admin-table tbody tr:hover {
    background-color: #D2B48C; /* survol marron clair */
    color: #3B2F2F;             /* texte foncé */
    transform: scale(1.01);
}

.admin-table tbody tr td {
    vertical-align: middle;
}

.admin-table tbody tr td:first-child {
    font-weight: 600;
}

h1 {
    font-family: 'Playfair Display', serif;
    color: #8B4513; /* marron foncé */
    margin-top: 30px;
    text-align: center;
}

/* Responsive pour mobile */
@media (max-width: 768px) {
    .admin-table {
        display: block;
        overflow-x: auto;
        white-space: nowrap;
    }
}
</style>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
