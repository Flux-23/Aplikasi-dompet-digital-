<?php
session_start();
require_once('../fpdf/fpdf.php'); // Pastikan path benar
include '../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit();
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("ID transaksi tidak valid.");
}

$transactionId = (int)$_GET['id'];

// Ambil data transaksi dari tabel transaksi
$query = "SELECT t.id, p.username, t.nomorHP, t.type, t.jumlah, t.status, t.reason, t.created_at 
          FROM transaksi t 
          JOIN pengguna p ON t.user_id = p.id 
          WHERE t.id = ?";

$stmt = $pdo->prepare($query);
$stmt->execute([$transactionId]);
$transaction = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$transaction) {
    die("Transaksi tidak ditemukan.");
}

// Buat objek PDF
$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 16);

// Tambahkan Logo
$logoPath = '../assets/logo/1.png'; 
if (file_exists($logoPath)) {
    $pdf->Image($logoPath, 10, 5, 30);
}

// Judul
$pdf->Cell(190, 10, 'Laporan Transaksi ', 0, 1, 'C');
$pdf->Ln(10);
$pdf->SetFont('Arial', '', 12);

// Data Transaksi
$pdf->Cell(50, 10, 'ID Transaksi:', 1, 0, 'L');
$pdf->Cell(140, 10, htmlspecialchars($transaction['id']), 1, 1, 'L');

$pdf->Cell(50, 10, 'Username:', 1, 0, 'L');
$pdf->Cell(140, 10, htmlspecialchars($transaction['username']), 1, 1, 'L');

$pdf->Cell(50, 10, 'Nomor HP:', 1, 0, 'L');
$pdf->Cell(140, 10, htmlspecialchars($transaction['nomorHP']), 1, 1, 'L');

$pdf->Cell(50, 10, 'Jenis Transaksi:', 1, 0, 'L');
$pdf->Cell(140, 10, ucfirst(htmlspecialchars($transaction['type'])), 1, 1, 'L');

$pdf->Cell(50, 10, 'Jumlah:', 1, 0, 'L');
$pdf->Cell(140, 10, 'Rp. ' . number_format($transaction['jumlah'], 0, ',', '.'), 1, 1, 'L');

$pdf->Cell(50, 10, 'Status:', 1, 0, 'L');
$pdf->SetTextColor($transaction['status'] == 'success' ? 0 : 255, $transaction['status'] == 'success' ? 128 : 0, 0);
$pdf->Cell(140, 10, ucfirst(htmlspecialchars($transaction['status'])), 1, 1, 'L');
$pdf->SetTextColor(0, 0, 0); // Reset warna teks

$pdf->Cell(50, 10, 'Alasan:', 1, 0, 'L');
$pdf->Cell(140, 10, htmlspecialchars($transaction['reason'] ?: '-'), 1, 1, 'L');

$pdf->Cell(50, 10, 'Tanggal:', 1, 0, 'L');
$pdf->Cell(140, 10, date('d-m-Y H:i', strtotime($transaction['created_at'])), 1, 1, 'L');

// Outputkan PDF
$pdf->Output('D', 'Transaksi_' . $transaction['id'] . '.pdf');
exit();
