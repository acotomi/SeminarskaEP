-- Create kosarica table if it doesn't exist
CREATE TABLE IF NOT EXISTS kosarica (
    id INT PRIMARY KEY AUTO_INCREMENT,
    stranka_id INT NOT NULL,
    artikel_id INT NOT NULL,
    kolicina INT NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (stranka_id) REFERENCES stranka(id),
    FOREIGN KEY (artikel_id) REFERENCES artikel(id)
);
