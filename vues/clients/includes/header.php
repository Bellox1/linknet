<?php
session_start();

try {
    if (file_exists(__DIR__ . '/../../back-office/config/database.php')) {
        require_once __DIR__ . '/../../back-office/config/database.php';
    } elseif (file_exists(__DIR__ . '../../back-office/config/database.php')) {
        require_once __DIR__ . '../../back-office/config/database.php';
    }
}

//catch exception
catch(Exception $e) {
    echo "";
}
if (!isset($_SESSION["user"])) {
  header("Location: auth/login.php");
  exit();
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Social Media</title>
    <link rel="stylesheet" href="/vues/clients/styles.css">
    <link rel="stylesheet" href="/assets/css/header.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

</head>
<body>
<nav class="navbar">
        <!-- Partie gauche avec logo et recherche -->
        <div class="nav-left">
            <a href="/vues/clients/" class="logo">
                <i class="fas fa-thumbtack" style="margin-right: 8px;"></i>
                <span>Linknet</span>
            </a>
            <input type="text" id="global-user-search" placeholder="Rechercher un utilisateur..." style="flex:1;padding:10px 16px;border-radius:22px;border:1.5px solid #e9d5ff;background:#f6f3fd;font-size:16px;outline:none;color:#7c3aed;transition:border 0.2s;max-width:220px;margin-left:12px;" />
        </div>
        
        <!-- Menu central avec liens -->
        <div class="nav-center">
            <a href="/vues/clients/index.php">
                <i class="fas fa-home" style="margin-right: 8px;"></i>Accueil
            </a>
            <a href="/vues/clients/user/profile.php">
                <i class="fas fa-user" style="margin-right: 8px;"></i>Profil
            </a>
            <a href="/vues/clients/friends/followers.php">
                <i class="fas fa-users" style="margin-right: 8px;"></i>Amis
            </a>
            <a href="/vues/clients/chat/">
                <i class="fas fa-comments" style="margin-right: 8px;"></i>Messages
            </a>
            <a href="/vues/clients/notifications/list.php">
                <i class="fas fa-bell" style="margin-right: 8px;"></i>Notifications
            </a>
        </div>
        
        <!-- Partie droite avec actions utilisateur -->
        <div class="user-menu">
  <div class="header-avatar"></div>
</div>
<a href="/vues/clients/auth/logout.php" class="logout-btn">Déconnexion</a>
    </nav>
<script>
document.addEventListener('DOMContentLoaded', async function() {
  try {
    const res = await fetch('https://linknet.wuaze.com/api/profile.php', { credentials: 'include' });
    const data = await res.json();
    if (data.status === 'success' && data.data && data.data.user) {
      const user = data.data.user;
      // Normaliser le chemin de la photo
      let pic = user.profile_picture || 'default_profile.jpg';
      if (!pic.startsWith('http')) {
        pic = '/vues/back-office/uploads/' + pic.replace(/^.*uploads[\\/]/, '');
      }
      // Mettre à jour l'avatar
      const avatar = document.querySelector('.header-avatar');
      if (avatar) {
        avatar.textContent = '';
        avatar.style.background = 'none';
        avatar.style.padding = '0';
        avatar.innerHTML = `<img src="${pic}" alt="Avatar" style="width:38px;height:38px;border-radius:50%;object-fit:cover;">`;
      }
      // Mettre à jour le nom (optionnel)
      const userMenu = document.querySelector('.user-menu');
      if (userMenu && user.username) {
        userMenu.title = user.username;
      }
    }
  } catch (e) {
    // En cas d'erreur, on garde les initiales
  }
});
document.addEventListener('DOMContentLoaded', function() {
  const path = window.location.pathname;
  let found = false;
  document.querySelectorAll('.nav-center a').forEach(link => {
    // On considère le lien actif si l'URL courante contient le href du lien (hors index.php)
    if (
      (link.getAttribute('href') !== '/vues/clients/index.php' && path.startsWith(link.getAttribute('href').replace('.php', ''))) ||
      path === link.getAttribute('href')
    ) {
      link.classList.add('active');
      found = true;
    }
  });
  // Si aucun lien n'est trouvé, on met Accueil par défaut
  if (!found) {
    document.querySelector('.nav-center a[href=\"/vues/clients/index.php\"]').classList.add('active');
  }
});
</script>