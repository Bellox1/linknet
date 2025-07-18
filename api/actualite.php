<?php
error_reporting(E_ALL);
ini_set('display_errors', 0); // Désactiver l'affichage des erreurs en production

// Démarrer la session
session_start();

// Headers JSON
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

try {
    // Chemin ABSOLU corrigé pour database.php
    $db_path = __DIR__ . '/../vues/back-office/config/database.php';

    // Vérifier que le fichier existe et est accessible
    if (!file_exists($db_path)) {
        throw new Exception('Fichier de configuration introuvable');
    }

    // Inclure le fichier de configuration
    require_once $db_path;

    // Vérifier si la connexion $conn existe
    if (!isset($conn)) {
        throw new Exception('Connexion à la base de données non initialisée');
    }

    // Vérifier la session
    if (!isset($_SESSION["user"])) {
        http_response_code(401);
        echo json_encode([
            'status' => 'error',
            'message' => 'Non autorisé - Session invalide'
        ]);
        exit();
    }

    $user_id = $_SESSION["user"];
    $method = $_SERVER['REQUEST_METHOD'];
    
    // Gérer les différentes méthodes HTTP
    switch ($method) {
        case 'GET':
            // Récupérer tous les posts (actualités)
            getActualites($conn, $user_id);
            break;
            
        case 'POST':
            // Gérer les actions (like, commentaire)
            $input = json_decode(file_get_contents('php://input'), true);
            $action = $input['action'] ?? '';
            
            switch ($action) {
                case 'like':
                    toggleLike($conn, $user_id, $input);
                    break;
                case 'comment':
                    addComment($conn, $user_id, $input);
                    break;
                default:
                    http_response_code(400);
                    echo json_encode([
                        'status' => 'error',
                        'message' => 'Action non reconnue'
                    ]);
            }
            break;
            
        default:
            http_response_code(405);
            echo json_encode([
                'status' => 'error',
                'message' => 'Méthode non autorisée'
            ]);
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Erreur de base de données'
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Erreur serveur'
    ]);
}

// Fonction pour récupérer les actualités
function getActualites($conn, $user_id) {
    $current_user_id = $user_id;

    $query = "
    SELECT 
        -- Informations du post
        p.id AS post_id,
        p.content AS post_content,
        p.media AS post_media,
        p.created_at AS post_created_at,
        
        -- Informations de l'utilisateur qui a créé le post
        u.id AS user_id,
        u.username AS user_username,
        u.profile_picture AS user_profile_picture,
        u.bio AS user_bio,
        u.birthday AS user_birthday,
        u.created_at AS user_created_at,
        
        -- Compteurs
        (SELECT COUNT(*) FROM likes WHERE post_id = p.id) AS likes_count,
        (SELECT COUNT(*) FROM comments WHERE post_id = p.id) AS comments_count,
        
        -- Vérifier si l'utilisateur connecté a liké ce post
        " . ($current_user_id ? "(SELECT COUNT(*) FROM likes WHERE post_id = p.id AND user_id = $current_user_id)" : "0") . " AS current_user_liked
        
    FROM posts p
    LEFT JOIN users u ON p.user_id = u.id
    ORDER BY p.created_at DESC
    ";

    $stmt = $conn->prepare($query);
    $stmt->execute();
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Traitement des données pour chaque post
    foreach ($posts as &$post) {
        // Récupérer les hashtags spécifiques à ce post
        $hashtag_query = "
            SELECT 
                h.id AS hashtag_id,
                h.tag AS hashtag_name,
                h.created_at AS hashtag_created_at
            FROM hashtags h
            WHERE h.post_id = ?
            ORDER BY h.created_at ASC
        ";
        $hashtag_stmt = $conn->prepare($hashtag_query);
        $hashtag_stmt->execute([$post['post_id']]);
        $post['hashtags'] = $hashtag_stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Récupérer TOUS les commentaires avec infos utilisateur
        $comment_query = "
            SELECT 
                c.id AS comment_id,
                c.comment_text,
                c.created_at AS comment_created_at,
                cu.id AS commenter_id,
                cu.username AS commenter_username,
                cu.profile_picture AS commenter_profile_picture
            FROM comments c
            LEFT JOIN users cu ON c.user_id = cu.id
            WHERE c.post_id = ?
            ORDER BY c.created_at DESC
        ";
        $comment_stmt = $conn->prepare($comment_query);
        $comment_stmt->execute([$post['post_id']]);
        $post['all_comments'] = $comment_stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Récupérer TOUS les likes avec infos utilisateur
        $like_query = "
            SELECT 
                l.id AS like_id,
                l.created_at AS like_created_at,
                lu.id AS liker_id,
                lu.username AS liker_username,
                lu.profile_picture AS liker_profile_picture
            FROM likes l
            LEFT JOIN users lu ON l.user_id = lu.id
            WHERE l.post_id = ?
            ORDER BY l.created_at DESC
        ";
        $like_stmt = $conn->prepare($like_query);
        $like_stmt->execute([$post['post_id']]);
        $post['all_likes'] = $like_stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Formater les noms d'utilisateur avec majuscule
        $post['user_username'] = ucfirst(strtolower($post['user_username']));
        
        // Formater les noms des commentateurs
        foreach ($post['all_comments'] as &$comment) {
            $comment['commenter_username'] = ucfirst(strtolower($comment['commenter_username']));
        }
        
        // Formater les noms des likers
        foreach ($post['all_likes'] as &$like) {
            $like['liker_username'] = ucfirst(strtolower($like['liker_username']));
        }
    }

    // Réponse succès
    echo json_encode([
        'status' => 'success',
        'data' => [
            'posts' => $posts,
            'total_posts' => count($posts)
        ]
    ], JSON_PRETTY_PRINT);
}

// Fonction pour liker/unliker
function toggleLike($conn, $user_id, $input) {
    $post_id = $input['post_id'] ?? null;
    
    if (!$post_id) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'ID du post manquant']);
        return;
    }

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
}

// Fonction pour ajouter un commentaire
function addComment($conn, $user_id, $input) {
    $post_id = $input['post_id'] ?? null;
    $comment_text = trim($input['comment_text'] ?? '');
    
    if (!$post_id || !$comment_text) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'ID du post et texte du commentaire requis']);
        return;
    }

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
}
?> 