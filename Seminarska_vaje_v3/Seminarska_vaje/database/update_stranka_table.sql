-- Add telefon and naslov columns to stranka table if they don't exist
ALTER TABLE stranka
ADD COLUMN IF NOT EXISTS telefon VARCHAR(20) NULL DEFAULT NULL,
ADD COLUMN IF NOT EXISTS naslov TEXT NULL DEFAULT NULL;
