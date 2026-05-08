E-Wallet Web Application
Aplikasi sistem dompet digital berbasis web yang dirancang menggunakan PHP dan MySQL. Proyek ini mendukung fitur transaksi modern seperti pembuatan QR Code dan cetak laporan PDF.
Persyaratan Sistem
PHP versi 7.4 atau lebih baru
MySQL / MariaDB Server
Web Server (Apache/Nginx)
Instruksi Instalasi Library
Aplikasi ini memerlukan library pihak ketiga. Pastikan Anda mengunduh dan mengekstrak library berikut ke dalam folder proyek:
FPDF (Cetak PDF)
Download: http://www.fpdf.org/en/download.php
Instalasi: Ekstrak dan letakkan di folder /lib/fpdf/
Kegunaan: Menghasilkan struk transaksi dan laporan mutasi saldo secara otomatis.
PHP QR Code (Generate QR)
Download: http://phpqrcode.sourceforge.net/
Instalasi: Ekstrak dan letakkan di folder /lib/phpqrcode/
Kegunaan: Membuat kode QR unik untuk setiap transaksi atau profil pengguna.
Cara Menjalankan
Database:
Buat database baru di MySQL (contoh: db_ewallet).
Import file .sql yang tersedia di folder /database.
Sesuaikan konfigurasi koneksi di file config.php.
Server:
Pindahkan folder proyek ke direktori web server Anda (htdocs atau var/www/html).
Akses melalui browser: http://localhost/nama-folder-proyek
Struktur Direktori
/lib : Penyimpanan library FPDF dan PHPQR.
/assets : File CSS, JS, dan Gambar.
/database : File SQL untuk skema MySQL.
