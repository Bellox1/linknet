-- Désactiver les vérifications de clés étrangères pour vider les tables en toute sécurité
SET FOREIGN_KEY_CHECKS = 0;

-- Vider toutes les tables existantes (si elles existent)
TRUNCATE TABLE `comments`;
TRUNCATE TABLE `featured_posts`;
TRUNCATE TABLE `followers`;
TRUNCATE TABLE `friends`;
TRUNCATE TABLE `friend_requests`;
TRUNCATE TABLE `hashtags`;
TRUNCATE TABLE `likes`;
TRUNCATE TABLE `messages`;
TRUNCATE TABLE `notifications`;
TRUNCATE TABLE `reports`;
TRUNCATE TABLE `posts`;
TRUNCATE TABLE `users`;
TRUNCATE TABLE `admins`;

-- Supprimer les tables existantes avant de les recréer pour s'assurer de la bonne collation
DROP TABLE IF EXISTS `comments`;
DROP TABLE IF EXISTS `featured_posts`;
DROP TABLE IF EXISTS `followers`;
DROP TABLE IF EXISTS `friends`;
DROP TABLE IF EXISTS `friend_requests`;
DROP TABLE IF EXISTS `hashtags`;
DROP TABLE IF EXISTS `likes`;
DROP TABLE IF EXISTS `messages`;
DROP TABLE IF EXISTS `notifications`;
DROP TABLE IF EXISTS `reports`;
DROP TABLE IF EXISTS `posts`;
DROP TABLE IF EXISTS `users`;
DROP TABLE IF EXISTS `admins`;

--
-- Structure de la table `admins`
--
CREATE TABLE `admins` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(50) NOT NULL,
  `reset_token` varchar(255) DEFAULT NULL,
  `reset_expires` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Structure de la table `users`
--
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `birthday` date DEFAULT NULL,
  `profile_picture` varchar(255) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Structure de la table `posts`
--
CREATE TABLE `posts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `content` text NOT NULL,
  `media` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `posts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Structure de la table `comments`
