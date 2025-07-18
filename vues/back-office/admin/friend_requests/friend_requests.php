<?php
require_once '../menu.php';

// Récupération des requêtes d'amis avec pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';

// Construction de la requête avec recherche et filtres
$whereConditions = [];
$params = [];

if (!empty($search)) {
    $whereConditions[] = "(u1.username LIKE ? OR u1.email LIKE ? OR u2.username LIKE ? OR u2.email LIKE ?)";
    $params = array_merge($params, ["%$search%", "%$search%", "%$search%", "%$search%"]);
}

if (!empty($status_filter)) {
    $whereConditions[] = "fr.status = ?";
    $params[] = $status_filter;
}

$whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';

// Comptage total
$countStmt = $conn->prepare("
    SELECT COUNT(*) 
    FROM friend_requests fr
    JOIN users u1 ON fr.sender_id = u1.id
    JOIN users u2 ON fr.receiver_id = u2.id
    $whereClause
");
$countStmt->execute($params);
$totalRequests = $countStmt->fetchColumn();
$totalPages = ceil($totalRequests / $limit);

// Récupération des requêtes d'amis
$stmt = $conn->prepare("
    SELECT fr.*,
           u1.username as sender_username,
           u1.email as sender_email,
           u1.profile_picture as sender_picture,
           u2.username as receiver_username,
           u2.email as receiver_email,
           u2.profile_picture as receiver_picture
    FROM friend_requests fr
    JOIN users u1 ON fr.sender_id = u1.id
    JOIN users u2 ON fr.receiver_id = u2.id
    $whereClause
    ORDER BY fr.created_at DESC
    LIMIT $limit OFFSET $offset
");

$stmt->execute($params);
$friendRequests = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Traitement des actions
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];
        
        try {
            switch ($action) {
                case 'accept':
                    $requestId = (int)$_POST['request_id'];
                    
                    // Accepter la requête
                    $conn->beginTransaction();
                    
                    // Mettre à jour le statut de la requête
                    $updateStmt = $conn->prepare("UPDATE friend_requests SET status = 'accepted' WHERE id = ?");
                    $updateStmt->execute([$requestId]);
                    
                    // Récupérer les informations de la requête
                    $reqStmt = $conn->prepare("SELECT sender_id, receiver_id FROM friend_requests WHERE id = ?");
                    $reqStmt->execute([$requestId]);
                    $request = $reqStmt->fetch(PDO::FETCH_ASSOC);
                    
                    if ($request) {
                        // Ajouter dans la table friends
                        $friendStmt = $conn->prepare("INSERT INTO friends (sender_id, receiver_id, status) VALUES (?, ?, 'accepted')");
                        $friendStmt->execute([$request['sender_id'], $request['receiver_id']]);
                        
                        // Créer une notification
                        $notifStmt = $conn->prepare("INSERT INTO notifications (user_id, sender_id, type) VALUES (?, ?, 'friend_request')");
                        $notifStmt->execute([$request['sender_id'], $request['receiver_id']]);
                    }
                    
                    $conn->commit();
                    $message = "Requête d'ami acceptée avec succès";
                    break;
                    
                case 'reject':
                    $requestId = (int)$_POST['request_id'];
                    
                    // Rejeter la requête
                    $updateStmt = $conn->prepare("UPDATE friend_requests SET status = 'rejected' WHERE id = ?");
                    $updateStmt->execute([$requestId]);
                    
                    $message = "Requête d'ami rejetée avec succès";
                    break;
                    
                case 'delete':
                    $requestId = (int)$_POST['request_id'];
                    
                    // Supprimer la requête
                    $deleteStmt = $conn->prepare("DELETE FROM friend_requests WHERE id = ?");
                    $deleteStmt->execute([$requestId]);
                    
                    $message = "Requête d'ami supprimée avec succès";
                    break;
            }
        } catch (PDOException $e) {
            if (isset($conn)) {
                $conn->rollBack();
            }
            $message = "Erreur lors de l'opération: " . $e->getMessage();
        }
    }
}

