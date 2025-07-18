<?php
error_reporting(E_ALL);

// Démarrer la session
session_start();

// Headers JSON
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// Chemin ABSOLU vers la config DB
$db_path = __DIR__ . '/../vues/back-office/config/database.php';
if (!file_exists($db_path)) {
    die(json_encode([
        'status' => 'error',
        'message' => 'Fichier de configuration introuvable',
        'debug' => [
            'path_attempted' => $db_path,
            'allowed_paths' => ini_get('open_basedir'),
            'current_dir' => __DIR__
        ]
    ]));
}
require_once $db_path;

if (!isset($conn)) {
    die(json_encode([
        'status' => 'error',
        'message' => 'Connexion à la base de données non initialisée'
    ]));
}

$response = ['status' => 'error', 'message' => ''];

try {
    if (!isset($_SESSION["user"])) {
        throw new Exception("Non autorisé", 401);
    }
    $currentUserId = $_SESSION["user"];
    
    // GET : Lire le statut de la relation
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $userId = null;
        if (isset($_GET['user_id'])) {
            $userId = intval($_GET['user_id']);
        }
        if (!$userId || $userId == $currentUserId) {
            throw new Exception("ID utilisateur invalide", 400);
        }
        $stmt = $conn->prepare("SELECT status FROM friends WHERE (sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?) ORDER BY id DESC LIMIT 1");
        $stmt->execute([$currentUserId, $userId, $userId, $currentUserId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $friend_status = $row ? $row['status'] : 'none';
        $response = [
            'status' => 'success',
            'data' => [
                'friend_status' => $friend_status
            ]
        ];
        echo json_encode($response, JSON_PRETTY_PRINT);
        exit;
    }

    // POST : Gérer les actions (add, cancel, accept, reject)
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        $action = $input['action'] ?? ($_POST['action'] ?? null);
        $userId = $input['user_id'] ?? ($_POST['user_id'] ?? null);
        $userId = intval($userId);
        if (!$userId || $userId == $currentUserId) {
            throw new Exception("ID utilisateur invalide", 400);
        }
        if (!in_array($action, ['add', 'cancel', 'accept', 'reject'])) {
            throw new Exception("Action inconnue", 400);
        }
        if ($action === 'add') {
            // Vérifier si déjà une relation
            $stmt = $conn->prepare("SELECT status FROM friends WHERE (sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?) ORDER BY id DESC LIMIT 1");
            $stmt->execute([$currentUserId, $userId, $userId, $currentUserId]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row && $row['status'] === 'pending') {
                throw new Exception("Demande déjà envoyée", 409);
            }
            if ($row && $row['status'] === 'accepted') {
                throw new Exception("Déjà amis", 409);
            }
            // Insérer la demande
            $stmt = $conn->prepare("INSERT INTO friends (sender_id, receiver_id, status, created_at) VALUES (?, ?, 'pending', NOW())");
            if ($stmt->execute([$currentUserId, $userId])) {
                $response = ['status' => 'success', 'message' => 'Demande envoyée', 'friend_status' => 'pending'];
            } else {
                throw new Exception("Erreur lors de l'ajout", 500);
            }
        } elseif ($action === 'cancel') {
            // Supprimer la demande ou l'amitié
            $stmt = $conn->prepare("DELETE FROM friends WHERE ((sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?)) AND status IN ('pending','rejected')");
            if ($stmt->execute([$currentUserId, $userId, $userId, $currentUserId])) {
                $response = ['status' => 'success', 'message' => 'Demande ou amitié annulée', 'friend_status' => 'none'];
            } else {
                throw new Exception("Erreur lors de l'annulation", 500);
            }
        } elseif ($action === 'accept') {
            // Accepter la demande (seul le receveur peut accepter)
            $stmt = $conn->prepare("UPDATE friends SET status = 'accepted' WHERE sender_id = ? AND receiver_id = ? AND status = 'pending'");
            if ($stmt->execute([$userId, $currentUserId]) && $stmt->rowCount() > 0) {
                $response = ['status' => 'success', 'message' => 'Amitié acceptée', 'friend_status' => 'accepted'];
            } else {
                throw new Exception("Aucune demande à accepter", 404);
            }
        } elseif ($action === 'reject') {
            // Rejeter la demande (seul le receveur peut rejeter)
            $stmt = $conn->prepare("UPDATE friends SET status = 'rejected' WHERE sender_id = ? AND receiver_id = ? AND status = 'pending'");
            if ($stmt->execute([$userId, $currentUserId]) && $stmt->rowCount() > 0) {
                $response = ['status' => 'success', 'message' => 'Demande rejetée', 'friend_status' => 'rejected'];
            } else {
                throw new Exception("Aucune demande à rejeter", 404);
            }
        }
        echo json_encode($response, JSON_PRETTY_PRINT);
        exit;
    }

    throw new Exception("Méthode non supportée", 405);

} catch (PDOException $e) {
    $response['message'] = "Erreur de base de données: " . $e->getMessage();
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
    http_response_code($e->getCode() ?: 500);
}
echo json_encode($response, JSON_PRETTY_PRINT);
