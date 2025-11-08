<?php
class PaymentGateway {
    private $conn;
    private $api_key;
    private $merchant_id;
    private $settings;

    public function __construct() {
        global $conn;
        $this->conn = $conn;
        $this->settings = new Settings();
        $config = $this->settings->getPaymentConfig();
        $this->api_key = $config['api_key'];
        $this->merchant_id = $config['merchant_id'];
    }

    public function initializePayment($order_id, $amount, $payment_method) {
        try {
            // Get order details
            $sql = "SELECT o.*, u.email, u.phone 
                    FROM orders o 
                    JOIN users u ON o.user_id = u.id 
                    WHERE o.id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("i", $order_id);
            $stmt->execute();
            $order = $stmt->get_result()->fetch_assoc();

            if (!$order) {
                throw new Exception('Order not found');
            }

            // Initialize payment based on method
            switch ($payment_method) {
                case 'card':
                    return $this->initializeCardPayment($order, $amount);
                case 'upi':
                    return $this->initializeUPIPayment($order, $amount);
                case 'cod':
                    return $this->initializeCODPayment($order);
                default:
                    throw new Exception('Invalid payment method');
            }
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    private function initializeCardPayment($order, $amount) {
        // In production, integrate with a payment gateway API
        $payment_data = [
            'merchant_id' => $this->merchant_id,
            'order_id' => $order['id'],
            'amount' => $amount,
            'currency' => 'INR',
            'redirect_url' => 'https://your-domain.com/payment-callback.php',
            'cancel_url' => 'https://your-domain.com/payment-cancel.php',
            'customer_email' => $order['email'],
            'customer_phone' => $order['phone']
        ];

        // Store payment initialization data
        $sql = "INSERT INTO payment_initializations 
                (order_id, payment_method, amount, status, created_at) 
                VALUES (?, 'card', ?, 'initialized', NOW())";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("id", $order['id'], $amount);
        $stmt->execute();

        return [
            'success' => true,
            'payment_data' => $payment_data,
            'message' => 'Card payment initialized'
        ];
    }

    private function initializeUPIPayment($order, $amount) {
        // In production, integrate with UPI payment gateway
        $upi_data = [
            'merchant_id' => $this->merchant_id,
            'order_id' => $order['id'],
            'amount' => $amount,
            'merchant_upi' => 'merchant@upi',
            'customer_vpa' => '',  // To be filled by customer
            'transaction_note' => 'Payment for Order #' . $order['id']
        ];

        // Store payment initialization data
        $sql = "INSERT INTO payment_initializations 
                (order_id, payment_method, amount, status, created_at) 
                VALUES (?, 'upi', ?, 'initialized', NOW())";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("id", $order['id'], $amount);
        $stmt->execute();

        return [
            'success' => true,
            'payment_data' => $upi_data,
            'message' => 'UPI payment initialized'
        ];
    }

    private function initializeCODPayment($order) {
        // Store COD payment initialization
        $sql = "INSERT INTO payment_initializations 
                (order_id, payment_method, amount, status, created_at) 
                VALUES (?, 'cod', ?, 'initialized', NOW())";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("id", $order['id'], $order['total_amount']);
        $stmt->execute();

        return [
            'success' => true,
            'message' => 'COD payment initialized'
        ];
    }

    public function verifyPayment($payment_id, $order_id, $signature) {
        try {
            // In production, verify payment signature with payment gateway
            $is_valid = true; // Replace with actual verification

            if ($is_valid) {
                // Update payment status
                $sql = "UPDATE payment_initializations 
                        SET status = 'completed', 
                            payment_id = ?, 
                            signature = ?, 
                            completed_at = NOW() 
                        WHERE order_id = ? AND status = 'initialized'";
                $stmt = $this->conn->prepare($sql);
                $stmt->bind_param("ssi", $payment_id, $signature, $order_id);
                $stmt->execute();

                return ['success' => true, 'message' => 'Payment verified successfully'];
            }

            throw new Exception('Invalid payment signature');
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function getPaymentStatus($order_id) {
        $sql = "SELECT * FROM payment_initializations WHERE order_id = ? ORDER BY created_at DESC LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        $payment = $stmt->get_result()->fetch_assoc();

        return $payment ? $payment['status'] : 'not_initialized';
    }
}