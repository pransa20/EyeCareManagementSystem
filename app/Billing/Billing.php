<?php

namespace App\Billing;

use App\Mail\Mailer;

class Billing {
    private $mailer;

    public function __construct() {
        $this->mailer = new Mailer();
    }

    public function generateInvoice($order) {
        $invoiceHtml = $this->createInvoiceTemplate($order);
        return $invoiceHtml;
    }

    public function sendInvoice($email, $order) {
        $invoiceHtml = $this->generateInvoice($order);
        $subject = 'Your Invoice from Trinetra Eye Care - Order #' . $order['id'];
        return $this->mailer->send($email, $subject, $invoiceHtml);
    }

    private function createInvoiceTemplate($order) {
        $items = $order['items'];
        $total = 0;
        $itemsHtml = '';

        foreach ($items as $item) {
            $itemTotal = $item['price'] * $item['quantity'];
            $total += $itemTotal;
            $itemsHtml .= "<tr>
                <td>{$item['name']}</td>
                <td>{$item['quantity']}</td>
                <td>₹{$item['price']}</td>
                <td>₹{$itemTotal}</td>
            </tr>";
        }

        return "<html>
            <head>
                <style>
                    table { width: 100%; border-collapse: collapse; }
                    th, td { padding: 8px; text-align: left; border-bottom: 1px solid #ddd; }
                    .total { font-weight: bold; }
                </style>
            </head>
            <body>
                <h1>Invoice</h1>
                <p>Order #: {$order['id']}</p>
                <p>Date: " . date('Y-m-d') . "</p>
                <table>
                    <tr>
                        <th>Item</th>
                        <th>Quantity</th>
                        <th>Price</th>
                        <th>Total</th>
                    </tr>
                    {$itemsHtml}
                    <tr class='total'>
                        <td colspan='3'>Total</td>
                        <td>₹{$total}</td>
                    </tr>
                </table>
            </body>
        </html>";
    }
}