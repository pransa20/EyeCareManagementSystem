CREATE TABLE IF NOT EXISTS settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    setting_key VARCHAR(255) NOT NULL UNIQUE,
    setting_value TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert default settings
INSERT IGNORE INTO settings (setting_key, setting_value) VALUES
('payment_gateway_api_key', ''),
('payment_gateway_merchant_id', ''),
('smtp_host', ''),
('smtp_port', ''),
('smtp_username', ''),
('smtp_password', '');