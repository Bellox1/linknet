<?php
require_once "../menu.php";

// Gestion des actions POST
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action) {
        try {
            switch ($action) {
                case 'add_notification':
                    if ($role === 'Administrateur') {
                        $user_id = (int)($_POST['user_id'] ?? 0);
                        $sender_id = (int)($_POST['sender_id'] ?? 0);
                        $type = $_POST['type'] ?? '';
                        $post_id = !empty($_POST['post_id']) ? (int)$_POST['post_id'] : null;
                        
                        if ($user_id <= 0) {
                            $message = "Veuillez sélectionner un utilisateur destinataire";
                            break;
                        }
                        
                        if ($sender_id <= 0) {
                            $message = "Veuillez sélectionner un utilisateur expéditeur";
                            break;
                        }
                        
                        if (!in_array($type, ['friend_request', 'follow', 'like', 'comment'])) {
                            $message = "Type de notification invalide";
                            break;
                        }
                        
                        $insertStmt = $conn->prepare("INSERT INTO notifications (user_id, sender_id, type, post_id, created_at) VALUES (?, ?, ?, ?, NOW())");
                        if ($insertStmt->execute([$user_id, $sender_id, $type, $post_id])) {
                            $message = "Notification ajoutée avec succès";
                        } else {
                            $message = "Erreur lors de l'ajout";
                        }
                    } else {
                        $message = "Vous n'avez pas les permissions pour ajouter une notification";
                    }
                    break;
                    
                case 'delete':
                    if (!isset($_POST['notification_id'])) {
                        $message = "ID notification manquant";
                        break;
                    }
                    $notificationId = (int)$_POST['notification_id'];
                    if ($role === 'Administrateur' || $role === 'Modérateur') {
                        $stmt = $conn->prepare("DELETE FROM notifications WHERE id = ?");
                        $stmt->execute([$notificationId]);
                        $message = "Notification supprimée avec succès";
                    } else {
                        $message = "Vous n'avez pas les permissions pour supprimer une notification";
                    }
                    break;
                    
                case 'mark_read':
                    if (!isset($_POST['notification_id'])) {
                        $message = "ID notification manquant";
                        break;
                    }
                    $notificationId = (int)$_POST['notification_id'];
                    if ($role === 'Administrateur' || $role === 'Modérateur') {
                        $stmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE id = ?");
                        $stmt->execute([$notificationId]);
                        $message = "Notification marquée comme lue";
                    } else {
                        $message = "Vous n'avez pas les permissions pour modifier une notification";
                    }
                    break;
            }
        } catch (PDOException $e) {
            $message = "Erreur lors de l'opération: " . $e->getMessage();
        }
    }
}

// Paramètres de pagination et recherche
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 15;
$offset = ($page - 1) * $limit;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$filter_type = isset($_GET['type']) ? $_GET['type'] : '';
$filter_status = isset($_GET['status']) ? $_GET['status'] : '';

$whereClause = '';
$params = [];
$conditions = [];

if (!empty($search)) {
    $conditions[] = "(u.username LIKE ? OR s.username LIKE ? OR p.content LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if (!empty($filter_type)) {
    $conditions[] = "n.type = ?";
    $params[] = $filter_type;
}

if (!empty($filter_status)) {
    if ($filter_status === 'read') {
        $conditions[] = "n.is_read = 1";
    } else {
        $conditions[] = "n.is_read = 0";
    }
}

if (!empty($conditions)) {
    $whereClause = "WHERE " . implode(" AND ", $conditions);
}

$countQuery = "SELECT COUNT(*) FROM notifications n 
               LEFT JOIN users u ON n.user_id = u.id 
               LEFT JOIN users s ON n.sender_id = s.id 
               LEFT JOIN posts p ON n.post_id = p.id 
               $whereClause";
$countStmt = $conn->prepare($countQuery);
$countStmt->execute($params);
$total = $countStmt->fetchColumn();
$totalPages = ceil($total / $limit);

