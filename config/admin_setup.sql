-- Create admin user if not exists
INSERT INTO users (email, password, role, name, phone, email_verified)
SELECT 'admin@admin.com', '$2y$10$YourHashedPasswordHere', 'admin', 'Admin', '', 1
WHERE NOT EXISTS (
    SELECT 1 FROM users WHERE email = 'admin@admin.com'
);