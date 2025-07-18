<?php
session_start();
require_once "../../config/database.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST["username"];
    $password = $_POST["password"];
    $remember = isset($_POST["remember"]);

    $stmt = $conn->prepare("SELECT * FROM admins WHERE username = ?");
    $stmt->execute([$username]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($admin && password_verify($password, $admin["password"])) {
        $_SESSION["admin"] = [
            'id' => $admin['id'],
            'username' => $admin['username'],
            'email' => $admin['email'],
            'role' => $admin['role'],
            'created_at' => $admin['created_at'],
        ];
        
        if ($remember) {
            // Stocker les infos dans un cookie (30 jours)
            setcookie('admin_remember', $username, time() + 30*24*3600, '/');
        }
        
        header("Location: /vues/back-office/admin/dashboard/dashboard.php");
        exit();
    } else {
        $error = "Identifiants invalides !";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion Admin</title>
    <style>
        :root {
            --primary-color: #4361ee;
            --secondary-color: #3f37c9;
            --light-color: #f8f9fa;
            --dark-color: #212529;
            --danger-color: #ef233c;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: #f5f7fa;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }
        
        .login-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 450px;
            padding: 40px;
            animation: fadeIn 0.5s ease;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .login-header h2 {
            color: var(--primary-color);
            font-size: 28px;
            margin-bottom: 10px;
        }
        
        .login-header p {
            color: #6c757d;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: var(--dark-color);
            font-weight: 500;
        }
        
        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ced4da;
            border-radius: 6px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        
        .form-control:focus {
            border-color: var(--primary-color);
            outline: none;
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.2);
        }
        
        .remember-forgot {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .remember-me {
            display: flex;
            align-items: center;
        }
        
        .remember-me input {
            margin-right: 8px;
        }
        
        .forgot-password a {
            color: var(--primary-color);
            text-decoration: none;
            font-size: 14px;
        }
        
        .forgot-password a:hover {
            text-decoration: underline;
        }
        
        .btn {
            display: inline-block;
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: 6px;
            padding: 12px 20px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            width: 100%;
            transition: background-color 0.3s;
        }
        
        .btn:hover {
            background-color: var(--secondary-color);
        }
        
        .error-message {
            color: var(--danger-color);
            text-align: center;
            margin-bottom: 20px;
            font-size: 14px;
        }
        
        @media (max-width: 576px) {
            .login-container {
                padding: 30px 20px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <h2>Connexion Admin</h2>
            <p>Veuillez entrer vos identifiants</p>
        </div>
        
        <?php if (isset($error)): ?>
            <div class="error-message"><?= $error ?></div>
        <?php endif; ?>
        
        <form method="POST" id="loginForm">
            <div class="form-group">
                <input type="text" class="form-control" name="username" id="username" placeholder="Nom d'utilisateur" required>
            </div>
            
            <div class="form-group">
                <input type="password" class="form-control" name="password" id="password" placeholder="Mot de passe" required>
            </div>
            
            <div class="remember-forgot">
                <div class="remember-me">
                    <input type="checkbox" name="remember" id="remember">
                    <label for="remember">Se souvenir de moi</label>
                </div>
                <div class="forgot-password">
                    <a href="forgot-password.php">Mot de passe oublié ?</a>
                </div>
            </div>
            
            <button type="submit" class="btn">Se connecter</button>
        </form>
    </div>

    <script>
        // Gestion de "Se souvenir de moi" avec sessionStorage
        document.addEventListener('DOMContentLoaded', function() {
            const rememberCheckbox = document.getElementById('remember');
            const usernameInput = document.getElementById('username');
            const passwordInput = document.getElementById('password');
            const loginForm = document.getElementById('loginForm');
            
            // Vérifier si des informations sont stockées
            if (sessionStorage.getItem('rememberAdmin') === 'true') {
                rememberCheckbox.checked = true;
                usernameInput.value = sessionStorage.getItem('adminUsername') || '';
                passwordInput.value = sessionStorage.getItem('adminPassword') || '';
            }
            
            // Gérer la soumission du formulaire
            loginForm.addEventListener('submit', function() {
                if (rememberCheckbox.checked) {
                    sessionStorage.setItem('rememberAdmin', 'true');
                    sessionStorage.setItem('adminUsername', usernameInput.value);
                    sessionStorage.setItem('adminPassword', passwordInput.value);
                } else {
                    sessionStorage.removeItem('rememberAdmin');
                    sessionStorage.removeItem('adminUsername');
                    sessionStorage.removeItem('adminPassword');
                }
            });
        });
    </script>
</body>
</html>