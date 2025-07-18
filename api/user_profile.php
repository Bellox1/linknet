<?php
error_reporting(E_ALL);
session_start();

// Headers JSON
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// Chemin ABSOLU corrigé pour database.php
$db_path = __DIR__ . '/../vues/back-office/config/database.php';

// Vérifier que le fichier existe et est accessible
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

// Inclure le fichier de configuration
require_once $db_path;

// Vérifier si la connexion $conn existe
if (!isset($conn)) {
    die(json_encode([
        'status' => 'error',
        'message' => 'Connexion à la base de données non initialisée'
    ]));
}

$response = ['status' => 'error', 'message' => ''];

try {
    // Vérifier si user_id est fourni
    if (!isset($_GET['user_id'])) {
        throw new Exception("ID utilisateur requis", 400);
    }

    $user_id = $_GET['user_id'];
    
    // 1. Infos utilisateur (ajout de birthday dans la requête)
    $stmt = $conn->prepare("SELECT id, username, email, bio, profile_picture, created_at, birthday FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        throw new Exception("Utilisateur non trouvé", 404);
    }

    // 2. Amis
    $stmt = $conn->prepare("SELECT users.id, users.username, users.profile_picture, users.birthday FROM friends 
                          JOIN users ON (friends.sender_id = users.id OR friends.receiver_id = users.id) 
                          WHERE (friends.sender_id = ? OR friends.receiver_id = ?) 
                          AND friends.status = 'accepted' AND users.id != ?");
    $stmt->execute([$user_id, $user_id, $user_id]);
    $friends = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 3. Posts enrichis (identique à actualite.php)
    $current_user_id = isset($_SESSION['user']) ? $_SESSION['user'] : 0;
    $stmt = $conn->prepare("
        SELECT 
            p.id AS post_id,
            p.content AS post_content,
            p.media AS post_media,
            p.created_at AS post_created_at,
            u.id AS user_id,
            u.username AS user_username,
            u.profile_picture AS user_profile_picture,
            u.bio AS user_bio,
            u.birthday AS user_birthday,
            u.created_at AS user_created_at,
            (SELECT COUNT(*) FROM likes WHERE post_id = p.id) AS likes_count,
            (SELECT COUNT(*) FROM comments WHERE post_id = p.id) AS comments_count,
            (SELECT COUNT(*) FROM likes WHERE post_id = p.id AND user_id = ?) AS current_user_liked
        FROM posts p
        LEFT JOIN users u ON p.user_id = u.id
        WHERE p.user_id = ?
        ORDER BY p.created_at DESC
    ");
    $stmt->execute([$current_user_id, $user_id]);
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($posts as &$post) {
        // Hashtags
        $hashtag_stmt = $conn->prepare("SELECT h.id AS hashtag_id, h.tag AS hashtag_name, h.created_at AS hashtag_created_at FROM hashtags h WHERE h.post_id = ? ORDER BY h.created_at ASC");
        $hashtag_stmt->execute([$post['post_id']]);
        $post['hashtags'] = $hashtag_stmt->fetchAll(PDO::FETCH_ASSOC);

        // Commentaires
        $comment_stmt = $conn->prepare("SELECT c.id AS comment_id, c.comment_text, c.created_at AS comment_created_at, cu.id AS commenter_id, cu.username AS commenter_username, cu.profile_picture AS commenter_profile_picture FROM comments c LEFT JOIN users cu ON c.user_id = cu.id WHERE c.post_id = ? ORDER BY c.created_at DESC");
        $comment_stmt->execute([$post['post_id']]);
        $post['all_comments'] = $comment_stmt->fetchAll(PDO::FETCH_ASSOC);

        // Likes
        $like_stmt = $conn->prepare("SELECT l.id AS like_id, l.created_at AS like_created_at, lu.id AS liker_id, lu.username AS liker_username, lu.profile_picture AS liker_profile_picture FROM likes l LEFT JOIN users lu ON l.user_id = lu.id WHERE l.post_id = ? ORDER BY l.created_at DESC");
        $like_stmt->execute([$post['post_id']]);
        $post['all_likes'] = $like_stmt->fetchAll(PDO::FETCH_ASSOC);

        // Formatage des noms
        $post['user_username'] = ucfirst(strtolower($post['user_username']));
        foreach ($post['all_comments'] as &$comment) {
            $comment['commenter_username'] = ucfirst(strtolower($comment['commenter_username']));
        }
        foreach ($post['all_likes'] as &$like) {
            $like['liker_username'] = ucfirst(strtolower($like['liker_username']));
        }
    }
    unset($post); // Bonnes pratiques

    // 4. Nombre d'abonnés (followers)
    $stmt = $conn->prepare("SELECT COUNT(*) FROM followers WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $followers_count = (int)$stmt->fetchColumn();
    $user['followers_count'] = $followers_count;

    // 5. Liste des abonnés (followers)
    $stmt = $conn->prepare("SELECT users.id, users.username, users.profile_picture, users.birthday FROM followers 
                            JOIN users ON followers.follower_id = users.id 
                            WHERE followers.user_id = ?");
    $stmt->execute([$user_id]);
    $followers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 6. Nombre d'abonnements (following)
    $stmt = $conn->prepare("SELECT COUNT(*) FROM followers WHERE follower_id = ?");
    $stmt->execute([$user_id]);
    $following_count = (int)$stmt->fetchColumn();
    $user['following_count'] = $following_count;

    // Réponse succès (avec tous les champs)
    $response = [
        'status' => 'success',
        'data' => [
            'user' => $user,
            'friends' => $friends,
            'followers' => $followers,
            'posts' => $posts
        ]
    ];

} catch (PDOException $e) {
    $response['message'] = "Erreur de base de données: " . $e->getMessage();
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
    http_response_code($e->getCode() ?: 500);
}

echo json_encode($response, JSON_PRETTY_PRINT);
?> 