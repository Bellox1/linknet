<?php
// Démarrer la session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Vérifier si l'administrateur est connecté
if (isset($_SESSION["admin"]) && !empty($_SESSION["admin"])) {
    // L'administrateur est connecté, rediriger vers le dashboard
    header("Location: /vues/back-office/admin/dashboard/dashboard.php");
    exit();
} else {
    // L'administrateur n'est pas connecté, rediriger vers la page de connexion
    header("Location: /vues/back-office/admin/auth/login.php");
    exit();
}
?>
