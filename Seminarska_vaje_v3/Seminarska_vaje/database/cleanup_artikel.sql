USE eprodajalna;

-- Delete test data (Artikel 1, 2, 3)
DELETE FROM artikel WHERE naziv LIKE 'Artikel%';

-- Remove duplicates keeping only the first entry for each naziv
DELETE t1 FROM artikel t1
INNER JOIN artikel t2 
WHERE t1.id > t2.id 
AND t1.naziv = t2.naziv;