$query = "SELECT n.*, 
                 u.username as recipient_username, u.profile_picture as recipient_picture,
                 s.username as sender_username, s.profile_picture as sender_picture,
                 p.content as post_content
          FROM notifications n 
          LEFT JOIN users u ON n.user_id = u.id 
          LEFT JOIN users s ON n.sender_id = s.id 
          LEFT JOIN posts p ON n.post_id = p.id 
          $whereClause
          ORDER BY n.created_at DESC 
          LIMIT $limit OFFSET $offset";
$stmt = $conn->prepare($query);
$stmt->execute($params);
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupération de tous les utilisateurs pour le select d'ajout
$usersQuery = "SELECT id, username FROM users ORDER BY username";
$usersList = $conn->query($usersQuery)->fetchAll(PDO::FETCH_ASSOC);

// Récupération de tous les posts pour le select d'ajout
$postsQuery = "SELECT p.id, p.content, u.username 
               FROM posts p 
               JOIN users u ON p.user_id = u.id 
               ORDER BY p.created_at DESC 
               LIMIT 100";
$postsList = $conn->query($postsQuery)->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="main-content">
    <div class="dashboard-container">
        <div class="header">
            <h1>Gestion des Notifications</h1>
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
                        <input type="text" name="search" placeholder="Rechercher par utilisateur ou contenu..." 
                               value="<?= htmlspecialchars($search) ?>" class="search-input">
                        <select name="type" class="filter-select">
                            <option value="">Tous les types</option>
                            <option value="friend_request" <?= $filter_type === 'friend_request' ? 'selected' : '' ?>>Demande d'ami</option>
                            <option value="follow" <?= $filter_type === 'follow' ? 'selected' : '' ?>>Abonnement</option>
                            <option value="like" <?= $filter_type === 'like' ? 'selected' : '' ?>>Like</option>
                            <option value="comment" <?= $filter_type === 'comment' ? 'selected' : '' ?>>Commentaire</option>
                        </select>
                        <select name="status" class="filter-select">
                            <option value="">Tous les statuts</option>
                            <option value="unread" <?= $filter_status === 'unread' ? 'selected' : '' ?>>Non lues</option>
                            <option value="read" <?= $filter_status === 'read' ? 'selected' : '' ?>>Lues</option>
                        </select>
                        <button type="submit" class="search-btn">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </form>
                
                <?php if ($role === 'Administrateur'): ?>
                    <button class="btn btn-primary" onclick="showAddNotificationModal()">
                        <i class="fas fa-plus"></i>
                        Ajouter une notification
                    </button>
                <?php endif; ?>
            </div>
        </div>

        <!-- Statistiques -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-bell"></i>
                </div>
                <div class="stat-content">
                    <h3>Total Notifications</h3>
                    <div class="stat-value"><?= $total ?></div>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-eye"></i>
                </div>
                <div class="stat-content">
                    <h3>Notifications Lues</h3>
                    <div class="stat-value">
                        <?php
                        $readStmt = $conn->query("SELECT COUNT(*) FROM notifications WHERE is_read = 1");
                        echo $readStmt->fetchColumn();
                        ?>
                    </div>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-eye-slash"></i>
                </div>
                <div class="stat-content">
                    <h3>Notifications Non Lues</h3>
                    <div class="stat-value">
                        <?php
                        $unreadStmt = $conn->query("SELECT COUNT(*) FROM notifications WHERE is_read = 0");
                        echo $unreadStmt->fetchColumn();
                        ?>
                    </div>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-content">
                    <h3>Utilisateurs Notifiés</h3>
                    <div class="stat-value">
                        <?php
                        $usersStmt = $conn->query("SELECT COUNT(DISTINCT user_id) FROM notifications");
                        echo $usersStmt->fetchColumn();
                        ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Liste des notifications -->
        <div class="notifications-table-container">
            <div class="table-header">
                <h2>Liste des Notifications</h2>
                <div class="table-actions">
                    <span class="results-count"><?= $total ?> notification(s) trouvée(s)</span>
                </div>
            </div>

            <div class="notifications-grid">
                <?php foreach ($notifications as $notification): ?>
                    <div class="notification-card <?= !$notification['is_read'] ? 'unread' : '' ?>" data-notification-id="<?= $notification['id'] ?>">
                        <div class="notification-header">
                            <div class="notification-avatar">
                                <img src="<?= !empty($notification['sender_picture']) ? '../../uploads/' . $notification['sender_picture'] : '../../uploads/default_profile.jpg' ?>" 
                                     alt="Avatar" onerror="this.src='../../uploads/default_profile.jpg'">
                            </div>
                            <div class="notification-info">
                                <h3><?= htmlspecialchars($notification['sender_username']) ?></h3>
                                <p class="notification-date"><?= date('d/m/Y H:i', strtotime($notification['created_at'])) ?></p>
                            </div>
                            <div class="notification-status <?= $notification['is_read'] ? 'read' : 'unread' ?>">
                                <i class="fas <?= $notification['is_read'] ? 'fa-eye' : 'fa-eye-slash' ?>"></i>
                            </div>
                        </div>
                        
                        <div class="notification-content">
                            <div class="notification-type">
                                <span class="type-badge type-<?= $notification['type'] ?>">
                                    <?php
                                    $typeLabels = [
                                        'friend_request' => 'Demande d\'ami',
                                        'follow' => 'Abonnement',
                                        'like' => 'Like',
                                        'comment' => 'Commentaire'
                                    ];
                                    echo $typeLabels[$notification['type']] ?? $notification['type'];
                                    ?>
                                </span>
                            </div>
                            
                            <div class="notification-details">
                                <p><strong>Destinataire :</strong> <?= htmlspecialchars($notification['recipient_username']) ?></p>
                                <?php if (!empty($notification['post_content'])): ?>
                                    <p><strong>Post :</strong> <?= htmlspecialchars(substr($notification['post_content'], 0, 100)) ?>...</p>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="notification-actions">
                            <?php if (!$notification['is_read']): ?>
                                <button class="btn btn-success btn-sm" onclick="markAsRead(<?= $notification['id'] ?>)">
                                    <i class="fas fa-eye"></i>
                                    Marquer comme lue
                                </button>
                            <?php endif; ?>
                            
                            <?php if ($role === 'Administrateur' || $role === 'Modérateur'): ?>
                                <button type="button" class="btn btn-danger btn-sm" onclick="deleteNotification(<?= $notification['id'] ?>)">
                                    <i class="fas fa-trash"></i>
                                    Supprimer
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>&type=<?= urlencode($filter_type) ?>&status=<?= urlencode($filter_status) ?>" class="page-link">
                            <i class="fas fa-chevron-left"></i>
                            Précédent
                        </a>
                    <?php endif; ?>
                    
                    <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                        <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&type=<?= urlencode($filter_type) ?>&status=<?= urlencode($filter_status) ?>" 
                           class="page-link <?= $i === $page ? 'active' : '' ?>">
                            <?= $i ?>
                        </a>
                    <?php endfor; ?>
                    
                    <?php if ($page < $totalPages): ?>
                        <a href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>&type=<?= urlencode($filter_type) ?>&status=<?= urlencode($filter_status) ?>" class="page-link">
                            Suivant
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal d'ajout de notification -->
<?php if ($role === 'Administrateur' || $role === 'Modérateur'): ?>
<div id="addNotificationModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Ajouter une Notification</h2>
            <span class="close" onclick="closeAddNotificationModal()">&times;</span>
        </div>
        
        <form id="addNotificationForm" method="POST">
            <input type="hidden" name="action" value="add_notification">
            
            <div class="form-group">
                <label for="user_id">Destinataire *</label>
                <select name="user_id" id="user_id" required>
                    <option value="">Sélectionner un destinataire</option>
                    <?php foreach ($usersList as $user): ?>
                        <option value="<?= $user['id'] ?>"><?= htmlspecialchars($user['username']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="sender_id">Expéditeur *</label>
                <select name="sender_id" id="sender_id" required>
                    <option value="">Sélectionner un expéditeur</option>
                    <?php foreach ($usersList as $user): ?>
                        <option value="<?= $user['id'] ?>"><?= htmlspecialchars($user['username']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="type">Type de notification *</label>
                <select name="type" id="type" required onchange="togglePostField()">
                    <option value="">Sélectionner un type</option>
                    <option value="friend_request">Demande d'ami</option>
                    <option value="follow">Abonnement</option>
                    <option value="like">Like</option>
                    <option value="comment">Commentaire</option>
                </select>
            </div>
            
            <div class="form-group" id="postField" style="display: none;">
                <label for="post_id">Post associé</label>
                <select name="post_id" id="post_id">
                    <option value="">Aucun post</option>
                    <?php foreach ($postsList as $post): ?>
                        <option value="<?= $post['id'] ?>"><?= htmlspecialchars(substr($post['content'], 0, 60)) ?>... (<?= htmlspecialchars($post['username']) ?>)</option>
                    <?php endforeach; ?>
                </select>
                <small class="form-help">Obligatoire pour les likes et commentaires</small>
            </div>
            
            <div class="form-actions">
                <button type="button" onclick="closeAddNotificationModal()" class="btn btn-secondary">Annuler</button>
                <button type="submit" class="btn btn-primary">Ajouter</button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<script>
// Fonctions pour les modals
function showAddNotificationModal() {
    document.getElementById('addNotificationModal').style.display = 'block';
}

function closeAddNotificationModal() {
    document.getElementById('addNotificationModal').style.display = 'none';
    document.getElementById('addNotificationForm').reset();
    document.getElementById('postField').style.display = 'none';
}

// Fonction pour afficher/masquer le champ post selon le type
function togglePostField() {
    const type = document.getElementById('type').value;
    const postField = document.getElementById('postField');
    const postSelect = document.getElementById('post_id');
    
    if (type === 'like' || type === 'comment') {
        postField.style.display = 'block';
        postSelect.required = true;
    } else {
        postField.style.display = 'none';
        postSelect.required = false;
        postSelect.value = '';
    }
}

// Fonction de suppression avec confirmation
function deleteNotification(notificationId) {
    if (confirm('Êtes-vous sûr de vouloir supprimer cette notification ?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="notification_id" value="${notificationId}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

// Fonction pour marquer comme lue
function markAsRead(notificationId) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.innerHTML = `
        <input type="hidden" name="action" value="mark_read">
        <input type="hidden" name="notification_id" value="${notificationId}">
    `;
    document.body.appendChild(form);
    form.submit();
}

// Validation du formulaire d'ajout
document.addEventListener('DOMContentLoaded', function() {
    const addNotificationForm = document.getElementById('addNotificationForm');
    if (addNotificationForm) {
        addNotificationForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const userId = document.getElementById('user_id').value;
            const senderId = document.getElementById('sender_id').value;
            const type = document.getElementById('type').value;
            const postId = document.getElementById('post_id').value;
            
            if (!userId || !senderId || !type) {
                showMessage('Tous les champs obligatoires doivent être remplis', false);
                return false;
            }
            
            if (userId === senderId) {
                showMessage('Le destinataire et l\'expéditeur ne peuvent pas être identiques', false);
                return false;
            }
            
            if ((type === 'like' || type === 'comment') && !postId) {
                showMessage('Un post est obligatoire pour les likes et commentaires', false);
                return false;
            }
            
            const formData = new FormData();
            formData.append('action', 'add_notification');
            formData.append('user_id', userId);
            formData.append('sender_id', senderId);
            formData.append('type', type);
            formData.append('post_id', postId);
            
            const submitBtn = addNotificationForm.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Ajout en cours...';
            submitBtn.disabled = true;
            
            fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(html => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const alertElement = doc.querySelector('.alert');
                
                if (alertElement) {
                    const message = alertElement.textContent.trim();
                    const isSuccess = alertElement.classList.contains('alert-success');
                    
                    showMessage(message, isSuccess);
                    
                    if (isSuccess) {
                        closeAddNotificationModal();
                        setTimeout(() => {
                            window.location.reload();
                        }, 1500);
                    }
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                showMessage('Erreur lors de l\'ajout de la notification', false);
            })
            .finally(() => {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            });
        });
    }
});

// Fonction pour afficher les messages
function showMessage(message, isSuccess) {
    const existingAlerts = document.querySelectorAll('.alert');
    existingAlerts.forEach(alert => alert.remove());
    
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert ${isSuccess ? 'alert-success' : 'alert-error'}`;
    alertDiv.innerHTML = `
        <i class="fas ${isSuccess ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i>
        ${message}
    `;
    
    const header = document.querySelector('.header');
    header.parentNode.insertBefore(alertDiv, header.nextSibling);
    
    setTimeout(() => {
        alertDiv.style.opacity = '0';
        setTimeout(() => alertDiv.remove(), 300);
    }, 3000);
}

// Fermer les modals en cliquant à l'extérieur
window.onclick = function(event) {
    const addModal = document.getElementById('addNotificationModal');
    
    if (event.target === addModal) {
        closeAddNotificationModal();
    }
}

// Auto-hide des messages de succès/erreur
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 300);
        }, 3000);
    });
});
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

.search-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 20px;
}

.search-form {
    flex: 1;
    max-width: 800px;
}

.search-input-group {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.search-input {
    flex: 1;
    min-width: 200px;
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

.filter-select {
    padding: 12px 16px;
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    font-size: 14px;
    background: white;
    transition: all 0.3s;
}

.filter-select:focus {
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
    font-size: 1.2rem;
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

.notifications-table-container {
    background: white;
    border-radius: 12px;
    padding: 25px;
    box-shadow: 0 4px 12px rgba(37,99,235,0.07);
    margin-bottom: 20px;
}

.table-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
    padding-bottom: 15px;
    border-bottom: 1px solid #e5e7eb;
}

.table-header h2 {
    margin: 0;
    color: var(--primary);
    font-size: 1.5rem;
}

.table-actions {
    display: flex;
    align-items: center;
    gap: 15px;
}

.results-count {
    color: #64748b;
    font-size: 14px;
}

.notifications-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
    gap: 20px;
}

.notification-card {
    background: white;
    border-radius: 16px;
    padding: 24px;
    border: 1px solid #e5e7eb;
    transition: all 0.3s ease;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.notification-card:hover {
    box-shadow: 0 8px 25px rgba(37,99,235,0.15);
    transform: translateY(-4px);
    border-color: var(--primary);
}

.notification-card.unread {
    border-left: 4px solid var(--primary);
}

.notification-header {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 18px;
    padding-bottom: 15px;
    border-bottom: 1px solid #f1f5f9;
}

.notification-avatar {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    overflow: hidden;
    flex-shrink: 0;
    border: 3px solid #e5e7eb;
    transition: border-color 0.3s ease;
}

.notification-card:hover .notification-avatar {
    border-color: var(--primary);
}

.notification-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.notification-info {
    flex: 1;
}

.notification-info h3 {
    margin: 0 0 4px 0;
    font-size: 16px;
    font-weight: 600;
    color: var(--primary);
}

.notification-date {
    margin: 0;
    font-size: 13px;
    color: #64748b;
    font-weight: 500;
}

.notification-status {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
}

.notification-status.unread {
    background: var(--primary);
    color: white;
}

.notification-status.read {
    background: #e5e7eb;
    color: #64748b;
}

.notification-content {
    margin-bottom: 18px;
}

.notification-type {
    margin-bottom: 12px;
}

.type-badge {
    display: inline-block;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
}

.type-friend_request {
    background: #fef3c7;
    color: #92400e;
}

.type-follow {
    background: #e0e7ff;
    color: #3730a3;
}

.type-like {
    background: #fecaca;
    color: #991b1b;
}

.type-comment {
    background: #d1fae5;
    color: #065f46;
}

.notification-details p {
    margin: 0 0 8px 0;
    line-height: 1.6;
    color: #374151;
    font-size: 14px;
}

.notification-actions {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.btn {
    padding: 10px 20px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 600;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s;
    font-size: 14px;
}

.btn-primary {
    background: var(--primary);
    color: white;
}

.btn-primary:hover {
    background: #1d4ed8;
}

.btn-secondary {
    background: #6b7280;
    color: white;
}

.btn-secondary:hover {
    background: #4b5563;
}

.btn-success {
    background: var(--success);
    color: white;
}

.btn-success:hover {
    background: #16a34a;
}

.btn-danger {
    background: var(--danger);
    color: white;
}

.btn-danger:hover {
    background: #dc2626;
}

.btn-sm {
    padding: 8px 12px;
    font-size: 12px;
}

/* Styles pour les alertes */
.alert {
    padding: 15px 20px;
    border-radius: 8px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
    transition: all 0.3s;
    font-weight: 500;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.alert-success {
    background: #d1fae5;
    color: #065f46;
    border: 1px solid #a7f3d0;
}

.alert-error {
    background: #fee2e2;
    color: #991b1b;
    border: 1px solid #fecaca;
}

.alert i {
    font-size: 1.1rem;
}

/* Pagination */
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

/* Modal */
.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.5);
    backdrop-filter: blur(5px);
}

.modal-content {
    background-color: white;
    margin: 5% auto;
    padding: 0;
    border-radius: 12px;
    width: 90%;
    max-width: 500px;
    box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1);
    animation: modalSlideIn 0.3s ease-out;
}

@keyframes modalSlideIn {
    from {
        opacity: 0;
        transform: translateY(-50px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.modal-header {
    padding: 20px 25px;
    border-bottom: 1px solid #e5e7eb;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-header h2 {
    margin: 0;
    color: var(--primary);
    font-size: 1.3rem;
}

.close {
    color: #64748b;
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
    transition: color 0.3s;
}

.close:hover {
    color: #1e293b;
}

/* Styles pour le formulaire */
.form-group {
    margin-bottom: 20px;
    padding: 0 25px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: #374151;
}

.form-group input,
.form-group textarea,
.form-group select {
    width: 100%;
    padding: 12px 16px;
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    font-size: 14px;
    transition: all 0.3s;
    box-sizing: border-box;
}

.form-group input:focus,
.form-group textarea:focus,
.form-group select:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(37,99,235,0.1);
}

.form-help {
    display: block;
    margin-top: 5px;
    font-size: 12px;
    color: #64748b;
}

.form-actions {
    display: flex;
    gap: 10px;
    justify-content: flex-end;
    padding: 20px 25px;
    border-top: 1px solid #e5e7eb;
}

/* Animation pour les boutons */
.btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.btn i.fa-spinner {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Responsive */
@media (max-width: 768px) {
    .search-header {
        flex-direction: column;
        align-items: stretch;
    }
    
    .search-form {
        max-width: none;
    }
    
    .search-input-group {
        flex-direction: column;
    }
    
    .modal-content {
        width: 95%;
        margin: 10% auto;
    }
    
    .form-actions {
        flex-direction: column;
    }
    
    .notifications-grid {
        grid-template-columns: 1fr;
    }
    
    .stats-grid {
        grid-template-columns: 1fr;
    }
    
    .notification-actions {
        flex-direction: column;
    }
    
    .pagination {
        gap: 5px;
    }
    
    .page-link {
        padding: 8px 12px;
        font-size: 14px;
    }
}

.dashboard-container {
    padding-bottom: 40px;
}

.notifications-table-container {
    margin-bottom: 20px;
}
</style>
