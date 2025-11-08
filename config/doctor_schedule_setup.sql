CREATE TABLE IF NOT EXISTS doctor_schedules (
    id INT PRIMARY KEY AUTO_INCREMENT,
    doctor_id INT NOT NULL,
    day_of_week TINYINT NOT NULL COMMENT '0=Sunday, 1=Monday, ..., 6=Saturday',
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    break_start TIME,
    break_end TIME,
    is_available BOOLEAN DEFAULT TRUE,
    slot_duration INT DEFAULT 60 COMMENT 'Duration in minutes',
    FOREIGN KEY (doctor_id) REFERENCES users(id),
    UNIQUE KEY unique_doctor_day (doctor_id, day_of_week)
);

-- Insert default schedules for existing doctors
INSERT IGNORE INTO doctor_schedules (doctor_id, day_of_week, start_time, end_time, break_start, break_end)
SELECT 
    id as doctor_id,
    day_of_week,
    '09:00' as start_time,
    '17:00' as end_time,
    '13:00' as break_start,
    '14:00' as break_end
FROM users
CROSS JOIN (SELECT 1 as day_of_week UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5) as days
WHERE role = 'doctor';