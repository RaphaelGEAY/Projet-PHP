# AutoMarket - Projet final PHP E-Commerce

Site e-commerce 100% PHP (sans framework backend) pour vendre/acheter des voitures, de l'entrée de gamme aux modèles de luxe.

## Fonctionnalités implémentées

- `register.php` : création de compte avec unicité `username` + `email` et connexion automatique.
- `login.php` / `logout.php` : authentification.
- `index.php` : home avec liste des annonces, tri et recherche.
- `detail.php?id=...` : détail d'un article, ajout au panier.
- `sell.php` : création d'article + stock.
- `edit.php` : modification/suppression d'article (auteur ou admin uniquement, accès via `POST`).
- `cart/index.php` : panier utilisateur (modifier quantité, supprimer, total).
- `cart/validate.php` : confirmation commande, facturation, génération facture, vidage panier.
- `account.php` / `account.php?user_id=...` :
  - profil utilisateur,
  - articles postés,
  - achats et factures (pour son propre compte),
  - modification infos + ajout de solde.
- `admin/index.php` : tableau administrateur (gestion utilisateurs + articles).

## Base de données

Le fichier **obligatoire pour le rendu** est fourni :

- `php_exam_db.sql`

Il contient :
- schéma complet (`users`, `articles`, `cart`, `stock`, `invoice`, `invoice_items`),
- contraintes d'intégrité,
- données de démonstration (voitures pas chères à voiture à `1 000 000 000 €`).

## Installation rapide (XAMPP/LAMP)

1. Placer le dossier dans `htdocs/php_exam` (ou `/var/www/html/php_exam`).
2. Créer la base via phpMyAdmin en important `php_exam_db.sql`.
3. Vérifier la connexion DB dans `db.php`.

Configuration par défaut dans `db.php` :

- host: `127.0.0.1`
- db: `php_exam_db`
- user: `root`
- pass: `root`

Vous pouvez aussi utiliser des variables d'environnement :
- `DB_HOST`
- `DB_NAME`
- `DB_USER`
- `DB_PASS`

## Utiliser vos propres images

Le projet accepte maintenant :
- uniquement des chemins locaux dans le projet (`assets/images/ma-voiture.jpg`)

Workflow recommandé :
1. Copier vos fichiers image dans `assets/images/`.
2. Depuis `sell.php`, `edit.php` ou `account.php`, renseigner le champ image avec un chemin comme `assets/images/ma-voiture.jpg`.
3. Ouvrir le site et vérifier l'affichage (les fichiers manquants ou à `0` octet ne sont pas affichés).

Pour remplacer rapidement les images de démo déjà en base :

```sql
UPDATE articles SET image_url = 'assets/images/clio.jpg' WHERE id = 1;
UPDATE users SET profile_photo = 'assets/images/profil-admin.jpg' WHERE id = 1;
```

## Comptes de démonstration

- Admin
  - email: `admin@automarket.local`
  - mot de passe: `admin123`

- Utilisateur
  - email: `seller@automarket.local`
  - mot de passe: `user1234`

- Utilisateur
  - email: `buyer@automarket.local`
  - mot de passe: `user1234`

## Arborescence principale

- `includes/bootstrap.php` : chargement session + dépendances
- `includes/helpers.php` : fonctions utilitaires (auth, redirect, format, etc.)
- `includes/layout.php` : header/footer communs
- `assets/style.css` : style global

## Notes

- Backend uniquement en PHP natif.
- Les pages protégées redirigent vers la connexion si nécessaire.
- Les mots de passe sont hashés en bcrypt.
