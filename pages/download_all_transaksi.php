<?php
session_start();
require_once('../fpdf/fpdf.php'); // Pastikan path benar
include '../includes/db.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] != 1)) {
    header('Location: ../index.php');
    exit();
}

// Query untuk mengambil semua transaksi
$query = "
    SELECT t.id, t.type, t.nomorHP, t.jumlah, t.created_at, t.status, 
           COALESCE(t.reason, '-') AS reason, 
           u.username AS pengguna,
           sender.username AS pengirim, receiver.username AS penerima,
           sender.nomorHP AS pengirim_nomorHP, receiver.nomorHP AS penerima_nomorHP
    FROM (
        -- Transaksi Top-Up dan Withdraw
        SELECT id, type, nomorHP, jumlah, created_at, status, reason, user_id, NULL AS sender_id, NULL AS receiver_id 
        FROM transaksi
        UNION ALL 
        -- Transaksi Transfer (tidak memiliki reason)
        SELECT id, 'transfer' AS type, nomorHP, jumlah, created_at, status, NULL AS reason, NULL AS user_id, sender_id, receiver_id 
        FROM transaksi_pengguna
    ) t
    LEFT JOIN pengguna u ON t.user_id = u.id
    LEFT JOIN pengguna sender ON t.sender_id = sender.id
    LEFT JOIN pengguna receiver ON t.receiver_id = receiver.id
    ORDER BY t.created_at DESC";

$stmt = $pdo->prepare($query);
$stmt->execute();
$transactions = $stmt->fetchAll();

// Membuat objek PDF
$pdf = new FPDF();
$pdf->AddPage('L'); // Landscape
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(0, 10, 'Laporan Semua Transaksi', 0, 1, 'C');
$pdf->Ln(5);

// Header tabel
$pdf->SetFont('Arial', 'B', 10);
$pdf->SetFillColor(0, 102, 204); // Warna biru untuk header
$pdf->SetTextColor(255, 255, 255);
$pdf->Cell(15, 8, 'ID', 1, 0, 'C', true);
$pdf->Cell(35, 8, 'User/Pengirim', 1, 0, 'C', true);
$pdf->Cell(35, 8, 'Nomor HP', 1, 0, 'C', true);
$pdf->Cell(35, 8, 'Penerima', 1, 0, 'C', true);
$pdf->Cell(35, 8, 'Nomor HP', 1, 0, 'C', true);
$pdf->Cell(30, 8, 'Jenis Transaksi', 1, 0, 'C', true);
$pdf->Cell(30, 8, 'Jumlah', 1, 0, 'C', true);
$pdf->Cell(40, 8, 'Status', 1, 0, 'C', true);
$pdf->Cell(40, 8, 'Tanggal', 1, 1, 'C', true);

// Isi tabel
$pdf->SetFont('Arial', '', 10);
$pdf->SetTextColor(0, 0, 0);

foreach ($transactions as $transaction) {
    $pdf->Cell(15, 8, $transaction['id'], 1, 0, 'C');
    
    if ($transaction['type'] == 'transfer') {
        $pdf->Cell(35, 8, $transaction['pengirim'], 1, 0, 'C');
        $pdf->Cell(35, 8, $transaction['pengirim_nomorHP'], 1, 0, 'C');
        $pdf->Cell(35, 8, $transaction['penerima'], 1, 0, 'C');
        $pdf->Cell(35, 8, $transaction['penerima_nomorHP'], 1, 0, 'C');
    } else {
        $pdf->Cell(35, 8, $transaction['pengguna'], 1, 0, 'C');
        $pdf->Cell(35, 8, $transaction['nomorHP'], 1, 0, 'C');
        $pdf->Cell(35, 8, '-', 1, 0, 'C'); // Penerima kosong jika bukan transfer
        $pdf->Cell(35, 8, '-', 1, 0, 'C'); // Nomor HP penerima kosong jika bukan transfer
    }
    
    $pdf->Cell(30, 8, ucfirst($transaction['type']), 1, 0, 'C');
    $pdf->Cell(30, 8, 'Rp. ' . number_format($transaction['jumlah'], 0, ',', '.'), 1, 0, 'C');
    
    $statusText = ucfirst($transaction['status']);
    if (strtolower($transaction['status']) == 'rejected') {
        $statusText .= ' (' . $transaction['reason'] . ')';
    }
    $pdf->Cell(40, 8, $statusText, 1, 0, 'C');
    
    $pdf->Cell(40, 8, date('d-m-Y H:i', strtotime($transaction['created_at'])), 1, 1, 'C');
}

// Output PDF
$pdf->Output('D', 'Semua_Transaksi.pdf');
exit();
?>
