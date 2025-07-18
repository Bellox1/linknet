<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once "../../config/database.php";

if (!isset($_SESSION["admin"])) {
    http_response_code(403);
    exit('Accès refusé');
}

$role = $_SESSION["admin"]["role"] ?? 'Modérateur';

if (!isset($_GET['id'])) {
    exit('ID utilisateur manquant');
}

$userId = (int)$_GET['id'];

// Récupérer les informations détaillées de l'utilisateur
$stmt = $conn->prepare("
    SELECT u.*, 
           COUNT(DISTINCT p.id) as posts_count,
           COUNT(DISTINCT c.id) as comments_count,
           COUNT(DISTINCT l.id) as likes_count,
           COUNT(DISTINCT f.id) as friends_count,
           COUNT(DISTINCT fl.id) as followers_count,
           COUNT(DISTINCT fp.id) as featured_posts_count,
           COUNT(DISTINCT h.id) as hashtags_count,
           COUNT(DISTINCT n.id) as notifications_count,
           COUNT(DISTINCT m.id) as messages_count
    FROM users u
    LEFT JOIN posts p ON u.id = p.user_id
    LEFT JOIN comments c ON u.id = c.user_id
    LEFT JOIN likes l ON u.id = l.user_id
    LEFT JOIN friends f ON (u.id = f.sender_id OR u.id = f.receiver_id) AND f.status = 'accepted'
    LEFT JOIN followers fl ON u.id = fl.user_id
    LEFT JOIN featured_posts fp ON p.id = fp.post_id
    LEFT JOIN hashtags h ON p.id = h.post_id
    LEFT JOIN notifications n ON u.id = n.user_id
    LEFT JOIN messages m ON u.id = m.sender_id
    WHERE u.id = ?
    GROUP BY u.id
");

$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    exit('Utilisateur non trouvé');
}

