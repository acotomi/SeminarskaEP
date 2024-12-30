USE eprodajalna;

CREATE TABLE IF NOT EXISTS narocilo (
    id INT PRIMARY KEY AUTO_INCREMENT,
    stranka_id INT NOT NULL,
    datum_narocila DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    status ENUM('novo', 'potrjeno', 'preklicano', 'zakljuceno') NOT NULL DEFAULT 'novo',
    skupna_cena DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    FOREIGN KEY (stranka_id) REFERENCES stranka(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS narocilo_artikel (
    narocilo_id INT NOT NULL,
    artikel_id INT NOT NULL,
    kolicina INT NOT NULL DEFAULT 1,
    cena_na_kos DECIMAL(10,2) NOT NULL,
    PRIMARY KEY (narocilo_id, artikel_id),
    FOREIGN KEY (narocilo_id) REFERENCES narocilo(id),
    FOREIGN KEY (artikel_id) REFERENCES artikel(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