// Statistiques
$statsStmt = $conn->query("
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
        SUM(CASE WHEN status = 'accepted' THEN 1 ELSE 0 END) as accepted,
        SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected
    FROM friend_requests
");
$stats = $statsStmt->fetch(PDO::FETCH_ASSOC);
?>

<div class="main-content">
    <div class="dashboard-container">
        <div class="header">
            <h1>Gestion des Requêtes d'Amis</h1>
        </div>

        <?php if ($message): ?>
            <div class="alert <?= strpos($message, 'succès') !== false ? 'alert-success' : 'alert-error' ?>">
                <i class="fas <?= strpos($message, 'succès') !== false ? 'fa-check-circle' : 'fa-exclamation-circle' ?>"></i>
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <!-- Barre de recherche et filtres -->
        <div class="search-section">
            <div class="search-header">
                <form method="GET" class="search-form">
                    <div class="search-input-group">
                        <input type="text" name="search" placeholder="Rechercher par nom ou email..." value="<?= htmlspecialchars($search) ?>" class="search-input">
                        <select name="status" class="filter-select">
                            <option value="">Tous les statuts</option>
                            <option value="pending" <?= $status_filter === 'pending' ? 'selected' : '' ?>>En attente</option>
                            <option value="accepted" <?= $status_filter === 'accepted' ? 'selected' : '' ?>>Acceptées</option>
                            <option value="rejected" <?= $status_filter === 'rejected' ? 'selected' : '' ?>>Rejetées</option>
                        </select>
                        <button type="submit" class="search-btn">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Statistiques -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-user-plus"></i>
                </div>
                <div class="stat-content">
                    <h3>Total Requêtes</h3>
                    <div class="stat-value"><?= $stats['total'] ?></div>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-content">
                    <h3>En Attente</h3>
                    <div class="stat-value"><?= $stats['pending'] ?></div>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-content">
                    <h3>Acceptées</h3>
                    <div class="stat-value"><?= $stats['accepted'] ?></div>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-times-circle"></i>
                </div>
                <div class="stat-content">
                    <h3>Rejetées</h3>
                    <div class="stat-value"><?= $stats['rejected'] ?></div>
                </div>
            </div>
        </div>

        <!-- Tableau des requêtes d'amis -->
        <div class="users-table-container">
            <div class="table-header">
                <h2>Liste des Requêtes d'Amis</h2>
                <div class="table-actions">
                    <span class="results-count"><?= $totalRequests ?> requête(s) trouvée(s)</span>
                </div>
            </div>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Expéditeur</th>
                            <th>Destinataire</th>
                            <th>Statut</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($friendRequests)): ?>
                            <tr><td colspan="5" style="text-align:center;color:#888;">Aucune requête trouvée</td></tr>
                        <?php else: foreach ($friendRequests as $request): ?>
                        <tr>
                            <td>
                                <div class="user-table-info">
                                    <img src="<?= !empty($request['sender_picture']) ? '../../uploads/' . $request['sender_picture'] : '../../uploads/default_profile.jpg' ?>" class="user-avatar-table" alt="avatar">
                                    <div>
                                        <div class="username-table"><?= htmlspecialchars($request['sender_username']) ?></div>
                                        <div class="email-table"><?= htmlspecialchars($request['sender_email']) ?></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="user-table-info">
                                    <img src="<?= !empty($request['receiver_picture']) ? '../../uploads/' . $request['receiver_picture'] : '../../uploads/default_profile.jpg' ?>" class="user-avatar-table" alt="avatar">
                                    <div>
                                        <div class="username-table"><?= htmlspecialchars($request['receiver_username']) ?></div>
                                        <div class="email-table"><?= htmlspecialchars($request['receiver_email']) ?></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="status-badge status-<?= $request['status'] ?>">
                                    <?php
                                    switch ($request['status']) {
                                        case 'pending':
                                            echo '<i class="fas fa-clock"></i> En attente';
                                            break;
                                        case 'accepted':
                                            echo '<i class="fas fa-check"></i> Acceptée';
                                            break;
                                        case 'rejected':
                                            echo '<i class="fas fa-times"></i> Rejetée';
                                            break;
                                    }
                                    ?>
                                </span>
                            </td>
                            <td>
                                <span class="join-date">
                                    <i class="fas fa-calendar"></i> <?= date('d/m/Y à H:i', strtotime($request['created_at'])) ?>
                                </span>
                            </td>
                            <td>
                                <div class="user-actions">
                                    <?php if ($request['status'] === 'pending'): ?>
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="action" value="accept">
                                            <input type="hidden" name="request_id" value="<?= $request['id'] ?>">
                                            <button type="submit" class="btn btn-success btn-sm" title="Accepter"><i class="fas fa-check"></i></button>
                                        </form>
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="action" value="reject">
                                            <input type="hidden" name="request_id" value="<?= $request['id'] ?>">
                                            <button type="submit" class="btn btn-warning btn-sm" title="Rejeter"><i class="fas fa-times"></i></button>
                                        </form>
                                    <?php endif; ?>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="request_id" value="<?= $request['id'] ?>">
                                        <button type="submit" class="btn btn-danger btn-sm" title="Supprimer"><i class="fas fa-trash"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($status_filter) ?>" class="page-link">
                            <i class="fas fa-chevron-left"></i> Précédent
                        </a>
                    <?php endif; ?>
                    <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                        <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($status_filter) ?>" class="page-link <?= $i === $page ? 'active' : '' ?>">
                            <?= $i ?>
                        </a>
                    <?php endfor; ?>
                    <?php if ($page < $totalPages): ?>
                        <a href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($status_filter) ?>" class="page-link">
                            Suivant <i class="fas fa-chevron-right"></i>
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
// Fonction pour actualiser la page
function refreshPage() {
    window.location.reload();
}
</script>

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
.search-section {
    margin-bottom: 30px;
}
.search-form {
    max-width: 500px;
}
.search-input-group {
    display: flex;
    gap: 10px;
}
.search-input {
    flex: 1;
    padding: 12px 16px;
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    font-size: 14px;
    transition: all 0.3s;
}
.search-input:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(37,99,235,0.1);
}
.search-btn {
    padding: 12px 20px;
    background: var(--primary);
    color: white;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.3s;
}
.search-btn:hover {
    background: #1d4ed8;
}
.filter-select {
    padding: 10px 15px;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    background: white;
    font-size: 14px;
    min-width: 150px;
}
.users-table-container {
    background: white;
    border-radius: 12px;
    padding: 25px;
    box-shadow: 0 4px 12px rgba(37,99,235,0.07);
}
.table-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
    padding-bottom: 15px;
    border-bottom: 2px solid #f1f5f9;
}
.table-header h2 {
    margin: 0;
    color: #1e293b;
    font-size: 1.4rem;
}
.results-count {
    color: #64748b;
    font-size: 14px;
}
.table-responsive {
    overflow-x: auto;
}
.data-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 0;
}
.data-table th, .data-table td {
    padding: 14px 12px;
    text-align: left;
    border-bottom: 1px solid #f1f5f9;
    vertical-align: middle;
}
.data-table th {
    background: #f8fafc;
    color: #1e293b;
    font-size: 15px;
    font-weight: 700;
}
.user-table-info {
    display: flex;
    align-items: center;
    gap: 10px;
}
.user-avatar-table {
    width: 38px;
    height: 38px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid #e5e7eb;
}
.username-table {
    font-weight: 600;
    color: #1e293b;
    font-size: 14px;
}
.email-table {
    color: #64748b;
    font-size: 12px;
}
.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 500;
}
.status-pending {
    background: #fef3c7;
    color: #92400e;
}
.status-accepted {
    background: #d1fae5;
    color: #065f46;
}
.status-rejected {
    background: #fee2e2;
    color: #991b1b;
}
.join-date {
    color: #64748b;
    font-size: 0.95rem;
    display: flex;
    align-items: center;
    gap: 5px;
}
.user-actions {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}
.btn {
    padding: 8px 14px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 600;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: all 0.3s;
    font-size: 14px;
}
.btn-success {
    background: #22c55e;
    color: #fff;
}
.btn-success:hover {
    background: #16a34a;
}
.btn-warning {
    background: #f59e42;
    color: #fff;
}
.btn-warning:hover {
    background: #d97706;
}
.btn-danger {
    background: #ef4444;
    color: #fff;
}
.btn-danger:hover {
    background: #b91c1c;
}
.btn-sm {
    padding: 6px 10px;
    font-size: 12px;
    min-width: 32px;
    height: 32px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}
