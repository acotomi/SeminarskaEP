-- Drop existing tables if they exist
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS kosarica;
DROP TABLE IF EXISTS artikel;
DROP TABLE IF EXISTS prodajalec;
DROP TABLE IF EXISTS stranka;
DROP TABLE IF EXISTS narocilo_artikel;
DROP TABLE IF EXISTS narocilo;
SET FOREIGN_KEY_CHECKS = 1;

-- Create database
CREATE DATABASE IF NOT EXISTS eprodajalna CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE eprodajalna;

-- Create table for customers (stranka)
CREATE TABLE IF NOT EXISTS stranka (
    id INT PRIMARY KEY AUTO_INCREMENT,
    ime VARCHAR(50) NOT NULL,
    priimek VARCHAR(50) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    geslo VARCHAR(255) NOT NULL,
    postna_stevilka VARCHAR(10),
    naslov VARCHAR(255),
    aktiviran BOOLEAN DEFAULT FALSE,
    aktivacijska_koda VARCHAR(50),
    datum_registracije TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create table for sellers (prodajalec)
CREATE TABLE IF NOT EXISTS prodajalec (
    id INT PRIMARY KEY AUTO_INCREMENT,
    ime VARCHAR(50) NOT NULL,
    priimek VARCHAR(50) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    geslo VARCHAR(255) NOT NULL,
    datum_zaposlitve TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create table for articles (artikel)
CREATE TABLE IF NOT EXISTS artikel (
    id INT PRIMARY KEY AUTO_INCREMENT,
    naziv VARCHAR(100) NOT NULL,
    opis TEXT,
    cena DECIMAL(10,2) NOT NULL,
    zaloga INT NOT NULL DEFAULT 0,
    slika_url VARCHAR(255),
    datum_vnosa TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    zadnja_sprememba TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create table for shopping cart (kosarica)
CREATE TABLE IF NOT EXISTS kosarica (
    id INT PRIMARY KEY AUTO_INCREMENT,
    stranka_id INT NOT NULL,
    artikel_id INT NOT NULL,
    kolicina INT NOT NULL DEFAULT 1,
    datum_dodajanja TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (stranka_id) REFERENCES stranka(id),
    FOREIGN KEY (artikel_id) REFERENCES artikel(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create table for orders (narocilo)
CREATE TABLE IF NOT EXISTS narocilo (
    id INT PRIMARY KEY AUTO_INCREMENT,
    stranka_id INT NOT NULL,
    prodajalec_id INT,
    status ENUM('oddano', 'potrjeno', 'preklicano', 'stornirano') NOT NULL DEFAULT 'oddano',
    datum_oddaje TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    datum_spremembe TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    skupna_cena DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (stranka_id) REFERENCES stranka(id),
    FOREIGN KEY (prodajalec_id) REFERENCES prodajalec(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create table for order items (narocilo_artikel)
CREATE TABLE IF NOT EXISTS narocilo_artikel (
    id INT PRIMARY KEY AUTO_INCREMENT,
    narocilo_id INT NOT NULL,
    artikel_id INT NOT NULL,
    kolicina INT NOT NULL,
    cena_na_kos DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (narocilo_id) REFERENCES narocilo(id) ON DELETE CASCADE,
    FOREIGN KEY (artikel_id) REFERENCES artikel(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Testni podatki za stranke
INSERT INTO stranka (ime, priimek, email, geslo) VALUES
('Janez', 'Novak', 'janez@example.com', '$2y$10$6jf7Z8YYxB4qnyf.YFxB.eqqa4WUUlxjLxwKZpxHRVm1rGHVbxZYy'),
('Ana', 'Kovač', 'ana@example.com', '$2y$10$6jf7Z8YYxB4qnyf.YFxB.eqqa4WUUlxjLxwKZpxHRVm1rGHVbxZYy');

-- Testni podatki za prodajalce
INSERT INTO prodajalec (ime, priimek, email, geslo) VALUES
('Marko', 'Prodajalec', 'marko@trgovina.com', '$2y$10$6jf7Z8YYxB4qnyf.YFxB.eqqa4WUUlxjLxwKZpxHRVm1rGHVbxZYy'),
('Maja', 'Prodajalka', 'maja@trgovina.com', '$2y$10$6jf7Z8YYxB4qnyf.YFxB.eqqa4WUUlxjLxwKZpxHRVm1rGHVbxZYy');

-- Testni podatki za artikle
INSERT INTO artikel (naziv, opis, cena, zaloga) VALUES
('Artikel 1', 'Opis artikla 1', 19.99, 100),
('Artikel 2', 'Opis artikla 2', 29.99, 50),
('Artikel 3', 'Opis artikla 3', 39.99, 75);

-- Testni podatki za košarico
INSERT INTO kosarica (stranka_id, artikel_id, kolicina) VALUES
(1, 1, 2),
(1, 2, 1),
(2, 3, 3);
