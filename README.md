# VoitiBox - Projet final PHP E-Commerce

Application e-commerce 100% PHP natif (sans framework backend), développée pour le projet final du module PHP.

## Objectif

VoitiBox permet de:
- créer un compte,
- publier des voitures,
- consulter les annonces,
- gérer un panier,
- valider une commande avec facturation,
- administrer les utilisateurs et les annonces (rôle admin).

## Stack technique

- PHP 8+
- MySQL / MariaDB
- PDO
- HTML/CSS
- Aucune techno backend autre que PHP (conforme au sujet)

## Structure des routes

Routes principales disponibles:
- `/` (Accueil)
- `/login/`
- `/register/`
- `/sell/`
- `/detail/?id=...`
- `/cart/`
- `/cart/validate/`
- `/edit/` (accès en POST depuis un article)
- `/account/` et `/account/?user_id=...`
- `/admin/` (admin uniquement)
- `/logout/`

Compatibilité conservée: les fichiers historiques `*.php` existent toujours et restent fonctionnels.

## Fonctionnalités couvertes (cahier des charges)

- Authentification:
  - inscription (`username` + `email` uniques),
  - connexion,
  - connexion automatique après inscription,
  - mots de passe hashés en bcrypt.
- Home:
  - liste des annonces,
  - plus récentes en premier,
  - tri/recherche (bonus).
- Vente (`/sell/`):
  - création d'annonce,
  - création du stock associé.
- Détail (`/detail/`):
  - affichage complet,
  - ajout au panier avec quantité,
  - contrôle du stock.
- Panier (`/cart/`):
  - affichage des lignes,
  - modification quantité,
  - suppression,
  - total et contrôle du solde.
- Confirmation (`/cart/validate/`):
  - informations de facturation,
  - validation si stock + solde OK,
  - décrément du stock,
  - génération facture,
  - vidage panier.
- Edition (`/edit/`):
  - modif/suppression d'article,
  - accès limité à l'auteur ou admin,
  - entrée attendue en POST.
- Compte (`/account/`):
  - vue d'un autre utilisateur: infos + articles publiés,
  - vue de son propre compte: achats, factures, modification profil, ajout de solde.
- Administration (`/admin/`):
  - accès admin uniquement,
  - gestion utilisateurs (modifier/supprimer),
  - gestion articles (modifier/supprimer).
- Contrôle d'accès:
  - non connecté: accès à Home + Detail seulement,
  - autres pages: redirection vers `/login/`.

## Base de données

Le fichier SQL requis par le rendu est bien présent:
- `php_exam_db.sql`

Contenu du SQL:
- création de la DB `php_exam_db`,
- tables: `users`, `articles`, `cart`, `stock`, `invoice`, `invoice_items`,
- contraintes d'intégrité (FK, unicité),
- données de démonstration (utilisateurs + annonces + stocks).

Remarque importante:
- les chemins d'images seedés dans `php_exam_db.sql` pointent vers des fichiers réellement présents dans le dépôt (`assets/images/cars/...`, `assets/images/profiles/...`).

## Installation locale (XAMPP/LAMP/MAMP)

1. Placer le projet dans:
- XAMPP: `htdocs/php_exam`
- LAMP (Linux): `/var/www/html/php_exam`
- MAMP: dossier web MAMP

2. Démarrer Apache + MySQL.

3. Créer/importer la base:
- ouvrir phpMyAdmin,
- créer ou sélectionner `php_exam_db`,
- importer `php_exam_db.sql`.

4. Vérifier la config DB (`db.php`):
- `DB_HOST` (défaut: `127.0.0.1`)
- `DB_NAME` (défaut: `php_exam_db`)
- `DB_USER` (défaut: `root`)
- `DB_PASS` (défaut: `root`)

5. Accéder à l'application:
- `http://localhost/php_exam/`
- MAMP (port par défaut): `http://localhost:8888/php_exam/`

## Comptes de démo

- Admin
  - email: `admin@voitibox.local`
  - mot de passe: `admin123`

- Utilisateur vendeur
  - email: `seller@voitibox.local`
  - mot de passe: `user1234`

- Utilisateur acheteur
  - email: `buyer@voitibox.local`
  - mot de passe: `user1234`

## Arborescence utile

- `includes/bootstrap.php`: init session + chargement global
- `includes/helpers.php`: helpers auth, redirections, utilitaires
- `includes/layout.php`: layout partagé
- `cart/index.php`: page panier
- `cart/validate.php`: validation de commande
- `admin/index.php`: back-office admin
- `php_exam_db.sql`: script SQL de rendu

## Vérifications recommandées avant rendu

1. Importer `php_exam_db.sql` sur un environnement propre.
2. Tester les routes:
- `/`, `/login/`, `/register/`, `/sell/`, `/detail/?id=1`, `/cart/`, `/cart/validate/`, `/account/`, `/admin/`.
3. Vérifier qu'un utilisateur non connecté est redirigé vers `/login/` sur les pages protégées.
4. Vérifier le flux complet:
- inscription -> connexion auto,
- création annonce,
- ajout panier,
- validation commande,
- facture visible dans `/account/`.
5. Vérifier le compte admin sur `/admin/`.

## Conformité rendu

- Projet versionné Git.
- README explicatif présent.
- `php_exam_db.sql` présent (exigence explicite du sujet, sinon malus).