.pagination {
    display: flex;
    justify-content: center;
    gap: 10px;
    margin-top: 30px;
    flex-wrap: wrap;
}
.page-link {
    padding: 10px 15px;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    color: #64748b;
    text-decoration: none;
    transition: all 0.3s;
    display: flex;
    align-items: center;
    gap: 5px;
}
.page-link:hover {
    background: var(--primary);
    color: white;
    border-color: var(--primary);
}
.page-link.active {
    background: var(--primary);
    color: white;
    border-color: var(--primary);
}
@media (max-width: 900px) {
    .stats-grid {
        grid-template-columns: 1fr;
    }
    .users-grid {
        grid-template-columns: 1fr;
    }
    .data-table th, .data-table td {
        padding: 10px 6px;
        font-size: 13px;
    }
}
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}
.stat-card {
    background: white;
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 4px 12px rgba(37,99,235,0.07);
    display: flex;
    align-items: center;
    gap: 15px;
    min-height: 90px;
}
.stat-icon {
    width: 50px;
    height: 50px;
    background: linear-gradient(135deg, var(--primary), var(--secondary));
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.5rem;
}
.stat-content h3 {
    margin: 0 0 5px 0;
    font-size: 14px;
    color: #64748b;
}
.stat-value {
    font-size: 24px;
    font-weight: 700;
    color: var(--primary);
}
@media (max-width: 900px) {
    .stats-grid {
        grid-template-columns: 1fr;
    }
}
</style> 