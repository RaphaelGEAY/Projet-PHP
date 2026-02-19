*Fait par Raphaël GEAY et Hugo AGUER*

# VoitiBox

## Description

Voitibox est un site web qui permet de vendre des voitures de toutes sortes ainsi que d'en acheter, que se soit des voitures à 1 000 € ou bien des voitures à 1 000 000 000 € !

## Fonctionnalités

- Inscription / Connexion / Déconnexion
- Gestion du compte
- Publier une annonce pour vendre sa voiture
- Page d'accueil avec toutes les annonces
- Page de détail de l'annonce
- Système de tri et de filtres pour les annonces
- Ajouter au Panier
- Valider la commande avec facturation
- Historique des achats
- Modifier / Supprimer son annonce
- Administrer les utilisateurs et les annonces (rôle admin)

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

## Comptes de démonstration

- Admin
  - email: `admin@voitibox.local`
  - mot de passe: `admin123`

- Utilisateur vendeur
  - email: `seller@voitibox.local`
  - mot de passe: `user1234`

- Utilisateur acheteur
  - email: `buyer@voitibox.local`
  - mot de passe: `user1234`

