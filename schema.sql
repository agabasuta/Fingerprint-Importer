CREATE TABLE IF NOT EXISTS attendance_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    departemen VARCHAR(100),
    nama VARCHAR(100),
    no_id VARCHAR(50),
    tgl_waktu DATETIME,
    status ENUM('Masuk', 'Keluar') NULL,
    lokasi_id VARCHAR(50),
    no_pin VARCHAR(50),
    kode_kerja VARCHAR(50),
    kode_verifikasi VARCHAR(50),
    no_kartu VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);