# Linknet

## Description du projet

Linknet est un réseau social complet permettant aux utilisateurs de :
- Créer un compte, se connecter, gérer leur profil
- Publier des posts, commenter, aimer, partager
- Envoyer et accepter des demandes d’amis
- Discuter en temps réel avec d’autres membres
- Recevoir des notifications (demandes d’amis)

Une interface d’administration permet de gérer les utilisateurs, les contenus, les signalements et la modération.

## Mode de fonctionnement

- **Frontend client** (`vues/clients/`) : Interface utilisateur pour toutes les fonctionnalités sociales.
- **Backend/API** (`api/`) : Fournit les endpoints pour la gestion des utilisateurs, des posts, des messages etc.
- **Back-office admin** (`vues/back-office/admin/`) : Interface de gestion et de modération pour les administrateurs.
- **Base de données** : Schéma SQL dans `vues/back-office/config/db.sql` et `vues/clients/config/db.sql`.



> Tu peux aussi t’inscrire via le formulaire d’inscription (``vues/clients/auth/register.php`).

### Administrateur
- Un compte administrateur doit être créé via la base de données ou le back-office.
- Table concernée : `admins` (voir `db.sql`).


## Installation

1. Cloner le dépôt et placer le dossier dans votre serveur local (ex : XAMPP).
2. Importer le fichier `db.sql` dans votre base de données MySQL.
3. Configurer les accès à la base de données dans `vues/back-office/config/database.php` et `vues/clients/config/database.php`.
4. Accéder à l’interface client via `vues/clients/index.php` et à l’interface admin via `vues/back-office/admin/auth/login.php`.

## Fonctionnalités principales

- Inscription / Connexion / Déconnexion
- Gestion du profil utilisateur
- Publication, édition 
- Commentaires et likes
- Système d’amis et de demandes d’amis
- Messagerie instantanée
- Notifications en temps réel
- Interface d’administration complète

Identifiants test
Client : 
nom d'utilisateur : user
mot de passe : user123

Admin  : 
nom d'utilisateur : admin
mot de passe : admin123



Membres du groupe 1:
- Mantinou  BELLO -> A gérer le dossier back office 
- Denise DAMASSOH -> A gérer le dossier clients
- DEGAN Maelle    -> A gérer le dossier assets
- Mohamed SALOU   -> A gérer le dossier api
