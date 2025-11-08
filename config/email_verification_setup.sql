ALTER TABLE users
ADD COLUMN IF NOT EXISTS verification_code VARCHAR(6) DEFAULT NULL,
ADD COLUMN IF NOT EXISTS email_verified BOOLEAN DEFAULT FALSE,
ADD COLUMN IF NOT EXISTS verification_expiry TIMESTAMP DEFAULT NULL;

UPDATE users SET email_verified = TRUE WHERE role = 'admin' OR role = 'doctor';