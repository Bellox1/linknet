<?php
// Démarrer la session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Vérifier si l'utilisateur est connecté
if (isset($_SESSION["user"]) && !empty($_SESSION["user"])) {
    // l'utilisateur est connecté, rediriger vers la page principale
    header("Location: vues/clients/index.php");
    exit();
} else {
    // l'utilisateur n'est pas connecté, rediriger vers la page de connexion
    header("Location: vues/clients/auth/login.php");
    exit();
}
?> 