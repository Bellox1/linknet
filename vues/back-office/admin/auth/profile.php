<?php
require_once "../menu.php";

if (session_status() === PHP_SESSION_NONE) session_start();
require_once "../../config/database.php";

if (!isset($_SESSION["admin"])) {
    header("Location: login.php");
    exit();
}

$adminId = $_SESSION["admin"]["id"] ?? null;
$role = $_SESSION["admin"]["role"] ?? 'Modérateur';

if (!$adminId) {
    header("Location: login.php");
    exit();
}

// Initialisation
$error = $success = '';

// Récupération des données admin
try {
    $stmt = $conn->prepare("SELECT * FROM admins WHERE id = ?");
    $stmt->execute([$adminId]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$admin) {
        header("Location: login.php");
        exit();
    }
} catch (PDOException $e) {
    $error = "Erreur de base de données";
}

// Traitement des formulaires
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Mise à jour des infos
    if (isset($_POST['update_info'])) {
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        
        if (empty($username) || empty($email)) {
            $error = "Tous les champs sont requis";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Email invalide";
        } else {
            try {
                $stmt = $conn->prepare("UPDATE admins SET username = ?, email = ? WHERE id = ?");
                $stmt->execute([$username, $email, $adminId]);
                
                $_SESSION['admin']['username'] = $username;
                $_SESSION['admin']['email'] = $email;
                $admin['username'] = $username;
                $admin['email'] = $email;
                
                $success = "Informations mises à jour avec succès";
            } catch (PDOException $e) {
                $error = "Erreur lors de la mise à jour";
            }
        }
    }
    
    // Changement de mot de passe
    if (isset($_POST['update_password'])) {
        $current = $_POST['current_password'] ?? '';
        $new = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';
        
        if (empty($current) || empty($new) || empty($confirm)) {
            $error = "Tous les champs sont requis";
        } elseif (!password_verify($current, $admin['password'])) {
            $error = "Mot de passe actuel incorrect";
        } elseif ($new !== $confirm) {
            $error = "Les mots de passe ne correspondent pas";
        } elseif (strlen($new) < 8) {
            $error = "Le mot de passe doit contenir au moins 8 caractères";
        } else {
            try {
                $hash = password_hash($new, PASSWORD_BCRYPT);
                $stmt = $conn->prepare("UPDATE admins SET password = ? WHERE id = ?");
                $stmt->execute([$hash, $adminId]);
                $success = "Mot de passe mis à jour avec succès";
            } catch (PDOException $e) {
                $error = "Erreur lors de la mise à jour du mot de passe";
            }
        }
    }
    
    // Suppression de compte (seulement pour les administrateurs)
    if (isset($_POST['delete_account']) && $role === 'Administrateur') {
        $password = $_POST['delete_password'] ?? '';
        
        if (empty($password)) {
            $error = "Veuillez entrer votre mot de passe";
        } elseif (!password_verify($password, $admin['password'])) {
            $error = "Mot de passe incorrect";
        } else {
            try {
                $stmt = $conn->prepare("DELETE FROM admins WHERE id = ?");
                $stmt->execute([$adminId]);
                session_destroy();
                header("Location: login.php");
                exit();
            } catch (PDOException $e) {
                $error = "Erreur lors de la suppression";
            }
        }
    }
}

