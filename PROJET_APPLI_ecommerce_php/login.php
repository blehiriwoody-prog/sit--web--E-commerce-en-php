<?php
require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/config/database.php';

$error = '';
$success = '';

// Si déjà connecté, rediriger
if (isLoggedIn()) {
    $role = $_SESSION['role'] ?? 'user';
    if ($role === 'admin') {
        header('Location: /ecommerce-php/admin/index.php');
    } else {
        header('Location: /ecommerce-php/index.php');
    }
    exit;
}

// Traitement POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $_SESSION['error'] = 'Veuillez remplir tous les champs';
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }

    $conn = getConnection();
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];

        if ($user['role'] === 'admin') {
            header('Location: /ecommerce-php/admin/index.php');
        } else {
            header('Location: /ecommerce-php/index.php');
        }
        exit;
    } else {
        $_SESSION['error'] = 'Email ou mot de passe incorrect';
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }
}

// Récupération des messages PRG
$error = $_SESSION['error'] ?? '';
$success = $_SESSION['success'] ?? '';
unset($_SESSION['error'], $_SESSION['success']);

$pageTitle = 'Connexion - Boutique golabel sandale';
require_once __DIR__ . '/includes/header.php';
?>

<div class="auth-container">
    <div class="auth-box">
        <h1 class="auth-title">Connexion</h1>
        <p class="auth-subtitle">Accédez à votre compte</p>

        <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <form method="POST" action="" class="auth-form">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required 
                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label for="password">Mot de passe</label>
                <input type="password" id="password" name="password" required>
            </div>

            <button type="submit" class="btn-primary btn-full">Se connecter</button>
        </form>

        <div class="auth-links">
            <p>Pas encore de compte ? <a href="/ecommerce-php/register.php">Créer un compte</a></p>
            <p><a href="/ecommerce-php/forgot_password.php">Mot de passe oublié ?</a></p>

        </div>
    </div>
</div>

<style>
.auth-container {
    min-height: 70vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 60px 20px;
}

.auth-box {
    background: var(--color-white);
    padding: 50px;
    max-width: 450px;
    width: 100%;
    box-shadow: var(--shadow-lg);
}

.auth-title {
    font-family: var(--font-display);
    font-size: 2.5rem;
    color: var(--color-primary);
    text-align: center;
    margin-bottom: 10px;
}

.auth-subtitle {
    text-align: center;
    color: var(--color-text-light);
    margin-bottom: 40px;
}

.auth-form { margin-bottom: 30px; }

.form-group { margin-bottom: 25px; }

.form-group label { display: block; margin-bottom: 8px; font-weight: 600; color: var(--color-text); }

.form-group input {
    width: 100%;
    padding: 12px 15px;
    border: 1px solid var(--color-border);
    background: var(--color-light);
    font-size: 1rem;
    transition: var(--transition);
}

.form-group input:focus {
    outline: none;
    border-color: var(--color-secondary);
    background: var(--color-white);
}

.btn-full { width: 100%; }

.auth-links { text-align: center; }
.auth-links a { color: var(--color-secondary); text-decoration: none; font-weight: 600; }
.auth-links a:hover { text-decoration: underline; }

.alert { padding: 15px; margin-bottom: 25px; border-radius: 4px; }
.alert-error { background: #fee; color: var(--color-error); border: 1px solid var(--color-error); }
.alert-success { background: #efe; color: var(--color-success); border: 1px solid var(--color-success); }
</style>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
