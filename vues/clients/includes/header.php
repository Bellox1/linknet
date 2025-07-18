<?php
session_start();
try {
    if (file_exists(__DIR__ . '/../../back-office/config/database.php')) {
        require_once __DIR__ . '/../../back-office/config/database.php';
    } elseif (file_exists(__DIR__ . '../../back-office/config/database.php')) {
        require_once __DIR__ . '../../back-office/config/database.php';
    }
}
catch(Exception $e) {
    echo "";
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SocialApp</title>
    <link rel="stylesheet" href="../styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body>
<nav class="navbar">
    <!-- Section gauche : logo + recherche -->
    <div class="nav-left">
        <a href="/vues/clients/" class="logo">
            <img src="/assets/images/linknet_logo.webp" alt="Facebook" style="width:40px;height:40px;">
        </a>
        <div class="search-container">
            <form action="/vues/clients/friends/search.php" method="GET">
                <i class="fas fa-search search-icon"></i>
                <input type="text" name="q" placeholder="Rechercher sur SocialApp">
            </form>
        </div>
    </div>
    <!-- Section centrale : icônes de navigation -->
    <div class="nav-center">
        <a href="/vues/clients/index.php" class="nav-item active" title="Accueil">
            <i class="fas fa-home"></i>
        </a>
        <a href="/vues/clients/friends/followers.php" class="nav-item" title="Amis">
            <i class="fas fa-user-friends"></i>
        </a>
        <a href="/vues/clients/chat/" class="nav-item" title="Messenger">
            <i class="fab fa-facebook-messenger"></i>
        </a>
        <a href="/vues/clients/notifications/list.php" class="nav-item" title="Notifications">
            <i class="fas fa-bell"></i>
        </a>
        <a href="/vues/clients/user/profile.php" class="nav-item" title="Profil">
            <i class="fas fa-user-circle"></i>
        </a>
    </div>
    <!-- Section droite : actions rapides + utilisateur -->
   
       
               
                <a href="/vues/clients/auth/logout.php" class="menu-item">
                    <i class="fas fa-sign-out-alt"></i>
                    <div class="text">
                        <div class="title">Déconnexion</div>
                    </div>
                </a>
            </div>
        </div>
    </div>
</nav>
