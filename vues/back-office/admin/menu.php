<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once "../../config/database.php";
if (!isset($_SESSION["admin"])) {
    header("Location: /vues/back-office/admin/auth/login.php");
    exit();
}

$role = $_SESSION["admin"]["role"] ?? 'Modérateur';

// Table => [fichier, icône FontAwesome]
$tables = [
    'Utilisateurs' => ['../users/users.php', 'fa-user'],
    'Posts' => ['../posts/posts.php', 'fa-pen-nib'],
    'Commentaires' => ['../comments/comments.php', 'fa-comment-dots'],
    'Likes' => ['../likes/likes.php', 'fa-heart'],
    'Messages' => ['../messages/messages.php', 'fa-envelope'],
    'Amitiés' => ['../friends/friends.php', 'fa-user-friends'],
    'Abonnés' => ['../followers/followers.php', 'fa-users'],
    'Posts en vedette' => ['../featured_posts/featured_posts.php', 'fa-star'],
    'Hashtags' => ['../hashtags/hashtags.php', 'fa-hashtag'],
    'Notifications' => ['../notifications/notifications.php', 'fa-bell'],
    'Signalements' => ['../reports/reports.php', 'fa-flag'],
    'Admins' => ['../admins/admins.php', 'fa-user-shield'],
    'Requêtes d\'amis' => ['../friend_requests/friend_requests.php', 'fa-user-plus']
];

$mod_links =  array_keys($tables);
$admin_links = array_keys($tables);
$accessible = ($role === 'Administrateur') ? $admin_links : $mod_links;

