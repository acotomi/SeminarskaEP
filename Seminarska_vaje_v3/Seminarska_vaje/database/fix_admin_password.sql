USE eprodajalna;

-- Delete existing admins
DELETE FROM administrator;

-- Insert new admin with password: admin123
INSERT INTO administrator (ime, priimek, email, geslo, aktiven) VALUES 
('Admin', 'User', 'admin@eprodajalna.si', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', TRUE);
