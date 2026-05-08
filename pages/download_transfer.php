<?php
session_start();
require('../fpdf/fpdf.php'); // Pastikan FPDF sudah ada di folder

if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    die("Akses ditolak!");
}

include '../includes/db.php';

$user_id = $_SESSION['user_id'];
$transaction_id = $_GET['id'];

// Ambil data transaksi berdasarkan ID transaksi
$stmt = $pdo->prepare('
    SELECT t.id, t.receiver_id, p.username AS penerima_nama, t.nomorHP,t.jumlah, t.created_at 
    FROM transaksi_pengguna t
    JOIN pengguna p ON t.receiver_id = p.id
    WHERE t.sender_id = ? AND t.id = ?
');
$stmt->execute([$user_id, $transaction_id]);
$transfer = $stmt->fetch();

if (!$transfer) {
    die("Data tidak ditemukan!");
}

// Membuat PDF
$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 16);

// Menambahkan Logo
$logoPath = '../assets/logo/1.png'; // Pastikan path sesuai dengan lokasi logo
if (file_exists($logoPath)) {
    $pdf->Image($logoPath, 10, 10, 30); // (path, x, y, width)
}
$pdf->Ln(20); // Spasi setelah logo

// Header
$pdf->Cell(0, 10, 'Bukti Transfer', 0, 1, 'C');
$pdf->Ln(10);

// Detail Transaksi
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(50, 10, 'Tanggal:', 0, 0);
$pdf->Cell(100, 10, date('d M Y H:i', strtotime($transfer['created_at'])), 0, 1);

$pdf->Cell(50, 10, 'ID Penerima:', 0, 0);
$pdf->Cell(100, 10, $transfer['receiver_id'], 0, 1);

$pdf->Cell(50, 10, 'Nama Penerima:', 0, 0);
$pdf->Cell(100, 10, $transfer['penerima_nama'], 0, 1);
$pdf->Cell(50, 10, 'no Penerima:', 0, 0);
$pdf->Cell(100, 10, $transfer['nomorHP'], 0, 1);


$pdf->Cell(50, 10, 'Jumlah:', 0, 0);
$pdf->Cell(100, 10, 'Rp. ' . number_format($transfer['jumlah'], 2), 0, 1);

$pdf->Ln(10);
$pdf->Cell(0, 10, 'Terima kasih telah menggunakan layanan kami.', 0, 1, 'C');

// Output PDF
$pdf->Output('D', 'Bukti_Transfer_' . $transfer['id'] . '.pdf');
exit;
?>
