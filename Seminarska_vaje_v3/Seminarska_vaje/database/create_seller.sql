USE eprodajalna;

-- Delete existing sellers to avoid conflicts
DELETE FROM prodajalec;

-- Insert new seller with password: password
INSERT INTO prodajalec (ime, priimek, email, geslo, aktiven) VALUES 
('Janez', 'Prodajalec', 'prodajalec@eprodajalna.si', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', TRUE);
