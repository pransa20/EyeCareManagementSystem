<?php
class Payment {
    private $conn;
    private $user_id;

    public function __construct($user_id = null) {
        global $conn;
        $this->conn = $conn;
        $this->user_id = $user_id;
    }

    public function processCardPayment($order_id, $card_details) {
        try {
            $this->conn->begin_transaction();

            // Validate card details
            if (!$this->validateCardDetails($card_details)) {
                throw new Exception('Invalid card details');
            }

            // Get order amount
            $sql = "SELECT total_amount FROM orders WHERE id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("i", $order_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $order = $result->fetch_assoc();

            if (!$order) {
                throw new Exception('Order not found');
            }

            // Process card payment (integrate with payment gateway)
            $transaction_id = $this->generateTransactionId();
            
            // Store payment details
            $sql = "INSERT INTO payment_details (order_id, payment_method, transaction_id, amount, status)
                    VALUES (?, 'card', ?, ?, 'completed')";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("isd", $order_id, $transaction_id, $order['total_amount']);
            $stmt->execute();

            // Update order status
            $sql = "UPDATE orders SET payment_status = 'paid', status = 'processing' WHERE id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("i", $order_id);
            $stmt->execute();

            $this->conn->commit();
            return [
                'success' => true,
                'message' => 'Payment processed successfully',
                'transaction_id' => $transaction_id
            ];
        } catch (Exception $e) {
            $this->conn->rollback();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function processUPIPayment($order_id, $upi_details) {
        try {
            $this->conn->begin_transaction();

            // Validate UPI ID
            if (!$this->validateUPIId($upi_details['upi_id'])) {
                throw new Exception('Invalid UPI ID');
            }

            // Get order amount
            $sql = "SELECT total_amount FROM orders WHERE id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("i", $order_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $order = $result->fetch_assoc();

            if (!$order) {
                throw new Exception('Order not found');
            }

            // Process UPI payment (integrate with UPI gateway)
            $transaction_id = $this->generateTransactionId();
            
            // Store payment details
            $sql = "INSERT INTO payment_details (order_id, payment_method, transaction_id, amount, status)
                    VALUES (?, 'upi', ?, ?, 'completed')";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("isd", $order_id, $transaction_id, $order['total_amount']);
            $stmt->execute();

            // Update order status
            $sql = "UPDATE orders SET payment_status = 'paid', status = 'processing' WHERE id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("i", $order_id);
            $stmt->execute();

            $this->conn->commit();
            return [
                'success' => true,
                'message' => 'Payment processed successfully',
                'transaction_id' => $transaction_id
            ];
        } catch (Exception $e) {
            $this->conn->rollback();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function processCODPayment($order_id) {
        try {
            $this->conn->begin_transaction();

            // Store payment details
            $sql = "INSERT INTO payment_details (order_id, payment_method, amount, status)
                    SELECT ?, 'cod', total_amount, 'pending'
                    FROM orders WHERE id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("ii", $order_id, $order_id);
            $stmt->execute();

            // Update order status
            $sql = "UPDATE orders SET payment_status = 'pending', status = 'processing' WHERE id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("i", $order_id);
            $stmt->execute();

            $this->conn->commit();
            return ['success' => true, 'message' => 'COD order placed successfully'];
        } catch (Exception $e) {
            $this->conn->rollback();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    private function validateCardDetails($card_details) {
        // Basic card validation
        $card_number = preg_replace('/\D/', '', $card_details['number']);
        $expiry = $card_details['expiry'];
        $cvv = $card_details['cvv'];

        // Check card number length
        if (strlen($card_number) < 13 || strlen($card_number) > 19) {
            return false;
        }

        // Check expiry date
        if (!preg_match('/^(0[1-9]|1[0-2])\/([0-9]{2})$/', $expiry)) {
            return false;
        }

        // Check CVV
        if (!preg_match('/^[0-9]{3,4}$/', $cvv)) {
            return false;
        }

        return true;
    }

    private function validateUPIId($upi_id) {
        // Basic UPI ID validation
        return preg_match('/^[\w.-]+@[\w.-]+$/', $upi_id);
    }

    private function generateTransactionId() {
        return uniqid('TXN_') . substr(md5(rand()), 0, 8);
    }
}