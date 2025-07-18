<?php
require_once "../menu.php";

// Gestion des actions POST
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action) {
        try {
            switch ($action) {
                case 'add_hashtag':
                    if ($role === 'Administrateur') {
                        $tag = trim($_POST['tag'] ?? '');
                        $post_id = (int)($_POST['post_id'] ?? 0);
                        
                        if (empty($tag)) {
                            $message = "Le nom du hashtag est obligatoire";
                            break;
                        }
                        
                        if ($post_id <= 0) {
                            $message = "Veuillez sélectionner un post";
                            break;
                        }
                        
                        // Vérifier si le post existe
                        $checkPostStmt = $conn->prepare("SELECT id FROM posts WHERE id = ?");
                        $checkPostStmt->execute([$post_id]);
                        if (!$checkPostStmt->fetch()) {
                            $message = "Post introuvable";
                            break;
                        }
                        
                        // Vérifier si le hashtag existe déjà pour ce post
                        $checkStmt = $conn->prepare("SELECT id FROM hashtags WHERE tag = ? AND post_id = ?");
                        $checkStmt->execute([$tag, $post_id]);
                        if ($checkStmt->fetch()) {
                            $message = "Ce hashtag existe déjà pour ce post";
                            break;
                        }
                        
                        $insertStmt = $conn->prepare("INSERT INTO hashtags (tag, post_id, created_at) VALUES (?, ?, NOW())");
                        if ($insertStmt->execute([$tag, $post_id])) {
                            $message = "Hashtag ajouté avec succès";
                        } else {
                            $message = "Erreur lors de l'ajout";
                        }
                    } else {
                        $message = "Vous n'avez pas les permissions pour ajouter un hashtag";
                    }
                    break;
                    
                case 'delete':
                    if (!isset($_POST['hashtag_id'])) {
                        $message = "ID hashtag manquant";
                        break;
                    }
                    $hashtagId = (int)$_POST['hashtag_id'];
                    if ($role === 'Administrateur' || $role === 'Modérateur') {
                        $stmt = $conn->prepare("DELETE FROM hashtags WHERE id = ?");
                        $stmt->execute([$hashtagId]);
                        $message = "Hashtag supprimé avec succès";
                    } else {
                        $message = "Vous n'avez pas les permissions pour supprimer un hashtag";
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
$limit = 12;
$offset = ($page - 1) * $limit;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

$whereClause = '';
$params = [];
if (!empty($search)) {
    $whereClause = "WHERE h.tag LIKE ? OR p.content LIKE ?";
    $params = ["%$search%", "%$search%"];
}

$countQuery = "SELECT COUNT(DISTINCT h.id) FROM hashtags h 
               LEFT JOIN posts p ON h.post_id = p.id 
               $whereClause";
$countStmt = $conn->prepare($countQuery);
$countStmt->execute($params);
$total = $countStmt->fetchColumn();
$totalPages = ceil($total / $limit);

$query = "SELECT h.*, p.content as post_content, u.username as post_author,
                 (SELECT COUNT(*) FROM hashtags WHERE tag = h.tag) as usage_count,
                 (SELECT COUNT(DISTINCT post_id) FROM hashtags WHERE tag = h.tag) as posts_count
          FROM hashtags h 
          LEFT JOIN posts p ON h.post_id = p.id 
          LEFT JOIN users u ON p.user_id = u.id 
          $whereClause
          GROUP BY h.tag
          ORDER BY h.created_at DESC 
          LIMIT $limit OFFSET $offset";
$stmt = $conn->prepare($query);
$stmt->execute($params);
$hashtags = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
            <h1>Gestion des Hashtags</h1>
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
                        <input type="text" name="search" placeholder="Rechercher par nom ou contenu de post..." 
                               value="<?= htmlspecialchars($search) ?>" class="search-input">
                        <button type="submit" class="search-btn">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </form>
                
                <?php if ($role === 'Administrateur'): ?>
                    <button class="btn btn-primary" onclick="showAddHashtagModal()">
                        <i class="fas fa-plus"></i>
                        Ajouter un hashtag
                    </button>
                <?php endif; ?>
            </div>
        </div>

        <!-- Statistiques -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-hashtag"></i>
                </div>
                <div class="stat-content">
                    <h3>Total Hashtags</h3>
                    <div class="stat-value"><?= $total ?></div>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-pen-nib"></i>
                </div>
                <div class="stat-content">
                    <h3>Total Posts</h3>
                    <div class="stat-value">
                        <?php
                        $postsStmt = $conn->query("SELECT COUNT(*) FROM posts");
                        echo $postsStmt->fetchColumn();
                        ?>
                    </div>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-content">
                    <h3>Total Utilisateurs</h3>
                    <div class="stat-value">
                        <?php
                        $usersStmt = $conn->query("SELECT COUNT(*) FROM users");
                        echo $usersStmt->fetchColumn();
                        ?>
                    </div>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="stat-content">
                    <h3>Hashtags Populaires</h3>
                    <div class="stat-value">
                        <?php
                        $popularStmt = $conn->query("SELECT COUNT(DISTINCT tag) FROM hashtags WHERE tag IN (SELECT tag FROM hashtags GROUP BY tag HAVING COUNT(*) > 1)");
                        echo $popularStmt->fetchColumn();
                        ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Liste des hashtags -->
        <div class="posts-table-container">
            <div class="table-header">
                <h2>Liste des Hashtags</h2>
                <div class="table-actions">
                    <span class="results-count"><?= $total ?> hashtag(s) trouvé(s)</span>
                </div>
            </div>

            <div class="posts-grid">
                <?php foreach ($hashtags as $hashtag): ?>
                    <div class="post-card" data-hashtag-id="<?= $hashtag['id'] ?>">
                        <div class="post-header">
                            <div class="post-avatar">
                                <div class="hashtag-icon">
                                    <i class="fas fa-hashtag"></i>
                                </div>
                            </div>
                            <div class="post-info">
                                <h3>#<?= htmlspecialchars($hashtag['tag']) ?></h3>
                                <p class="post-date">Créé le <?= date('d/m/Y H:i', strtotime($hashtag['created_at'])) ?></p>
                            </div>
                        </div>
                        
                        <div class="post-content">
                            <p><strong>Post associé :</strong> <?= !empty($hashtag['post_content']) ? htmlspecialchars(substr($hashtag['post_content'], 0, 100)) . '...' : 'Post supprimé' ?></p>
                            <p><strong>Auteur :</strong> <?= htmlspecialchars($hashtag['post_author'] ?? 'Inconnu') ?></p>
                        </div>
                        
                        <div class="post-stats">
                            <div class="stat-item">
                                <i class="fas fa-pen-nib"></i>
                                <span><?= $hashtag['posts_count'] ?> posts</span>
                            </div>
                            <div class="stat-item">
                                <i class="fas fa-hashtag"></i>
                                <span><?= $hashtag['usage_count'] ?> utilisations</span>
                            </div>
                        </div>
                        
                        <div class="post-actions">
                            <button class="btn btn-info btn-sm" onclick="viewHashtag(<?= $hashtag['id'] ?>)">
                                <i class="fas fa-eye"></i>
                                Voir
                            </button>
                            
                            <?php if ($role === 'Administrateur' || $role === 'Modérateur'): ?>
                                <button type="button" class="btn btn-danger btn-sm" onclick="deleteHashtag(<?= $hashtag['id'] ?>, '<?= htmlspecialchars($hashtag['tag']) ?>')">
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
                        <a href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>" class="page-link">
                            <i class="fas fa-chevron-left"></i>
                            Précédent
                        </a>
                    <?php endif; ?>
                    
                    <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                        <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>" 
                           class="page-link <?= $i === $page ? 'active' : '' ?>">
                            <?= $i ?>
                        </a>
                    <?php endfor; ?>
                    
                    <?php if ($page < $totalPages): ?>
                        <a href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>" class="page-link">
                            Suivant
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal d'ajout de hashtag -->
<?php if ($role === 'Administrateur'): ?>
<div id="addHashtagModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Ajouter un Hashtag</h2>
            <span class="close" onclick="closeAddHashtagModal()">&times;</span>
        </div>
        
        <form id="addHashtagForm" method="POST">
            <input type="hidden" name="action" value="add_hashtag">
            
            <div class="form-group">
                <label for="tag">Nom du hashtag *</label>
                <input type="text" name="tag" id="tag" placeholder="exemple" required>
                <small class="form-help">Entrez le nom sans le # (il sera ajouté automatiquement)</small>
            </div>
            
            <div class="form-group">
                <label for="post_id">Post associé *</label>
                <select name="post_id" id="post_id" required>
                    <option value="">Sélectionner un post</option>
                    <?php foreach ($postsList as $post): ?>
                        <option value="<?= $post['id'] ?>"><?= htmlspecialchars(substr($post['content'], 0, 60)) ?>... (<?= htmlspecialchars($post['username']) ?>)</option>
                    <?php endforeach; ?>
                </select>
                <small class="form-help">Choisissez le post auquel associer ce hashtag</small>
            </div>
            
            <div class="form-actions">
                <button type="button" onclick="closeAddHashtagModal()" class="btn btn-secondary">Annuler</button>
                <button type="submit" class="btn btn-primary">Ajouter</button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<!-- Modal de détail du hashtag -->
<div id="hashtagDetailModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Détails du Hashtag</h2>
            <span class="close" onclick="closeHashtagDetailModal()">&times;</span>
        </div>
        <div id="hashtagDetailContent">
            <!-- Le contenu sera chargé dynamiquement -->
        </div>
    </div>
</div>

<script>
// Fonctions pour les modals
function showAddHashtagModal() {
    document.getElementById('addHashtagModal').style.display = 'block';
}

function closeAddHashtagModal() {
    document.getElementById('addHashtagModal').style.display = 'none';
    document.getElementById('addHashtagForm').reset();
}

function closeHashtagDetailModal() {
    document.getElementById('hashtagDetailModal').style.display = 'none';
}

// Fonction de suppression avec confirmation
function deleteHashtag(hashtagId, hashtagName) {
    if (confirm(`Êtes-vous sûr de vouloir supprimer le hashtag #${hashtagName} ?`)) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="hashtag_id" value="${hashtagId}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

// Fonction pour voir les détails d'un hashtag
function viewHashtag(hashtagId) {
    fetch(`hashtag_detail.php?id=${hashtagId}`)
        .then(response => response.text())
        .then(html => {
            document.getElementById('hashtagDetailContent').innerHTML = html;
            document.getElementById('hashtagDetailModal').style.display = 'block';
        })
        .catch(error => {
            console.error('Erreur:', error);
            alert('Erreur lors du chargement des détails du hashtag');
        });
}

// Validation du formulaire d'ajout
document.addEventListener('DOMContentLoaded', function() {
    const addHashtagForm = document.getElementById('addHashtagForm');
    if (addHashtagForm) {
        addHashtagForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const tag = document.getElementById('tag').value.trim();
            const postId = document.getElementById('post_id').value;
            
            if (!tag) {
                showMessage('Le nom du hashtag est obligatoire', false);
                return false;
            }
            
            if (!postId) {
                showMessage('Veuillez sélectionner un post', false);
                return false;
            }
            
            if (tag.length < 2) {
                showMessage('Le nom du hashtag doit contenir au moins 2 caractères', false);
                return false;
            }
            
            // Nettoyer le nom (enlever les caractères spéciaux sauf lettres, chiffres et underscore)
            const cleanTag = tag.replace(/[^a-zA-Z0-9_]/g, '');
            if (cleanTag !== tag) {
                showMessage('Le nom du hashtag ne peut contenir que des lettres, chiffres et underscore', false);
                return false;
            }
            
            const formData = new FormData();
            formData.append('action', 'add_hashtag');
            formData.append('tag', cleanTag);
            formData.append('post_id', postId);
            
            const submitBtn = addHashtagForm.querySelector('button[type="submit"]');
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
                        closeAddHashtagModal();
                        setTimeout(() => {
                            window.location.reload();
                        }, 1500);
                    }
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                showMessage('Erreur lors de l\'ajout du hashtag', false);
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
    const addModal = document.getElementById('addHashtagModal');
    const detailModal = document.getElementById('hashtagDetailModal');
    
    if (event.target === addModal) {
        closeAddHashtagModal();
    }
    if (event.target === detailModal) {
        closeHashtagDetailModal();
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

.posts-table-container {
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

.posts-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 20px;
}

.post-card {
    background: white;
    border-radius: 16px;
    padding: 24px;
    border: 1px solid #e5e7eb;
    transition: all 0.3s ease;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.post-card:hover {
    box-shadow: 0 8px 25px rgba(37,99,235,0.15);
    transform: translateY(-4px);
    border-color: var(--primary);
}

.post-card.fade-out {
    opacity: 0;
    transform: translateY(-10px);
}

.post-header {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 18px;
    padding-bottom: 15px;
    border-bottom: 1px solid #f1f5f9;
}

.post-avatar {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    overflow: hidden;
    flex-shrink: 0;
    border: 3px solid #e5e7eb;
    transition: border-color 0.3s ease;
}

.post-card:hover .post-avatar {
    border-color: var(--primary);
}

.hashtag-icon {
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, var(--primary), var(--secondary));
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.5rem;
}

.post-info h3 {
    margin: 0 0 4px 0;
    font-size: 16px;
    font-weight: 600;
    color: var(--primary);
}

.post-date {
    margin: 0;
    font-size: 13px;
    color: #64748b;
    font-weight: 500;
}

.post-content {
    margin-bottom: 18px;
}

.post-content p {
    margin: 0 0 15px 0;
    line-height: 1.6;
    color: #374151;
    font-size: 15px;
}

.post-stats {
    display: flex;
    gap: 24px;
    margin-bottom: 18px;
    padding: 12px 0;
    border-top: 1px solid #f1f5f9;
    border-bottom: 1px solid #f1f5f9;
}

.stat-item {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 14px;
    color: #64748b;
    font-weight: 500;
}

.stat-item i {
    color: var(--primary);
    font-size: 16px;
}

.post-actions {
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

.btn-info {
    background: var(--info);
    color: white;
}

.btn-info:hover {
    background: #0284c7;
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
    
    .modal-content {
        width: 95%;
        margin: 10% auto;
    }
    
    .form-actions {
        flex-direction: column;
    }
    
    .posts-grid {
        grid-template-columns: 1fr;
    }
    
    .stats-grid {
        grid-template-columns: 1fr;
    }
    
    .post-stats {
        flex-direction: column;
        gap: 8px;
    }
    
    .post-actions {
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
</style> 