--
CREATE TABLE `comments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `post_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `comment_text` text NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `post_id` (`post_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `comments_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `comments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Structure de la table `featured_posts`
--
CREATE TABLE `featured_posts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `post_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `post_id` (`post_id`),
  CONSTRAINT `featured_posts_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Structure de la table `followers`
--
CREATE TABLE `followers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `follower_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`,`follower_id`),
  KEY `follower_id` (`follower_id`),
  CONSTRAINT `followers_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `followers_ibfk_2` FOREIGN KEY (`follower_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Structure de la table `friends`
--
CREATE TABLE `friends` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sender_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `status` enum('pending','accepted','rejected') NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `friend_pair` (`sender_id`,`receiver_id`),
  KEY `receiver_id` (`receiver_id`),
  CONSTRAINT `friends_ibfk_1` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `friends_ibfk_2` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Structure de la table `friend_requests`
--
CREATE TABLE `friend_requests` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sender_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `status` enum('pending','accepted','rejected') NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `request_pair` (`sender_id`,`receiver_id`),
  KEY `receiver_id` (`receiver_id`),
  CONSTRAINT `friend_requests_ibfk_1` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `friend_requests_ibfk_2` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Structure de la table `hashtags`
--
CREATE TABLE `hashtags` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tag` varchar(255) NOT NULL,
  `post_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `post_id` (`post_id`),
  CONSTRAINT `hashtags_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Structure de la table `likes`
--
CREATE TABLE `likes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_post_like` (`user_id`,`post_id`),
  KEY `post_id` (`post_id`),
  CONSTRAINT `likes_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `likes_ibfk_2` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Structure de la table `messages`
--
CREATE TABLE `messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sender_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `group_id` int(11) DEFAULT NULL, -- Pour les messages de groupe, si implémenté
  `message` text NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `sender_id` (`sender_id`),
  KEY `receiver_id` (`receiver_id`),
  CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `messages_ibfk_2` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Structure de la table `notifications`
--
CREATE TABLE `notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `type` varchar(50) NOT NULL, -- ex: 'like', 'comment', 'friend_request', 'follow'
  `post_id` int(11) DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `sender_id` (`sender_id`),
  KEY `post_id` (`post_id`),
  CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `notifications_ibfk_2` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `notifications_ibfk_3` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Structure de la table `reports`
--
CREATE TABLE `reports` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `reporter_id` int(11) NOT NULL,
  `reported_id` int(11) NOT NULL,
  `report_type` varchar(50) NOT NULL, -- 'user' or 'post'
  `reason` text NOT NULL,
  `status` enum('pending','reviewed') NOT NULL DEFAULT 'pending',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `reporter_id` (`reporter_id`),
  CONSTRAINT `reports_ibfk_1` FOREIGN KEY (`reporter_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Réactiver les vérifications de clés étrangères
SET FOREIGN_KEY_CHECKS = 1;

--
-- Données pour la table `admins` (50 entrées)
--
INSERT INTO `admins` (`username`, `email`, `password`, `role`, `reset_token`, `reset_expires`) VALUES
('Jean', 'jean@linknet.com', '$2y$10$D2ZCFBqsY4UK/6TF7NE3VOAHqkxgUq7cgrwvNWVHZ3SfWKaLnHSyS', 'Administrateur', NULL, NULL),
('Marie', 'marie@linknet.com', '$2y$10$D2ZCFBqsY4UK/6TF7NE3VOAHqkxgUq7cgrwvNWVHZ3SfWKaLnHSyS', 'Modérateur', NULL, NULL),
('Pierre', 'pierre@linknet.com', '$2y$10$D2ZCFBqsY4UK/6TF7NE3VOAHqkxgUq7cgrwvNWVHZ3SfWKaLnHSyS', 'Administrateur', NULL, NULL),
('Sophie', 'sophie@linknet.com', '$2y$10$D2ZCFBqsY4UK/6TF7NE3VOAHqkxgUq7cgrwvNWVHZ3SfWKaLnHSyS', 'Modérateur', NULL, NULL),
('Antoine', 'antoine@linknet.com', '$2y$10$D2ZCFBqsY4UK/6TF7NE3VOAHqkxgUq7cgrwvNWVHZ3SfWKaLnHSyS', 'Administrateur', NULL, NULL),
('Julie', 'julie@linknet.com', '$2y$10$D2ZCFBqsY4UK/6TF7NE3VOAHqkxgUq7cgrwvNWVHZ3SfWKaLnHSyS', 'Modérateur', NULL, NULL),
('Marc', 'marc@linknet.com', '$2y$10$D2ZCFBqsY4UK/6TF7NE3VOAHqkxgUq7cgrwvNWVHZ3SfWKaLnHSyS', 'Administrateur', NULL, NULL),
('Isabelle', 'isabelle@linknet.com', '$2y$10$D2ZCFBqsY4UK/6TF7NE3VOAHqkxgUq7cgrwvNWVHZ3SfWKaLnHSyS', 'Modérateur', NULL, NULL),
('Thomas', 'thomas@linknet.com', '$2y$10$D2ZCFBqsY4UK/6TF7NE3VOAHqkxgUq7cgrwvNWVHZ3SfWKaLnHSyS', 'Administrateur', NULL, NULL),
('Laura', 'laura@linknet.com', '$2y$10$D2ZCFBqsY4UK/6TF7NE3VOAHqkxgUq7cgrwvNWVHZ3SfWKaLnHSyS', 'Modérateur', NULL, NULL),
('Nicolas', 'nicolas@linknet.com', '$2y$10$D2ZCFBqsY4UK/6TF7NE3VOAHqkxgUq7cgrwvNWVHZ3SfWKaLnHSyS', 'Administrateur', NULL, NULL),
('Claire', 'claire@linknet.com', '$2y$10$D2ZCFBqsY4UK/6TF7NE3VOAHqkxgUq7cgrwvNWVHZ3SfWKaLnHSyS', 'Modérateur', NULL, NULL),
('David', 'david@linknet.com', '$2y$10$D2ZCFBqsY4UK/6TF7NE3VOAHqkxgUq7cgrwvNWVHZ3SfWKaLnHSyS', 'Administrateur', NULL, NULL),
('Émilie', 'emilie@linknet.com', '$2y$10$D2ZCFBqsY4UK/6TF7NE3VOAHqkxgUq7cgrwvNWVHZ3SfWKaLnHSyS', 'Modérateur', NULL, NULL),
('Sébastien', 'sebastien@linknet.com', '$2y$10$D2ZCFBqsY4UK/6TF7NE3VOAHqkxgUq7cgrwvNWVHZ3SfWKaLnHSyS', 'Administrateur', NULL, NULL),
('Camille', 'camille@linknet.com', '$2y$10$D2ZCFBqsY4UK/6TF7NE3VOAHqkxgUq7cgrwvNWVHZ3SfWKaLnHSyS', 'Modérateur', NULL, NULL),
('Vincent', 'vincent@linknet.com', '$2y$10$D2ZCFBqsY4UK/6TF7NE3VOAHqkxgUq7cgrwvNWVHZ3SfWKaLnHSyS', 'Administrateur', NULL, NULL),
('Manon', 'manon@linknet.com', '$2y$10$D2ZCFBqsY4UK/6TF7NE3VOAHqkxgUq7cgrwvNWVHZ3SfWKaLnHSyS', 'Modérateur', NULL, NULL),
('Alexandre', 'alexandre@linknet.com', '$2y$10$D2ZCFBqsY4UK/6TF7NE3VOAHqkxgUq7cgrwvNWVHZ3SfWKaLnHSyS', 'Administrateur', NULL, NULL),
('Léa', 'lea@linknet.com', '$2y$10$D2ZCFBqsY4UK/6TF7NE3VOAHqkxgUq7cgrwvNWVHZ3SfWKaLnHSyS', 'Modérateur', NULL, NULL),
('Romain', 'romain@linknet.com', '$2y$10$D2ZCFBqsY4UK/6TF7NE3VOAHqkxgUq7cgrwvNWVHZ3SfWKaLnHSyS', 'Administrateur', NULL, NULL),
('Chloé', 'chloe@linknet.com', '$2y$10$D2ZCFBqsY4UK/6TF7NE3VOAHqkxgUq7cgrwvNWVHZ3SfWKaLnHSyS', 'Modérateur', NULL, NULL),
('Maxime', 'maxime@linknet.com', '$2y$10$D2ZCFBqsY4UK/6TF7NE3VOAHqkxgUq7cgrwvNWVHZ3SfWKaLnHSyS', 'Administrateur', NULL, NULL),
('Emma', 'emma@linknet.com', '$2y$10$D2ZCFBqsY4UK/6TF7NE3VOAHqkxgUq7cgrwvNWVHZ3SfWKaLnHSyS', 'Modérateur', NULL, NULL),
('Florian', 'florian@linknet.com', '$2y$10$D2ZCFBqsY4UK/6TF7NE3VOAHqkxgUq7cgrwvNWVHZ3SfWKaLnHSyS', 'Administrateur', NULL, NULL),
('Alice', 'alice@linknet.com', '$2y$10$D2ZCFBqsY4UK/6TF7NE3VOAHqkxgUq7cgrwvNWVHZ3SfWKaLnHSyS', 'Modérateur', NULL, NULL),
('Benjamin', 'benjamin@linknet.com', '$2y$10$D2ZCFBqsY4UK/6TF7NE3VOAHqkxgUq7cgrwvNWVHZ3SfWKaLnHSyS', 'Administrateur', NULL, NULL),
('Sarah', 'sarah@linknet.com', '$2y$10$D2ZCFBqsY4UK/6TF7NE3VOAHqkxgUq7cgrwvNWVHZ3SfWKaLnHSyS', 'Modérateur', NULL, NULL),
('Clément', 'clement@linknet.com', '$2y$10$D2ZCFBqsY4UK/6TF7NE3VOAHqkxgUq7cgrwvNWVHZ3SfWKaLnHSyS', 'Administrateur', NULL, NULL),
('Pauline', 'pauline@linknet.com', '$2y$10$D2ZCFBqsY4UK/6TF7NE3VOAHqkxgUq7cgrwvNWVHZ3SfWKaLnHSyS', 'Modérateur', NULL, NULL),
('Guillaume', 'guillaume@linknet.com', '$2y$10$D2ZCFBqsY4UK/6TF7NE3VOAHqkxgUq7cgrwvNWVHZ3SfWKaLnHSyS', 'Administrateur', NULL, NULL),
('Clara', 'clara@linknet.com', '$2y$10$D2ZCFBqsY4UK/6TF7NE3VOAHqkxgUq7cgrwvNWVHZ3SfWKaLnHSyS', 'Modérateur', NULL, NULL),
('Julien', 'julien@linknet.com', '$2y$10$D2ZCFBqsY4UK/6TF7NE3VOAHqkxgUq7cgrwvNWVHZ3SfWKaLnHSyS', 'Administrateur', NULL, NULL),
('Océane', 'oceane@linknet.com', '$2y$10$D2ZCFBqsY4UK/6TF7NE3VOAHqkxgUq7cgrwvNWVHZ3SfWKaLnHSyS', 'Modérateur', NULL, NULL),
('Lucas', 'lucas@linknet.com', '$2y$10$D2ZCFBqsY4UK/6TF7NE3VOAHqkxgUq7cgrwvNWVHZ3SfWKaLnHSyS', 'Administrateur', NULL, NULL),
('Eva', 'eva@linknet.com', '$2y$10$D2ZCFBqsY4UK/6TF7NE3VOAHqkxgUq7cgrwvNWVHZ3SfWKaLnHSyS', 'Modérateur', NULL, NULL),
('Arthur', 'arthur@linknet.com', '$2y$10$D2ZCFBqsY4UK/6TF7NE3VOAHqkxgUq7cgrwvNWVHZ3SfWKaLnHSyS', 'Administrateur', NULL, NULL),
('Louise', 'louise@linknet.com', '$2y$10$D2ZCFBqsY4UK/6TF7NE3VOAHqkxgUq7cgrwvNWVHZ3SfWKaLnHSyS', 'Modérateur', NULL, NULL),
('Hugo', 'hugo@linknet.com', '$2y$10$D2ZCFBqsY4UK/6TF7NE3VOAHqkxgUq7cgrwvNWVHZ3SfWKaLnHSyS', 'Administrateur', NULL, NULL),
('Zoé', 'zoe@linknet.com', '$2y$10$D2ZCFBqsY4UK/6TF7NE3VOAHqkxgUq7cgrwvNWVHZ3SfWKaLnHSyS', 'Modérateur', NULL, NULL),
('Gabriel', 'gabriel@linknet.com', '$2y$10$D2ZCFBqsY4UK/6TF7NE3VOAHqkxgUq7cgrwvNWVHZ3SfWKaLnHSyS', 'Administrateur', NULL, NULL),
('Inès', 'ines@linknet.com', '$2y$10$D2ZCFBqsY4UK/6TF7NE3VOAHqkxgUq7cgrwvNWVHZ3SfWKaLnHSyS', 'Modérateur', NULL, NULL),
('Nathan', 'nathan@linknet.com', '$2y$10$D2ZCFBqsY4UK/6TF7NE3VOAHqkxgUq7cgrwvNWVHZ3SfWKaLnHSyS', 'Administrateur', NULL, NULL),
('Jade', 'jade@linknet.com', '$2y$10$D2ZCFBqsY4UK/6TF7NE3VOAHqkxgUq7cgrwvNWVHZ3SfWKaLnHSyS', 'Modérateur', NULL, NULL),
('Raphaël', 'raphaël@linknet.com', '$2y$10$D2ZCFBqsY4UK/6TF7NE3VOAHqkxgUq7cgrwvNWVHZ3SfWKaLnHSyS', 'Administrateur', NULL, NULL),
('Maëlys', 'maëlys@linknet.com', '$2y$10$D2ZCFBqsY4UK/6TF7NE3VOAHqkxgUq7cgrwvNWVHZ3SfWKaLnHSyS', 'Modérateur', NULL, NULL),
('Théo', 'theo@linknet.com', '$2y$10$D2ZCFBqsY4UK/6TF7NE3VOAHqkxgUq7cgrwvNWVHZ3SfWKaLnHSyS', 'Administrateur', NULL, NULL),
('Lina', 'lina@linknet.com', '$2y$10$D2ZCFBqsY4UK/6TF7NE3VOAHqkxgUq7cgrwvNWVHZ3SfWKaLnHSyS', 'Modérateur', NULL, NULL),
('Valentin', 'valentin@linknet.com', '$2y$10$D2ZCFBqsY4UK/6TF7NE3VOAHqkxgUq7cgrwvNWVHZ3SfWKaLnHSyS', 'Administrateur', NULL, NULL),
('Ambre', 'ambre@linknet.com', '$2y$10$D2ZCFBqsY4UK/6TF7NE3VOAHqkxgUq7cgrwvNWVHZ3SfWKaLnHSyS', 'Modérateur', NULL, NULL);

--
-- Données pour la table `users` (50 entrées)
--
INSERT INTO `users` (`username`, `email`, `password`, `birthday`, `profile_picture`, `bio`, `created_at`) VALUES
('Paul', 'paul@linknet.com', '$2y$10$D2ZCFBqsY4UK/6TF7NE3VOAHqkxgUq7cgrwvNWVHZ3SfWKaLnHSyS', '1995-08-06', 'https://picsum.photos/seed/Paul0/200/300', 'Passionné(e) de voyages et de découvertes. Toujours à la recherche de nouvelles aventures ! 🌍✈️', '2024-09-30 17:44:09'),
('Lucie', 'lucie@linknet.com', '$2y$10$D2ZCFBqsY4UK/6TF7NE3VOAHqkxgUq7cgrwvNWVHZ3SfWKaLnHSyS', '1991-02-11', 'https://picsum.photos/seed/Lucie1/200/300', 'Amateur(e) de cuisine du monde. Un plat, une histoire. 🧑‍🍳🌍', '2025-02-01 19:06:36'),
('Kevin', 'kevin@linknet.com', '$2y$10$D2ZCFBqsY4UK/6TF7NE3VOAHqkxgUq7cgrwvNWVHZ3SfWKaLnHSyS', '1986-11-15', 'https://picsum.photos/seed/Kevin2/200/300', 'Étudiant(e) curieux(se), toujours en quête de savoir. 📚🔬', '2024-11-24 12:42:37'),
('Marion', 'marion@linknet.com', '$2y$10$D2ZCFBqsY4UK/6TF7NE3VOAHqkxgUq7cgrwvNWVHZ3SfWKaLnHSyS', '1975-12-27', 'https://picsum.photos/seed/Marion3/200/300', 'Collectionneur(se) de vinyles et nostalgique des bonnes vieilles choses. 📀📻', '2024-10-16 13:35:06'),
('Simon', 'simon@linknet.com', '$2y$10$D2ZCFBqsY4UK/6TF7NE3VOAHqkxgUq7cgrwvNWVHZ3SfWKaLnHSyS', '1986-02-28', 'https://picsum.photos/seed/Simon4/200/300', 'Amoureux(se) de la nature et des animaux. Mon jardin est mon sanctuaire. 🌿🐾', '2025-05-02 11:50:14'),
('Anna', 'anna@linknet.com', '$2y$10$D2ZCFBqsY4UK/6TF7NE3VOAHqkxgUq7cgrwvNWVHZ3SfWKaLnHSyS', '2000-04-17', 'https://picsum.photos/seed/Anna5/200/300', 'Entrepreneur(e) et visionnaire. Je construis mes rêves. 🚀🌟', '2025-03-03 05:58:34'),
('Louis', 'louis@linknet.com', '$2y$10$D2ZCFBqsY4UK/6TF7NE3VOAHqkxgUq7cgrwvNWVHZ3SfWKaLnHSyS', '1978-08-18', 'https://picsum.photos/seed/Louis6/200/300', 'Amoureux(se) de la nature et des animaux. Mon jardin est mon sanctuaire. 🌿🐾', '2025-02-26 22:00:03'),
('Élodie', 'elodie@linknet.com', '$2y$10$D2ZCFBqsY4UK/6TF7NE3VOAHqkxgUq7cgrwvNWVHZ3SfWKaLnHSyS', '1987-09-01', 'https://picsum.photos/seed/Élodie7/200/300', 'Bénévole engagé(e) pour les causes qui me tiennent à cœur. 💖🤝', '2024-07-26 09:08:42'),
('Enzo', 'enzo@linknet.com', '$2y$10$D2ZCFBqsY4UK/6TF7NE3VOAHqkxgUq7cgrwvNWVHZ3SfWKaLnHSyS', '1994-06-03', 'https://picsum.photos/seed/Enzo8/200/300', 'Amateur(e) de cuisine du monde. Un plat, une histoire. 🧑‍🍳🌍', '2025-06-12 11:49:21'),
('Fanny', 'fanny@linknet.com', '$2y$10$D2ZCFBqsY4UK/6TF7NE3VOAHqkxgUq7cgrwvNWVHZ3SfWKaLnHSyS', '1978-02-19', 'https://picsum.photos/seed/Fanny9/200/300', 'Aventurier(ère) urbain(e), jexplore ma ville sous toutes ses coutures. 🏙️🚶', '2025-01-29 06:07:33'),
('Tom', 'tom@linknet.com', '$2y$10$D2ZCFBqsY4UK/6TF7NE3VOAHqkxgUq7cgrwvNWVHZ3SfWKaLnHSyS', '1974-12-02', 'https://picsum.photos/seed/Tom10/200/300', 'Étudiant(e) curieux(se), toujours en quête de savoir. 📚🔬', '2025-02-21 17:17:47'),
('Eva', 'eva@linknet.com', '$2y$10$D2ZCFBqsY4UK/6TF7NE3VOAHqkxgUq7cgrwvNWVHZ3SfWKaLnHSyS', '1999-11-24', 'https://picsum.photos/seed/Eva11/200/300', 'Passionné(e) de voyages et de découvertes. Toujours à la recherche de nouvelles aventures ! 🌍✈️', '2025-06-04 01:33:49'),
('Léo', 'leo@linknet.com', '$2y$10$D2ZCFBqsY4UK/6TF7NE3VOAHqkxgUq7cgrwvNWVHZ3SfWKaLnHSyS', '1988-11-14', 'https://picsum.photos/seed/Léo12/200/300', 'Collectionneur(se) de vinyles et nostalgique des bonnes vieilles choses. 📀📻', '2024-09-01 15:08:48'),
('Jade', 'jade@linknet.com', '$2y$10$D2ZCFBqsY4UK/6TF7NE3VOAHqkxgUq7cgrwvNWVHZ3SfWKaLnHSyS', '1976-08-11', 'https://picsum.photos/seed/Jade13/200/300', 'Bénévole engagé(e) pour les causes qui me tiennent à cœur. 💖🤝', '2025-04-25 11:13:13'),
('Adam', 'adam@linknet.com', '$2y$10$D2ZCFBqsY4UK/6TF7NE3VOAHqkxgUq7cgrwvNWVHZ3SfWKaLnHSyS', '1971-02-23', 'https://picsum.photos/seed/Adam14/200/300', 'Amoureux(se) de la nature et des animaux. Mon jardin est mon sanctuaire. 🌿🐾', '2024-10-07 04:35:08'),
('Lola', 'lola@linknet.com', '$2y$10$D2ZCFBqsY4UK/6TF7NE3VOAHqkxgUq7cgrwvNWVHZ3SfWKaLnHSyS', '1997-04-04', 'https://picsum.photos/seed/Lola15/200/300', 'Aventurier(ère) urbain(e), jexplore ma ville sous toutes ses coutures. 🏙️🚶', '2024-08-25 05:55:17'),
('Noah', 'noah@linknet.com', '$2y$10$D2ZCFBqsY4UK/6TF7NE3VOAHqkxgUq7cgrwvNWVHZ3SfWKaLnHSyS', '1977-07-03', 'https://picsum.photos/seed/Noah16/200/300', 'Aventurier(ère) urbain(e), jexplore ma ville sous toutes ses coutures. 🏙️🚶', '2025-06-04 13:45:58'),
('Manon', 'manon@linknet.com', '$2y$10$D2ZCFBqsY4UK/6TF7NE3VOAHqkxgUq7cgrwvNWVHZ3SfWKaLnHSyS', '1972-10-04', 'https://picsum.photos/seed/Manon17/200/300', 'Passionné(e) de jeux vidéo et de culture geek. 🎮👾', '2025-02-20 07:48:11'),
('Sacha', 'sacha@linknet.com', '$2y$10$D2ZCFBqsY4UK/6TF7NE3VOAHqkxgUq7cgrwvNWVHZ3SfWKaLnHSyS', '1993-01-22', 'https://picsum.photos/seed/Sacha18/200/300', 'Coach sportif et adepte du bien-être. Le sport, cest la vie ! 💪🏃‍♀️', '2025-03-13 10:57:45'),
('Romane', 'romane@linknet.com', '$2y$10$D2ZCFBqsY4UK/6TF7NE3VOAHqkxgUq7cgrwvNWVHZ3SfWKaLnHSyS', '1970-12-13', 'https://picsum.photos/seed/Romane19/200/300', 'Entrepreneur(e) et visionnaire. Je construis mes rêves. 🚀🌟', '2024-11-15 22:33:05'),
('Jules', 'jules@linknet.com', '$2y$10$D2ZCFBqsY4UK/6TF7NE3VOAHqkxgUq7cgrwvNWVHZ3SfWKaLnHSyS', '1999-08-22', 'https://picsum.photos/seed/Jules20/200/300', 'Collectionneur(se) de vinyles et nostalgique des bonnes vieilles choses. 📀', '2025-05-15 06:03:30'),
('Alice', 'alice@linknet.com', '$2y$10$D2ZCFBqsY4UK/6TF7NE3VOAHqkxgUq7cgrwvNWVHZ3SfWKaLnHSyS', '1985-06-17', 'https://picsum.photos/seed/Alice21/200/300', 'Bénévole engagé(e) pour les causes qui me tiennent à cœur. 💖🤝', '2025-03-31 10:16:35'),
('Samy', 'samy@linknet.com', '$2y$10$D2ZCFBqsY4UK/6TF7NE3VOAHqkxgUq7cgrwvNWVHZ3SfWKaLnHSyS', '1974-09-03', 'https://picsum.photos/seed/Samy22/200/300', 'Amateur(e) de cuisine du monde. Un plat, une histoire. 🧑‍🍳🌍', '2025-01-13 05:52:22'),
('Clara', 'clara@linknet.com', '$2y$10$D2ZCFBqsY4UK/6TF7NE3VOAHqkxgUq7cgrwvNWVHZ3SfWKaLnHSyS', '1992-12-23', 'https://picsum.photos/seed/Clara23/200/300', 'Adepte du zéro déchet et de la vie minimaliste. Moins, cest plus. ♻️✨', '2025-05-30 08:03:38'),
('Yanis', 'yanis@linknet.com', '$2y$10$D2ZCFBqsY4UK/6TF7NE3VOAHqkxgUq7cgrwvNWVHZ3SfWKaLnHSyS', '1994-08-11', 'https://picsum.photos/seed/Yanis24/200/300', 'Développeur(se) web et fan de tech. Jaime créer et innover. 💻💡', '2025-07-02 14:35:32'),
('Léna', 'lena@linknet.com', '$2y$10$D2ZCFBqsY4UK/6TF7NE3VOAHqkxgUq7cgrwvNWVHZ3SfWKaLnHSyS', '1994-12-04', 'https://picsum.photos/seed/Léna25/200/300', 'Amateur(e) de cuisine du monde. Un plat, une histoire. 🧑‍🍳🌍', '2024-10-30 18:40:24'),
('Axel', 'axel@linknet.com', '$2y$10$D2ZCFBqsY4UK/6TF7NE3VOAHqkxgUq7cgrwvNWVHZ3SfWKaLnHSyS', '1990-03-14', 'https://picsum.photos/seed/Axel26/200/300', 'Cinéphile et sériephile. Mon canapé, mon cinéma. 🎬🍿', '2024-12-01 14:34:56'),
('Inès', 'ines@linknet.com', '$2y$10$D2ZCFBqsY4UK/6TF7NE3VOAHqkxgUq7cgrwvNWVHZ3SfWKaLnHSyS', '1977-10-11', 'https://picsum.photos/seed/Inès27/200/300', 'Passionné(e) de voyages et de découvertes. Toujours à la recherche de nouvelles aventures ! 🌍✈️', '2024-10-08 19:24:41'),
('Mathis', 'mathis@linknet.com', '$2y$10$D2ZCFBqsY4UK/6TF7NE3VOAHqkxgUq7cgrwvNWVHZ3SfWKaLnHSyS', '1997-01-19', 'https://picsum.photos/seed/Mathis28/200/300', 'Bénévole engagé(e) pour les causes qui me tiennent à cœur. 💖🤝', '2025-04-13 12:24:52'),
('Maëlys', 'maëlys@linknet.com', '$2y$10$D2ZCFBqsY4UK/6TF7NE3VOAHqkxgUq7cgrwvNWVHZ3SfWKaLnHSyS', '1981-12-10', 'https://picsum.photos/seed/Maëlys29/200/300', 'Développeur(se) web et fan de tech. Jaime créer et innover. 💻💡', '2024-08-07 14:50:35'),
('Rayan', 'rayan@linknet.com', '$2y$10$D2ZCFBqsY4UK/6TF7NE3VOAHqkxgUq7cgrwvNWVHZ3SfWKaLnHSyS', '1983-02-10', 'https://picsum.photos/seed/Rayan30/200/300', 'Coach sportif et adepte du bien-être. Le sport, cest la vie ! 💪🏃‍♀️', '2024-11-20 20:09:28'),
('Chloé', 'chloe@linknet.com', '$2y$10$D2ZCFBqsY4UK/6TF7NE3VOAHqkxgUq7cgrwvNWVHZ3SfWKaLnHSyS', '1972-07-16', 'https://picsum.photos/seed/Chloé31/200/300', 'Amateur(e) de cuisine du monde. Un plat, une histoire. 🧑‍🍳🌍', '2025-01-24 08:50:08'),
('Ilyes', 'ilyes@linknet.com', '$2y$10$D2ZCFBqsY4UK/6TF7NE3VOAHqkxgUq7cgrwvNWVHZ3SfWKaLnHSyS', '1971-01-06', 'https://picsum.photos/seed/Ilyes32/200/300', 'Lecteur(trice) assidu(e) et écrivain(e) en herbe. Les mots sont ma force. ✍️📖', '2024-12-21 08:48:17'),
('Emma', 'emma@linknet.com', '$2y$10$D2ZCFBqsY4UK/6TF7NE3VOAHqkxgUq7cgrwvNWVHZ3SfWKaLnHSyS', '1991-06-20', 'https://picsum.photos/seed/Emma33/200/300', 'Foodie invétéré(e), toujours prêt(e) à tester de nouvelles saveurs. 🍜😋', '2025-06-08 15:24:43'),
('Nolan', 'nolan@linknet.com', '$2y$10$D2ZCFBqsY4UK/6TF7NE3VOAHqkxgUq7cgrwvNWVHZ3SfWKaLnHSyS', '1997-12-06', 'https://picsum.photos/seed/Nolan34/200/300', 'Cycliste passionné(e), la route est mon terrain de jeu. 🚴‍♀️💨', '2025-03-04 08:13:32'),
('Zoé', 'zoe@linknet.com', '$2y$10$D2ZCFBqsY4UK/6TF7NE3VOAHqkxgUq7cgrwvNWVHZ3SfWKaLnHSyS', '1972-07-25', 'https://picsum.photos/seed/Zoé35/200/300', 'Adepte du zéro déchet et de la vie minimaliste. Moins, cest plus. ♻️✨', '2025-01-05 00:26:33'),
('Tiago', 'tiago@linknet.com', '$2y$10$D2ZCFBqsY4UK/6TF7NE3VOAHqkxgUq7cgrwvNWVHZ3SfWKaLnHSyS', '1988-05-07', 'https://picsum.photos/seed/Tiago36/200/300', 'Cinéphile et sériephile. Mon canapé, mon cinéma. 🎬🍿', '2024-10-31 10:16:43'),
('Ambre', 'ambre@linknet.com', '$2y$10$D2ZCFBqsY4UK/6TF7NE3VOAHqkxgUq7cgrwvNWVHZ3SfWKaLnHSyS', '1975-03-09', 'https://picsum.photos/seed/Ambre37/200/300', 'Randonneur(se) et amoureux(se) des grands espaces. La montagne mappelle. ⛰️🌲', '2025-07-16 02:59:11'),
('Milan', 'milan@linknet.com', '$2y$10$D2ZCFBqsY4UK/6TF7NE3VOAHqkxgUq7cgrwvNWVHZ3SfWKaLnHSyS', '1986-09-07', 'https://picsum.photos/seed/Milan38/200/300', 'Lecteur(trice) assidu(e) et écrivain(e) en herbe. Les mots sont ma force. ✍️📖', '2024-09-27 13:32:45'),
('Lina', 'lina@linknet.com', '$2y$10$D2ZCFBqsY4UK/6TF7NE3VOAHqkxgUq7cgrwvNWVHZ3SfWKaLnHSyS', '1972-10-09', 'https://picsum.photos/seed/Lina39/200/300', 'Passionné(e) de jeux vidéo et de culture geek. 🎮👾', '2025-04-29 01:45:06'),
('Robin', 'robin@linknet.com', '$2y$10$D2ZCFBqsY4UK/6TF7NE3VOAHqkxgUq7cgrwvNWVHZ3SfWKaLnHSyS', '1998-02-13', 'https://picsum.photos/seed/Robin40/200/300', 'Randonneur(se) et amoureux(se) des grands espaces. La montagne mappelle. ⛰️🌲', '2025-02-26 10:34:57'),
('Louise', 'louise@linknet.com', '$2y$10$D2ZCFBqsY4UK/6TF7NE3VOAHqkxgUq7cgrwvNWVHZ3SfWKaLnHSyS', '1986-06-11', 'https://picsum.photos/seed/Louise41/200/300', 'Collectionneur(se) de vinyles et nostalgique des bonnes vieilles choses. 📀📻', '2025-04-18 07:37:37'),
('Enzo2', 'enzo2@linknet.com', '$2y$10$D2ZCFBqsY4UK/6TF7NE3VOAHqkxgUq7cgrwvNWVHZ3SfWKaLnHSyS', '1992-09-07', 'https://picsum.photos/seed/Enzo42/200/300', 'Musicien(ne) et mélomane. La musique adoucit les mœurs. 🎶🎧', '2025-06-14 03:21:18'),
('Jade2', 'jade2@linknet.com', '$2y$10$D2ZCFBqsY4UK/6TF7NE3VOAHqkxgUq7cgrwvNWVHZ3SfWKaLnHSyS', '1998-03-21', 'https://picsum.photos/seed/Jade43/200/300', 'Lecteur(trice) assidu(e) et écrivain(e) en herbe. Les mots sont ma force. ✍️📖', '2024-12-20 10:36:36'),
('Léo2', 'leo2@linknet.com', '$2y$10$D2ZCFBqsY4UK/6TF7NE3VOAHqkxgUq7cgrwvNWVHZ3SfWKaLnHSyS', '1995-01-07', 'https://picsum.photos/seed/Léo44/200/300', 'Bénévole engagé(e) pour les causes qui me tiennent à cœur. 💖🤝', '2024-07-30 14:01:32'),
('Manon2', 'manon2@linknet.com', '$2y$10$D2ZCFBqsY4UK/6TF7NE3VOAHqkxgUq7cgrwvNWVHZ3SfWKaLnHSyS', '1976-12-25', 'https://picsum.photos/seed/Manon45/200/300', 'Coach sportif et adepte du bien-être. Le sport, cest la vie ! 💪🏃‍♀️', '2024-11-03 08:53:56'),
('Adam2', 'adam2@linknet.com', '$2y$10$D2ZCFBqsY4UK/6TF7NE3VOAHqkxgUq7cgrwvNWVHZ3SfWKaLnHSyS', '1994-02-10', 'https://picsum.photos/seed/Adam46/200/300', 'Cinéphile et sériephile. Mon canapé, mon cinéma. 🎬🍿', '2025-03-24 17:19:33'),
('Romane2', 'romane2@linknet.com', '$2y$10$D2ZCFBqsY4UK/6TF7NE3VOAHqkxgUq7cgrwvNWVHZ3SfWKaLnHSyS', '1978-02-02', 'https://picsum.photos/seed/Romane47/200/300', 'Étudiant(e) curieux(se), toujours en quête de savoir. 📚🔬', '2024-10-13 01:21:37'),
('Sacha2', 'sacha2@linknet.com', '$2y$10$D2ZCFBqsY4UK/6TF7NE3VOAHqkxgUq7cgrwvNWVHZ3SfWKaLnHSyS', '1974-11-06', 'https://picsum.photos/seed/Sacha48/200/300', 'Artiste dans lâme, je mexprime à travers mes créations. 🎨✨', '2025-04-01 06:30:54'),
('Alice2', 'alice2@linknet.com', '$2y$10$D2ZCFBqsY4UK/6TF7NE3VOAHqkxgUq7cgrwvNWVHZ3SfWKaLnHSyS', '1978-07-13', 'https://picsum.photos/seed/Alice49/200/300', 'Aventurier(ère) urbain(e), jexplore ma ville sous toutes ses coutures. 🏙️🚶', '2025-06-30 19:48:12');

--
-- Données pour la table `posts` (50 entrées)
--
INSERT INTO `posts` (`user_id`, `content`, `media`, `created_at`) VALUES
(7, 'Défi du jour : apprendre quelque chose de nouveau. Qu''avez-vous appris récemment ? 🤔💡 #Apprentissage #Curiosité', 'https://picsum.photos/seed/post1648/600/400', '2024-10-26 22:24:38'),
(45, 'Admirer les étoiles, toujours aussi magique. ✨🌌 #Astronomie #NuitÉtoilée', 'https://picsum.photos/seed/post2151/600/400', '2025-06-22 11:08:31'),
(15, 'Nouveau projet en cours ! Tellement excité(e) de partager ça bientôt. Restez connectés ! ✨ #Innovation #WorkInProgress', 'https://picsum.photos/seed/post328/600/400', '2024-11-15 10:11:11'),
(44, 'Un peu de musique douce pour se détendre. Que du bonheur. 🎶😌 #Relaxation #MusicTherapy', 'https://picsum.photos/seed/post4108/600/400', '2025-01-15 17:49:30'),
(18, 'La pluie ne m''arrête pas ! Toujours un bon moment pour une balade. ☔🚶‍♀️ #RainyDay #PositiveVibes', 'https://picsum.photos/seed/post540/600/400', '2025-02-18 02:22:09'),
(50, 'Nouvel article sur mon blog ! Lien en bio. N''hésitez pas à jeter un œil ! ✍️🌐 #Blog #Écriture', 'https://picsum.photos/seed/post665/600/400', '2025-03-09 17:19:02'),
(23, 'Le café du matin, mon rituel sacré. Comment aimez-vous le vôtre ? ☕🌞 #CoffeeLover #MorningVibes', 'https://picsum.photos/seed/post7463/600/400', '2024-09-04 20:32:53'),
(41, 'Le café du matin, mon rituel sacré. Comment aimez-vous le vôtre ? ☕🌞 #CoffeeLover #MorningVibes', 'https://picsum.photos/seed/post86/600/400', '2024-11-03 16:11:30'),
(31, 'Nouvelle playlist pour mes entraînements. La motivation est au max ! 🎧🏃‍♂️ #WorkoutMusic #SportMotivation', 'https://picsum.photos/seed/post9732/600/400', '2025-06-04 17:51:59'),
(26, 'La vie est belle, profitez de chaque instant. ✨😊 #PositiveAttitude #LifeIsGood', 'https://picsum.photos/seed/post10619/600/400', '2025-06-26 04:31:36'),
(30, 'Nouvelle coiffure ! Qu''en pensez-vous ? 💇‍♀️💁‍♂️ #Haircut #NewLook', 'https://picsum.photos/seed/post1197/600/400', '2024-08-02 11:50:16'),
(5, 'Mon endroit préféré pour me ressourcer. La nature est ma thérapie. 🌲🧘‍♀️ #NatureTherapy #Peaceful', 'https://picsum.photos/seed/post12727/600/400', '2025-01-30 09:54:51'),
(26, 'Journée parfaite ! ☀️ Profiter du soleil et de la bonne compagnie. #ChillVibes #Été', 'https://picsum.photos/seed/post13246/600/400', '2025-02-21 17:05:02'),
(11, 'Un moment de calme avant la tempête. Préparer la semaine. 🤫🗓️ #Calme #Préparation', 'https://picsum.photos/seed/post14912/600/400', '2024-07-19 13:41:08'),
(6, 'Vue imprenable depuis le sommet ! Ça valait le coup de monter. 🏔️ breathtaking #Randonnée #Aventure', 'https://picsum.photos/seed/post15524/600/400', '2025-01-08 15:57:59'),
(17, 'Moment de méditation pour se recentrer. Trouvez votre paix intérieure. 🧘‍♀️🧘‍♂️ #Méditation #BienÊtre', 'https://picsum.photos/seed/post16180/600/400', '2024-11-26 04:19:45'),
(48, 'Vue imprenable depuis le sommet ! Ça valait le coup de monter. 🏔️ breathtaking #Randonnée #Aventure', 'https://picsum.photos/seed/post17703/600/400', '2025-06-27 02:56:30'),
(28, 'Journée parfaite ! ☀️ Profiter du soleil et de la bonne compagnie. #ChillVibes #Été', 'https://picsum.photos/seed/post18648/600/400', '2024-10-31 04:49:31'),
(46, 'Mon endroit préféré pour me ressourcer. La nature est ma thérapie. 🌲🧘‍♀️ #NatureTherapy #Peaceful', 'https://picsum.photos/seed/post19653/600/400', '2024-12-27 19:26:54'),
(29, 'Le café du matin, mon rituel sacré. Comment aimez-vous le vôtre ? ☕🌞 #CoffeeLover #MorningVibes', 'https://picsum.photos/seed/post20526/600/400', '2024-11-27 20:38:55'),
(46, 'La meilleure façon de passer un après-midi pluvieux : un plaid et un chocolat chaud. 🌧️🍫 #CozyVibes #WinterMood', 'https://picsum.photos/seed/post21922/600/400', '2025-07-13 10:26:02'),
(36, 'Mon endroit préféré pour me ressourcer. La nature est ma thérapie. 🌲🧘‍♀️ #NatureTherapy #Peaceful', 'https://picsum.photos/seed/post22187/600/400', '2025-01-20 18:23:44'),
(2, 'Passionné(e) de voyages et de découvertes. Toujours à la recherche de nouvelles aventures ! 🌍✈️', 'https://picsum.photos/seed/post23349/600/400', '2024-09-21 07:07:05'),
(37, 'Un bon livre et un café, le combo parfait pour commencer la journée. ☕📖 #Lecture #MorningRoutine', 'https://picsum.photos/seed/post24564/600/400', '2025-05-18 00:09:47'),
(13, 'Exploration urbaine aujourd''hui. Chaque coin de rue raconte une histoire. 🚶‍♀️🏙️ #CityLife #Découverte', 'https://picsum.photos/seed/post2564/600/400', '2024-08-16 13:58:34'),
(4, 'Entraînement du jour terminé ! On ne lâche rien ! 💪💦 #Fitness #Motivation', 'https://picsum.photos/seed/post26359/600/400', '2025-03-10 16:21:03'),
(20, 'Moment magique au coucher du soleil. La nature est incroyable. 🌅🧡 #NatureLovers #GoldenHour', 'https://picsum.photos/seed/post27367/600/400', '2024-11-09 19:42:15'),
(49, 'Nouvelle recette testée et approuvée ! Qui veut la recette ? 😋🍴 #Foodie #CuisineMaison', 'https://picsum.photos/seed/post28122/600/400', '2025-04-03 09:28:18'),
(12, 'Flashback à mes dernières vacances. J''ai déjà hâte de repartir ! ✈️🏝️ #TravelMemories #Wanderlust', 'https://picsum.photos/seed/post29621/600/400', '2025-05-24 16:47:33'),
(39, 'Petite pause créative. L''inspiration est partout. 🎨💡 #Art #Création', 'https://picsum.photos/seed/post30480/600/400', '2024-08-20 03:06:50'),
(9, 'Qu''est-ce que vous écoutez en ce moment ? Partagez vos coups de cœur ! 🎶🎧 #Musique #Playlist', 'https://picsum.photos/seed/post31908/600/400', '2025-02-05 06:18:27'),
(47, 'Pensée du jour : Soyez le changement que vous voulez voir dans le monde. ✨😊 #Inspiration #PenséePositive', 'https://picsum.photos/seed/post3259/600/400', '2024-07-28 01:50:49'),
(1, 'Détente absolue ce week-end. Recharger les batteries est essentiel. 🛀💆‍♀️ #Relax #SelfCare', 'https://picsum.photos/seed/post33568/600/400', '2025-03-27 10:29:40'),
(42, 'Mon compagnon à quatre pattes est le meilleur ! 🐶❤️ #PetLover #BestFriend', 'https://picsum.photos/seed/post34199/600/400', '2024-10-09 00:07:22'),
(19, 'Un peu de jardinage pour se vider la tête. Tellement apaisant. 🪴💚 #Jardin #GreenThumb', 'https://picsum.photos/seed/post35759/600/400', '2025-04-10 11:30:19'),
(3, 'Soirée cinéma à la maison. Quel est votre film préféré ? 🎬🍿 #MovieNight #Cinéma', 'https://picsum.photos/seed/post36306/600/400', '2024-12-14 06:40:51'),
(43, 'Nouvel article sur mon blog ! Lien en bio. N''hésitez pas à jeter un œil ! ✍️🌐 #Blog #Écriture', 'https://picsum.photos/seed/post3716/600/400', '2025-07-06 17:09:00'),
(14, 'Le bonheur est dans les petites choses. Appréciez chaque instant. 😊💖 #Gratitude #Bonheur', 'https://picsum.photos/seed/post38865/600/400', '2025-03-17 01:28:46'),
(38, 'Défi du jour : apprendre quelque chose de nouveau. Qu''avez-vous appris récemment ? 🤔💡 #Apprentissage #Curiosité', 'https://picsum.photos/seed/post39832/600/400', '2024-09-08 20:37:34'),
(10, 'Un bon repas entre amis, rien de tel pour recharger les liens. 🥂🍽️ #Amis #Partage', 'https://picsum.photos/seed/post4074/600/400', '2025-01-22 13:02:45'),
(40, 'Vue imprenable depuis le sommet ! Ça valait le coup de monter. 🏔️ breathtaking #Randonnée #Aventure', 'https://picsum.photos/seed/post41259/600/400', '2024-07-23 09:12:06'),
(16, 'Le café du matin, mon rituel sacré. Comment aimez-vous le vôtre ? ☕🌞 #CoffeeLover #MorningVibes', 'https://picsum.photos/seed/post42171/600/400', '2024-12-05 15:36:10'),
(34, 'En mode exploration de la ville. Il y a tant à voir ! 🗺️🚶‍♂️ #UrbanExplorer #Cityscape', 'https://picsum.photos/seed/post43494/600/400', '2025-06-19 09:59:03'),
(25, 'Nouvelle acquisition pour ma collection. Tellement content(e) ! 😍✨ #Collection #Passion', 'https://picsum.photos/seed/post44535/600/400', '2024-08-12 04:55:27'),
(33, 'Un peu de lecture avant de dormir. Le meilleur moyen de s''évader. 😴📚 #Bookworm #NightReads', 'https://picsum.photos/seed/post4597/600/400', '2025-02-10 21:04:16'),
(22, 'La pluie ne m''arrête pas ! Toujours un bon moment pour une balade. ☔🚶‍♀️ #RainyDay #PositiveVibes', 'https://picsum.photos/seed/post46890/600/400', '2024-11-06 00:30:11'),
(35, 'Coucher de soleil sur la plage, un spectacle à couper le souffle. 🌅🌊 #BeachLife #SunsetLover', 'https://picsum.photos/seed/post47547/600/400', '2025-01-01 12:45:00'),
(27, 'Mon animal de compagnie est trop mignon ! Impossible de ne pas craquer. 😻🐾 #CatLover #CutePets', 'https://picsum.photos/seed/post48419/600/400', '2024-10-20 05:10:23'),
(24, 'Session gaming intense ce soir ! Qui est partant(e) pour une partie ? 🎮🔥 #Gaming #Esports', 'https://picsum.photos/seed/post49791/600/400', '2025-05-06 14:00:00'),
(32, 'Petit déjeuner équilibré pour bien démarrer la journée. 🥝🍓 #HealthyFood #Breakfast', 'https://picsum.photos/seed/post50666/600/400', '2024-09-13 11:22:33');

--
-- Données pour la table `comments` (50 entrées)
--
INSERT INTO `comments` (`post_id`, `user_id`, `comment_text`, `created_at`) VALUES
(41, 14, 'J''adore ! 😍', '2025-04-20 05:07:34'),
(13, 2, 'Magnifique photo !', '2025-04-06 18:02:40'),
(35, 33, 'Incroyable !', '2024-10-09 20:30:04'),
(42, 48, 'J''adore ! 😍', '2025-01-21 00:02:26'),
(35, 41, 'J''adore l''idée.', '2024-09-20 03:06:58'),
(19, 42, 'J''adore tes posts.', '2024-12-07 07:11:36'),
(1, 38, 'J''adore l''idée.', '2024-12-09 00:19:16'),
(23, 22, 'C''est super !', '2025-06-13 14:38:29'),
(2, 33, 'Magnifique photo !', '2025-06-03 08:34:04'),
(44, 49, 'Tu as tout dit !', '2025-03-26 09:27:14'),
(47, 10, 'J''adore l''idée.', '2025-01-08 07:44:43'),
(42, 33, 'J''adore ! 😍', '2025-05-09 06:17:34'),
(12, 10, 'J''adore ! 😍', '2025-03-12 04:31:37'),
(33, 44, 'J''adore l''idée.', '2024-09-02 04:47:33'),
(2, 36, 'J''adore ! 😍', '2024-11-25 15:44:59'),
(20, 23, 'J''adore l''idée.', '2025-05-13 11:06:05'),
(1, 36, 'J''adore ! 😍', '2024-07-27 19:35:10'),
(17, 12, 'J''adore l''idée.', '2025-01-14 18:24:20'),
(24, 25, 'J''adore l''idée.', '2025-01-09 06:54:19'),
(30, 20, 'J''adore ! 😍', '2024-10-21 02:40:48'),
(1, 41, 'J''adore ! 😍', '2024-11-04 18:41:40'),
(36, 17, 'J''adore ! 😍', '2024-09-07 09:25:01'),
(17, 33, 'J''adore ! 😍', '2025-02-14 13:46:17'),
(22, 2, 'J''adore l''idée.', '2024-12-06 02:42:04'),
(47, 43, 'J''adore l''idée.', '2025-06-10 13:07:33'),
(10, 48, 'J''adore ! 😍', '2025-05-27 10:11:05'),
(31, 28, 'J''adore l''idée.', '2025-06-25 04:54:55'),
(11, 47, 'J''adore l''idée.', '2025-04-19 09:12:35'),
(13, 2, 'J''adore ! 😍', '2025-04-12 17:34:39'),
(40, 29, 'J''adore l''idée.', '2024-10-23 15:53:30'),
(25, 20, 'J''adore ! 😍', '2024-12-16 02:27:07'),
(1, 40, 'J''adore l''idée.', '2025-04-22 00:46:42'),
(43, 44, 'J''adore l''idée.', '2025-01-19 03:04:47'),
(10, 18, 'J''adore ! 😍', '2024-11-05 13:50:52'),
(43, 21, 'J''adore l''idée.', '2025-03-29 02:49:50'),
(28, 48, 'J''adore ! 😍', '2025-07-07 16:51:56'),
(17, 30, 'J''adore l''idée.', '2025-03-01 07:18:22'),
(39, 44, 'J''adore ! 😍', '2025-05-19 19:42:49'),
(29, 39, 'J''adore l''idée.', '2024-07-20 18:16:32'),
(30, 15, 'J''adore ! 😍', '2024-10-18 17:09:59'),
(19, 29, 'J''adore l''idée.', '2025-02-09 10:04:08'),
(45, 11, 'J''adore ! 😍', '2025-03-06 17:02:45'),
(44, 45, 'J''adore l''idée.', '2024-12-25 15:47:04'),
(39, 13, 'J''adore ! 😍', '2025-05-25 04:30:17'),
(49, 19, 'J''adore l''idée.', '2024-11-17 19:20:20'),
(31, 37, 'J''adore ! 😍', '2025-02-27 16:26:01'),
(1, 46, 'J''adore l''idée.', '2025-01-11 15:40:09'),
(23, 10, 'J''adore ! 😍', '2025-04-05 11:39:19'),
(33, 44, 'J''adore l''idée.', '2025-06-01 02:00:23'),
(16, 22, 'J''adore ! 😍', '2024-09-29 09:15:30');

--
-- Données pour la table `featured_posts` (50 entrées)
--
INSERT INTO `featured_posts` (`post_id`, `created_at`) VALUES
(1, '2025-03-09 02:18:52'),
(2, '2024-10-12 19:25:31'),
(3, '2025-03-12 13:00:54'),
(4, '2024-12-07 11:44:32'),
(5, '2024-08-25 05:07:05'),
(6, '2025-03-04 06:17:34'),
(7, '2025-04-16 11:23:45'),
(8, '2025-01-29 18:03:19'),
(9, '2024-07-28 17:42:06'),
(10, '2025-06-15 01:29:58'),
(11, '2025-04-02 08:50:11'),
(12, '2024-11-20 10:05:22'),
(13, '2025-02-28 23:49:03'),
(14, '2024-09-06 07:11:18'),
(15, '2025-07-01 14:36:20'),
(16, '2024-10-03 09:55:00'),
(17, '2025-05-11 16:08:37'),
(18, '2024-12-21 21:00:15'),
(19, '2025-01-18 04:30:49'),
(20, '2024-08-01 12:14:26'),
(21, '2025-06-09 19:51:10'),
(22, '2024-09-17 02:07:33'),
(23, '2025-03-20 05:44:59'),
(24, '2024-11-07 13:28:16'),
(25, '2025-02-02 08:19:02'),
(26, '2024-07-21 16:53:48'),
(27, '2025-05-29 10:37:00'),
(28, '2024-10-25 22:11:22'),
(29, '2025-01-06 03:59:17'),
(30, '2024-08-14 06:20:30'),
(31, '2025-06-23 11:04:45'),
(32, '2024-09-10 18:35:01'),
(33, '2025-03-01 00:12:56'),
(34, '2024-11-13 09:48:29'),
(35, '2025-02-16 15:57:08'),
(36, '2024-07-24 04:01:19'),
(37, '2025-05-04 07:26:35'),
(38, '2024-10-10 14:10:50'),
(39, '2025-01-26 19:33:04'),
(40, '2024-08-05 20:47:12'),
(41, '2025-06-18 02:54:21'),
(42, '2024-09-23 11:09:36'),
(43, '2025-03-25 01:17:40'),
(44, '2024-11-01 04:52:55'),
(45, '2025-02-08 10:06:13'),
(46, '2024-07-31 13:40:00'),
(47, '2025-05-15 17:29:18'),
(48, '2024-10-19 23:03:50'),
(49, '2025-01-12 06:58:07'),
(50, '2024-08-09 15:16:33');

--
-- Données pour la table `followers` (50 entrées)
--
INSERT INTO `followers` (`user_id`, `follower_id`, `created_at`) VALUES
(38, 25, '2025-02-28 01:21:49'),
(26, 32, '2024-10-26 05:46:17'),
(43, 10, '2024-12-25 08:34:11'),
(34, 18, '2025-01-15 00:03:00'),
(13, 31, '2024-08-06 14:38:09'),
(42, 2, '2025-06-07 09:05:13'),
(15, 30, '2024-09-12 17:09:22'),
(22, 19, '2025-03-02 02:47:30'),
(35, 1, '2024-11-21 11:58:45'),
(40, 20, '2025-04-14 06:33:55'),
(11, 46, '2024-07-20 23:17:00'),
(33, 4, '2025-05-23 15:20:10'),
(48, 28, '2024-10-05 08:50:25'),
(17, 36, '2025-01-07 03:11:38'),
(27, 47, '2024-08-18 20:00:49'),
(3, 44, '2025-06-29 01:40:55'),
(49, 9, '2024-09-03 10:25:00'),
(24, 50, '2025-03-16 19:12:30'),
(39, 16, '2024-11-29 04:55:00'),
(10, 41, '2025-04-08 13:00:15'),
(37, 23, '2024-07-25 07:44:20'),
(14, 29, '2025-05-01 22:30:00'),
(45, 6, '2024-10-11 16:50:18'),
(21, 3, '2025-01-23 09:00:00'),
(30, 42, '2024-08-10 00:00:00'),
(5, 12, '2025-06-16 14:10:05'),
(4, 26, '2024-09-28 21:00:00'),
(18, 34, '2025-03-07 05:25:00'),
(46, 7, '2024-11-02 12:30:00'),
(29, 39, '2025-04-21 10:45:00'),
(8, 27, '2024-07-19 06:00:00'),
(32, 13, '2025-05-17 18:00:00'),
(41, 5, '2024-10-28 09:30:00'),
(16, 40, '2025-01-01 00:00:00'),
(2, 43, '2024-08-29 11:15:00'),
(36, 17, '2025-06-20 03:40:00'),
(44, 21, '2024-09-05 14:55:00'),
(6, 45, '2025-03-18 20:05:00'),
(20, 11, '2024-11-10 07:00:00'),
(50, 24, '2025-04-04 16:20:00'),
(9, 49, '2024-07-22 10:00:00'),
(28, 37, '2025-05-10 23:59:00'),
(47, 15, '2024-10-17 01:00:00'),
(1, 35, '2025-01-27 12:00:00'),
(23, 8, '2024-08-03 08:30:00'),
(31, 14, '2025-06-11 04:00:00'),
(42, 38, '2024-09-19 13:00:00'),
(7, 30, '2025-03-21 17:00:00'),
(19, 22, '2024-11-08 02:00:00'),
(3, 4, '2025-04-26 21:00:00');

--
-- Données pour la table `friends` (50 entrées)
--
INSERT INTO `friends` (`sender_id`, `receiver_id`, `status`, `created_at`) VALUES
(38, 48, 'accepted', '2025-06-25 15:10:03'),
(35, 11, 'accepted', '2024-10-09 23:17:59'),
(3, 40, 'rejected', '2025-02-03 02:40:22'),
(46, 21, 'accepted', '2024-08-16 19:25:30'),
(26, 42, 'pending', '2025-05-18 09:00:00'),
(16, 39, 'accepted', '2024-09-01 07:15:45'),
(43, 10, 'accepted', '2025-03-29 12:30:10'),
(29, 36, 'rejected', '2024-11-24 04:50:00'),
(13, 31, 'accepted', '2025-01-10 18:00:00'),
(49, 24, 'pending', '2024-07-29 21:40:00'),
(2, 44, 'accepted', '2025-06-01 06:00:00'),
(15, 30, 'accepted', '2024-09-15 10:10:10'),
(32, 19, 'rejected', '2025-03-08 01:20:00'),
(45, 6, 'accepted', '2024-11-20 15:00:00'),
(25, 47, 'pending', '2025-04-11 08:30:00'),
(12, 33, 'accepted', '2024-07-23 16:45:00'),
(50, 28, 'accepted', '2025-05-27 20:00:00'),
(17, 34, 'rejected', '2024-10-02 03:10:00'),
(40, 20, 'accepted', '2025-01-05 11:55:00'),
(27, 41, 'pending', '2024-08-13 07:05:00'),
(7, 49, 'accepted', '2025-06-10 13:00:00'),
(37, 23, 'accepted', '2024-09-08 22:00:00'),
(14, 29, 'rejected', '2025-03-14 09:40:00'),
(42, 5, 'accepted', '2024-11-05 00:15:00'),
(22, 3, 'pending', '2025-04-20 14:00:00'),
(1, 35, 'accepted', '2024-07-18 10:00:00'),
(36, 17, 'accepted', '2025-05-03 17:30:00'),
(44, 21, 'rejected', '2024-10-14 06:00:00'),
(20, 11, 'accepted', '2025-01-26 19:00:00'),
(48, 24, 'pending', '2024-08-08 11:20:00'),
(9, 43, 'accepted', '2025-06-13 02:45:00'),
(28, 37, 'accepted', '2024-09-22 04:00:00'),
(18, 34, 'rejected', '2025-03-23 16:00:00'),
(47, 15, 'accepted', '2024-11-12 09:10:00'),
(23, 8, 'pending', '2025-04-01 12:00:00'),
(41, 50, 'accepted', '2024-07-26 05:30:00'),
(31, 14, 'accepted', '2025-05-14 21:00:00'),
(4, 26, 'rejected', '2024-10-27 10:50:00'),
(10, 41, 'accepted', '2025-01-09 07:00:00'),
(39, 16, 'pending', '2024-08-20 14:00:00'),
(24, 49, 'accepted', '2025-06-28 08:00:00'),
(34, 18, 'accepted', '2024-09-04 17:00:00'),
(19, 22, 'rejected', '2025-03-05 23:00:00'),
(46, 7, 'accepted', '2024-11-01 04:00:00'),
(21, 3, 'pending', '2025-04-07 10:00:00'),
(5, 12, 'accepted', '2024-07-21 15:00:00'),
(30, 42, 'accepted', '2025-05-09 19:00:00'),
(42, 38, 'rejected', '2024-10-16 08:00:00'),
(11, 46, 'accepted', '2025-01-20 13:00:00'),
(33, 4, 'pending', '2024-08-27 09:00:00');

--
-- Données pour la table `friend_requests` (50 entrées)
--
INSERT INTO `friend_requests` (`sender_id`, `receiver_id`, `status`, `created_at`) VALUES
(38, 25, 'pending', '2025-02-28 01:21:49'),
(26, 32, 'accepted', '2024-10-26 05:46:17'),
(43, 10, 'pending', '2024-12-25 08:34:11'),
(34, 18, 'rejected', '2025-01-15 00:03:00'),
(13, 31, 'pending', '2024-08-06 14:38:09'),
(42, 2, 'accepted', '2025-06-07 09:05:13'),
(15, 30, 'pending', '2024-09-12 17:09:22'),
(22, 19, 'rejected', '2025-03-02 02:47:30'),
(35, 1, 'pending', '2024-11-21 11:58:45'),
(40, 20, 'accepted', '2025-04-14 06:33:55'),
(11, 46, 'pending', '2024-07-20 23:17:00'),
(33, 4, 'accepted', '2025-05-23 15:20:10'),
(48, 28, 'pending', '2024-10-05 08:50:25'),
(17, 36, 'rejected', '2025-01-07 03:11:38'),
(27, 47, 'pending', '2024-08-18 20:00:49'),
(3, 44, 'accepted', '2025-06-29 01:40:55'),
(49, 9, 'pending', '2024-09-03 10:25:00'),
(24, 50, 'rejected', '2025-03-16 19:12:30'),
(39, 16, 'pending', '2024-11-29 04:55:00'),
(10, 41, 'accepted', '2025-04-08 13:00:15'),
(37, 23, 'pending', '2024-07-25 07:44:20'),
(14, 29, 'rejected', '2025-05-01 22:30:00'),
(45, 6, 'pending', '2024-10-11 16:50:18'),
(21, 3, 'accepted', '2025-01-23 09:00:00'),
(30, 42, 'pending', '2024-08-10 00:00:00'),
(5, 12, 'accepted', '2025-06-16 14:10:05'),
(4, 26, 'pending', '2024-09-28 21:00:00'),
(18, 34, 'rejected', '2025-03-07 05:25:00'),
(46, 7, 'pending', '2024-11-02 12:30:00'),
(29, 39, 'accepted', '2025-04-21 10:45:00'),
(8, 27, 'pending', '2024-07-19 06:00:00'),
(32, 13, 'rejected', '2025-05-17 18:00:00'),
(41, 5, 'pending', '2024-10-28 09:30:00'),
(16, 40, 'accepted', '2025-01-01 00:00:00'),
(2, 43, 'pending', '2024-08-29 11:15:00'),
(36, 17, 'rejected', '2025-06-20 03:40:00'),
(44, 21, 'pending', '2024-09-05 14:55:00'),
(6, 45, 'accepted', '2025-03-18 20:05:00'),
(20, 11, 'pending', '2024-11-10 07:00:00'),
(50, 24, 'rejected', '2025-04-04 16:20:00'),
(9, 49, 'pending', '2024-07-22 10:00:00'),
(28, 37, 'accepted', '2025-05-10 23:59:00'),
(47, 15, 'pending', '2024-10-17 01:00:00'),
(1, 35, 'rejected', '2025-01-27 12:00:00'),
(23, 8, 'pending', '2024-08-03 08:30:00'),
(31, 14, 'accepted', '2025-06-11 04:00:00'),
(42, 38, 'pending', '2024-09-19 13:00:00'),
(7, 30, 'rejected', '2025-03-21 17:00:00'),
(19, 22, 'pending', '2024-11-08 02:00:00'),
(3, 4, 'accepted', '2025-04-26 21:00:00');

--
-- Données pour la table `hashtags` (50 entrées)
--
INSERT INTO `hashtags` (`tag`, `post_id`, `created_at`) VALUES
('#Développement', 29, '2025-03-29 03:00:00'),
('#Voyage', 44, '2024-09-02 08:00:00'),
('#Fitness', 3, '2025-05-19 14:00:00'),
('#Musique', 2, '2024-10-10 22:00:00'),
('#Inspiration', 13, '2025-01-04 06:00:00'),
('#Art', 47, '2024-08-11 17:00:00'),
('#Foodie', 28, '2025-06-17 09:00:00'),
('#Lecture', 49, '2024-09-25 01:00:00'),
('#Nature', 18, '2025-03-03 11:00:00'),
('#Sport', 12, '2024-11-22 19:00:00'),
('#Motivation', 20, '2025-04-10 04:00:00'),
('#BienÊtre', 41, '2024-07-27 13:00:00'),
('#Tech', 34, '2025-05-08 20:00:00'),
('#Développement', 7, '2024-10-29 02:00:00'),
('#Art', 32, '2025-01-16 10:00:00'),
('#Musique', 25, '2024-08-04 05:00:00'),
('#Lecture', 42, '2025-06-21 16:00:00'),
('#Cinéma', 14, '2024-09-18 23:00:00'),
('#Nature', 36, '2025-03-11 07:00:00'),
('#Animaux', 5, '2024-11-09 12:00:00'),
('#Sport', 23, '2025-04-28 09:00:00'),
('#Santé', 48, '2024-07-20 18:00:00'),
('#Lifestyle', 1, '2025-05-26 15:00:00'),
('#Passion', 30, '2024-10-06 00:00:00'),
('#Découverte', 10, '2025-01-24 21:00:00'),
('#Aventure', 39, '2024-08-23 03:00:00'),
('#Famille', 16, '2025-06-05 10:00:00'),
('#Amis', 45, '2024-09-11 14:00:00'),
('#Bonheur', 8, '2025-03-06 19:00:00'),
('#Gratitude', 35, '2024-11-19 08:00:00'),
('#Success', 11, '2025-04-17 11:00:00'),
('#Innovation', 46, '2024-07-24 07:00:00'),
('#Design', 4, '2025-05-02 16:00:00'),
('#PhotoDuJour', 37, '2024-10-01 20:00:00'),
('#InstaGood', 17, '2025-01-02 09:00:00'),
('#Love', 26, '2024-08-09 12:00:00'),
('#Happy', 40, '2025-06-14 05:00:00'),
('#Life', 15, '2024-09-20 10:00:00'),
('#DreamBig', 33, '2025-03-28 14:00:00'),
('#WorkHard', 9, '2024-11-03 22:00:00'),
('#PlayHard', 24, '2025-04-09 06:00:00'),
('#HealthyLife', 50, '2024-07-21 11:00:00'),
('#TravelGram', 19, '2025-05-12 18:00:00'),
('#Fashion', 31, '2024-10-22 01:00:00'),
('#Style', 6, '2025-01-13 15:00:00'),
('#Beauty', 43, '2024-08-07 09:00:00'),
('#SelfCare', 27, '2025-06-02 20:00:00'),
('#Mindfulness', 38, '2024-09-27 04:00:00'),
('#Productivité', 1, '2025-03-15 13:00:00'),
('#Apprentissage', 2, '2024-11-26 07:00:00');

--
-- Données pour la table `likes` (50 entrées)
--
INSERT INTO `likes` (`user_id`, `post_id`, `created_at`) VALUES
(38, 25, '2025-02-28 01:21:49'),
(26, 32, '2024-10-26 05:46:17'),
(43, 10, '2024-12-25 08:34:11'),
(34, 18, '2025-01-15 00:03:00'),
(13, 31, '2024-08-06 14:38:09'),
(42, 2, '2025-06-07 09:05:13'),
(15, 30, '2024-09-12 17:09:22'),
(22, 19, '2025-03-02 02:47:30'),
(35, 1, '2024-11-21 11:58:45'),
(40, 20, '2025-04-14 06:33:55'),
(11, 46, '2024-07-20 23:17:00'),
(33, 4, '2025-05-23 15:20:10'),
(48, 28, '2024-10-05 08:50:25'),
(17, 36, '2025-01-07 03:11:38'),
(27, 47, '2024-08-18 20:00:49'),
(3, 44, '2025-06-29 01:40:55'),
(49, 9, '2024-09-03 10:25:00'),
(24, 50, '2025-03-16 19:12:30'),
(39, 16, '2024-11-29 04:55:00'),
(10, 41, '2025-04-08 13:00:15'),
(37, 23, '2024-07-25 07:44:20'),
(14, 29, '2025-05-01 22:30:00'),
(45, 6, '2024-10-11 16:50:18'),
(21, 3, '2025-01-23 09:00:00'),
(30, 42, '2024-08-10 00:00:00'),
(5, 12, '2025-06-16 14:10:05'),
(4, 26, '2024-09-28 21:00:00'),
(18, 34, '2025-03-07 05:25:00'),
(46, 7, '2024-11-02 12:30:00'),
(29, 39, '2025-04-21 10:45:00'),
(8, 27, '2024-07-19 06:00:00'),
(32, 13, '2025-05-17 18:00:00'),
(41, 5, '2024-10-28 09:30:00'),
(16, 40, '2025-01-01 00:00:00'),
(2, 43, '2024-08-29 11:15:00'),
(36, 17, '2025-06-20 03:40:00'),
(44, 21, '2024-09-05 14:55:00'),
(6, 45, '2025-03-18 20:05:00'),
(20, 11, '2024-11-10 07:00:00'),
(50, 24, '2025-04-04 16:20:00'),
(9, 49, '2024-07-22 10:00:00'),
(28, 37, '2025-05-10 23:59:00'),
(47, 15, '2024-10-17 01:00:00'),
(1, 35, '2025-01-27 12:00:00'),
(23, 8, '2024-08-03 08:30:00'),
(31, 14, '2025-06-11 04:00:00'),
(42, 38, '2024-09-19 13:00:00'),
(7, 30, '2025-03-21 17:00:00'),
(19, 22, '2024-11-08 02:00:00'),
(3, 4, '2025-04-26 21:00:00');

--
-- Données pour la table `messages` (50 entrées)
--
INSERT INTO `messages` (`sender_id`, `receiver_id`, `group_id`, `message`, `is_read`, `created_at`) VALUES
(41, 22, NULL, 'Tu as bien reçu mon message ?', 0, '2025-04-18 05:43:53'),
(43, 2, NULL, 'C''est incroyable ce qui s''est passé !', 1, '2024-09-17 02:05:00'),
(15, 30, NULL, 'J''ai besoin de tes conseils pour un truc.', 0, '2025-03-20 18:00:00'),
(22, 19, NULL, 'Tu as des plans pour les vacances ?', 1, '2024-11-07 13:28:16'),
(35, 1, NULL, 'J''ai une surprise pour toi !', 0, '2025-02-02 08:19:02'),
(40, 20, NULL, 'On se voit quand pour notre prochaine session ?', 1, '2024-07-21 16:53:48'),
(11, 46, NULL, 'J''ai vu ton dernier post, il est génial ! Vraiment inspirant.', 0, '2025-05-29 10:37:00'),
(33, 4, NULL, 'Salut ! Comment vas-tu ? Ça fait longtemps !', 1, '2024-10-25 22:11:22'),
(48, 28, NULL, 'Tu penses quoi de cette idée ? J''aimerais avoir ton avis.', 0, '2025-01-06 03:59:17'),
(17, 36, NULL, 'On se fait une visio bientôt ? Il faut qu''on se parle !', 1, '2024-08-14 06:20:30'),
(27, 47, NULL, 'Merci pour ton aide hier, tu m''as vraiment sauvé la mise !', 0, '2025-06-23 11:04:45'),
(3, 44, NULL, 'J''ai hâte de te raconter ma journée, c''était fou !', 1, '2024-09-10 18:35:01'),
(49, 9, NULL, 'Tu as des recommandations de films/séries pour le week-end ?', 0, '2025-03-01 00:12:56'),
(24, 50, NULL, 'Je viens de découvrir un truc super intéressant, je t''envoie le lien.', 1, '2024-11-13 09:48:29'),
(39, 16, NULL, 'Désolé(e) pour le retard, j''étais un peu débordé(e).', 0, '2025-02-16 15:57:08'),
(10, 41, NULL, 'C''était génial de te voir l''autre jour !', 1, '2024-07-24 04:01:19'),
(37, 23, NULL, 'N''oublie pas notre rendez-vous de demain !', 0, '2025-05-04 07:26:35'),
(14, 29, NULL, 'Je suis en route, j''arrive dans 5 minutes.', 1, '2024-10-10 14:10:50'),
(45, 6, NULL, 'Je suis tellement fatigué(e) aujourd''hui...', 0, '2025-01-26 19:33:04'),
(21, 3, NULL, 'On va où pour le déjeuner ?', 1, '2024-08-05 20:47:12'),
(30, 42, NULL, 'Tu me manques ! Quand est-ce qu''on se revoit ?', 0, '2025-06-18 02:54:21'),
(5, 12, NULL, 'J''adore ta nouvelle photo de profil !', 1, '2024-09-23 11:09:36'),
(4, 26, NULL, 'C''est noté, merci beaucoup !', 0, '2025-03-25 01:17:40'),
(18, 34, NULL, 'Je te tiens au courant.', 1, '2024-11-01 04:52:55'),
(46, 7, NULL, 'Passe une excellente soirée !', 0, '2025-02-08 10:06:13'),
(29, 39, NULL, 'On se capte plus tard.', 1, '2024-07-31 13:40:00'),
(8, 27, NULL, 'Je suis désolé(e) d''entendre ça.', 0, '2025-05-15 17:29:18'),
(32, 13, NULL, 'Courage pour la semaine !', 1, '2024-10-19 23:03:50'),
(41, 5, NULL, 'J''ai une question rapide pour toi.', 0, '2025-01-12 06:58:07'),
(16, 40, NULL, 'C''est une super idée !', 1, '2024-08-09 15:16:33'),
(2, 43, NULL, 'Je suis d''accord avec toi.', 0, '2025-06-25 15:10:03'),
(36, 17, NULL, 'Je n''y avais pas pensé, merci !', 1, '2024-10-09 23:17:59'),
(44, 21, NULL, 'Tiens-moi informé(e).', 0, '2025-02-03 02:40:22'),
(6, 45, NULL, 'Je suis là si tu as besoin.', 1, '2024-08-16 19:25:30'),
(20, 11, NULL, 'On se tient au courant, ok ?', 0, '2025-05-18 09:00:00'),
(50, 24, NULL, 'Tu as des projets pour ce soir ?', 1, '2024-09-01 07:15:45'),
(9, 49, NULL, 'J''ai une bonne nouvelle à t''annoncer !', 0, '2025-03-29 12:30:10'),
(28, 37, NULL, 'C''est incroyable ce qui s''est passé !', 1, '2024-11-24 04:50:00'),
(47, 15, NULL, 'Je te souhaite le meilleur.', 0, '2025-01-10 18:00:00'),
(1, 35, NULL, 'On se voit bientôt !', 1, '2024-07-29 21:40:00'),
(23, 8, NULL, 'C''est un plaisir de te parler.', 0, '2025-06-01 06:00:00'),
(31, 14, NULL, 'J''espère que tu vas bien.', 1, '2024-09-15 10:10:10'),
(42, 38, NULL, 'Prends soin de toi.', 0, '2025-03-08 01:20:00'),
(7, 30, NULL, 'À très vite !', 1, '2024-11-20 15:00:00'),
(19, 22, NULL, 'Envoie-moi un message quand tu es prêt(e).', 0, '2025-04-11 08:30:00'),
(3, 4, NULL, 'Salut ! Comment vas-tu ? Ça fait longtemps !', 1, '2024-07-23 16:45:00'),
(26, 32, NULL, 'Hey, tu es libre ce soir ? On pourrait aller prendre un verre.', 0, '2025-05-27 20:00:00'),
(43, 10, NULL, 'J''ai vu ton dernier post, il est génial ! Vraiment inspirant.', 1, '2024-10-02 03:10:00'),
(34, 18, NULL, 'Peux-tu me rappeler l''adresse du restaurant dont tu m''as parlé ?', 0, '2025-01-05 11:55:00'),
(13, 31, NULL, 'Félicitations pour ton nouveau projet, ça a l''air super !', 1, '2024-08-13 07:05:00');

--
-- Données pour la table `notifications` (50 entrées)
--
INSERT INTO `notifications` (`user_id`, `sender_id`, `type`, `post_id`, `is_read`, `created_at`) VALUES
(41, 14, 'like', 25, 0, '2025-04-18 05:43:53'),
(43, 2, 'comment', 44, 1, '2024-09-17 02:05:00'),
(15, 30, 'friend_request', NULL, 0, '2025-03-20 18:00:00'),
(22, 19, 'follow', NULL, 1, '2024-11-07 13:28:16'),
(35, 1, 'like', 3, 0, '2025-02-02 08:19:02'),
(40, 20, 'comment', 10, 1, '2024-07-21 16:53:48'),
(11, 46, 'friend_request', NULL, 0, '2025-05-29 10:37:00'),
(33, 4, 'follow', NULL, 1, '2024-10-25 22:11:22'),
(48, 28, 'like', 34, 0, '2025-01-06 03:59:17'),
(17, 36, 'comment', 17, 1, '2024-08-14 06:20:30'),
(27, 47, 'friend_request', NULL, 0, '2025-06-23 11:04:45'),
(3, 44, 'follow', NULL, 1, '2024-09-10 18:35:01'),
(49, 9, 'like', 49, 0, '2025-03-01 00:12:56'),
(24, 50, 'comment', 24, 1, '2024-11-13 09:48:29'),
(39, 16, 'friend_request', NULL, 0, '2025-02-16 15:57:08'),
(10, 41, 'follow', NULL, 1, '2024-07-24 04:01:19'),
(37, 23, 'like', 37, 0, '2025-05-04 07:26:35'),
(14, 29, 'comment', 14, 1, '2024-10-10 14:10:50'),
(45, 6, 'friend_request', NULL, 0, '2025-01-26 19:33:04'),
(21, 3, 'follow', NULL, 1, '2024-08-05 20:47:12'),
(30, 42, 'like', 30, 0, '2025-06-18 02:54:21'),
(5, 12, 'comment', 12, 1, '2024-09-23 11:09:36'),
(4, 26, 'friend_request', NULL, 0, '2025-03-25 01:17:40'),
(18, 34, 'follow', NULL, 1, '2024-11-01 04:52:55'),
(46, 7, 'like', 19, 0, '2025-02-08 10:06:13'),
(29, 39, 'comment', 20, 1, '2024-07-31 13:40:00'),
(8, 27, 'friend_request', NULL, 0, '2025-05-15 17:29:18'),
(32, 13, 'follow', NULL, 1, '2024-10-19 23:03:50'),
(41, 5, 'like', 1, 0, '2025-01-12 06:58:07'),
(16, 40, 'comment', 41, 1, '2024-08-09 15:16:33'),
(2, 43, 'friend_request', NULL, 0, '2025-06-25 15:10:03'),
(36, 17, 'follow', NULL, 1, '2024-10-09 23:17:59'),
(44, 21, 'like', 4, 0, '2025-02-03 02:40:22'),
(6, 45, 'comment', 6, 1, '2024-08-16 19:25:30'),
(20, 11, 'friend_request', NULL, 0, '2025-05-18 09:00:00'),
(50, 24, 'follow', NULL, 1, '2024-09-01 07:15:45'),
(9, 49, 'like', 31, 0, '2025-03-29 12:30:10'),
(28, 37, 'comment', 28, 1, '2024-11-24 04:50:00'),
(47, 15, 'friend_request', NULL, 0, '2025-01-10 18:00:00'),
(1, 35, 'follow', NULL, 1, '2024-07-29 21:40:00'),
(23, 8, 'like', 7, 0, '2025-06-01 06:00:00'),
(31, 14, 'comment', 31, 1, '2024-09-15 10:10:10'),
(42, 38, 'friend_request', NULL, 0, '2025-03-08 01:20:00'),
(7, 30, 'follow', NULL, 1, '2024-11-20 15:00:00'),
(19, 22, 'like', 35, 0, '2025-04-11 08:30:00'),
(3, 4, 'comment', 3, 1, '2024-07-23 16:45:00'),
(26, 32, 'friend_request', NULL, 0, '2025-05-27 20:00:00'),
(43, 10, 'follow', NULL, 1, '2024-10-02 03:10:00'),
(34, 18, 'like', 43, 0, '2025-01-05 11:55:00'),
(13, 31, 'comment', 13, 1, '2024-08-13 07:05:00');

--
-- Données pour la table `reports` (50 entrées)
--
INSERT INTO `reports` (`reporter_id`, `reported_id`, `report_type`, `reason`, `status`, `created_at`) VALUES
(41, 22, 'user', 'Contenu inapproprié', 'pending', '2025-04-18 05:43:53'),
(43, 2, 'post', 'Spam', 'reviewed', '2024-09-17 02:05:00'),
(15, 30, 'user', 'Harcèlement', 'pending', '2025-03-20 18:00:00'),
(22, 19, 'post', 'Faux profil', 'reviewed', '2024-11-07 13:28:16'),
(35, 1, 'user', 'Violation des règles', 'pending', '2025-02-02 08:19:02'),
(40, 20, 'post', 'Discours de haine', 'reviewed', '2024-07-21 16:53:48'),
(11, 46, 'user', 'Usurpation d''identité', 'pending', '2025-05-29 10:37:00'),
(33, 4, 'post', 'Contenu violent', 'reviewed', '2024-10-25 22:11:22'),
(48, 28, 'user', 'Nudité', 'pending', '2025-01-06 03:59:17'),
(17, 36, 'post', 'Autre', 'reviewed', '2024-08-14 06:20:30'),
(27, 47, 'user', 'Contenu inapproprié', 'pending', '2025-06-23 11:04:45'),
(3, 44, 'post', 'Spam', 'reviewed', '2024-09-10 18:35:01'),
(49, 9, 'user', 'Harcèlement', 'pending', '2025-03-01 00:12:56'),
(24, 50, 'post', 'Faux profil', 'reviewed', '2024-11-13 09:48:29'),
(39, 16, 'user', 'Violation des règles', 'pending', '2025-02-16 15:57:08'),
(10, 41, 'post', 'Discours de haine', 'reviewed', '2024-07-24 04:01:19'),
(37, 23, 'user', 'Usurpation d''identité', 'pending', '2025-05-04 07:26:35'),
(14, 29, 'post', 'Contenu violent', 'reviewed', '2024-10-10 14:10:50'),
(45, 6, 'user', 'Nudité', 'pending', '2025-01-26 19:33:04'),
(21, 3, 'post', 'Autre', 'reviewed', '2024-08-05 20:47:12'),
(30, 42, 'user', 'Contenu inapproprié', 'pending', '2025-06-18 02:54:21'),
(5, 12, 'post', 'Spam', 'reviewed', '2024-09-23 11:09:36'),
(4, 26, 'user', 'Harcèlement', 'pending', '2025-03-25 01:17:40'),
(18, 34, 'post', 'Faux profil', 'reviewed', '2024-11-01 04:52:55'),
(46, 7, 'user', 'Violation des règles', 'pending', '2025-02-08 10:06:13'),
(29, 39, 'post', 'Discours de haine', 'reviewed', '2024-07-31 13:40:00'),
(8, 27, 'user', 'Usurpation d''identité', 'pending', '2025-05-15 17:29:18'),
(32, 13, 'post', 'Contenu violent', 'reviewed', '2024-10-19 23:03:50'),
(41, 5, 'user', 'Nudité', 'pending', '2025-01-12 06:58:07'),
(16, 40, 'post', 'Autre', 'reviewed', '2024-08-09 15:16:33'),
(2, 43, 'user', 'Contenu inapproprié', 'pending', '2025-06-25 15:10:03'),
(36, 17, 'post', 'Spam', 'reviewed', '2024-10-09 23:17:59'),
(44, 21, 'user', 'Harcèlement', 'pending', '2025-02-03 02:40:22'),
(6, 45, 'post', 'Faux profil', 'reviewed', '2024-08-16 19:25:30'),
(20, 11, 'user', 'Violation des règles', 'pending', '2025-05-18 09:00:00'),
(50, 24, 'post', 'Discours de haine', 'reviewed', '2024-09-01 07:15:45'),
(9, 49, 'user', 'Usurpation d''identité', 'pending', '2025-03-29 12:30:10'),
(28, 37, 'post', 'Contenu violent', 'reviewed', '2024-11-24 04:50:00'),
(47, 15, 'user', 'Nudité', 'pending', '2025-01-10 18:00:00'),
(1, 35, 'post', 'Autre', 'reviewed', '2024-07-29 21:40:00'),
(23, 8, 'user', 'Contenu inapproprié', 'pending', '2025-06-01 06:00:00'),
(31, 14, 'post', 'Spam', 'reviewed', '2024-09-15 10:10:10'),
(42, 38, 'user', 'Harcèlement', 'pending', '2025-03-08 01:20:00'),
(7, 30, 'post', 'Faux profil', 'reviewed', '2024-11-20 15:00:00'),
(19, 22, 'user', 'Violation des règles', 'pending', '2025-04-11 08:30:00'),
(3, 4, 'post', 'Discours de haine', 'reviewed', '2024-07-23 16:45:00'),
(26, 32, 'user', 'Usurpation d''identité', 'pending', '2025-05-27 20:00:00'),
(43, 10, 'post', 'Contenu violent', 'reviewed', '2024-10-02 03:10:00'),
(34, 18, 'user', 'Nudité', 'pending', '2025-01-05 11:55:00'),
(13, 31, 'post', 'Autre', 'reviewed', '2024-08-13 07:05:00');
