-- Add certificate fields to administrator table
ALTER TABLE administrator
ADD COLUMN certificate_subject VARCHAR(255) NOT NULL AFTER email,
ADD COLUMN certificate_issuer VARCHAR(255) NOT NULL AFTER certificate_subject,
ADD COLUMN certificate_serial VARCHAR(40) NOT NULL AFTER certificate_issuer,
ADD UNIQUE INDEX idx_certificate (certificate_subject, certificate_issuer, certificate_serial);

-- Add aktiven field for soft delete functionality
ALTER TABLE administrator
ADD COLUMN aktiven BOOLEAN DEFAULT TRUE AFTER geslo;
