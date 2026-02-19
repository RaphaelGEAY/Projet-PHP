-- phpMyAdmin SQL Dump
-- version 5.1.1deb5ubuntu1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : jeu. 19 fév. 2026 à 19:00
-- Version du serveur : 8.0.45-0ubuntu0.22.04.1
-- Version de PHP : 8.1.2-1ubuntu2.23

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `php_exam_db`
--

CREATE DATABASE IF NOT EXISTS `php_exam_db`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;
USE `php_exam_db`;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS `invoice_items`;
DROP TABLE IF EXISTS `invoice`;
DROP TABLE IF EXISTS `cart`;
DROP TABLE IF EXISTS `stock`;
DROP TABLE IF EXISTS `articles`;
DROP TABLE IF EXISTS `users`;
SET FOREIGN_KEY_CHECKS = 1;

-- --------------------------------------------------------

--
-- Structure de la table `articles`
--

CREATE TABLE `articles` (
  `id` int NOT NULL,
  `title` varchar(160) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `price` decimal(15,2) NOT NULL,
  `published_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `author_id` int NOT NULL,
  `image_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `articles`
--

INSERT INTO `articles` (`id`, `title`, `description`, `price`, `published_at`, `author_id`, `image_url`) VALUES
(1, 'Renault Clio 2012', 'Citadine fiable, faible consommation, ideale premier achat.', '4900.00', '2026-02-15 11:20:00', 2, 'assets/images/cars/20260218-130014-2f2905ed500b54cb.jpg'),
(2, 'Peugeot 208 2016', 'Compacte essence, bon etat, carnet d\'entretien a jour.', '8900.00', '2026-02-12 16:45:00', 2, 'assets/images/cars/20260219-013922-8e9dd6612df5292c.jpg'),
(3, 'Tesla Model 3 Long Range', 'Berline electrique, autonomie elevee, autopilot inclus.', '42990.00', '2026-02-17 09:05:00', 2, 'assets/images/cars/20260219-014042-288e3fcaefe0289e.webp'),
(4, 'BMW M3 Competition', 'Performance sportive, pack carbone, historique clair.', '98900.00', '2026-02-11 13:40:00', 2, 'assets/images/cars/20260219-014127-91bccd748ef7ef7c.webp'),
(5, 'Porsche 911 GT3', 'Coupe iconique, atmospherique, etat collection.', '235000.00', '2026-02-16 18:30:00', 2, 'assets/images/cars/20260219-014156-653cd2616676d114.webp'),
(6, 'Ferrari SF90 Stradale', 'Hybride haute performance, configuration personnalisee.', '520000.00', '2026-02-13 08:55:00', 2, 'assets/images/cars/20260219-014244-0ac01f1e98263e66.jpg'),
(7, 'Bugatti Chiron Super Sport', 'Hypercar 1600 ch, production ultra limitee.', '3900000.00', '2026-02-18 12:15:00', 2, 'assets/images/cars/20260219-014441-67c2a5358f7a38c0.webp'),
(8, 'Rolls-Royce Boat Tail', 'Luxe artisanal extreme, finition sur mesure.', '28000000.00', '2026-02-10 19:10:00', 2, 'assets/images/cars/20260219-014513-fd5997b98f7328d6.jpg'),
(9, 'Hyperion Imperium One-Off', 'Concept unique en diamant noir, piece de collection ultime.', '1000000000.00', '2026-02-14 07:35:00', 1, 'assets/images/cars/20260219-014537-9a6fcf88b3f7ad64.webp');

-- --------------------------------------------------------

--
-- Structure de la table `cart`
--

CREATE TABLE `cart` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `article_id` int NOT NULL,
  `quantity` int NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `invoice`
--

CREATE TABLE `invoice` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `transaction_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `amount` decimal(15,2) NOT NULL,
  `billing_address` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `billing_city` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `billing_postal_code` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `invoice_items`
--

CREATE TABLE `invoice_items` (
  `id` int NOT NULL,
  `invoice_id` int NOT NULL,
  `article_id` int DEFAULT NULL,
  `article_name` varchar(160) COLLATE utf8mb4_unicode_ci NOT NULL,
  `unit_price` decimal(15,2) NOT NULL,
  `quantity` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `stock`
--

CREATE TABLE `stock` (
  `id` int NOT NULL,
  `article_id` int NOT NULL,
  `quantity` int NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `stock`
--

INSERT INTO `stock` (`id`, `article_id`, `quantity`) VALUES
(1, 1, 12),
(2, 2, 8),
(3, 3, 5),
(4, 4, 3),
(5, 5, 2),
(6, 6, 2),
(7, 7, 1),
(8, 8, 1),
(9, 9, 1);

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `username` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `balance` decimal(15,2) NOT NULL DEFAULT '0.00',
  `profile_photo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `role` enum('user','admin') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'user',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `email`, `balance`, `profile_photo`, `role`, `created_at`) VALUES
(1, 'admin', '$2y$10$wF1WJECjfEC4HfwqKs6hAOD7HRhfq/0WpNYOSuDgpwQE3ASGDNRIy', 'admin@voitibox.local', '5000000000.00', 'assets/images/profiles/20260219-015810-1c31de4504123b4f.webp', 'admin', '2026-02-10 10:00:00'),
(2, 'seller_pro', '$2y$10$469Zo2jDYxO29RUodLeiW.lt8r8RlRx4mUxtCNDgdCeDex7mJXAey', 'seller@voitibox.local', '15000.00', 'assets/images/profiles/20260219-015959-589e335d29fc42d9.png', 'user', '2026-02-11 11:00:00'),
(3, 'buyer_demo', '$2y$10$469Zo2jDYxO29RUodLeiW.lt8r8RlRx4mUxtCNDgdCeDex7mJXAey', 'buyer@voitibox.local', '1200000.00', 'assets/images/profiles/20260219-020025-2d920606a55fc2cf.png', 'user', '2026-02-12 12:00:00');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `articles`
--
ALTER TABLE `articles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_articles_author` (`author_id`);

--
-- Index pour la table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_user_article` (`user_id`,`article_id`),
  ADD KEY `fk_cart_article` (`article_id`);

--
-- Index pour la table `invoice`
--
ALTER TABLE `invoice`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_invoice_user` (`user_id`);

--
-- Index pour la table `invoice_items`
--
ALTER TABLE `invoice_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_invoice_items_invoice` (`invoice_id`),
  ADD KEY `fk_invoice_items_article` (`article_id`);

--
-- Index pour la table `stock`
--
ALTER TABLE `stock`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `article_id` (`article_id`);

--
-- Index pour la table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `articles`
--
ALTER TABLE `articles`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT pour la table `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `invoice`
--
ALTER TABLE `invoice`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `invoice_items`
--
ALTER TABLE `invoice_items`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `stock`
--
ALTER TABLE `stock`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT pour la table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `articles`
--
ALTER TABLE `articles`
  ADD CONSTRAINT `fk_articles_author` FOREIGN KEY (`author_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `fk_cart_article` FOREIGN KEY (`article_id`) REFERENCES `articles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_cart_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `invoice`
--
ALTER TABLE `invoice`
  ADD CONSTRAINT `fk_invoice_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `invoice_items`
--
ALTER TABLE `invoice_items`
  ADD CONSTRAINT `fk_invoice_items_article` FOREIGN KEY (`article_id`) REFERENCES `articles` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_invoice_items_invoice` FOREIGN KEY (`invoice_id`) REFERENCES `invoice` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `stock`
--
ALTER TABLE `stock`
  ADD CONSTRAINT `fk_stock_article` FOREIGN KEY (`article_id`) REFERENCES `articles` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
