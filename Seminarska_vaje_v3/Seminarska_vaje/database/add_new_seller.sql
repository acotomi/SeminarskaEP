USE eprodajalna;

-- Insert new seller (password will be: test123)
INSERT INTO prodajalec (ime, priimek, email, geslo, aktiven) VALUES 
('Prodajalec', 'Test', 'prodajalec@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', TRUE);
