# Fingerprint-Importer

Komponen Utama Sistem:

- **Server:** Ubuntu Desktop
- **Database:** MySQL
- **Web Server:** Apache2
- **Dashboard:** PHP
- **Scheduler:** Cron job

Pengaturan Alamat IP

- **IP Mesin Fingerprint:** `192.168.1.201`.
- **IP Server Ubuntu (VirtualBox):** `192.168.1.200` (dikonfigurasi statis).
- **IP Host (Windows):** `192.168.1.123` (sebagai referensi).

## Setup 

Clone Repo
```
sudo git clone https://github.com/agabasuta/Fingerprint-Importer.git
cd Fingerprint-Importer
```
Instalasi web server Apache, database MySQL, dan PHP
```
sudo apt install apache2 mysql-server php apache2-mod-php php-mysql -y
```

masuk mysql untuk buat tabel dan isi kolom pada database menggunakan tabel yang ada di schema.sql
```
CREATE DATABASE attendance_db;
```
```
CREATE TABLE attendance_logs (
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
```

Buat environment dan install python

```
sudo apt install python3-pip -y
```
```
sudo apt install python3-venv -y
```
```
python3 -m venv venv

source venv/bin/activate
pip install -r requirements.txt
```
Pindahkan file php untuk webserver
```
sudo mv ~/Fingerprint-Importer/index.php /var/www/html/
```

Konfigurasi apache
```
sudo nano /etc/apache2/mods-enabled/dir.conf

```
Pindahkan index.php ke posisi pertama setelah DirectoryIndex
```
DirectoryIndex index.php index.html index.cgi index.pl index.xhtml index.htm
```

Restart Apache
```
sudo systemctl restart apache2
```
Buat Cron Job
```
0 */8 * * * /home/user/Fingerprint-Importer/venv/bin/python /home/user/Fingerprint-Importer/fingerprint_sync.py >> /home/user/Fingerprint_importer/fingerprint_sync.log 2>&1
```

## Testing

### Jalankan script manual
```
source venv/bin/activate
```
```
python fingerprint_sync.py
```

### Liat log pada crontab
```
cd Fingerprint_Importer
cat fingerprint_sync.log
```

### Melihat output di webserver
Masuk browser dan masukkan
```
localhost
```