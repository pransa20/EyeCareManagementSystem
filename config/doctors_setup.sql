-- Create doctors table if not exists
CREATE TABLE IF NOT EXISTS doctors (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    specialization VARCHAR(100) NOT NULL,
    schedule TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Insert sample doctor data
INSERT INTO doctors (user_id, specialization) 
SELECT id, 'General Ophthalmologist' 
FROM users 
WHERE role = 'doctor' 
AND NOT EXISTS (SELECT 1 FROM doctors WHERE doctors.user_id = users.id);