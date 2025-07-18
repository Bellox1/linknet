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
$comment_text = trim($input['comment_text'] ?? '');
$user_id = $_SESSION["user"];

if (!$post_id || !$comment_text) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'ID du post et texte du commentaire requis']);
    exit();
}

try {
    // Insérer le commentaire
    $insert_query = "INSERT INTO comments (post_id, user_id, comment_text) VALUES (?, ?, ?)";
    $insert_stmt = $conn->prepare($insert_query);
    $insert_stmt->execute([$post_id, $user_id, $comment_text]);
    
    // Récupérer les informations du commentaire ajouté
    $comment_id = $conn->lastInsertId();
    
    // Récupérer les détails du commentaire avec les infos utilisateur
    $comment_query = "
        SELECT 
            c.id AS comment_id,
            c.comment_text,
            c.created_at AS comment_created_at,
            u.id AS commenter_id,
            u.username AS commenter_username,
            u.profile_picture AS commenter_profile_picture
        FROM comments c
        LEFT JOIN users u ON c.user_id = u.id
        WHERE c.id = ?
    ";
    $comment_stmt = $conn->prepare($comment_query);
    $comment_stmt->execute([$comment_id]);
    $new_comment = $comment_stmt->fetch(PDO::FETCH_ASSOC);
    
    // Formater le nom d'utilisateur
    $new_comment['commenter_username'] = ucfirst(strtolower($new_comment['commenter_username']));
    
    echo json_encode([
        'status' => 'success',
        'message' => 'Commentaire ajouté',
        'comment' => $new_comment
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Erreur serveur: ' . $e->getMessage()
    ]);
}
?>