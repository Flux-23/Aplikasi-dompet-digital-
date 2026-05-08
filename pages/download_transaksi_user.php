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

$user_id = $_SESSION['user_id'];
$transactionId = (int)$_GET['id'];

// Ambil data transaksi, termasuk nomor HP
$query = "
    SELECT t.id, 'transfer' AS type, t.jumlah, t.created_at, NULL AS status, NULL AS reason, 
           sender.username AS sender_name, receiver.username AS receiver_name,
           sender.nomorHP AS sender_nomorHP, receiver.nomorHP AS receiver_nomorHP,
           t.sender_id, t.receiver_id 
    FROM transaksi_pengguna t
    LEFT JOIN pengguna sender ON t.sender_id = sender.id
    LEFT JOIN pengguna receiver ON t.receiver_id = receiver.id
    WHERE t.id = ? AND (t.sender_id = ? OR t.receiver_id = ?)
    
    UNION ALL
    
    SELECT t.id, t.type, t.jumlah, t.created_at, t.status, t.reason,
           NULL AS sender_name, NULL AS receiver_name,
           t.nomorHP AS nomorHP, NULL AS sender_id, NULL AS receiver_id
    FROM transaksi t
    WHERE t.id = ? AND t.user_id = ?
";
$stmt = $pdo->prepare($query);
$stmt->execute([$transactionId, $user_id, $user_id, $transactionId, $user_id]);
$transaction = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$transaction) {
    die("Transaksi tidak ditemukan.");
}

// Buat objek PDF
$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 16);

// Ambil logo
$logoPath = '../assets/logo/1.png'; // Pastikan path sesuai dengan lokasi logo
if (file_exists($logoPath)) {
    $pdf->Image($logoPath, 20, 5, 30); // (path, x, y, width)
}

// Judul
$pdf->Cell(190, 10, 'Laporan Transaksi', 0, 1, 'C');
$pdf->Ln(10);
$pdf->SetFont('Arial', '', 12);

// Data Transaksi
$pdf->Cell(50, 10, 'ID Transaksi:', 1, 0, 'L');
$pdf->Cell(140, 10, $transaction['id'], 1, 1, 'L');

$pdf->Cell(50, 10, 'Jenis Transaksi:', 1, 0, 'L');
$pdf->Cell(140, 10, ucfirst($transaction['type']), 1, 1, 'L');

$pdf->Cell(50, 10, 'Jumlah:', 1, 0, 'L');
$pdf->Cell(140, 10, 'Rp. ' . number_format($transaction['jumlah'], 0, ',', '.'), 1, 1, 'L');

// Cek apakah ini transaksi transfer
if ($transaction['type'] == 'transfer') {
    if ($transaction['sender_id'] == $user_id) {
        // Jika pengguna adalah pengirim
        $pdf->Cell(50, 10, 'Dikirim ke:', 1, 0, 'L');
        $pdf->Cell(140, 10, htmlspecialchars($transaction['receiver_name']), 1, 1, 'L');

        $pdf->Cell(50, 10, 'No HP Penerima:', 1, 0, 'L');
        $pdf->Cell(140, 10, htmlspecialchars($transaction['receiver_nomorHP']), 1, 1, 'L');
    } else {
        // Jika pengguna adalah penerima
        $pdf->Cell(50, 10, 'Diterima dari:', 1, 0, 'L');
        $pdf->Cell(140, 10, htmlspecialchars($transaction['sender_name']), 1, 1, 'L');

        $pdf->Cell(50, 10, 'No HP Pengirim:', 1, 0, 'L');
        $pdf->Cell(140, 10, htmlspecialchars($transaction['sender_nomorHP']), 1, 1, 'L');
    }
} else {
    // Jika transaksi bukan transfer (Top-Up/Withdraw)
    $pdf->Cell(50, 10, 'Nomor HP:', 1, 0, 'L');
    $pdf->Cell(140, 10, htmlspecialchars($transaction['nomorHP']), 1, 1, 'L');
}

// Hanya tampilkan status jika bukan transaksi transfer
if ($transaction['status'] !== NULL) {
    $pdf->Cell(50, 10, 'Status:', 1, 0, 'L');
    $pdf->Cell(140, 10, ucfirst($transaction['status']), 1, 1, 'L');
}

// Hanya tampilkan alasan jika ada
if ($transaction['reason'] !== NULL) {
    $pdf->Cell(50, 10, 'Alasan:', 1, 0, 'L');
    $pdf->Cell(140, 10, ($transaction['reason']) ? $transaction['reason'] : '-', 1, 1, 'L');
}

$pdf->Cell(50, 10, 'Tanggal:', 1, 0, 'L');
$pdf->Cell(140, 10, date('d-m-Y H:i', strtotime($transaction['created_at'])), 1, 1, 'L');

// Outputkan PDF
$pdf->Output('D', 'Transaksi_' . $transaction['id'] . '.pdf');
exit();
?>
