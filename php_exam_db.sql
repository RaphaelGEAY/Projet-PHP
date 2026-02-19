-- Projet final PHP E-Commerce - VoitiBox
-- Importez ce fichier dans phpMyAdmin pour générer la base complète.

CREATE DATABASE IF NOT EXISTS php_exam_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE php_exam_db;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS invoice_items;
DROP TABLE IF EXISTS invoice;
DROP TABLE IF EXISTS cart;
DROP TABLE IF EXISTS stock;
DROP TABLE IF EXISTS articles;
DROP TABLE IF EXISTS users;
SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(80) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(190) NOT NULL UNIQUE,
    balance DECIMAL(15,2) NOT NULL DEFAULT 0,
    profile_photo VARCHAR(255) NULL,
    role ENUM('user', 'admin') NOT NULL DEFAULT 'user',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE articles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(160) NOT NULL,
    description TEXT NOT NULL,
    price DECIMAL(15,2) NOT NULL,
    published_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    author_id INT NOT NULL,
    image_url VARCHAR(255) NULL,
    CONSTRAINT fk_articles_author
        FOREIGN KEY (author_id) REFERENCES users(id)
        ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE stock (
    id INT AUTO_INCREMENT PRIMARY KEY,
    article_id INT NOT NULL UNIQUE,
    quantity INT NOT NULL DEFAULT 0,
    CONSTRAINT fk_stock_article
        FOREIGN KEY (article_id) REFERENCES articles(id)
        ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE cart (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    article_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    UNIQUE KEY uniq_user_article (user_id, article_id),
    CONSTRAINT fk_cart_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE,
    CONSTRAINT fk_cart_article
        FOREIGN KEY (article_id) REFERENCES articles(id)
        ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE invoice (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    transaction_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    amount DECIMAL(15,2) NOT NULL,
    billing_address VARCHAR(255) NOT NULL,
    billing_city VARCHAR(120) NOT NULL,
    billing_postal_code VARCHAR(20) NOT NULL,
    CONSTRAINT fk_invoice_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE invoice_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    invoice_id INT NOT NULL,
    article_id INT NULL,
    article_name VARCHAR(160) NOT NULL,
    unit_price DECIMAL(15,2) NOT NULL,
    quantity INT NOT NULL,
    CONSTRAINT fk_invoice_items_invoice
        FOREIGN KEY (invoice_id) REFERENCES invoice(id)
        ON DELETE CASCADE,
    CONSTRAINT fk_invoice_items_article
        FOREIGN KEY (article_id) REFERENCES articles(id)
        ON DELETE SET NULL
) ENGINE=InnoDB;

INSERT INTO users (id, username, password, email, balance, profile_photo, role, created_at) VALUES
(1, 'admin', '$2y$10$wF1WJECjfEC4HfwqKs6hAOD7HRhfq/0WpNYOSuDgpwQE3ASGDNRIy',  'admin@voitibox.local', 5000000000.00, 'assets/images/profiles/20260219-015810-1c31de4504123b4f.webp', 'admin', '2026-02-10 10:00:00'),
(2, 'seller_pro', '$2y$10$469Zo2jDYxO29RUodLeiW.lt8r8RlRx4mUxtCNDgdCeDex7mJXAey', 'seller@voitibox.local', 15000.00, 'assets/images/profiles/20260219-015959-589e335d29fc42d9.png', 'user', '2026-02-11 11:00:00'),
(3, 'buyer_demo', '$2y$10$469Zo2jDYxO29RUodLeiW.lt8r8RlRx4mUxtCNDgdCeDex7mJXAey', 'buyer@voitibox.local', 1200000.00, 'assets/images/profiles/20260219-020025-2d920606a55fc2cf.png', 'user', '2026-02-12 12:00:00');

-- Mot de passe admin: admin123
-- Mot de passe utilisateurs de démo: user1234

INSERT INTO articles (id, title, description, price, published_at, author_id, image_url) VALUES
(1, 'Renault Clio 2012', 'Citadine fiable, faible consommation, idéale premier achat.', 4900.00, '2026-02-15 11:20:00', 2, 'assets/images/cars/20260218-130014-2f2905ed500b54cb.jpg'),
(2, 'Peugeot 208 2016', 'Compacte essence, bon état, carnet d\'entretien à jour.', 8900.00, '2026-02-12 16:45:00', 2, 'assets/images/cars/20260219-013922-8e9dd6612df5292c.jpg'),
(3, 'Tesla Model 3 Long Range', 'Berline électrique, autonomie élevée, autopilot inclus.', 42990.00, '2026-02-17 09:05:00', 2, 'assets/images/cars/20260219-014042-288e3fcaefe0289e.webp'),
(4, 'BMW M3 Competition', 'Performance sportive, pack carbone, historique clair.', 98900.00, '2026-02-11 13:40:00', 2, 'assets/images/cars/20260219-014127-91bccd748ef7ef7c.webp'),
(5, 'Porsche 911 GT3', 'Coupé iconique, atmosphérique, état collection.', 235000.00, '2026-02-16 18:30:00', 2, 'assets/images/cars/20260219-014156-653cd2616676d114.webp'),
(6, 'Ferrari SF90 Stradale', 'Hybride haute performance, configuration personnalisée.', 520000.00, '2026-02-13 08:55:00', 2, 'assets/images/cars/20260219-014244-0ac01f1e98263e66.jpg'),
(7, 'Bugatti Chiron Super Sport', 'Hypercar 1600 ch, production ultra limitée.', 3900000.00, '2026-02-18 12:15:00', 2, 'assets/images/cars/20260219-014441-67c2a5358f7a38c0.webp'),
(8, 'Rolls-Royce Boat Tail', 'Luxe artisanal extrême, finition sur mesure.', 28000000.00, '2026-02-10 19:10:00', 2, 'assets/images/cars/20260219-014513-fd5997b98f7328d6.jpg'),
(9, 'Hyperion Imperium One-Off', 'Concept unique en diamant noir, pièce de collection ultime.', 1000000000.00, '2026-02-14 07:35:00', 1, 'assets/images/cars/20260219-014537-9a6fcf88b3f7ad64.webp');

INSERT INTO stock (article_id, quantity) VALUES
(1, 12),
(2, 8),
(3, 5),
(4, 3),
(5, 2),
(6, 2),
(7, 1),
(8, 1),
(9, 1);
