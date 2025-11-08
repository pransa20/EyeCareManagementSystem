<?php
class KhaltiPayment {
    private $publicKey;
    private $secretKey;
    private $liveMode;
    
    public function __construct($liveMode = false) {
        $this->liveMode = $liveMode;
        // Set your Khalti merchant keys here
        $this->publicKey = '4664dd6b9c79406da13b02d89c50a202'; // Replace with your public key
        $this->secretKey = '861dc74dd33549579183047669234c4d'; // Replace with your secret key
    }
    
    public function getPublicKey() {
        return $this->publicKey;
    }
    
    public function initiatePayment($orderId, $amount, $email, $phone) {
        // Amount should be in paisa (1 NPR = 100 paisa)
        $amountInPaisa = $amount * 100;
        
        return [
            'publicKey' => $this->publicKey,
            'amount' => $amountInPaisa,
            'productIdentity' => $orderId,
            'productName' => 'Trinetra Eye Care Order #' . $orderId,
            'productUrl' => 'https://trinetraeyecare.com/order/' . $orderId,
            'callbackUrl' => 'https://trinetraeyecare.com/payment-callback.php',
            'customerEmail' => $email,
            'customerPhone' => $phone
        ];
    }
    
    public function verifyPayment($token, $amount) {
        $url = $this->liveMode ? 
            'https://khalti.com/api/v2/payment/verify/' : 
            'https://khalti.com/api/v2/payment/verify/';
        
        $payload = [
            'token' => $token,
            'amount' => $amount
        ];
        
        $headers = [
            'Authorization: Key ' . $this->secretKey,
            'Content-Type: application/json'
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        
        $response = curl_exec($ch);
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($statusCode == 200) {
            $responseData = json_decode($response, true);
            return [
                'success' => true,
                'data' => $responseData
            ];
        }
        
        return [
            'success' => false,
            'message' => 'Payment verification failed'
        ];
    }
}