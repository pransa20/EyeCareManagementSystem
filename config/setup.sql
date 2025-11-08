-- Create website_content table if not exists
CREATE TABLE IF NOT EXISTS website_content (
    id INT AUTO_INCREMENT PRIMARY KEY,
    section_name VARCHAR(100) UNIQUE NOT NULL,
    content TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create admin user if not exists
INSERT INTO users (email, password, role, name, phone, email_verified)
SELECT 'admin@admin.com', '$2y$10$Hs4Hs9Hs8Hs7Hs6Hs5Hs.Hs4Hs3Hs2Hs1Hs0HsZHs9Hs8Hs7Hs6', 'admin', 'Admin', '', 1
WHERE NOT EXISTS (
    SELECT 1 FROM users WHERE email = 'admin@admin.com'
);