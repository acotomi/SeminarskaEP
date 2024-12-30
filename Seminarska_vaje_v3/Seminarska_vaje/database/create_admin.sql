USE eprodajalna;

-- Delete existing admins to avoid conflicts
DELETE FROM administrator;

-- Insert new admin with password: admin123
INSERT INTO administrator (ime, priimek, email, geslo, aktiven) VALUES 
('Admin', 'User', 'admin@eprodajalna.si', '$2y$10$HY4Hs0kQ9hTYUvCgHKR9/.VgZXYZOGqI3sD0v1P5yaRxGF3QF7.4K', TRUE);
