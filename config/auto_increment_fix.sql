-- Function to reset auto-increment values and reorder IDs
DELIMITER //

CREATE PROCEDURE IF NOT EXISTS reset_auto_increment(IN table_name VARCHAR(64))
BEGIN
    SET @alter_statement = CONCAT('ALTER TABLE ', table_name, ' AUTO_INCREMENT = 1');
    SET @update_statement = CONCAT(
        'SET @count = 0; ',
        'UPDATE ', table_name, ' SET id = (@count:=@count+1) ORDER BY id;'
    );
    
    PREPARE alter_stmt FROM @alter_statement;
    PREPARE update_stmt FROM @update_statement;
    
    EXECUTE alter_stmt;
    EXECUTE update_stmt;
    
    DEALLOCATE PREPARE alter_stmt;
    DEALLOCATE PREPARE update_stmt;
END //

DELIMITER ;

-- Create trigger for each table to maintain continuous IDs
DELIMITER //

CREATE TRIGGER after_delete_users
    AFTER DELETE ON users
    FOR EACH ROW
    BEGIN
        CALL reset_auto_increment('users');
    END //

CREATE TRIGGER after_delete_appointments
    AFTER DELETE ON appointments
    FOR EACH ROW
    BEGIN
        CALL reset_auto_increment('appointments');
    END //

CREATE TRIGGER after_delete_products
    AFTER DELETE ON products
    FOR EACH ROW
    BEGIN
        CALL reset_auto_increment('products');
    END //

CREATE TRIGGER after_delete_orders
    AFTER DELETE ON orders
    FOR EACH ROW
    BEGIN
        CALL reset_auto_increment('orders');
    END //

DELIMITER ;