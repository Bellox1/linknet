<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once "../../config/database.php";

if (!isset($_SESSION["admin"])) {
    http_response_code(403);
    exit('Accès refusé');
}

$role = $_SESSION["admin"]["role"] ?? 'Modérateur';

if (!isset($_GET['id'])) {
    exit('ID hashtag manquant');
}

$hashtagId = (int)$_GET['id'];

// Récupérer les informations détaillées du hashtag
$stmt = $conn->prepare("
    SELECT h.*, 
           COUNT(DISTINCT h2.id) as total_occurrences,
           COUNT(DISTINCT h2.post_id) as total_posts,
           COUNT(DISTINCT p.id) as posts_with_hashtag,
           COUNT(DISTINCT c.id) as total_comments,
           COUNT(DISTINCT l.id) as total_likes,
           COUNT(DISTINCT u.id) as unique_users
    FROM hashtags h
    LEFT JOIN hashtags h2 ON h.tag = h2.tag
    LEFT JOIN posts p ON h2.post_id = p.id
    LEFT JOIN comments c ON p.id = c.post_id
    LEFT JOIN likes l ON p.id = l.post_id
    LEFT JOIN users u ON p.user_id = u.id
    WHERE h.id = ?
    GROUP BY h.id, h.tag
");

$stmt->execute([$hashtagId]);
$hashtag = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$hashtag) {
    exit('Hashtag non trouvé');
}