function calculateTimeSince($date) {
    $now = new DateTime();
    $created = new DateTime($date);
    $interval = $now->diff($created);
    
    if ($interval->y > 0) {
        return $interval->y . ' an' . ($interval->y > 1 ? 's' : '');
    } elseif ($interval->m > 0) {
        return $interval->m . ' mois';
    } else {
        return $interval->d . ' jour' . ($interval->d > 1 ? 's' : '');
    }
}
?>
<style>
    /* Styles spécifiques à la page profile */
    .profile-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 20px;
    }
    
    .header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
        padding-bottom: 20px;
        border-bottom: 1px solid #e0e0e0;
    }
    
    .header h1 {
        font-size: 2rem;
        color: #1e293b;
        margin: 0;
    }
    
    /* Alertes */
    .alert {
        padding: 15px 20px;
        border-radius: 8px;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
        font-weight: 500;
    }
    
    .alert-success {
        background: #ecfdf5;
        color: #065f46;
        border: 1px solid #a7f3d0;
    }
    
    .alert-danger {
        background: #fef2f2;
        color: #991b1b;
        border: 1px solid #fecaca;
    }
    
    /* Cartes */
    .profile-card,
    .profile-section {
        background: white;
        border-radius: 12px;
        padding: 30px;
        margin-bottom: 20px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        border: 1px solid #e5e7eb;
    }
    
    .profile-header {
        display: flex;
        align-items: center;
        gap: 20px;
        margin-bottom: 30px;
    }
    
    .profile-avatar {
        width: 80px;
        height: 80px;
        background: linear-gradient(135deg, var(--primary), var(--secondary));
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 32px;
        color: white;
        box-shadow: 0 4px 15px rgba(37,99,235,0.3);
    }
    
    .profile-info h2 {
        font-size: 1.8rem;
        color: #1e293b;
        margin-bottom: 8px;
    }
    
    .profile-meta {
        display: flex;
        gap: 20px;
        color: #6b7280;
        font-size: 14px;
    }
    
    .profile-meta span {
        display: flex;
        align-items: center;
        gap: 5px;
    }
    
    /* Sections */
    .section-title {
        font-size: 1.3rem;
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .section-title i {
        color: var(--primary);
    }
    
    /* Formulaires */
    .profile-form {
        max-width: 500px;
    }
    
    .form-group {
        margin-bottom: 20px;
    }
    
    .form-label {
        display: block;
        margin-bottom: 8px;
        font-weight: 500;
        color: #374151;
        font-size: 14px;
    }
    
    .input-wrapper {
        position: relative;
        max-width: 400px;
    }
    
    .form-input {
        width: 100%;
        padding: 12px 16px;
        border: 2px solid #e5e7eb;
        border-radius: 8px;
        font-size: 14px;
        transition: all 0.3s;
        background: #f9fafb;
    }
    
    .form-input:focus {
        outline: none;
        border-color: var(--primary);
        background: white;
        box-shadow: 0 0 0 3px rgba(37,99,235,0.1);
    }
    
    .password-wrapper {
        position: relative;
    }
    
    .password-toggle {
        position: absolute;
        right: 12px;
        top: 50%;
        transform: translateY(-50%);
        cursor: pointer;
        color: #6b7280;
        transition: color 0.3s;
    }
    
    .password-toggle:hover {
        color: var(--primary);
    }
    
    /* Boutons */
    .btn {
        padding: 12px 24px;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        font-size: 14px;
        cursor: pointer;
        transition: all 0.3s;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    
    .btn-primary {
        background: linear-gradient(135deg, #2563eb, #3b82f6);
        color: white;
    }
    
    .btn-primary:hover {
        background: linear-gradient(135deg, #1d4ed8, #2563eb);
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(37,99,235,0.3);
    }
    
    .btn-danger {
        background: linear-gradient(135deg, #ef4444, #f87171);
        color: white;
    }
    
    .btn-danger:hover {
        background: linear-gradient(135deg, #dc2626, #ef4444);
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(239,68,68,0.3);
    }
    
    /* Zone dangereuse */
    .danger-zone {
        border-left: 4px solid var(--danger);
        background: linear-gradient(135deg, #fef2f2, #fecaca);
    }
    
    .warning-box {
        background: rgba(239,68,68,0.1);
        border: 1px solid rgba(239,68,68,0.2);
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 20px;
    }
    
    .warning-box p {
        color: #dc2626;
        font-size: 14px;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 8px;
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
        background: rgba(0,0,0,0.5);
        backdrop-filter: blur(4px);
    }
    
    .modal-content {
        background: white;
        margin: 10vh auto;
        padding: 30px;
        border-radius: 12px;
        width: 90%;
        max-width: 450px;
        position: relative;
        box-shadow: 0 20px 40px rgba(0,0,0,0.2);
    }
    
    .close-modal {
        position: absolute;
        right: 20px;
        top: 20px;
        font-size: 24px;
        cursor: pointer;
        color: #6b7280;
        transition: color 0.3s;
    }
    
    .close-modal:hover {
        color: var(--danger);
    }
    
    .modal h3 {
        color: #1e293b;
        font-size: 1.4rem;
        margin-bottom: 15px;
    }
    
    .modal p {
        color: #64748b;
        margin-bottom: 20px;
        line-height: 1.6;
    }
    
    /* Responsive */
    @media (max-width: 900px) {
        .profile-container {
            padding: 0 15px;
        }
        
        .header h1 {
            font-size: 1.8rem;
        }
        
        .profile-header {
            flex-direction: column;
            text-align: center;
        }
        
        .profile-meta {
            justify-content: center;
        }
        
        .input-wrapper {
            max-width: 100%;
        }
        
        .modal-content {
            margin: 5vh auto;
            padding: 20px;
        }
    }
    
    @media (max-width: 600px) {
        .profile-container {
            padding: 0 10px;
        }
        
        .profile-card,
        .profile-section {
            padding: 20px;
        }
        
        .header h1 {
            font-size: 1.5rem;
            margin-bottom: 20px;
        }
        
        .profile-header {
            flex-direction: column;
            text-align: center;
        }
        
        .profile-meta {
            justify-content: center;
            flex-direction: column;
            gap: 10px;
        }
        
        .input-wrapper {
            max-width: 100%;
        }
        
        .modal-content {
            margin: 5vh auto;
            padding: 15px;
        }
    }
</style>
<div class="main-content">
    <div class="profile-container">
        <div class="header">
            <h1>Profil Administrateur</h1>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i>
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>

        <!-- Carte de profil -->
        <div class="profile-card">
            <div class="profile-header">
                <div class="profile-avatar">
                    <i class="fas fa-user-shield"></i>
                </div>
                <div class="profile-info">
                    <h2><?= htmlspecialchars($admin['username']) ?></h2>
                    <div class="profile-meta">
                        <span><i class="fas fa-envelope"></i> <?= htmlspecialchars($admin['email']) ?></span>
                        <span><i class="fas fa-user-tag"></i> <?= htmlspecialchars($admin['role']) ?></span>
                        <span><i class="fas fa-calendar-alt"></i> Membre depuis <?= calculateTimeSince($admin['created_at']) ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Informations personnelles -->
        <div class="profile-section">
            <div class="section-title">
                <i class="fas fa-user-cog"></i>
                Informations personnelles
            </div>
            <form method="POST" class="profile-form">
                <input type="hidden" name="update_info" value="1">
                <div class="form-group">
                    <label class="form-label">Nom d'utilisateur</label>
                    <div class="input-wrapper">
                        <input type="text" name="username" class="form-input" value="<?= htmlspecialchars($admin['username']) ?>" required>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <div class="input-wrapper">
                        <input type="email" name="email" class="form-input" value="<?= htmlspecialchars($admin['email']) ?>" required>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i>
                    Mettre à jour
                </button>
            </form>
        </div>

        <!-- Sécurité du compte -->
        <div class="profile-section">
            <div class="section-title">
                <i class="fas fa-key"></i>
                Sécurité du compte
            </div>
            <form method="POST" class="profile-form">
                <input type="hidden" name="update_password" value="1">
                <div class="form-group">
                    <label class="form-label">Mot de passe actuel</label>
                    <div class="input-wrapper password-wrapper">
                        <input type="password" name="current_password" id="current_password" class="form-input" required>
                        <i class="fas fa-eye password-toggle" data-target="current_password"></i>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Nouveau mot de passe</label>
                    <div class="input-wrapper password-wrapper">
                        <input type="password" name="new_password" id="new_password" class="form-input" required minlength="8">
                        <i class="fas fa-eye password-toggle" data-target="new_password"></i>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Confirmation</label>
                    <div class="input-wrapper password-wrapper">
                        <input type="password" name="confirm_password" id="confirm_password" class="form-input" required minlength="8">
                        <i class="fas fa-eye password-toggle" data-target="confirm_password"></i>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-key"></i>
                    Changer le mot de passe
                </button>
            </form>
        </div>

        <!-- Zone dangereuse (seulement pour les administrateurs) -->
        <?php if ($role === 'Administrateur'): ?>
        <div class="profile-section danger-zone">
            <div class="section-title">
                <i class="fas fa-exclamation-triangle"></i>
                Zone dangereuse
            </div>
            <div class="warning-box">
                <p>
                    <i class="fas fa-warning"></i>
                    La suppression de compte est irréversible. Toutes vos données seront perdues définitivement.
                </p>
            </div>
            <button id="deleteBtn" class="btn btn-danger">
                <i class="fas fa-trash"></i>
                Supprimer mon compte
            </button>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal de suppression (seulement pour les administrateurs) -->
<?php if ($role === 'Administrateur'): ?>
<div id="deleteModal" class="modal">
    <div class="modal-content">
        <span class="close-modal">&times;</span>
        <h3>Confirmation de suppression</h3>
        <p>Pour confirmer la suppression de votre compte, veuillez entrer votre mot de passe :</p>
        <form method="POST" id="deleteForm">
            <input type="hidden" name="delete_account" value="1">
            <div class="form-group">
                <div class="input-wrapper password-wrapper">
                    <input type="password" name="delete_password" id="delete_password" class="form-input" required>
                    <i class="fas fa-eye password-toggle" data-target="delete_password"></i>
                </div>
            </div>
            <button type="submit" class="btn btn-danger">
                <i class="fas fa-trash"></i>
                Confirmer la suppression
            </button>
        </form>
    </div>
</div>
<?php endif; ?>

<script>
// Toggle password visibility
document.querySelectorAll('.password-toggle').forEach(icon => {
    icon.addEventListener('click', function() {
        const targetId = this.getAttribute('data-target');
        const input = document.getElementById(targetId);
        const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
        input.setAttribute('type', type);
        this.classList.toggle('fa-eye');
        this.classList.toggle('fa-eye-slash');
    });
});

// Auto-hide success messages after 3 seconds
document.addEventListener('DOMContentLoaded', function() {
    const successMessages = document.querySelectorAll('.alert-success');
    const errorMessages = document.querySelectorAll('.alert-danger');
    
    // Auto-hide success messages
    successMessages.forEach(function(message) {
        setTimeout(function() {
            message.style.transition = 'opacity 0.5s ease-out';
            message.style.opacity = '0';
            setTimeout(function() {
                message.remove();
            }, 500);
        }, 3000);
    });
    
    // Auto-hide error messages
    errorMessages.forEach(function(message) {
        setTimeout(function() {
            message.style.transition = 'opacity 0.5s ease-out';
            message.style.opacity = '0';
            setTimeout(function() {
                message.remove();
            }, 500);
        }, 3000);
    });
});

// Gestion de la suppression (seulement pour les administrateurs)
<?php if ($role === 'Administrateur'): ?>
const deleteBtn = document.getElementById('deleteBtn');
const deleteModal = document.getElementById('deleteModal');
const deleteForm = document.getElementById('deleteForm');

if (deleteBtn && deleteModal) {
    deleteBtn.addEventListener('click', () => {
        deleteModal.style.display = 'block';
    });

    document.querySelector('.close-modal').addEventListener('click', () => {
        deleteModal.style.display = 'none';
    });

    window.addEventListener('click', (event) => {
        if (event.target === deleteModal) {
            deleteModal.style.display = 'none';
        }
    });
}
<?php endif; ?>
</script> 