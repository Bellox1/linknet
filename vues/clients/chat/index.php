<?php include 'includes/header.php'; 
    require_once __DIR__ . '/../back-office/config/database.php';
// Check if user is logged in
if (!isset($_SESSION["user"])) {
    header("Location: auth/login.php");
    exit();
}

// Get logged-in user ID
$user_id = $_SESSION["user"];

// Fetch all posts from all users
$stmt = $conn->prepare("
    SELECT posts.*, users.username, users.profile_picture 
    FROM posts
    JOIN users ON posts.user_id = users.id
    ORDER BY posts.created_at DESC
");
$stmt->execute();
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
// Fetch featured posts with post details
$stmt = $conn->prepare("SELECT 
                                fp.id AS featured_id, 
                                p.id AS post_id, 
                                p.user_id, 
                                u.username,  -- Fetch username from users table
                                p.content, 
                                p.media, 
                                p.created_at 
                            FROM featured_posts fp
                            JOIN posts p ON fp.post_id = p.id
                            JOIN users u ON p.user_id = u.id  -- Join users table
                            ORDER BY fp.created_at DESC 
                            LIMIT 5");
$stmt->execute();

    // Fetch all results properly
$featured_posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
// Fetch top 5 trending hashtags
$stmt = $conn->prepare("SELECT tag, COUNT(*) AS count FROM hashtags GROUP BY tag ORDER BY count DESC LIMIT 5");
$stmt->execute();
$trending_tags = $stmt->fetchAll();



?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home - Social Media</title>
    <link rel="stylesheet" href="styles.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Swiper.js CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
</head>
<body>
    <div class="main-container">
        <!-- Left: Swiper Slider -->
        <div class="slider-container">
            <div class="swiper mySwiper">
                <div class="swiper-wrapper">
                    <?php foreach ($featured_posts as $featured): ?>
                        <div class="swiper-slide">
                            <img src="/vues/back-office/uploads/<?= $featured['media'] ?>" alt="Image en vedette">
                            <div class="slide-caption">
                                <h3><?= htmlspecialchars($featured['username']) ?></h3>
                                <p><?= htmlspecialchars($featured['content']) ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <!-- Swiper Pagination & Navigation -->
                <div class="swiper-pagination"></div>
                <div class="swiper-button-next"></div>
                <div class="swiper-button-prev"></div>
            </div>
            
            <div class="trending-tags">
                <h3><i class="fas fa-fire"></i> Hashtags Tendances</h3>
                <ul>
                    <?php foreach ($trending_tags as $tag): ?>
                        <li><a href="search.php?tag=<?= urlencode($tag['tag']) ?>">#<?= htmlspecialchars($tag['tag']) ?></a> (<?= $tag['count'] ?>)</li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>

        <!-- Right: Latest Posts -->
        <div class="user-feed">
            <h2><i class="fas fa-stream"></i> Derniers Posts</h2>

            <?php foreach ($posts as $post): ?>
                <div class="post">
                    <div class="post-header">
                        <img src="/vues/back-office/uploads/<?= $post['profile_picture'] ?>" alt="Photo de profil" class="profile-pic">
                        <strong><?= htmlspecialchars($post['username']) ?></strong>
                        <small><i class="far fa-clock"></i> <?= date("j M Y, H:i", strtotime($post['created_at'])) ?></small>
                    </div>
                    
                    <p><?= nl2br(htmlspecialchars($post['content'])) ?></p>

                    <?php if ($post['media']): ?>
                        <div class="post-media">
                            <?php
                            $mediaPath = $post['media'];
                            // On cherche la position de 'Posts/' dans le chemin
                            $pos = strpos($mediaPath, 'Posts/');
                            if ($pos !== false) {
                                $mediaPath = substr($mediaPath, $pos); // On garde à partir de 'Posts/'
                            }
                            ?>
                            <?php if (preg_match('/\.(jpg|jpeg|png|gif)$/i', $post['media'])): ?>
                                <img src="/vues/back-office/uploads/<?= htmlspecialchars($mediaPath) ?>" alt="Image du post">
                            <?php elseif (preg_match('/\.(mp4|mov|avi)$/i', $post['media'])): ?>
                                <video controls>
                                    <source src="/vues/back-office/uploads/<?= htmlspecialchars($mediaPath) ?>" type="video/mp4">
                                    Votre navigateur ne supporte pas la lecture de vidéos.
                                </video>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <div class="post-actions">
                        <?php
                            $stmt = $conn->prepare("SELECT COUNT(*) FROM likes WHERE post_id = ?");
                            $stmt->execute([$post['id']]);
                            $like_count = $stmt->fetchColumn();
                        ?>
                        <a href="posts/like.php?post_id=<?= $post['id'] ?>" class="like-btn">
                            <i class="fas fa-thumbs-up"></i>
                            <span><?php echo $like_count;?></span>
                        </a>
                        <?php
                            $stmt = $conn->prepare("SELECT COUNT(*) FROM comments WHERE post_id = ?");
                            $stmt->execute([$post['id']]);
                            $comment_c = $stmt->fetchColumn();
                        ?>
                        <a href="#" class="comment-btn" data-post-id="<?= $post['id']; ?>">
                            <i class="fas fa-comment"></i>
                            <span><?php echo $comment_c; ?></span>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Bouton flottant pour créer un post -->
    <a href="posts/create_post.php" class="floating-btn" title="Créer un nouveau post">
        <i class="fas fa-plus"></i>
    </a>

    <!-- Modal pour les commentaires -->
    <div id="commentModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2><i class="fas fa-comment"></i> Ajouter un commentaire</h2>
            <form id="commentForm">
                <input type="hidden" name="post_id" id="post_id">
                <textarea name="comment_text" id="comment_text" placeholder="Écrivez votre commentaire..." required></textarea>
                <button type="submit">
                    <i class="fas fa-paper-plane"></i> Publier le commentaire
                </button>
            </form>
        </div>
    </div>

    <!-- Swiper.js JS -->
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

    <script>
    document.addEventListener("DOMContentLoaded", function () {
        // Initialisation du Swiper
        var swiper = new Swiper(".mySwiper", {
            loop: true,
            autoplay: {
                delay: 3000,
                disableOnInteraction: false,
            },
            pagination: {
                el: ".swiper-pagination",
                clickable: true,
            },
            navigation: {
                nextEl: ".swiper-button-next",
                prevEl: ".swiper-button-prev",
            },
        });

        // Gestion du modal de commentaire
        const modal = document.getElementById("commentModal");
        const closeBtn = document.querySelector(".close");
        const commentBtns = document.querySelectorAll(".comment-btn");
        const commentForm = document.getElementById("commentForm");
        const postIdInput = document.getElementById("post_id");

        commentBtns.forEach(btn => {
            btn.addEventListener("click", function (event) {
                event.preventDefault();
                let postId = this.getAttribute("data-post-id");
                postIdInput.value = postId;
                modal.style.display = "flex";
            });
        });

        closeBtn.addEventListener("click", function () {
            modal.style.display = "none";
        });

        window.addEventListener("click", function (event) {
            if (event.target == modal) {
                modal.style.display = "none";
            }
        });

        commentForm.addEventListener("submit", function (event) {
            event.preventDefault();
            let formData = new FormData(commentForm);

            fetch("posts/comment.php", {
                method: "POST",
                body: formData
            })
            .then(response => response.text())
            .then(data => {
                alert("Commentaire ajouté avec succès !");
                modal.style.display = "none";
                commentForm.reset();
            })
            .catch(error => console.error("Erreur:", error));
        });
    });
    </script>
</body>
</html>