// Récupérer tous les posts utilisant ce hashtag
$postsStmt = $conn->prepare("
    SELECT p.*, u.username, u.profile_picture,
           COUNT(DISTINCT c.id) as comments_count,
           COUNT(DISTINCT l.id) as likes_count,
           h.created_at as hashtag_created_at
    FROM posts p
    INNER JOIN hashtags h ON p.id = h.post_id
    LEFT JOIN users u ON p.user_id = u.id
    LEFT JOIN comments c ON p.id = c.post_id
    LEFT JOIN likes l ON p.id = l.post_id
    WHERE h.tag = ?
    GROUP BY p.id
    ORDER BY p.created_at DESC
    LIMIT 10
");
$postsStmt->execute([$hashtag['tag']]);
$postsWithHashtag = $postsStmt->fetchAll(PDO::FETCH_ASSOC);

// Récupérer les utilisateurs les plus actifs avec ce hashtag
$usersStmt = $conn->prepare("
    SELECT u.id, u.username, u.profile_picture,
           COUNT(DISTINCT h.id) as hashtag_usage,
           COUNT(DISTINCT p.id) as total_posts
    FROM users u
    INNER JOIN posts p ON u.id = p.user_id
    INNER JOIN hashtags h ON p.id = h.post_id
    WHERE h.tag = ?
    GROUP BY u.id
    ORDER BY hashtag_usage DESC
    LIMIT 8
");
$usersStmt->execute([$hashtag['tag']]);
$activeUsers = $usersStmt->fetchAll(PDO::FETCH_ASSOC);

// Récupérer les hashtags similaires (utilisés dans les mêmes posts)
$similarStmt = $conn->prepare("
    SELECT h2.tag, COUNT(*) as co_occurrences
    FROM hashtags h1
    INNER JOIN hashtags h2 ON h1.post_id = h2.post_id
    WHERE h1.tag = ? AND h2.tag != ?
    GROUP BY h2.tag
    ORDER BY co_occurrences DESC
    LIMIT 8
");
$similarStmt->execute([$hashtag['tag'], $hashtag['tag']]);
$similarHashtags = $similarStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="hashtag-detail-container">
    <!-- En-tête du hashtag -->
    <div class="hashtag-header">
        <div class="hashtag-icon-large">
            <i class="fas fa-hashtag"></i>
        </div>
        <div class="hashtag-info">
            <h1>#<?= htmlspecialchars($hashtag['tag']) ?></h1>
            <div class="hashtag-meta">
                <div class="meta-item">
                    <i class="fas fa-calendar"></i>
                    Créé le <?= date('d/m/Y H:i', strtotime($hashtag['created_at'])) ?>
                </div>
                <div class="meta-item">
                    <i class="fas fa-pen-nib"></i>
                    <?= $hashtag['total_occurrences'] ?> occurrence(s)
                </div>
                <div class="meta-item">
                    <i class="fas fa-users"></i>
                    <?= $hashtag['unique_users'] ?> utilisateur(s)
                </div>
            </div>
        </div>
    </div>

    <!-- Statistiques -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-hashtag"></i>
            </div>
            <div class="stat-content">
                <h3>Total Occurrences</h3>
                <div class="stat-value"><?= $hashtag['total_occurrences'] ?></div>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-pen-nib"></i>
            </div>
            <div class="stat-content">
                <h3>Posts Utilisés</h3>
                <div class="stat-value"><?= $hashtag['total_posts'] ?></div>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-comment"></i>
            </div>
            <div class="stat-content">
                <h3>Commentaires</h3>
                <div class="stat-value"><?= $hashtag['total_comments'] ?></div>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-heart"></i>
            </div>
            <div class="stat-content">
                <h3>Likes</h3>
                <div class="stat-value"><?= $hashtag['total_likes'] ?></div>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-content">
                <h3>Utilisateurs Uniques</h3>
                <div class="stat-value"><?= $hashtag['unique_users'] ?></div>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-chart-line"></i>
            </div>
            <div class="stat-content">
                <h3>Popularité</h3>
                <div class="stat-value">
                    <?php
                    $popularity = $hashtag['total_occurrences'] > 10 ? 'Élevée' : 
                                ($hashtag['total_occurrences'] > 5 ? 'Moyenne' : 'Faible');
                    echo $popularity;
                    ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Contenu principal -->
    <div class="content-sections">
        <!-- Section principale -->
        <div class="main-section">
            <h2 class="section-title">Posts utilisant ce hashtag</h2>
            
            <?php if (empty($postsWithHashtag)): ?>
                <div class="empty-state">
                    <i class="fas fa-inbox"></i>
                    <p>Aucun post trouvé avec ce hashtag</p>
                </div>
            <?php else: ?>
                <div class="posts-list">
                    <?php foreach ($postsWithHashtag as $post): ?>
                        <div class="post-item">
                            <div class="post-header">
                                <div class="user-avatar">
                                    <img src="<?= !empty($post['profile_picture']) ? '../../uploads/' . $post['profile_picture'] : '../../uploads/default_profile.jpg' ?>" 
                                         alt="Avatar" onerror="this.src='../../uploads/default_profile.jpg'">
                                </div>
                                <div class="post-meta">
                                    <p class="post-author"><?= htmlspecialchars($post['username']) ?></p>
                                    <p class="post-date">
                                        <?= date('d/m/Y H:i', strtotime($post['created_at'])) ?>
                                        <span style="margin-left: 10px; color: var(--primary);">
                                            <i class="fas fa-hashtag"></i> Ajouté le <?= date('d/m/Y', strtotime($post['hashtag_created_at'])) ?>
                                        </span>
                                    </p>
                                </div>
                            </div>
                            
                            <div class="post-content">
                                <?= htmlspecialchars(substr($post['content'], 0, 150)) ?>
                                <?= strlen($post['content']) > 150 ? '...' : '' ?>
                            </div>
                            
                            <div class="post-stats">
                                <div class="post-stat">
                                    <i class="fas fa-comment"></i>
                                    <?= $post['comments_count'] ?> commentaires
                                </div>
                                <div class="post-stat">
                                    <i class="fas fa-heart"></i>
                                    <?= $post['likes_count'] ?> likes
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Sidebar -->
        <div class="sidebar-section">
            <!-- Utilisateurs actifs -->
            <h3 class="section-title">Utilisateurs actifs</h3>
            
            <?php if (empty($activeUsers)): ?>
                <div class="empty-state">
                    <i class="fas fa-users"></i>
                    <p>Aucun utilisateur actif</p>
                </div>
            <?php else: ?>
                <div class="users-list">
                    <?php foreach ($activeUsers as $user): ?>
                        <div class="user-item">
                            <div class="user-avatar-small">
                                <img src="<?= !empty($user['profile_picture']) ? '../../uploads/' . $user['profile_picture'] : '../../uploads/default_profile.jpg' ?>" 
                                     alt="Avatar" onerror="this.src='../../uploads/default_profile.jpg'">
                            </div>
                            <div class="user-info">
                                <p class="user-name"><?= htmlspecialchars($user['username']) ?></p>
                                <p class="user-stats">
                                    <?= $user['hashtag_usage'] ?> utilisation(s) • <?= $user['total_posts'] ?> posts
                                </p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- Hashtags similaires -->
            <h3 class="section-title" style="margin-top: 30px;">Hashtags similaires</h3>
            
            <?php if (empty($similarHashtags)): ?>
                <div class="empty-state">
                    <i class="fas fa-tags"></i>
                    <p>Aucun hashtag similaire</p>
                </div>
            <?php else: ?>
                <div class="similar-hashtags">
                    <?php foreach ($similarHashtags as $similar): ?>
                        <span class="similar-tag">
                            #<?= htmlspecialchars($similar['tag']) ?> (<?= $similar['co_occurrences'] ?>)
                        </span>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
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

.hashtag-detail-container {
    padding: 20px;
    max-height: 70vh;
    overflow-y: auto;
    scrollbar-width: thin;
    scrollbar-color: #cbd5e1 #f1f5f9;
}

.hashtag-detail-container::-webkit-scrollbar {
    width: 8px;
}

.hashtag-detail-container::-webkit-scrollbar-track {
    background: #f1f5f9;
    border-radius: 4px;
}

.hashtag-detail-container::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 4px;
}

.hashtag-detail-container::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}

.hashtag-header {
    display: flex;
    align-items: center;
    gap: 20px;
    margin-bottom: 25px;
    padding-bottom: 20px;
    border-bottom: 2px solid #f1f5f9;
}

.hashtag-icon-large {
    width: 60px;
    height: 60px;
    background: linear-gradient(135deg, var(--primary), var(--secondary));
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.8rem;
    box-shadow: 0 4px 15px rgba(37,99,235,0.3);
}

.hashtag-info h1 {
    margin: 0 0 10px 0;
    font-size: 1.8rem;
    color: var(--primary);
    font-weight: 700;
}

.hashtag-meta {
    display: flex;
    gap: 15px;
    flex-wrap: wrap;
}

.meta-item {
    display: flex;
    align-items: center;
    gap: 6px;
    color: #64748b;
    font-size: 13px;
}

.meta-item i {
    color: var(--primary);
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 15px;
    margin-bottom: 25px;
}

.stat-card {
    background: white;
    border-radius: 10px;
    padding: 15px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    display: flex;
    align-items: center;
    gap: 12px;
}

.stat-icon {
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

.stat-content h3 {
    margin: 0 0 5px 0;
    font-size: 12px;
    color: #64748b;
}

.stat-value {
    font-size: 18px;
    font-weight: 700;
    color: var(--primary);
}

.content-sections {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 20px;
}

.main-section, .sidebar-section {
    background: white;
    border-radius: 10px;
    padding: 20px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.section-title {
    font-size: 1.1rem;
    color: var(--primary);
    margin-bottom: 15px;
    padding-bottom: 10px;
    border-bottom: 1px solid #e5e7eb;
}

.posts-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.post-item {
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    padding: 15px;
    transition: all 0.3s;
}

.post-item:hover {
    border-color: var(--primary);
    box-shadow: 0 2px 8px rgba(37,99,235,0.1);
}

.post-header {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 10px;
}

.user-avatar {
    width: 35px;
    height: 35px;
    border-radius: 50%;
    overflow: hidden;
}

.user-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.post-meta {
    flex: 1;
}

.post-author {
    font-weight: 600;
    color: var(--primary);
    margin: 0;
    font-size: 14px;
}

.post-date {
    font-size: 12px;
    color: #64748b;
    margin: 0;
}

.post-content {
    margin-bottom: 10px;
    line-height: 1.5;
    color: #374151;
    font-size: 14px;
}

.post-stats {
    display: flex;
    gap: 15px;
    font-size: 12px;
    color: #64748b;
}

.post-stat {
    display: flex;
    align-items: center;
    gap: 4px;
}

.users-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.user-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    transition: all 0.3s;
}

.user-item:hover {
    border-color: var(--primary);
    background: #f8fafc;
}

.user-avatar-small {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    overflow: hidden;
}

.user-avatar-small img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.user-info {
    flex: 1;
}

.user-name {
    font-weight: 600;
    color: var(--primary);
    margin: 0;
    font-size: 13px;
}

.user-stats {
    font-size: 11px;
    color: #64748b;
    margin: 0;
}

.similar-hashtags {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
}

.similar-tag {
    background: #e5e7eb;
    color: #374151;
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 11px;
    transition: all 0.3s;
}

.similar-tag:hover {
    background: var(--primary);
    color: white;
}

.empty-state {
    text-align: center;
    padding: 30px 20px;
    color: #64748b;
}

.empty-state i {
    font-size: 2rem;
    margin-bottom: 10px;
    color: #cbd5e1;
}

.empty-state p {
    margin: 0;
    font-size: 14px;
}

@media (max-width: 768px) {
    .hashtag-header {
        flex-direction: column;
        text-align: center;
        gap: 15px;
    }

    .hashtag-info h1 {
        font-size: 1.5rem;
    }

    .content-sections {
        grid-template-columns: 1fr;
        gap: 15px;
    }

    .stats-grid {
        grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
        gap: 10px;
    }

    .hashtag-meta {
        justify-content: center;
    }
}
</style> 