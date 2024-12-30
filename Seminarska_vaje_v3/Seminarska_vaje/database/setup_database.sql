-- Drop and recreate database
DROP DATABASE IF EXISTS eprodajalna;
CREATE DATABASE eprodajalna CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE eprodajalna;

-- Create tables
CREATE TABLE IF NOT EXISTS administrator (
    id INT PRIMARY KEY AUTO_INCREMENT,
    ime VARCHAR(255) NOT NULL,
    priimek VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    certificate_subject VARCHAR(255),
    certificate_issuer VARCHAR(255),
    certificate_serial VARCHAR(40),
    geslo VARCHAR(255) NOT NULL,
    aktiven BOOLEAN DEFAULT TRUE,
    UNIQUE INDEX idx_certificate (certificate_subject, certificate_issuer, certificate_serial)
);

CREATE TABLE IF NOT EXISTS prodajalec (
    id INT PRIMARY KEY AUTO_INCREMENT,
    ime VARCHAR(255) NOT NULL,
    priimek VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    certificate_subject VARCHAR(255),
    certificate_issuer VARCHAR(255),
    certificate_serial VARCHAR(40),
    geslo VARCHAR(255) NOT NULL,
    aktiven BOOLEAN DEFAULT TRUE,
    UNIQUE INDEX idx_certificate (certificate_subject, certificate_issuer, certificate_serial)
);

CREATE TABLE IF NOT EXISTS stranka (
    id INT PRIMARY KEY AUTO_INCREMENT,
    ime VARCHAR(255) NOT NULL,
    priimek VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    geslo VARCHAR(255) NOT NULL,
    postna_stevilka CHAR(4) NOT NULL,
    aktiven BOOLEAN DEFAULT TRUE
);

CREATE TABLE IF NOT EXISTS artikel (
    id INT PRIMARY KEY AUTO_INCREMENT,
    naziv VARCHAR(255) NOT NULL,
    opis TEXT,
    cena DECIMAL(10,2) NOT NULL,
    aktiven BOOLEAN DEFAULT TRUE
);

CREATE TABLE IF NOT EXISTS kosarica (
    id INT PRIMARY KEY AUTO_INCREMENT,
    stranka_id INT NOT NULL,
    artikel_id INT NOT NULL,
    kolicina INT NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (stranka_id) REFERENCES stranka(id),
    FOREIGN KEY (artikel_id) REFERENCES artikel(id)
);

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
);

CREATE TABLE IF NOT EXISTS narocilo_artikel (
    id INT PRIMARY KEY AUTO_INCREMENT,
    narocilo_id INT NOT NULL,
    artikel_id INT NOT NULL,
    kolicina INT NOT NULL,
    cena_na_kos DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (narocilo_id) REFERENCES narocilo(id),
    FOREIGN KEY (artikel_id) REFERENCES artikel(id)
);

-- Insert test data
INSERT INTO administrator (ime, priimek, email, geslo) VALUES 
('Admin', 'Admin', 'admin@test.com', '$2y$10$ZX3F1qGQsJ3Qv4/EDgkzjOXg9VwZzxA5yJ.mG0nQwOxO.UgZohZGu');

INSERT INTO prodajalec (ime, priimek, email, geslo) VALUES 
('Prodajalec', 'Test', 'prodajalec@test.com', '$2y$10$ZX3F1qGQsJ3Qv4/EDgkzjOXg9VwZzxA5yJ.mG0nQwOxO.UgZohZGu');

INSERT INTO artikel (naziv, opis, cena) VALUES 
('Test Artikel 1', 'Opis artikla 1', 19.99),
('Test Artikel 2', 'Opis artikla 2', 29.99),
('Test Artikel 3', 'Opis artikla 3', 39.99);
