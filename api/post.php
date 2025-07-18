<?php
error_reporting(E_ALL);
session_start();

// Headers JSON
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

try {
    // Chemin ABSOLU pour database.php
    $db_path = __DIR__ . '/../vues/back-office/config/database.php';
    if (!file_exists($db_path)) {
        throw new Exception('Fichier de configuration introuvable');
    }
    require_once $db_path;
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

    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        http_response_code(405);
        echo json_encode(['status' => 'error', 'message' => 'Méthode non autorisée']);
        exit();
    }

    // Gérer l'upload du média
    $media = null;
    if (!empty($_FILES["media"]["name"])) {
        $target_dir = __DIR__ . "/../vues/back-office/uploads/Posts/" . $user_id . "/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $media_name = uniqid() . "_" . basename($_FILES["media"]["name"]);
        $media_path = $target_dir . $media_name;
        $file_type = strtolower(pathinfo($media_path, PATHINFO_EXTENSION));
        $allowed_types = ["jpg", "jpeg", "png", "gif", "mp4", "mov", "avi"];
        if (!in_array($file_type, $allowed_types)) {
            throw new Exception("Type de fichier non autorisé");
        }
        if (!move_uploaded_file($_FILES["media"]["tmp_name"], $media_path)) {
            throw new Exception("Échec de l'upload");
        }
        // Chemin relatif pour la BDD
        $media = "Posts/" . $user_id . "/" . $media_name;
    }

    // Récupérer le contenu
    $content = trim($_POST["content"] ?? "");
    if ($content === "") {
        throw new Exception("Le contenu est vide");
    }

    // Insertion du post
    $stmt = $conn->prepare("INSERT INTO posts (user_id, content, media, created_at) VALUES (?, ?, ?, NOW())");
    $stmt->execute([$user_id, $content, $media]);
    $post_id = $conn->lastInsertId();

    // Hashtags
    preg_match_all('/#(\\w+)/', $content, $matches);
    $hashtags = $matches[1];
    foreach ($hashtags as $tag) {
        $stmt = $conn->prepare("INSERT INTO hashtags (tag, post_id) VALUES (?, ?)");
        $stmt->execute([$tag, $post_id]);
    }

    echo json_encode([
        'status' => 'success',
        'message' => 'Post créé',
        'data' => [
            'post_id' => $post_id,
            'content' => $content,
            'media' => $media,
            'hashtags' => $hashtags
        ]
    ]);
    exit();

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Erreur de base de données']);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?> 