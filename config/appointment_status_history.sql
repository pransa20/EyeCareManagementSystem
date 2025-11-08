-- Create appointment status history table
CREATE TABLE IF NOT EXISTS `appointment_status_history` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `appointment_id` INT NOT NULL,
    `old_status` VARCHAR(50),
    `new_status` VARCHAR(50) NOT NULL,
    `status_history` TEXT NOT NULL,
    `changed_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `changed_by` INT,
    `change_reason` TEXT,
    FOREIGN KEY (`appointment_id`) REFERENCES `appointments`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`changed_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Add index for better query performance
CREATE INDEX idx_appointment_status_history_appointment_id ON appointment_status_history(appointment_id);
CREATE INDEX idx_appointment_status_history_changed_at ON appointment_status_history(changed_at);