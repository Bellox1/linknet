<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

session_start();
require_once __DIR__ . '/../../back-office/config/database.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION["user"])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Non autorisé']);
    exit();
}

// Récupérer les données JSON
$input = json_decode(file_get_contents('php://input'), true);
$post_id = $input['post_id'] ?? null;
$user_id = $_SESSION["user"];

if (!$post_id) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'ID du post manquant']);
    exit();
}

try {
    // Vérifier si l'utilisateur a déjà liké ce post
    $check_query = "SELECT id FROM likes WHERE user_id = ? AND post_id = ?";
    $check_stmt = $conn->prepare($check_query);
    $check_stmt->execute([$user_id, $post_id]);
    $existing_like = $check_stmt->fetch();

    if ($existing_like) {
        // Supprimer le like (unlike)
        $delete_query = "DELETE FROM likes WHERE user_id = ? AND post_id = ?";
        $delete_stmt = $conn->prepare($delete_query);
        $delete_stmt->execute([$user_id, $post_id]);
        
        echo json_encode([
            'status' => 'success',
            'action' => 'unliked',
            'message' => 'Like supprimé'
        ]);
    } else {
        // Ajouter le like
        $insert_query = "INSERT INTO likes (user_id, post_id) VALUES (?, ?)";
        $insert_stmt = $conn->prepare($insert_query);
        $insert_stmt->execute([$user_id, $post_id]);
        
        echo json_encode([
            'status' => 'success',
            'action' => 'liked',
            'message' => 'Post liké'
        ]);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Erreur serveur: ' . $e->getMessage()
    ]);
}
?>