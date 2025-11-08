<?php
class Settings {
    private $conn;

    public function __construct() {
        global $conn;
        $this->conn = $conn;
    }

    public function get($key) {
        $sql = "SELECT setting_value FROM settings WHERE setting_key = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('s', $key);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row ? $row['setting_value'] : null;
    }

    public function set($key, $value) {
        $sql = "INSERT INTO settings (setting_key, setting_value) 
                VALUES (?, ?) 
                ON DUPLICATE KEY UPDATE setting_value = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('sss', $key, $value, $value);
        return $stmt->execute();
    }

    public function getPaymentConfig() {
        return [
            'api_key' => $this->get('payment_gateway_api_key'),
            'merchant_id' => $this->get('payment_gateway_merchant_id')
        ];
    }

    public function setPaymentConfig($api_key, $merchant_id) {
        $success = true;
        $success &= $this->set('payment_gateway_api_key', $api_key);
        $success &= $this->set('payment_gateway_merchant_id', $merchant_id);
        return $success;
    }

    public function getSmtpConfig() {
        return [
            'host' => $this->get('smtp_host'),
            'port' => $this->get('smtp_port'),
            'username' => $this->get('smtp_username'),
            'password' => $this->get('smtp_password')
        ];
    }

    public function setSmtpConfig($host, $port, $username, $password) {
        $success = true;
        $success &= $this->set('smtp_host', $host);
        $success &= $this->set('smtp_port', $port);
        $success &= $this->set('smtp_username', $username);
        $success &= $this->set('smtp_password', $password);
        return $success;
    }
}