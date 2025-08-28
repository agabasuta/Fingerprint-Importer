from zk import ZK
import mysql.connector

# Konfigurasi fingerprint
MACHINE_IP = '' # Ganti dengan IP fingerprint   
MACHINE_PORT = 4370 # Default 4370

# Konfigurasi database
DB_CONFIG = {
    "host": "localhost",
    "user": "", # Sesuaikan user
    "password": "", # Sesuaikan pass
    "database": "attendance_db"
}

zk = ZK(MACHINE_IP, port=MACHINE_PORT, timeout=5)
conn = None

try:
    conn = zk.connect()
    conn.disable_device()
    print("Tersambung ke mesin fingerprint")

    # Ambil log absensi
    attendance = conn.get_attendance()

    # Ambil daftar user (untuk ambil nama)
    users = {u.user_id: u.name for u in conn.get_users()}

    # Koneksi ke database
    db = mysql.connector.connect(**DB_CONFIG)
    cursor = db.cursor()

    # Ambil waktu terakhir dari database
    cursor.execute("SELECT MAX(tgl_waktu) FROM attendance_logs")
    last_time = cursor.fetchone()[0]
    print("Data terakhir di DB:", last_time)

    # Mapping kode verifikasi
    verify_map = {
        1: "Sidik Jari",
        2: "Kartu ID"
    }

    # Mapping status masuk/keluar
    punch_map = {
        0: "Masuk",
        1: "Keluar"
    }

    new_count = 0
    for att in attendance:
        if last_time is None or att.timestamp > last_time:
            verifikasi = verify_map.get(att.status, "Lainnya")
            status_absen = punch_map.get(att.punch, None)

            sql = """INSERT IGNORE INTO attendance_logs
                     (departemen, nama, no_id, tgl_waktu, status, lokasi_id, no_pin, kode_kerja, kode_verifikasi, no_kartu)
                     VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s)"""

            # Insert value
            val = (
                "OUR COMPANY",                   # Departemen default
                users.get(att.user_id, ""),      # Nama dari mesin
                str(att.user_id),                # No.ID
                att.timestamp.strftime('%Y-%m-%d %H:%M:%S'),
                status_absen,                    # Masuk/Keluar
                "1",                             # Lokasi ID default
                "",                              # No.PIN
                "0",                             # Kode Kerja default
                verifikasi,                      # Kode Verifikasi
                ""                               # No.Kartu 
            )
            cursor.execute(sql, val)
            new_count += 1

    db.commit()
    cursor.close()
    db.close()

    print(f"{new_count} data baru berhasil disimpan ke database.")

    conn.enable_device()

except Exception as e:
    print("Error:", e)

finally:
    if conn:
        conn.disconnect()
