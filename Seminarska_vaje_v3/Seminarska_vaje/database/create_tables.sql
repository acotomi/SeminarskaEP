-- Create kosarica table
CREATE TABLE IF NOT EXISTS kosarica (
    id INT PRIMARY KEY AUTO_INCREMENT,
    stranka_id INT NOT NULL,
    artikel_id INT NOT NULL,
    kolicina INT NOT NULL DEFAULT 1,
    datum_dodajanja TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (stranka_id) REFERENCES stranka(id),
    FOREIGN KEY (artikel_id) REFERENCES artikel(id)
);

-- Create narocilo table
CREATE TABLE IF NOT EXISTS narocilo (
    id INT PRIMARY KEY AUTO_INCREMENT,
    stranka_id INT NOT NULL,
    datum_oddaje TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('oddano', 'v obdelavi', 'poslano', 'zakljuceno', 'preklicano') NOT NULL DEFAULT 'oddano',
    skupna_cena DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (stranka_id) REFERENCES stranka(id)
);

-- Create narocilo_artikel table
CREATE TABLE IF NOT EXISTS narocilo_artikel (
    id INT PRIMARY KEY AUTO_INCREMENT,
    narocilo_id INT NOT NULL,
    artikel_id INT NOT NULL,
    kolicina INT NOT NULL,
    cena_na_kos DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (narocilo_id) REFERENCES narocilo(id),
    FOREIGN KEY (artikel_id) REFERENCES artikel(id)
);
