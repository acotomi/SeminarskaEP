USE eprodajalna;

-- Disable foreign key checks
SET FOREIGN_KEY_CHECKS = 0;

-- Drop and recreate artikel table
DROP TABLE IF EXISTS artikel;

-- Create table for articles (artikel)
CREATE TABLE artikel (
    id INT PRIMARY KEY AUTO_INCREMENT,
    naziv VARCHAR(100) NOT NULL,
    opis TEXT,
    cena DECIMAL(10, 2) NOT NULL,
    aktiven BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert test products
INSERT INTO artikel (naziv, opis, cena, aktiven) VALUES
('Prenosni računalnik Lenovo ThinkPad', 'Zmogljiv prenosni računalnik s procesorjem Intel i7, 16GB RAM in 512GB SSD.', 999.99, TRUE),
('Gaming miška Logitech G502', 'Profesionalna gaming miška z natančnim senzorjem in programabilnimi gumbi.', 79.99, TRUE),
('Mehanska tipkovnica Ducky One 2', 'RGB mehanska tipkovnica s Cherry MX Blue stikali in PBT kapicami.', 129.99, TRUE),
('Monitor Dell 27"', '27-palčni IPS monitor z 2K ločljivostjo in 144Hz osveževanjem.', 349.99, TRUE),
('Slušalke Sony WH-1000XM4', 'Brezžične slušalke z aktivnim dušenjem hrupa in dolgo življenjsko dobo baterije.', 299.99, TRUE),
('Grafična kartica RTX 3060', 'NVIDIA RTX 3060 12GB grafična kartica za gaming in zahtevno delo.', 399.99, TRUE),
('SSD disk Samsung 1TB', 'Hiter in zanesljiv NVMe SSD disk s kapaciteto 1TB.', 119.99, TRUE),
('Spletna kamera Logitech C920', 'Full HD spletna kamera z avtomatskim fokusiranjem in stereo mikrofonoma.', 89.99, TRUE),
('Mrežni usmerjevalnik ASUS', 'WiFi 6 usmerjevalnik s podporo za hitrosti do 1Gbps.', 159.99, TRUE),
('Zunanji disk WD 2TB', 'Prenosni zunanji disk s kapaciteto 2TB in USB 3.0 povezavo.', 79.99, TRUE);

-- Re-enable foreign key checks
SET FOREIGN_KEY_CHECKS = 1;
