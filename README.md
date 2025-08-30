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
sudo apt install apache2 mysql-server php libapache2-mod-php php-mysql -y
```

masuk mysql untuk buat user, tabel dan isi kolom pada database
```
sudo mysql -u root -p
```
```
CREATE DATABASE attendance_db;
```
```
CREATE USER 'fingerprint_user'@'localhost' IDENTIFIED BY 'your_password';
GRANT ALL PRIVILEGES ON attendance_db.* TO 'fingerprint_user'@'localhost';
FLUSH PRIVILEGES;
```
```
USE attendance_db;

SOURCE schema.sql;
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
sudo mkdir -p /var/www/html/Dashboard
```
```
sudo mv ~/Fingerprint-Importer/index.php /var/www/html/Dashboard
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
crontab -e
```
Tambahkan Script ini untuk penarikkan data automatis per 8 jam (00:00, 08:00, 16:00)
```
0 */8 * * * /home/user/Fingerprint-Importer/venv/bin/python /home/user/Fingerprint-Importer/fingerprint_sync.py >> /home/user/Fingerprint-Importer/fingerprint_sync.log 2>&1
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
cd Fingerprint-Importer
cat fingerprint_sync.log
```

### Melihat output di webserver
Masuk browser dan masukkan
```
http://localhost/Dashboard
```
