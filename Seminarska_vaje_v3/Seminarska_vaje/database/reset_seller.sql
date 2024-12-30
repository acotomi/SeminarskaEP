USE eprodajalna;

-- Delete existing sellers
DELETE FROM prodajalec;

-- Insert new test seller with password: prodajalec123
INSERT INTO prodajalec (ime, priimek, email, geslo, aktiven) VALUES 
('Test', 'Prodajalec', 'prodajalec@eprodajalna.si', '$2y$10$HY4Hs0kQ9hTYUvCgHKR9/.VgZXYZOGqI3sD0v1P5yaRxGF3QF7.4K', TRUE);
