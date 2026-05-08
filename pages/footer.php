<footer class="footer">
    <div class="footer-container">
        <div class="footer-content">
            <p>&copy; <?php echo date('Y'); ?> Chiness</p>
            <p>Powered by <strong>Chiness System</strong></p>
        </div>
    </div>
</footer>
<style>
/* Pastikan halaman penuh */
html, body {
    height: 100%;
    margin: 0;
    display: flex;
    flex-direction: column;
}

/* Buat konten utama fleksibel agar footer selalu di bawah */
.content {
    flex: 1;
}

/* Footer styling */
.footer {
    background: linear-gradient(to right, #007bff, #00c6ff);
    color: white;
    text-align: center;
    padding: 15px 10px;
    font-size: 14px;
    font-weight: bold;
    width: 100%;
    margin-top: auto;
}

/* Kontainer footer */
.footer-container {
    max-width: 1200px;
    margin: auto;
}

/* Konten footer */
.footer-content {
    display: flex;
    flex-direction: column;
    align-items: center;
}

/* Efek hover untuk interaksi */
.footer-content strong {
    color: #fff;
    transition: color 0.3s ease-in-out;
}

.footer-content strong:hover {
    color: #ffdd57;
}

/* Responsif */
@media (max-width: 768px) {
    .footer {
        font-size: 12px;
        padding: 12px;
    }
}
</style>