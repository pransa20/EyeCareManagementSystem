<?php
class Cart {
    private $conn;
    private $user_id;

    public function __construct($user_id = null) {
        global $conn;
        $this->conn = $conn;
        $this->user_id = $user_id;

        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
        
        // Initialize cart from database for logged-in users
        if ($this->user_id) {
            try {
                $stmt = $this->conn->prepare("SELECT p.id as product_id, p.name, p.price, p.stock as stock_quantity, c.quantity 
                    FROM shopping_cart c 
                    JOIN products p ON c.product_id = p.id 
                    WHERE c.user_id = ?");
                $stmt->bind_param("i", $this->user_id);
                $stmt->execute();
                $result = $stmt->get_result();
            } catch (mysqli_sql_exception $e) {
                error_log("Database error: " . $e->getMessage());
                return ['success' => false, 'message' => 'Database connection error'];
            }

            $_SESSION['cart'] = [];
            while ($row = $result->fetch_assoc()) {
                if ($row['quantity'] > $row['stock_quantity']) {
                    $row['quantity'] = $row['stock_quantity'];
                    // Update database with corrected quantity
                    $update_stmt = $this->conn->prepare("UPDATE shopping_cart SET quantity = ? WHERE user_id = ? AND product_id = ?");
                    $update_stmt->bind_param("iii", $row['quantity'], $this->user_id, $row['product_id']);
                    $update_stmt->execute();
                }
                $_SESSION['cart'][] = $row;
            }
        }
    }

    public function addItem($product_id, $quantity = 1) {
        // Validate input parameters
        if (!is_numeric($product_id) || $product_id <= 0) {
            return ['success' => false, 'message' => 'Invalid product ID'];
        }
        if (!is_numeric($quantity) || $quantity <= 0) {
            return ['success' => false, 'message' => 'Invalid quantity'];
        }

        // Get product details
        try {
            $stmt = $this->conn->prepare("SELECT id, name, price, stock as stock_quantity FROM products WHERE id = ?");
            $stmt->bind_param("i", $product_id);
        } catch (mysqli_sql_exception $e) {
            error_log("Database error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Database connection error'];
        }
        $stmt->execute();
        $result = $stmt->get_result();

        if ($product = $result->fetch_assoc()) {
            if ($product['stock_quantity'] < $quantity) {
                return ['success' => false, 'message' => 'Not enough stock available'];
            }

            $cart_item = [
                'product_id' => $product['id'],
                'name' => $product['name'],
                'price' => $product['price'],
                'quantity' => $quantity
            ];

            // Check if product already exists in cart
            $found = false;
            foreach ($_SESSION['cart'] as &$item) {
                if ($item['product_id'] == $product_id) {
                    $new_quantity = $item['quantity'] + $quantity;
                    if ($new_quantity > $product['stock_quantity']) {
                        return ['success' => false, 'message' => 'Not enough stock available'];
                    }
                    $item['quantity'] = $new_quantity;
                    $found = true;
                    break;
                }
            }

            if (!$found) {
                $_SESSION['cart'][] = $cart_item;
            }

            // Sync with database for logged-in users
            if ($this->user_id) {
                if ($found) {
                    try {
                        $stmt = $this->conn->prepare("UPDATE shopping_cart SET quantity = ? WHERE user_id = ? AND product_id = ?");
                        $new_quantity = $cart_item['quantity'];
                        $stmt->bind_param("iii", $new_quantity, $this->user_id, $product_id);
                    } catch (mysqli_sql_exception $e) {
                        error_log("Database error: " . $e->getMessage());
                        return ['success' => false, 'message' => 'Database connection error'];
                    }
                } else {
                    try {
                        $stmt = $this->conn->prepare("INSERT INTO shopping_cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
                        $stmt->bind_param("iii", $this->user_id, $product_id, $quantity);
                    } catch (mysqli_sql_exception $e) {
                        error_log("Database error: " . $e->getMessage());
                        return ['success' => false, 'message' => 'Database connection error'];
                    }
                }
                $stmt->execute();
            }

            return ['success' => true, 'message' => 'Product added to cart'];
        }

        return ['success' => false, 'message' => 'Product not found'];
    }
    public function removeItem($product_id) {
        if (!is_numeric($product_id)) {
            return ['success' => false, 'message' => 'Invalid product ID'];
        }
    
        if ($this->user_id) {
            $stmt = $this->conn->prepare("DELETE FROM shopping_cart WHERE user_id = ? AND product_id = ?");
            $stmt->bind_param("ii", $this->user_id, $product_id);
            $stmt->execute();
        }
    
        // Remove from session cart
        $_SESSION['cart'] = array_filter($_SESSION['cart'], function ($item) use ($product_id) {
            return $item['product_id'] != $product_id;
        });
    
        return ['success' => true];
    }
    
    public function updateQuantity($product_id, $quantity) {
        if (!is_numeric($product_id) || !is_numeric($quantity)) {
            return ['success' => false, 'message' => 'Invalid input'];
        }
    
        if ($quantity <= 0) {
            return $this->removeItem($product_id);
        }
    
        if ($this->user_id) {
            $stmt = $this->conn->prepare("UPDATE shopping_cart SET quantity = ? WHERE user_id = ? AND product_id = ?");
            $stmt->bind_param("iii", $quantity, $this->user_id, $product_id);
            $stmt->execute();
        }
    
        foreach ($_SESSION['cart'] as &$item) {
            if ($item['product_id'] == $product_id) {
                $item['quantity'] = $quantity;
            }
        }
    
        return ['success' => true];
    }
    
    public function clear() {
        $_SESSION['cart'] = [];
        return ['success' => true, 'message' => 'Cart cleared'];
    }

    public function getItems() {
        if ($this->user_id) {
            // Fetch cart items with product details for logged-in users
            $stmt = $this->conn->prepare("SELECT p.id as product_id, p.name, p.price, p.description, p.image_path, p.stock as stock_quantity, c.quantity 
                FROM shopping_cart c 
                JOIN products p ON c.product_id = p.id 
                WHERE c.user_id = ?");
            $stmt->bind_param("i", $this->user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $_SESSION['cart'] = [];
            while ($row = $result->fetch_assoc()) {
                if ($row['quantity'] > $row['stock_quantity']) {
                    $row['quantity'] = $row['stock_quantity'];
                    // Update database with corrected quantity
                    $update_stmt = $this->conn->prepare("UPDATE shopping_cart SET quantity = ? WHERE user_id = ? AND product_id = ?");
                    $update_stmt->bind_param("iii", $row['quantity'], $this->user_id, $row['product_id']);
                    $update_stmt->execute();
                }
                $_SESSION['cart'][] = $row;
            }
        }
        return $_SESSION['cart'];
    }

    public function getTotal() {
        $total = 0;
        foreach ($_SESSION['cart'] as $item) {
            $total += $item['price'] * $item['quantity'];
        }
        return $total;
    }

    public function getItemCount() {
        $count = 0;
        foreach ($_SESSION['cart'] as $item) {
            $count += $item['quantity'];
        }
        return $count;
    }

    public function getCount() {
        return count($_SESSION['cart']);
    }

    public function checkout($payment_method, $shipping_address) {
        if (empty($_SESSION['cart'])) {
            return ['success' => false, 'message' => 'Cart is empty'];
        }

        if (!$this->user_id) {
            return ['success' => false, 'message' => 'Please login to checkout'];
        }

        try {
            $this->conn->begin_transaction();

            // Create order
            $total_amount = $this->getTotal();
            $stmt = $this->conn->prepare("INSERT INTO orders (user_id, total_amount, payment_method, shipping_address) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("idss", $this->user_id, $total_amount, $payment_method, $shipping_address);
            $stmt->execute();
            $order_id = $this->conn->insert_id;

            // Create order items and update stock
            foreach ($_SESSION['cart'] as $item) {
                $stmt = $this->conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("iiid", $order_id, $item['product_id'], $item['quantity'], $item['price']);
                $stmt->execute();

                // Update stock and verify stock availability
                $stmt = $this->conn->prepare("UPDATE products SET stock = stock - ? WHERE id = ? AND stock >= ?");
                $stmt->bind_param("iii", $item['quantity'], $item['product_id'], $item['quantity']);
                $stmt->execute();
                
                if ($stmt->affected_rows === 0) {
                    throw new Exception('Insufficient stock for one or more products');
                }
            }

            $this->conn->commit();
            
            // Clear cart from database
            if ($this->user_id) {
                $stmt = $this->conn->prepare("DELETE FROM shopping_cart WHERE user_id = ?");
                $stmt->bind_param("i", $this->user_id);
                $stmt->execute();
            }
            
            $this->clear();

            return [
                'success' => true,
                'message' => 'Order placed successfully',
                'order_id' => $order_id
            ];
        } catch (Exception $e) {
            $this->conn->rollback();
            return ['success' => false, 'message' => 'Failed to place order'];
        }
    }
}