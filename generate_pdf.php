<?php
require_once __DIR__ . '/includes/Fpdf.php';

$order_id = $_GET['order_id'] ?? 0;

// Create new PDF instance
$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial','B',16);

// Add content
$pdf->Cell(40,10,'Order ID: ' . $order_id);

// Output PDF
$pdf->Output('D', 'order_bill_' . $order_id . '.pdf');
?>