// Récupérer les derniers posts
$postsStmt = $conn->prepare("
    SELECT p.*, COUNT(c.id) as comments_count, COUNT(l.id) as likes_count
    FROM posts p
    LEFT JOIN comments c ON p.id = c.post_id
    LEFT JOIN likes l ON p.id = l.post_id
    WHERE p.user_id = ?
    GROUP BY p.id
    ORDER BY p.created_at DESC
    LIMIT 5
");
$postsStmt->execute([$userId]);
$recentPosts = $postsStmt->fetchAll(PDO::FETCH_ASSOC);

// Récupérer les amis
$friendsStmt = $conn->prepare("
    SELECT u.id, u.username, u.profile_picture
    FROM users u
    INNER JOIN friends f ON (u.id = f.sender_id OR u.id = f.receiver_id)
    WHERE (f.sender_id = ? OR f.receiver_id = ?) 
    AND f.status = 'accepted'
    AND u.id != ?
    LIMIT 10
");
$friendsStmt->execute([$userId, $userId, $userId]);
$friends = $friendsStmt->fetchAll(PDO::FETCH_ASSOC);

// Récupérer les abonnés
$followersStmt = $conn->prepare("
    SELECT u.id, u.username, u.profile_picture
    FROM users u
    INNER JOIN followers f ON u.id = f.follower_id
    WHERE f.user_id = ?
    LIMIT 10
");
$followersStmt->execute([$userId]);
$followers = $followersStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="user-detail-container">
    <!-- En-tête utilisateur -->
    <div class="user-header-detail">
        <div class="user-avatar-large">
            <img src="<?= !empty($user['profile_picture']) ? '../../uploads/' . $user['profile_picture'] : '../../uploads/default_profile.jpg' ?>" 
                 alt="Avatar" onerror="this.src='../../uploads/default_profile.jpg'">
        </div>
        <div class="user-info-detail">
            <h1><?= htmlspecialchars($user['username']) ?></h1>
            <p class="user-email"><?= htmlspecialchars($user['email']) ?></p>
            <?php if (!empty($user['bio'])): ?>
                <p class="user-bio"><?= htmlspecialchars($user['bio']) ?></p>
            <?php endif; ?>
            <p class="user-join-date">
                <i class="fas fa-calendar"></i>
                Membre depuis le <?= date('d/m/Y', strtotime($user['created_at'])) ?>
            </p>
        </div>
    </div>

    <!-- Statistiques détaillées -->
    <div class="stats-detail-grid">
        <div class="stat-detail-card">
            <div class="stat-icon">
                <i class="fas fa-pen"></i>
            </div>
            <div class="stat-content">
                <h3>Posts</h3>
                <div class="stat-value"><?= $user['posts_count'] ?></div>
            </div>
        </div>
        
        <div class="stat-detail-card">
            <div class="stat-icon">
                <i class="fas fa-comment"></i>
            </div>
            <div class="stat-content">
                <h3>Commentaires</h3>
                <div class="stat-value"><?= $user['comments_count'] ?></div>
            </div>
        </div>
        
        <div class="stat-detail-card">
            <div class="stat-icon">
                <i class="fas fa-heart"></i>
            </div>
            <div class="stat-content">
                <h3>Likes donnés</h3>
                <div class="stat-value"><?= $user['likes_count'] ?></div>
            </div>
        </div>
        
        <div class="stat-detail-card">
            <div class="stat-icon">
                <i class="fas fa-user-friends"></i>
            </div>
            <div class="stat-content">
                <h3>Amis</h3>
                <div class="stat-value"><?= $user['friends_count'] ?></div>
            </div>
        </div>
        
        <div class="stat-detail-card">
            <div class="stat-icon">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-content">
                <h3>Abonnés</h3>
                <div class="stat-value"><?= $user['followers_count'] ?></div>
            </div>
        </div>
        
        <div class="stat-detail-card">
            <div class="stat-icon">
                <i class="fas fa-star"></i>
            </div>
            <div class="stat-content">
                <h3>Posts en vedette</h3>
                <div class="stat-value"><?= $user['featured_posts_count'] ?></div>
            </div>
        </div>
        
        <div class="stat-detail-card">
            <div class="stat-icon">
                <i class="fas fa-hashtag"></i>
            </div>
            <div class="stat-content">
                <h3>Hashtags utilisés</h3>
                <div class="stat-value"><?= $user['hashtags_count'] ?></div>
            </div>
        </div>
        
        <div class="stat-detail-card">
            <div class="stat-icon">
                <i class="fas fa-bell"></i>
            </div>
            <div class="stat-content">
                <h3>Notifications</h3>
                <div class="stat-value"><?= $user['notifications_count'] ?></div>
            </div>
        </div>
        
        <div class="stat-detail-card">
            <div class="stat-icon">
                <i class="fas fa-envelope"></i>
            </div>
            <div class="stat-content">
                <h3>Messages envoyés</h3>
                <div class="stat-value"><?= $user['messages_count'] ?></div>
            </div>
        </div>
    </div>

    <!-- Derniers posts -->
    <?php if (!empty($recentPosts)): ?>
    <div class="section-detail">
        <h2>Derniers Posts</h2>
        <div class="posts-grid">
            <?php foreach ($recentPosts as $post): ?>
                <div class="post-card">
                    <div class="post-content">
                        <p><?= htmlspecialchars(substr($post['content'], 0, 100)) ?><?= strlen($post['content']) > 100 ? '...' : '' ?></p>
                    </div>
                    <?php if (!empty($post['media'])): ?>
                        <div class="post-media">
                            <img src="<?= $post['media'] ?>" alt="Media" onerror="this.style.display='none'">
                        </div>
                    <?php endif; ?>
                    <div class="post-meta">
                        <span><i class="fas fa-comment"></i> <?= $post['comments_count'] ?></span>
                        <span><i class="fas fa-heart"></i> <?= $post['likes_count'] ?></span>
                        <span class="post-date"><?= date('d/m/Y', strtotime($post['created_at'])) ?></span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Amis -->
    <?php if (!empty($friends)): ?>
    <div class="section-detail">
        <h2>Amis (<?= count($friends) ?>)</h2>
        <div class="users-list">
            <?php foreach ($friends as $friend): ?>
                <div class="user-item">
                    <img src="<?= !empty($friend['profile_picture']) ? '../../uploads/' . $friend['profile_picture'] : '../../uploads/default_profile.jpg' ?>" 
                         alt="Avatar" onerror="this.src='../../uploads/default_profile.jpg'">
                    <span><?= htmlspecialchars($friend['username']) ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Abonnés -->
    <?php if (!empty($followers)): ?>
    <div class="section-detail">
        <h2>Abonnés (<?= count($followers) ?>)</h2>
        <div class="users-list">
            <?php foreach ($followers as $follower): ?>
                <div class="user-item">
                    <img src="<?= !empty($follower['profile_picture']) ? '../../uploads/' . $follower['profile_picture'] : '../../uploads/default_profile.jpg' ?>" 
                         alt="Avatar" onerror="this.src='../../uploads/default_profile.jpg'">
                    <span><?= htmlspecialchars($follower['username']) ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
// Fonction pour vérifier si tout le contenu est visible et ajuster la hauteur
function adjustContainerHeight() {
    const container = document.querySelector('.user-detail-container');
    const lastSection = container.querySelector('.section-detail:last-child');
    
    if (!container || !lastSection) return;
    
    // Obtenir les positions
    const containerRect = container.getBoundingClientRect();
    const lastSectionRect = lastSection.getBoundingClientRect();
    
    // Calculer si la dernière section est complètement visible avec un espace supplémentaire
    const requiredSpace = 100; // Espace supplémentaire requis en pixels
    const isLastSectionFullyVisible = (lastSectionRect.bottom + requiredSpace) <= containerRect.bottom;
    
    // Si la dernière section n'est pas complètement visible ou s'il n'y a pas assez d'espace
    if (!isLastSectionFullyVisible) {
        const currentHeight = container.style.maxHeight || '95vh';
        const currentValue = parseInt(currentHeight);
        
        // Augmenter la hauteur de 10vh à chaque fois
        const newHeight = Math.min(currentValue + 10, 150); // Maximum 150vh
        container.style.maxHeight = newHeight + 'vh';
        
        // Ajuster aussi le padding-bottom
        const currentPadding = parseInt(container.style.paddingBottom) || 800;
        container.style.paddingBottom = (currentPadding + 100) + 'px';
        
        // Vérifier à nouveau après l'ajustement
        setTimeout(adjustContainerHeight, 100);
    } else {
        // Même si tout est visible, s'assurer qu'il y a au moins 150px d'espace en bas
        const currentPadding = parseInt(container.style.paddingBottom) || 800;
        const minRequiredPadding = 150;
        
        if (currentPadding < minRequiredPadding) {
            container.style.paddingBottom = minRequiredPadding + 'px';
        }
    }
}

// Fonction pour vérifier la visibilité lors du scroll
function checkVisibilityOnScroll() {
    const container = document.querySelector('.user-detail-container');
    const lastSection = container.querySelector('.section-detail:last-child');
    
    if (!container || !lastSection) return;
    
    const containerRect = container.getBoundingClientRect();
    const lastSectionRect = lastSection.getBoundingClientRect();
    
    // Si on est proche du bas et que la dernière section n'est pas complètement visible avec espace
    const requiredSpace = 100;
    if (container.scrollTop + container.clientHeight >= container.scrollHeight - 50) {
        if ((lastSectionRect.bottom + requiredSpace) > containerRect.bottom) {
            adjustContainerHeight();
        }
    }
}

// Initialiser quand le DOM est chargé
document.addEventListener('DOMContentLoaded', function() {
    // Ajuster la hauteur initiale
    setTimeout(adjustContainerHeight, 500);
    
    // Ajouter un listener pour le scroll
    const container = document.querySelector('.user-detail-container');
    if (container) {
        container.addEventListener('scroll', checkVisibilityOnScroll);
    }
    
    // Vérifier aussi lors du redimensionnement de la fenêtre
    window.addEventListener('resize', function() {
        setTimeout(adjustContainerHeight, 100);
    });
});

// Fonction pour forcer l'ajustement (peut être appelée manuellement)
function forceAdjustHeight() {
    adjustContainerHeight();
}
</script>
<style>
    :root {
    --primary: #2563eb; /* Un bleu plus vif pour le contraste */
    --secondary: #3b82f6; /* Une teinte secondaire */
}

/* --- Styles pour le centrage du modal --- */
.user-detail-container {
    padding: 20px;
    max-height: 95vh; /* Maintient une hauteur maximale avec défilement interne */
    overflow-y: auto; /* Permet le défilement interne si le contenu est trop long */
    padding-bottom: 150px; /* Réduisez le padding-bottom pour un défilement plus naturel, mais gardez un peu d'espace */
    
    /* Centrage absolu dans le viewport */
    position: fixed; /* Rend l'élément fixe par rapport à la fenêtre */
    top: 50%; /* Positionne le haut de l'élément à 50% de la hauteur de la fenêtre */
    left: 50%; /* Positionne le côté gauche de l'élément à 50% de la largeur de la fenêtre */
    transform: translate(-50%, -50%); /* Déplace l'élément de la moitié de sa propre largeur et hauteur en arrière, le centrant ainsi parfaitement */
    
    margin: 0; /* Important : supprime les marges automatiques qui pourraient interférer avec le centrage fixed */
    max-width: 800px;
    width: calc(100% - 40px); /* Ajuste la largeur pour laisser un peu d'espace sur les côtés */
    background: #f8fafc;
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
    scrollbar-width: thin;
    scrollbar-color: var(--primary) #e5e7eb;
    transition: max-height 0.3s ease, padding-bottom 0.3s ease; /* Gardez la transition si votre JS la gère */
    z-index: 1000; /* Assurez-vous qu'il est au-dessus de tout le reste */
    font-family: 'Inter', sans-serif; /* Assurez-vous que cette police est importée ou disponible */
    color: #333;
    box-sizing: border-box; /* Inclut padding et border dans la largeur/hauteur de l'élément */
}

/* Styles pour les barres de défilement internes du conteneur */
.user-detail-container::-webkit-scrollbar {
    width: 8px;
}

.user-detail-container::-webkit-scrollbar-track {
    background: #e5e7eb;
    border-radius: 4px;
}

.user-detail-container::-webkit-scrollbar-thumb {
    background: var(--primary);
    border-radius: 4px;
}

.user-detail-container::-webkit-scrollbar-thumb:hover {
    background: #1d4ed8;
}

/* --- Styles pour l'en-tête de l'utilisateur --- */
.user-header-detail {
    display: flex;
    align-items: center;
    gap: 20px;
    margin-bottom: 30px;
    padding: 25px;
    border-bottom: 2px solid #f1f5f9;
    background: white;
    border-radius: 12px 12px 0 0;
    margin: -20px -20px 30px -20px; /* Adaptez les marges pour qu'il s'étende aux bords du conteneur */
}

.user-avatar-large {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    overflow: hidden;
    flex-shrink: 0;
}

.user-avatar-large img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.user-info-detail h1 {
    margin: 0 0 10px 0;
    color: #1e293b;
    font-size: 1.8rem;
}

.user-email {
    color: #64748b;
    margin: 0 0 10px 0;
    font-size: 1rem;
}

.user-bio {
    color: #374151;
    margin: 0 0 10px 0;
    font-style: italic;
}

.user-join-date {
    color: #64748b;
    margin: 0;
    font-size: 0.9rem;
}

/* --- Styles pour la grille de statistiques --- */
.stats-detail-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
    padding: 25px;
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}

.stat-detail-card {
    background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    display: flex;
    align-items: center;
    gap: 15px;
    border: 1px solid #e5e7eb;
    transition: all 0.3s ease;
}

.stat-detail-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 16px rgba(0,0,0,0.15);
}

.stat-detail-card .stat-icon {
    width: 40px;
    height: 40px;
    background: linear-gradient(135deg, var(--primary), var(--secondary));
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1rem;
}

.stat-detail-card .stat-content h3 {
    margin: 0 0 5px 0;
    font-size: 12px;
    color: #64748b;
}

.stat-detail-card .stat-value {
    font-size: 18px;
    font-weight: 700;
    color: var(--primary);
}

/* --- Styles pour les sections de contenu (Derniers Posts, Amis, Abonnés) --- */
.section-detail {
    margin-bottom: 30px;
    padding: 25px;
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}

.section-detail:last-child {
    margin-bottom: 0; /* Pas de marge en bas pour la dernière section */
    /* Le padding-bottom du conteneur parent gère l'espace final de défilement */
}

.section-detail h2 {
    color: #1e293b;
    font-size: 1.3rem;
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 2px solid #e5e7eb;
    display: flex;
    align-items: center;
    gap: 10px;
}

.section-detail h2::before {
    content: '';
    width: 4px;
    height: 20px;
    background: var(--primary);
    border-radius: 2px;
}

/* --- Styles pour la grille de posts --- */
.posts-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 15px;
}

