<?php
require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/vendor/autoload.php'; // Si tu utilises PHPMailer

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    if (empty($email)) {
        $error = "Veuillez entrer votre email.";
    } else {
        $conn = getConnection();
        $stmt = $conn->prepare("SELECT id, prenom FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            $error = "Aucun compte trouvé avec cet email.";
        } else {
            // Générer un token unique et expiration
            $token = bin2hex(random_bytes(16));
            $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

            $stmt = $conn->prepare("UPDATE users SET reset_token = ?, reset_expires = ? WHERE id = ?");
            $stmt->execute([$token, $expires, $user['id']]);

            // Envoyer email
            $resetLink = "http://ton-site.com/ecommerce-php/reset_password.php?token=$token";

            $subject = "Réinitialisation de votre mot de passe";
            $message = "Bonjour {$user['prenom']},\n\nCliquez sur ce lien pour réinitialiser votre mot de passe : $resetLink\n\nCe lien expire dans 1 heure.";
            $headers = "From: noreply@ton-site.com";

            mail($email, $subject, $message, $headers);

            $success = "Un email avec le lien de réinitialisation a été envoyé.";
        }
    }
}
?>

<h1>Mot de passe oublié</h1>

<?php if ($error): ?>
    <div style="color:red"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<?php if ($success): ?>
    <div style="color:green"><?= htmlspecialchars($success) ?></div>
<?php endif; ?>

<form method="POST">
    <label>Email *</label><br>
    <input type="email" name="email" required><br><br>
    <button type="submit">Envoyer le lien</button>
</form>
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
