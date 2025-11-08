<?php

namespace App\Payment;

use Stripe\Stripe;
use Stripe\PaymentIntent;
use Stripe\Exception\ApiErrorException;

class Payment {
    private $stripe;

    public function __construct() {
        Stripe::setApiKey('your_stripe_secret_key');
    }

    public function createPaymentIntent($amount, $currency = 'usd') {
        try {
            $paymentIntent = PaymentIntent::create([
                'amount' => $amount * 100, // Convert to cents
                'currency' => $currency,
                'payment_method_types' => ['card'],
            ]);
            return [
                'success' => true,
                'clientSecret' => $paymentIntent->client_secret
            ];
        } catch (ApiErrorException $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    public function confirmPayment($paymentIntentId) {
        try {
            $paymentIntent = PaymentIntent::retrieve($paymentIntentId);
            $paymentIntent->confirm();
            return [
                'success' => true,
                'status' => $paymentIntent->status
            ];
        } catch (ApiErrorException $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}