// Couleurs de survol par index
$hover_colors = [
    '#2563eb', // bleu
    '#a16207', // marron/doré
    '#22c55e', // vert
    '#f59e42', // orange
    '#8b5cf6', // violet
    '#0ea5e9', // bleu clair
    '#f43f5e', // rose/rouge
    '#fbbf24', // jaune
    '#14b8a6', // turquoise
    '#6366f1', // indigo
    '#e11d48', // rouge foncé
    '#7c3aed', // violet foncé
    '#475569', // gris foncé
];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="shortcut icon" href="/assets/images/linknet_logo.webp" type="image/x-icon">
    <title>Admin Panel</title>
    <style>
        /* Styles pour agrandir l'icône favicon */
        link[rel="shortcut icon"] {
            width: 48px !important;
            height: 48px !important;
        }

        /* Alternative : utiliser une icône plus grande dans le menu */
        .menu-logo {
            width: 60px;
            height: 68px;
            object-fit: contain;
            border-radius: 8px;
            transition: all 0.3s ease;
            margin: 0;
            padding: 0;
        }

        /* Style pour l'icône quand le menu est réduit */
        .menu-sidebar-pro.collapsed .menu-logo {
            width: 32px;
            height: 32px;
        }

        /* Style pour le conteneur du titre et de l'icône */
        .menu-header-title {
            display: flex;
            align-items: center;
            gap: 0;
        }

        /* Masquer le titre quand le menu est réduit */
        .menu-sidebar-pro.collapsed .menu-title {
            display: none;
        }

        :root {
            --primary: #2563eb;
            --secondary: #3b82f6;
            --success: #22c55e;
            --danger: #ef4444;
            --warning: #f59e42;
            --info: #0ea5e9;
            --light: #f8fafc;
            --dark: #1e293b;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f7fa;
            margin: 0;
            padding: 0;
            color: #333;
            display: flex;
            min-height: 100vh;
        }

        /* Menu styles */
        .menu-sidebar-pro {
            width: 240px;
            background: #fff;
            border-right: 1px solid #e5e7eb;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.05);
            display: flex;
            flex-direction: column;
            transition: width 0.3s cubic-bezier(0.4,0,0.2,1);
            flex-shrink: 0;
            height: 100vh;
            position: sticky;
            top: 0;
            overflow-y: auto;
            z-index: 1000;
        }

        .menu-sidebar-pro.collapsed {
            width: 85px;
        }

        .menu-sidebar-pro.collapsed .ms-label,
        .menu-sidebar-pro.collapsed .menu-title {
            display: none;
        }

        .menu-sidebar-pro.collapsed .menu-header {
            justify-content: center;
        }

        .menu-sidebar-pro.collapsed .menu-sidebar-pro-link {
            justify-content: center;
            padding: 12px 8px;
            margin: 0 4px;
        }

        .menu-sidebar-pro.collapsed .menu-sidebar-pro-link .ms-icon {
            font-size: 18px;
            background: transparent;
            min-width: 32px;
            min-height: 32px;
        }

        .menu-sidebar-pro.collapsed .menu-sidebar-pro-profile,
        .menu-sidebar-pro.collapsed .menu-sidebar-pro-logout {
            justify-content: center;
            padding: 12px 8px;
            margin: 0 4px;
            gap: 0;
        }

        .menu-sidebar-pro.collapsed .menu-sidebar-pro-profile .ms-icon,
        .menu-sidebar-pro.collapsed .menu-sidebar-pro-logout .ms-icon {
            font-size: 18px;
            background: transparent;
            min-width: 32px;
            min-height: 32px;
        }

        .menu-sidebar-pro .menu-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 20px 16px;
            border-bottom: 1px solid #f3f4f6;
            min-height: 70px;
        }

        .menu-sidebar-pro .menu-title {
            color: #1e40af;
            font-size: 20px;
            font-weight: 700;
            white-space: nowrap;
        }

        .menu-sidebar-pro .menu-toggle-btn {
            background: #eff6ff;
            border: none;
            color: #1e40af;
            width: 32px;
            height: 32px;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .menu-sidebar-pro-list {
            list-style: none;
            padding: 12px 0;
            margin: 0;
            flex: 1;
            overflow-y: auto;
        }

        .menu-sidebar-pro-link {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            color: #4b5563;
            font-weight: 500;
            padding: 12px 14px;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.2s;
            margin: 0 8px;
            white-space: nowrap;
        }

        .menu-sidebar-pro-link .ms-icon {
            min-width: 32px;
            min-height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            border-radius: 8px;
            background: #f9fafb;
            color: #4b5563;
            transition: all 0.2s;
        }

        .menu-sidebar-pro-link .ms-label {
            transition: opacity 0.3s;
        }

        .menu-sidebar-pro-link:hover, .menu-sidebar-pro-link.active {
            color: #fff;
            background: #3b82f6;
        }

        .menu-sidebar-pro-link:hover .ms-icon,
        .menu-sidebar-pro-link.active .ms-icon {
            background: rgba(255,255,255,0.2);
            color: #fff;
        }

        <?php foreach ($hover_colors as $i => $color): ?>
        .menu-sidebar-pro-link.menu-hover-<?= $i ?>:hover,
        .menu-sidebar-pro-link.menu-hover-<?= $i ?>.active {
            background: <?= $color ?> !important;
        }
        <?php endforeach; ?>

        .menu-footer {
            padding: 16px;
            border-top: 1px solid #f3f4f6;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .menu-sidebar-pro-profile, .menu-sidebar-pro-logout {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            font-weight: 500;
            padding: 12px 14px;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.2s;
            margin: 0 8px;
        }

        .menu-sidebar-pro-profile {
            color: #4b5563; /* Couleur de base */
            background: #f9fafb;
        }

        .menu-sidebar-pro-logout {
            color: #dc2626; /* Couleur de base */
            background: #fef2f2;
        }

        .menu-sidebar-pro-profile .ms-icon {
            background: #f0fdf4;
            color: #16a34a; /* Couleur de base */
        }

        .menu-sidebar-pro-logout .ms-icon {
            background: #fee2e2;
            color: #dc2626; /* Couleur de base */
        }

        .menu-sidebar-pro-profile:hover {
            background: #16a34a;
            color: #fff;
        }

        .menu-sidebar-pro-profile:hover .ms-icon {
            background: rgba(255,255,255,0.2);
            color: #fff;
        }

        .menu-sidebar-pro-logout:hover {
            background: #dc2626;
            color: #fff;
        }

        /* Styles spécifiques pour l'état actif des liens Profil et Déconnexion */
        .menu-sidebar-pro-profile.active {
            background: #16a34a !important; /* Couleur de fond pour actif */
            color: #fff !important; /* Couleur du texte pour actif */
        }

        .menu-sidebar-pro-profile.active .ms-icon {
            background: rgba(255,255,255,0.2) !important; /* Couleur de fond de l'icône pour actif */
            color: #fff !important; /* Couleur de l'icône pour actif */
        }

        .menu-sidebar-pro-logout.active {
            background: #dc2626 !important; /* Couleur de fond pour actif */
            color: #fff !important; /* Couleur du texte pour actif */
        }

        .menu-sidebar-pro-logout.active .ms-icon {
            background: rgba(255,255,255,0.2) !important; /* Couleur de fond de l'icône pour actif */
            color: #fff !important; /* Couleur de l'icône pour actif */
        }

        /* Main content styles */
        .main-content {
            flex: 1;
            padding: 20px;
            overflow-y: auto;
            height: 100vh;
        }

        .dashboard-container {
            max-width: 1500px;
            margin: 0 auto;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid #e0e0e0;
        }

        .menu-categories {
            display: flex;
            gap: 15px;
            margin-bottom: 30px;
        }

        .menu-btn {
            padding: 10px 22px;
            border: none;
            border-radius: 6px;
            background-color: #e9ecef;
            color: #2563eb;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            font-size: 16px;
        }

        .menu-btn.active, .menu-btn:hover {
            background-color: var(--primary);
            color: #fff;
        }

        .analytics-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 24px 20px;
            box-shadow: 0 4px 12px rgba(37,99,235,0.07);
            border-left: 6px solid var(--primary);
            transition: box-shadow 0.3s;
            position: relative;
        }

        .stat-card[data-cat="users"] { border-color: var(--primary); }
        .stat-card[data-cat="posts"] { border-color: var(--info); }
        .stat-card[data-cat="interactions"] { border-color: var(--success); }
        .stat-card[data-cat="admin"] { border-color: var(--dark); }
        .stat-card[data-cat="reports"] { border-color: var(--danger); }

        .stat-card h3 {
            margin-top: 0;
            margin-bottom: 10px;
            font-size: 17px;
            color: #2563eb;
        }

        .stat-value {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .chart-section,
        .chart-circle {
            width: 49%;
            max-width: 49%;
        }

        @media (max-width: 900px) {
            .analytics-grid { grid-template-columns: 1fr; }
            .charts-row {
                flex-direction: column;
                gap: 0;
            }
            .charts-row > .chart-section,
            .charts-row > .chart-circle {
                width: 100%;
                max-width: 100%;
            }
        }

        .period-selector {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }

        .period-btn {
            padding: 8px 15px;
            border: none;
            border-radius: 5px;
            background-color: #e9ecef;
            color: #2563eb;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s;
        }

        .period-btn.active, .period-btn:hover {
            background-color: var(--primary);
            color: #fff;
        }

        .charts-row {
            display: flex;
            flex-wrap: wrap;
            gap: 0 2%;
            margin-bottom: 30px;
        }

        .charts-row > .chart-section,
        .charts-row > .chart-circle {
            width: 49%;
            max-width: 49%;
            margin: 0;
        }

        @media (max-width: 900px) {
            .charts-row {
                flex-direction: column;
                gap: 0;
            }
            .charts-row > .chart-section,
            .charts-row > .chart-circle {
                width: 100%;
                max-width: 100%;
            }
        }

        /* Styles pour le menu responsive sur mobile */
        .mobile-menu-toggle {
            display: none; /* Caché par default sur les grands écrans */
            position: fixed;
            top: 15px;
            right: 15px;
            left: auto;
            background-color: var(--primary);
            color: #fff;
            border: none;
            border-radius: 8px;
            width: 40px;
            height: 40px;
            font-size: 20px;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            z-index: 1100; /* Plus élevé que le menu */
            transition: opacity 0.3s ease, visibility 0.3s ease;
        }

        /* Styles appliqués aux écrans de largeur <= 750px OU hauteur <= 750px ET largeur <= 360px */
        @media (max-width: 750px), (max-height: 750px) and (max-width: 360px) {
            body {
                flex-direction: column;
                position: relative;
            }

            .menu-sidebar-pro {
                position: fixed;
                top: -100vh;
                left: 0;
                width: 100%;
                height: 100vh;
                box-shadow: 0 0 20px rgba(0, 0, 0, 0.2);
                transition: top 0.3s ease-in-out;
                z-index: 1050;
                background-color: rgba(0, 0, 0, 0.85);
                backdrop-filter: blur(8px);
                border-right: none;
                color: #fff;
            }
            .menu-sidebar-pro .ms-label,
            .menu-sidebar-pro .menu-title {
                color: #fff;
            }
             .menu-sidebar-pro-link .ms-icon {
                background: rgba(255, 255, 255, 0.1) !important;
                color: #fff !important;
            }

            /* FIX FINAL: Styles par DÉFAUT pour les liens "Profil" et "Déconnexion" sur mobile */
            .menu-sidebar-pro-profile,
            .menu-sidebar-pro-logout {
                background: #000000 !important; /* Cadre noir par défaut */
                color: #fff !important; /* Texte en blanc par défaut */
            }

            /* FIX FINAL: Icônes de profil et déconnexion en blanc (par défaut et au survol/actif) */
            .menu-sidebar-pro-profile .ms-icon,
            .menu-sidebar-pro-logout .ms-icon,
            .menu-sidebar-pro-profile:hover .ms-icon,
            .menu-sidebar-pro-profile.active .ms-icon,
            .menu-sidebar-pro-logout:hover .ms-icon,
            .menu-sidebar-pro-logout.active .ms-icon {
                background: transparent !important; /* Pas de fond pour l'icône, juste l'icône */
                color: #fff !important; /* Icônes en blanc */
            }

            /* Survol et actif des liens standards dans le menu sombre */
            <?php foreach ($hover_colors as $i => $color): ?>
            .menu-sidebar-pro-link.menu-hover-<?= $i ?>:hover,
            .menu-sidebar-pro-link.menu-hover-<?= $i ?>.active {
                background: <?= $color ?> !important;
                color: #fff !important;
            }
            <?php endforeach; ?>

            /* Survol pour Profil et Déconnexion (peut rester les couleurs ou passer au noir) */
            /* Si vous voulez qu'ils restent noirs au survol, mettez #000000 ici */
            .menu-sidebar-pro-profile:hover {
                background: #16a34a !important; /* Vert au survol/actif */
                color: #fff !important;
            }
            .menu-sidebar-pro-logout:hover {
                background: #dc2626 !important; /* Rouge au survol/actif */
                color: #fff !important;
            }


            .menu-sidebar-pro.active {
                top: 0;
                z-index: 1150;
            }

            /* Assurez-vous que le menu est toujours déplié sur mobile */
            .menu-sidebar-pro.collapsed {
                width: 100% !important;
            }
            .menu-sidebar-pro.collapsed .ms-label,
            .menu-sidebar-pro.collapsed .menu-title {
                display: block !important;
            }
            .menu-sidebar-pro.collapsed .menu-header {
                justify-content: space-between !important;
            }
            .menu-sidebar-pro.collapsed .menu-sidebar-pro-link,
            .menu-sidebar-pro.collapsed .menu-sidebar-pro-profile,
            .menu-sidebar-pro.collapsed .menu-sidebar-pro-logout {
                justify-content: flex-start !important;
            }

            .main-content {
                width: 100%;
                padding-top: 60px;
            }

            .mobile-menu-toggle {
                display: flex;
            }

            /* Masque l'icône d'ouverture quand le menu est actif sur mobile */
            body.menu-active .mobile-menu-toggle {
                opacity: 0;
                visibility: hidden;
                pointer-events: none;
            }

            .menu-sidebar-pro .menu-toggle-btn {
                display: flex;
                background: none;
                color: #fff;
                font-size: 24px;
                order: 1;
            }
            .menu-sidebar-pro .menu-title {
                color: #fff;
            }

            .menu-header-title {
                order: 0;
            }

            /* L'overlay externe n'est plus nécessaire du tout, on peut même le retirer du HTML */
            .menu-overlay {
                display: none !important;
            }
        }
    </style>
</head>
<body>
    <div class="menu-sidebar-pro" id="menuSidebarPro">
        <div class="menu-header">
            <div class="menu-header-title">
                <img src="/assets/images/linknet_logo.webp" alt="Linknet Logo" class="menu-logo">
                <span class="menu-title">Admin Panel</span>
            </div>
            <button class="menu-toggle-btn" id="menuSidebarProToggle" title="Fermer le menu">
            <i class="fas fa-times"></i> </button>
        </div>

        <ul class="menu-sidebar-pro-list">
            <li>
                <a class="menu-sidebar-pro-link menu-hover-0" href="../dashboard/dashboard.php">
                    <span class="ms-icon"><i class="fas fa-tachometer-alt"></i></span>
                    <span class="ms-label">Dashboard</span>
                </a>
            </li>

            <?php $i = 1; foreach ($tables as $label => $arr): ?>
                <?php if (in_array($label, $accessible)): ?>
                    <li>
                        <a class="menu-sidebar-pro-link menu-hover-<?= $i ?>" href="<?= htmlspecialchars($arr[0]) ?>">
                            <span class="ms-icon"><i class="fas <?= $arr[1] ?>"></i></span>
                            <span class="ms-label"><?= htmlspecialchars($label) ?></span>
                        </a>
                    </li>
                    <?php $i++; ?>
                <?php endif; ?>
            <?php endforeach; ?>
        </ul>

        <div class="menu-footer">
            <a class="menu-sidebar-pro-profile" href="/vues/back-office/admin/auth/profile.php">
                <span class="ms-icon"><i class="fas fa-user-circle"></i></span>
                <span class="ms-label">Profil</span>
            </a>
            <a class="menu-sidebar-pro-logout" href="/vues/back-office/admin/auth/logout.php">
                <span class="ms-icon"><i class="fas fa-sign-out-alt"></i></span>
                <span class="ms-label">Déconnexion</span>
            </a>
        </div>
    </div>

    <button class="mobile-menu-toggle" id="mobileMenuToggle">
        <i class="fas fa-bars"></i>
    </button>

    <script>
    const sidebar = document.getElementById('menuSidebarPro');
    const toggleBtn = document.getElementById('menuSidebarProToggle');
    const mobileMenuToggle = document.getElementById('mobileMenuToggle');

    // Function to check if the screen is considered mobile
    function isMobileScreen() {
        return window.innerWidth <= 750 || (window.innerHeight <= 750 && window.innerWidth <= 360);
    }

    // Function to close the mobile menu
    function closeMobileMenu() {
        sidebar.classList.remove('active');
        document.body.classList.remove('menu-active');
        // Ensure the toggle button icon is a hamburger when the menu is closed on mobile
        if (isMobileScreen()) {
            toggleBtn.querySelector('i').classList.remove('fa-times', 'fa-chevron-left', 'fa-chevron-right');
            toggleBtn.querySelector('i').classList.add('fa-bars');
        }
    }

    // Handle menu toggle for large screens (desktop)
    toggleBtn.addEventListener('click', function() {
        if (isMobileScreen()) {
            // On mobile, this is the close button
            closeMobileMenu(); // Call the function to close
        } else {
            // On desktop, this is the collapse/expand button
            sidebar.classList.toggle('collapsed');
            localStorage.setItem('menuCollapsed', sidebar.classList.contains('collapsed'));
            // Change the toggle button icon on desktop
            if (sidebar.classList.contains('collapsed')) {
                toggleBtn.querySelector('i').classList.remove('fa-chevron-left');
                toggleBtn.querySelector('i').classList.add('fa-chevron-right');
            } else {
                toggleBtn.querySelector('i').classList.remove('fa-chevron-right');
                toggleBtn.querySelector('i').classList.add('fa-chevron-left');
            }
        }
    });

    // Event listener for the mobile toggle button (hamburger icon)
    mobileMenuToggle.addEventListener('click', function() {
        sidebar.classList.add('active'); // Open the menu
        sidebar.classList.remove('collapsed'); // Ensure the menu is always expanded on mobile
        document.body.classList.add('menu-active'); // Hide the mobile button (hamburger)
        // The close button icon should always be a cross on mobile when open
        toggleBtn.querySelector('i').classList.remove('fa-chevron-left', 'fa-chevron-right', 'fa-bars');
        toggleBtn.querySelector('i').classList.add('fa-times');
    });

    // Handle active state of menu links
    // Select ALL relevant links, including those in the footer
    const menuLinks = document.querySelectorAll(
        '.menu-sidebar-pro-list .menu-sidebar-pro-link, ' +
        '.menu-footer .menu-sidebar-pro-profile, ' +
        '.menu-footer .menu-sidebar-pro-logout'
    );

    function setActiveLink(clickedLink) {
        menuLinks.forEach(link => {
            link.classList.remove('active');
        });
        clickedLink.classList.add('active');
    }

    menuLinks.forEach(link => {
        link.addEventListener('click', function() {
            setActiveLink(this);
            // Close the mobile menu after clicking a link if it's a mobile screen
            if (isMobileScreen()) {
                closeMobileMenu(); // Call the function to close
            }
        });
    });

    // Set the active link based on the current page on load
    const currentPage = window.location.pathname.split('/').pop();
    menuLinks.forEach(link => {
        const href = link.getAttribute('href');
        // Check if the href contains the current page name
        if (href.includes(currentPage) && currentPage !== '') {
            setActiveLink(link);
        } else if (currentPage === '' && href.includes('dashboard.php')) {
            // If the URL is the root (e.g., /admin/) and the link is to the dashboard, make it active
            // This is often the case if dashboard.php is the default file in a folder
            setActiveLink(link);
        }
        // Specific case for profile and logout links:
        // The link "/vues/back-office/admin/auth/profile.php" should activate "Profil"
        // The link "/vues/back-office/admin/auth/logout.php" should activate "Déconnexion"
        // Ensure the current page exactly matches the href
        if (window.location.pathname === href) {
            setActiveLink(link);
        }
    });

    // Adjust menu state on window resize
    window.addEventListener('resize', function() {
        if (!isMobileScreen()) {
            // If not on a mobile screen, disable mobile classes
            sidebar.classList.remove('active');
            document.body.classList.remove('menu-active');
            mobileMenuToggle.style.display = 'none'; // Hide the hamburger button
            // Restore "collapsed" state if saved for large screens
            if (localStorage.getItem('menuCollapsed') === 'true') {
                sidebar.classList.add('collapsed');
                toggleBtn.querySelector('i').classList.remove('fa-times', 'fa-chevron-left', 'fa-bars');
                toggleBtn.querySelector('i').classList.add('fa-chevron-right');
            } else {
                toggleBtn.querySelector('i').classList.remove('fa-times', 'fa-chevron-right', 'fa-bars');
                toggleBtn.querySelector('i').classList.add('fa-chevron-left');
            }
        } else {
            // If on a mobile screen, ensure the menu is not "collapsed"
            sidebar.classList.remove('collapsed');
            mobileMenuToggle.style.display = 'flex'; // Show the hamburger button
            // The close icon should always be a cross on mobile if the menu is open
            if (sidebar.classList.contains('active')) {
                 toggleBtn.querySelector('i').classList.remove('fa-chevron-left', 'fa-chevron-right', 'fa-bars');
                 toggleBtn.querySelector('i').classList.add('fa-times');
            } else {
                // Otherwise, it's the hamburger icon
                toggleBtn.querySelector('i').classList.remove('fa-chevron-left', 'fa-chevron-right', 'fa-times');
                toggleBtn.querySelector('i').classList.add('fa-bars');
            }
            // If the menu is open on mobile during resize, hide the mobile button
            if (sidebar.classList.contains('active')) {
                document.body.classList.add('menu-active');
            } else {
                document.body.classList.remove('menu-active');
            }
        }
    });

    // Initial call to set correct state on load
    window.dispatchEvent(new Event('resize'));
    </script>
</body>
</html>