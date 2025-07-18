<?php
session_start();
require_once "../../back-office/config/database.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $email = trim($_POST["email"]);
    $password = password_hash($_POST["password"], PASSWORD_BCRYPT);
    
    // Récupération des valeurs de date de naissance
    $birthday_day = $_POST['birthday_day'];
    $birthday_month = $_POST['birthday_month'];
    $birthday_year = $_POST['birthday_year'];
    
    // Validation de la date
    if (checkdate($birthday_month, $birthday_day, $birthday_year)) {
        $birthday = sprintf('%04d-%02d-%02d', $birthday_year, $birthday_month, $birthday_day);

        // Vérifier si l'email existe déjà
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);

        if ($stmt->rowCount() > 0) {
            $error = "Email déjà enregistré !";
        } else {
            // Insertion dans la base de données
            $stmt = $conn->prepare("INSERT INTO users (username, email, password, birthday) VALUES (?, ?, ?, ?)");
            if ($stmt->execute([$username, $email, $password, $birthday])) {
                $_SESSION["user"] = $conn->lastInsertId();
                header("Location: ../index.php");
                exit;
            } else {
                $error = "Échec de l'inscription !";
            }
        }
    } else {
        $error = "Date de naissance invalide !";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <title>Inscription</title>
    <link rel="stylesheet" href="/assets/css/login.css">
    
</head>
<body>
    <div class="login-container">
        <h2>Inscription</h2>
        <?php if ($error): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        <form method="POST">
            <input type="text" name="username" placeholder="Nom d'utilisateur" value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" required><br>
            <input type="email" name="email" placeholder="Adresse e-mail" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required><br>
            <input type="password" name="password" placeholder="Mot de passe" required><br>
            
            <!-- Champ de date de naissance -->
            <div class="birthday-field">
                <div class="birthday-label">Date de naissance</div>
                <div class="birthday-container">
                    <!-- Jour (1-31) -->
                    <select name="birthday_day" class="birthday-select" required>
                        <option value="" disabled selected>Jour</option>
                        <?php for ($i = 1; $i <= 31; $i++): ?>
                            <option value="<?php echo $i; ?>" <?php echo ($_POST['birthday_day'] ?? '') == $i ? 'selected' : ''; ?>>
                                <?php echo $i; ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                    
                    <!-- Mois (1-12) -->
                    <select name="birthday_month" class="birthday-select" required>
                        <option value="" disabled selected>Mois</option>
                        <?php
                        $months = [
                            1 => 'Janvier', 2 => 'Février', 3 => 'Mars', 4 => 'Avril',
                            5 => 'Mai', 6 => 'Juin', 7 => 'Juillet', 8 => 'Août',
                            9 => 'Septembre', 10 => 'Octobre', 11 => 'Novembre', 12 => 'Décembre'
                        ];
                        foreach ($months as $num => $name): ?>
                            <option value="<?php echo $num; ?>" <?php echo ($_POST['birthday_month'] ?? '') == $num ? 'selected' : ''; ?>>
                                <?php echo $name; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    
                    <!-- Année (100 ans en arrière) -->
                    <select name="birthday_year" class="birthday-select" required>
                        <option value="" disabled selected>Année</option>
                        <?php
                        $currentYear = date('Y');
                        $startYear = $currentYear - 100;
                        for ($year = $currentYear; $year >= $startYear; $year--): ?>
                            <option value="<?php echo $year; ?>" <?php echo ($_POST['birthday_year'] ?? '') == $year ? 'selected' : ''; ?>>
                                <?php echo $year; ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>
            </div>
            
            <button type="submit">S'inscrire</button>
        </form>
        <p>Vous avez déjà un compte ? <a href="login.php">Connexion</a></p>
    </div>
</body>
</html>