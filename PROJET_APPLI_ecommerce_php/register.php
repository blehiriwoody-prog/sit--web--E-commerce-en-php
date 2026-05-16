<?php
require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/config/database.php';

$error = '';
$success = '';

// Si déjà connecté, rediriger vers le site
if (isLoggedIn()) {
    header('Location: /ecommerce-php/index.php');
    exit;
}

// Gestion du POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telephone = trim($_POST['telephone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($nom) || empty($prenom) || empty($email) || empty($password)) {
        $_SESSION['error'] = 'Veuillez remplir tous les champs obligatoires';
    } elseif ($password !== $confirm_password) {
        $_SESSION['error'] = 'Les mots de passe ne correspondent pas';
    } elseif (strlen($password) < 6) {
        $_SESSION['error'] = 'Le mot de passe doit contenir au moins 6 caractères';
    } else {
        $conn = getConnection();

        // Vérifier si l'email existe déjà
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);

        if ($stmt->fetch()) {
            $_SESSION['error'] = 'Cet email est déjà utilisé';
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (nom, prenom, email, telephone, password) VALUES (?, ?, ?, ?, ?)");

            if ($stmt->execute([$nom, $prenom, $email, $telephone, $hashedPassword])) {
                $userId = $conn->lastInsertId();
                $_SESSION['user_id'] = $userId;
                $_SESSION['role'] = 'user';
                $_SESSION['success'] = "Bienvenue $prenom ! Votre compte a été créé avec succès.";
            } else {
                $_SESSION['error'] = 'Erreur lors de la création du compte';
            }
        }
    }

    // Redirection PRG pour éviter la répétition de formulaire
    header('Location: /ecommerce-php/index.php');
    exit;
}

// Récupération des messages PRG
$error = $_SESSION['error'] ?? '';
$success = $_SESSION['success'] ?? '';
unset($_SESSION['error'], $_SESSION['success']);

$pageTitle = 'Inscription - Boutique Élégance';
require_once __DIR__ . '/includes/header.php';
?>

<div class="auth-container">
    <div class="auth-box">
        <h1 class="auth-title">Inscription</h1>
        <p class="auth-subtitle">Créez votre compte</p>

        <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="" class="auth-form">
            <div class="form-row">
                <div class="form-group">
                    <label for="prenom">Prénom *</label>
                    <input type="text" id="prenom" name="prenom" required value="<?= htmlspecialchars($_POST['prenom'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="nom">Nom *</label>
                    <input type="text" id="nom" name="nom" required value="<?= htmlspecialchars($_POST['nom'] ?? '') ?>">
                </div>
            </div>

            <div class="form-group">
                <label for="email">Email *</label>
                <input type="email" id="email" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label for="telephone">Téléphone</label>
                <input type="tel" id="telephone" name="telephone" value="<?= htmlspecialchars($_POST['telephone'] ?? '') ?>">
            </div>

            <div class="form-group">
    <label for="password">Mot de passe *</label>
    <input type="password" id="password" name="password" required>
    <small>Au moins 6 caractères</small><br>
    <input type="checkbox" onclick="togglePassword()"> Afficher le mot de passe
</div>

<div class="form-group">
    <label for="confirm_password">Confirmer le mot de passe *</label>
    <input type="password" id="confirm_password" name="confirm_password" required>
</div>

<script>
function togglePassword() {
    var password = document.getElementById("password");
    var confirmPassword = document.getElementById("confirm_password");

    if (password.type === "password") {
        password.type = "text";
        confirmPassword.type = "text";
    } else {
        password.type = "password";
        confirmPassword.type = "password";
    }
}
</script>

            <button type="submit" class="btn-primary btn-full">Créer mon compte</button>
        </form>

        <div class="auth-links">
            <p>Déjà un compte ? <a href="/ecommerce-php/login.php">Se connecter</a></p>
        </div>
    </div>
</div>

<style>
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
.form-group small { display: block; margin-top: 5px; color: var(--color-text-light); font-size: 0.85rem; }
@media (max-width: 768px) { .form-row { grid-template-columns: 1fr; } }
</style>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
<style>
/* ==== Container principal ==== */
.auth-container {
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 90vh;
    background: #f5f1ed; /* couleur crème douce */
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

/* ==== Box formulaire ==== */
.auth-box {
    background: #ffffff;
    padding: 40px 30px;
    border-radius: 12px;
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
    width: 100%;
    max-width: 500px;
}

/* ==== Titres ==== */
.auth-title {
    margin-bottom: 10px;
    font-size: 2rem;
    color: #5a3e36; /* marron élégant */
    text-align: center;
}

.auth-subtitle {
    margin-bottom: 30px;
    color: #8c6b5e;
    text-align: center;
    font-size: 1rem;
}

/* ==== Messages ==== */
.alert {
    padding: 10px 15px;
    border-radius: 6px;
    margin-bottom: 20px;
    font-size: 0.95rem;
}
.alert-error {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

/* ==== Formulaire ==== */
.auth-form .form-group {
    margin-bottom: 20px;
}

.auth-form label {
    display: block;
    margin-bottom: 5px;
    font-weight: 600;
    color: #5a3e36;
}

.auth-form input[type="text"],
.auth-form input[type="email"],
.auth-form input[type="tel"],
.auth-form input[type="password"] {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid #ccc;
    border-radius: 8px;
    box-sizing: border-box;
    font-size: 1rem;
}

.auth-form input:focus {
    border-color: #8c6b5e;
    outline: none;
    box-shadow: 0 0 5px rgba(140, 107, 94, 0.3);
}

.auth-form small {
    color: #8c6b5e;
    font-size: 0.85rem;
}

/* ==== Bouton ==== */
.btn-primary {
    width: 100%;
    padding: 12px;
    background: #8c6b5e;
    color: #fff;
    border: none;
    border-radius: 8px;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: 0.3s;
}

.btn-primary:hover {
    background: #5a3e36;
}

/* ==== Lien ==== */
.auth-links {
    margin-top: 20px;
    text-align: center;
}

.auth-links a {
    color: #8c6b5e;
    text-decoration: none;
    font-weight: 600;
}

.auth-links a:hover {
    text-decoration: underline;
}

/* ==== Responsive ==== */
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
@media (max-width: 768px) { .form-row { grid-template-columns: 1fr; } }
</style>