.post-card {
    background: white;
    border-radius: 10px;
    padding: 15px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.post-content p {
    margin: 0 0 10px 0;
    color: #374151;
    line-height: 1.5;
}

.post-media img {
    width: 100%;
    max-height: 200px;
    object-fit: cover;
    border-radius: 8px;
    margin-bottom: 10px;
}

.post-meta {
    display: flex;
    gap: 15px;
    color: #64748b;
    font-size: 0.9rem;
}

.post-meta span {
    display: flex;
    align-items: center;
    gap: 5px;
}

/* --- Styles pour les listes d'utilisateurs (Amis, Abonnés) --- */
.users-list {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 10px;
}

.user-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px;
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.user-item img {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    object-fit: cover;
}

.user-item span {
    color: #374151;
    font-weight: 500;
}

/* --- Media Queries pour la réactivité --- */
@media (max-width: 1240px) {
    .user-detail-container {
        /* Quand la fenêtre est plus petite, nous voulons un peu plus de marge */
        width: calc(100% - 30px); /* Laisser 15px de chaque côté */
    }
}

@media (max-width: 768px) {
    .user-detail-container {
        padding: 15px; /* Réduire le padding interne pour les petits écrans */
        width: calc(100% - 30px); /* Assurez-vous qu'il y a de l'espace sur les côtés */
        max-height: 90vh; /* Peut être réduit un peu plus pour les écrans de mobile */
    }
    
    .user-header-detail {
        flex-direction: column; /* Empile l'avatar et les infos verticalement */
        text-align: center;
        gap: 15px; /* Réduit l'espace entre l'avatar et les infos */
    }
    
    .stats-detail-grid {
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); /* Ajuste la grille des stats */
    }
    
    .posts-grid {
        grid-template-columns: 1fr; /* Un seul post par ligne sur mobile */
    }
    
    .users-list {
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); /* Ajuste la grille des amis/abonnés */
    }
}
</style>