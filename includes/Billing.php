<?php
class Billing {
    private $conn;
    private $user_id;

    public function __construct($user_id = null) {
        global $conn;
        $this->conn = $conn;
        $this->user_id = $user_id;
    }

    public function generateInvoice($order_id) {
        $sql = "SELECT o.*, u.name as customer_name, u.email as customer_email, u.phone as customer_phone,
                       bd.billing_name, bd.billing_email, bd.billing_phone, bd.billing_address
                FROM orders o
                JOIN users u ON o.user_id = u.id
                LEFT JOIN billing_details bd ON o.id = bd.order_id
                WHERE o.id = ?";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        $order = $stmt->get_result()->fetch_assoc();

        if (!$order) {
            return ['success' => false, 'message' => 'Order not found'];
        }

        // Get order items
        $sql = "SELECT oi.*, p.name as product_name
                FROM order_items oi
                JOIN products p ON oi.product_id = p.id
                WHERE oi.order_id = ?";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        $items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        return [
            'success' => true,
            'order' => $order,
            'items' => $items
        ];
    }

    public function processPayment($order_id, $payment_method, $payment_details) {
        try {
            $this->conn->begin_transaction();

            // Update order payment status
            $sql = "UPDATE orders SET payment_status = 'paid', payment_method = ? WHERE id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("si", $payment_method, $order_id);
            $stmt->execute();

            // Store payment details
            $sql = "INSERT INTO payment_details (order_id, payment_method, transaction_id, amount, status)
                    SELECT ?, ?, ?, total_amount, 'completed'
                    FROM orders WHERE id = ?";
            $stmt = $this->conn->prepare($sql);
            $transaction_id = $payment_details['transaction_id'] ?? null;
            $stmt->bind_param("issi", $order_id, $payment_method, $transaction_id, $order_id);
            $stmt->execute();

            $this->conn->commit();
            return ['success' => true, 'message' => 'Payment processed successfully'];
        } catch (Exception $e) {
            $this->conn->rollback();
            return ['success' => false, 'message' => 'Payment processing failed'];
        }
    }

    public function saveBillingDetails($order_id, $billing_details) {
        try {
            $sql = "INSERT INTO billing_details 
                    (order_id, billing_name, billing_email, billing_phone, billing_address)
                    VALUES (?, ?, ?, ?, ?)";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("issss", 
                $order_id,
                $billing_details['name'],
                $billing_details['email'],
                $billing_details['phone'],
                $billing_details['address']
            );
            $stmt->execute();

            return ['success' => true, 'message' => 'Billing details saved successfully'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Failed to save billing details'];
        }
    }

    public function getOrderHistory($user_id = null) {
        $user_id = $user_id ?? $this->user_id;
        
        if (!$user_id) {
            return ['success' => false, 'message' => 'User not found'];
        }

        $sql = "SELECT o.*, COUNT(oi.id) as total_items
                FROM orders o
                LEFT JOIN order_items oi ON o.id = oi.order_id
                WHERE o.user_id = ?
                GROUP BY o.id
                ORDER BY o.created_at DESC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        return ['success' => true, 'orders' => $orders];
    }
}