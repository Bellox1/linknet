<?php
session_start();
require_once "../../back-office/config/database.php";

// Récupération des anciens comptes connectés depuis les cookies
$saved_accounts = [];
if (isset($_COOKIE['linknet_saved_accounts'])) {
    $saved_accounts = json_decode($_COOKIE['linknet_saved_accounts'], true) ?: [];
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);
    $remember_account = isset($_POST["remember_account"]);

    $stmt = $conn->prepare("SELECT id, password FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user["password"])) {
        $_SESSION["user"] = $user["id"];
        if (!isset($_SESSION["csrf_token"])) {
            $_SESSION["csrf_token"] = bin2hex(random_bytes(32)); // Générer un jeton sécurisé
        }

        // Sauvegarder le compte dans le cookie seulement si la case est cochée
        if ($remember_account) {
            $account_info = [
                'email' => $email,
                'timestamp' => time()
            ];
            $account_exists = false;
            foreach ($saved_accounts as $key => $account) {
                if ($account['email'] === $email) {
                    $saved_accounts[$key] = $account_info;
                    $account_exists = true;
                    break;
                }
            }
            if (!$account_exists) {
                $saved_accounts[] = $account_info;
            }
            if (count($saved_accounts) > 5) {
                array_shift($saved_accounts);
            }
            setcookie('linknet_saved_accounts', json_encode($saved_accounts), time() + (30 * 24 * 60 * 60), '/');
        }

        header("Location: ../index.php");
    } else {
        echo "Email ou mot de passe invalide !";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <title>Connexion</title>
    <link rel="stylesheet" href="/assets/css/login.css">
    
</head>
<body>
    <div class="login-container">
        <h2>Connexion à Linknet</h2>
        <!-- Comptes récents -->
        <?php if (!empty($saved_accounts)): ?>
            <div class="accounts-list">
                <?php foreach ($saved_accounts as $account): ?>
                    <div class="saved-account-circle" data-email="<?php echo htmlspecialchars($account['email']); ?>" onclick="fillAccount('<?php echo htmlspecialchars($account['email']); ?>')">
                        <div class="circle-email">
                            <?php echo strtoupper(substr($account['email'], 0, 1)); ?>
                        </div>
                        <div class="account-label">
                            <?php echo htmlspecialchars(explode('@', $account['email'])[0]); ?>
                        </div>
                        <button type="button" class="remove-account-circle" onclick="removeAccount('<?php echo htmlspecialchars($account['email']); ?>', event)">×</button>
                    </div>
                <?php endforeach; ?>
                <!-- Ajouter un compte -->
                <div class="saved-account-circle add-account" onclick="document.getElementById('email').focus();">
                    <div class="circle-add">
                        <svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <circle cx="16" cy="16" r="16" fill="#e4e6eb"/>
                            <path d="M16 10V22" stroke="#1877f2" stroke-width="2" stroke-linecap="round"/>
                            <path d="M10 16H22" stroke="#1877f2" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                    </div>
                    <div class="account-label">Ajouter</div>
                </div>
            </div>
        <?php endif; ?>
        <form method="POST">
            <input type="email" name="email" id="email" placeholder="Adresse e-mail" required>
            <input type="password" name="password" placeholder="Mot de passe" required>
            <div class="remember-account">
                <input type="checkbox" name="remember_account" id="remember_account">
                <label for="remember_account">Sauvegarder ce compte</label>
            </div>
            <button type="submit">Se connecter</button>
        </form>
        <p>Vous n'avez pas de compte ? <a href="register.php">Inscrivez-vous</a></p>
    </div>
    <script src="/assets/js/login-accounts.js"></script>
</body>
</html>