USE eprodajalna;

-- First, delete the existing test sellers to avoid duplicates
DELETE FROM prodajalec WHERE email IN ('prodajalec@test.com', 'prodajalec1@test.com');

-- Insert new seller with known password hash (password will be: test123)
INSERT INTO prodajalec (ime, priimek, email, geslo, aktiven) VALUES 
('Testni', 'Prodajalec', 'test.prodajalec@eprodajalna.si', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', TRUE);
