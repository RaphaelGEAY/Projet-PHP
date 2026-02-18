-- Projet final PHP E-Commerce - AutoMarket
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
(1, 'admin', '$2y$10$wF1WJECjfEC4HfwqKs6hAOD7HRhfq/0WpNYOSuDgpwQE3ASGDNRIy',  'admin@automarket.local', 5000000000.00, 'https://images.unsplash.com/photo-1599566150163-29194dcaad36?auto=format&fit=crop&w=400&q=60', 'admin', '2026-02-10 10:00:00'),
(2, 'seller_pro', '$2y$10$469Zo2jDYxO29RUodLeiW.lt8r8RlRx4mUxtCNDgdCeDex7mJXAey', 'seller@automarket.local', 15000.00, 'https://images.unsplash.com/photo-1544723795-3fb6469f5b39?auto=format&fit=crop&w=400&q=60', 'user', '2026-02-11 11:00:00'),
(3, 'buyer_demo', '$2y$10$469Zo2jDYxO29RUodLeiW.lt8r8RlRx4mUxtCNDgdCeDex7mJXAey', 'buyer@automarket.local', 1200000.00, 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&w=400&q=60', 'user', '2026-02-12 12:00:00');

-- Mot de passe admin: admin123
-- Mot de passe utilisateurs de démo: user1234

INSERT INTO articles (id, title, description, price, published_at, author_id, image_url) VALUES
(1, 'Renault Clio 2012', 'Citadine fiable, faible consommation, idéale premier achat.', 4900.00, '2026-02-12 09:10:00', 2, 'https://images.unsplash.com/photo-1549921296-3b6b6c7f53cb?auto=format&fit=crop&w=900&q=60'),
(2, 'Peugeot 208 2016', 'Compacte essence, bon état, carnet d\'entretien à jour.', 8900.00, '2026-02-12 09:15:00', 2, 'https://images.unsplash.com/photo-1553440569-bcc63803a83d?auto=format&fit=crop&w=900&q=60'),
(3, 'Tesla Model 3 Long Range', 'Berline électrique, autonomie élevée, autopilot inclus.', 42990.00, '2026-02-12 10:30:00', 2, 'https://images.unsplash.com/photo-1560958089-b8a1929cea89?auto=format&fit=crop&w=900&q=60'),
(4, 'BMW M3 Competition', 'Performance sportive, pack carbone, historique clair.', 98900.00, '2026-02-13 08:45:00', 2, 'https://images.unsplash.com/photo-1552519507-da3b142c6e3d?auto=format&fit=crop&w=900&q=60'),
(5, 'Porsche 911 GT3', 'Coupé iconique, atmosphérique, état collection.', 235000.00, '2026-02-13 14:20:00', 2, 'https://images.unsplash.com/photo-1617814076367-b759c7d7e738?auto=format&fit=crop&w=900&q=60'),
(6, 'Ferrari SF90 Stradale', 'Hybride haute performance, configuration personnalisée.', 520000.00, '2026-02-14 10:10:00', 2, 'https://images.unsplash.com/photo-1592198084033-aade902d1aae?auto=format&fit=crop&w=900&q=60'),
(7, 'Bugatti Chiron Super Sport', 'Hypercar 1600 ch, production ultra limitée.', 3900000.00, '2026-02-14 12:40:00', 2, 'https://images.unsplash.com/photo-1511919884226-fd3cad34687c?auto=format&fit=crop&w=900&q=60'),
(8, 'Rolls-Royce Boat Tail', 'Luxe artisanal extrême, finition sur mesure.', 28000000.00, '2026-02-15 09:05:00', 2, 'https://images.unsplash.com/photo-1503376780353-7e6692767b70?auto=format&fit=crop&w=900&q=60'),
(9, 'Hyperion Imperium One-Off', 'Concept unique en diamant noir, pièce de collection ultime.', 1000000000.00, '2026-02-16 16:00:00', 1, 'https://images.unsplash.com/photo-1492144534655-ae79c964c9d7?auto=format&fit=crop&w=900&q=60');

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
