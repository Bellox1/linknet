# 🌐 Linknet - Réseau Social Collaboratif

<p align="center">
  <img src="assets/images/linknet_logo.webp" width="150" alt="Linknet Logo">
</p>

**Linknet** est un réseau social complet et interactif conçu pour favoriser la connexion et l'échange. Développé en équipe, ce projet intègre toutes les fonctionnalités modernes d'une plateforme sociale : gestion de profil, publications dynamiques, messagerie instantanée et un tableau de bord d'administration robuste.

---

## ✨ Fonctionnalités Principales

### 👥 Expérience Sociale (Client)
*   **Interaction Totale** : Publiez des posts, commentez, likez et partagez du contenu en temps réel.
*   **Réseautage** : Système complet de gestion d'amis (envoi, réception et acceptation de demandes).
*   **Messagerie Instantanée** : Discutez en direct avec vos amis via une interface fluide.
*   **Notifications** : Restez informé des nouvelles demandes et interactions.

### 🛡️ Administration & Modération
*   **Back-Office Complet** : Une interface dédiée aux administrateurs pour piloter la plateforme.
*   **Gestion du Contenu** : Modération des utilisateurs, des publications et gestion des signalements.

---

## 🛠️ Stack Technique

*   **Backend** : PHP 8+ (Natif / API)
*   **Base de données** : MySQL (Hébergé sur InfinityFree / Local via XAMPP)
*   **Frontend** : HTML5, CSS3, JavaScript (Vanilla + AJAX)

---

## 🚀 Guide de Démarrage Rapide

Suivez ces étapes pour lancer Linknet sur votre machine locale :

### 1. Configuration de la Base de Données
Le projet utilise deux fichiers de connexion qu'il est impératif de configurer avec vos identifiants :
*   **Client** : `vues/clients/config/database.php`
*   **Admin** : `vues/back-office/config/database.php`

> [!TIP]
> Importez le schéma SQL `db.sql` (présent dans les dossiers config) dans votre base de données avant de tenter la connexion.

### 2. Lancement du Serveur Web
Vous pouvez utiliser **XAMPP/WAMP** (en plaçant le dossier dans `htdocs`) ou lancer le serveur intégré de PHP à la racine du projet :
```bash
php -S localhost:8000
```

### 3. Accès à l'Application
*   **Interface Utilisateur** : Ouvrez [http://localhost:8000](http://localhost:8000). Vous serez automatiquement redirigé vers la page de connexion si vous n'êtes pas authentifié.
*   **Interface Administration** : Accédez au tableau de bord via [http://localhost:8000/vues/back-office/admin/auth/login.php](http://localhost:8000/vues/back-office/admin/auth/login.php).

---

## ⚙️ Gestion du Projet (Git)
Un fichier `.gitignore` a été mis en place pour optimiser le dépôt :
*   **Ignoré** : Les fichiers médias dans `uploads/` (évite d'alourdir le repo) et les fichiers de configuration sensibles.
*   **Conservé** : Les dossiers de structure comme `Posts/` sont maintenus via des fichiers `.gitkeep`.

---

## 👥 L'Équipe du Projet (Groupe 1)

*   **Matinou BELLO** (Bellox1) : Responsable Back-Office.
*   **Denise DAMASSOH** (desse20) : Responsable Clients.
*   **DEGAN Maelle** (Maelle13) : Responsable Assets.
*   **Mohamed SALOU** (Conard12) : Responsable API.

---

## 📄 Licence
Ce projet est sous licence MIT.
