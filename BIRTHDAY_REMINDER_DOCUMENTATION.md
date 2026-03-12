# Birthday Reminder Dashboard - Documentation

## 📋 Overview

Dashboard Pengingat Ulang Tahun adalah fitur untuk mengelola pengiriman pesan WhatsApp otomatis kepada pasien pada hari ulang tahun mereka. Fitur ini terintegrasi dengan sistem WhatsApp yang sudah ada.

## 🎯 Fitur Utama

### 1. **Dashboard Pengingat Ulang Tahun**
- Tampilan semua pengingat ulang tahun pasien
- Filter berdasarkan:
  - Tanggal: Hari ini, Minggu ini, Bulan ini, Semua
  - Status: Pending, Terkirim, Gagal, Terjadwal
- Statistik: Ulang tahun hari ini, minggu ini, pesan pending, pesan terkirim
- Aksi cepat: Kirim, Lihat Detail, Hapus

### 2. **Buat Pengingat Baru**
- Pilih pasien dari daftar
- Masukkan nomor WhatsApp penerima
- Template pesan otomatis atau custom
- Opsi pengiriman: Sekarang atau Terjadwal
- Validasi input dan penanganan error

### 3. **Sinkronisasi Data**
- Sinkronisasi otomatis tanggal lahir dari data pasien
- Buat pengingat baru untuk pasien yang belum memiliki pengingat tahun ini
- Ekstrak nomor WhatsApp dari field pasien

### 4. **Pengiriman Otomatis**
- Command untuk mengirim pengingat setiap hari
- Job queue untuk pengiriman asinkron
- Tracking status pengiriman (pending, sent, failed)

## 🗂️ Struktur File

```
Database:
- birthday_reminders (table)

Models:
- app/Models/BirthdayReminder.php

Controllers:
- app/Http/Controllers/BirthdayReminderController.php

Views:
- resources/views/dashboard/birthday-reminder/index.blade.php
- resources/views/dashboard/birthday-reminder/create.blade.php

Jobs:
- app/Jobs/SendBirthdayReminderJob.php

Commands:
- app/Console/Commands/SendDailyBirthdayReminders.php

Routes:
- routes/web.php (dashboard.birthday-reminder.*)

Migrations:
- database/migrations/2026_01_03_081832_create_birthday_reminders_table.php
```

## 📊 Database Schema

### Tabel: birthday_reminders

| Kolom | Tipe | Deskripsi |
|-------|------|-----------|
| id | bigint | Primary Key |
| no_rkm_medis | varchar(15) | Foreign Key ke pasien |
| message | longtext | Pesan pengingat |
| sender_phone | varchar(255) | Nomor WA pengirim |
| receiver_phone | varchar(255) | Nomor WA penerima |
| birthday_date | date | Tanggal ulang tahun |
| scheduled_date | datetime | Tanggal jadwal pengiriman |
| status | enum | pending, sent, failed, scheduled |
| response | text | Response dari WhatsApp API |
| sent_at | datetime | Waktu pengiriman |
| created_at | timestamp | Dibuat |
| updated_at | timestamp | Diperbarui |

**Indexes:**
- status
- birthday_date
- no_rkm_medis

## 🚀 Cara Penggunaan

### Akses Dashboard
```
URL: http://localhost/dashboard/birthday-reminder
Route Name: dashboard.birthday-reminder.index
```

### Tambah Pengingat Baru
```
URL: http://localhost/dashboard/birthday-reminder/create
Route Name: dashboard.birthday-reminder.create
Method: GET/POST
```

### Kirim Pengingat Manual
```
Route Name: dashboard.birthday-reminder.send
Method: POST
Parameter: reminder (BirthdayReminder Model)
```

### Sinkronisasi Data Pasien
```
Route Name: dashboard.birthday-reminder.sync
Method: POST
Deskripsi: Sinkronisasi tanggal lahir pasien ke tabel pengingat
```

## 🔧 Konfigurasi

### 1. WhatsApp Device
Pastikan setidaknya satu device WhatsApp terhubung:
```php
$device = WhatsAppDevice::where('status', 'connected')->first();
```

### 2. Template Pesan
Tiga template default tersedia:
- Template 1: Selamat ulang tahun standar
- Template 2: Happy Birthday dalam bahasa Inggris
- Template 3: Dengan reminder kontrol kesehatan

Custom message juga bisa dibuat.

### 3. Phone Number Format
Format nomor WhatsApp yang diterima:
- Dengan +62: `+6281234567890`
- Tanpa +62: `81234567890` → dikonversi ke `6281234567890`
- Dengan 0: `081234567890` → dikonversi ke `6281234567890`

## 📱 Pengiriman Otomatis

### Setup Cron Job

Tambahkan ke crontab untuk menjalankan pengingat setiap hari:

```bash
# Edit crontab
crontab -e

# Tambahkan baris berikut (jalankan setiap hari jam 08:00)
0 8 * * * cd /path/to/waBlast && php artisan birthday-reminder:send-daily

# Atau untuk testing, jalankan manual:
php artisan birthday-reminder:send-daily
```

### Manual Command
```bash
php artisan birthday-reminder:send-daily
```

### Via Job Queue
Jobs dikirim melalui database queue:
- Queue Connection: database
- Job Class: `App\Jobs\SendBirthdayReminderJob`

## 🔄 Workflow

### 1. Pengingat Manual (Sekarang)
```
Create Form → Store → Send Immediately → Update Status → Redirect
```

### 2. Pengingat Terjadwal
```
Create Form → Store (Status: scheduled) → Cron/Manual Trigger → Send → Update Status
```

### 3. Sinkronisasi Data
```
Click Sync Button → Query Pasien dengan tgl_lahir → Create Birthday Reminders → Redirect
```

## 🛡️ Validasi dan Error Handling

### Input Validation
- no_rkm_medis: Required, exists di tabel pasien
- message: Required, 5-1000 karakter
- receiver_phone: Required, format +62 atau 0
- scheduled_date: Date format, harus di masa depan

### Error Messages
- Device tidak terhubung → "Tidak ada device WhatsApp yang terhubung"
- Pesan sudah dikirim → "Pesan pengingat sudah dikirim sebelumnya"
- Pengiriman gagal → Capture error detail di response

## 📈 Monitoring

### Status Pengingat
- **Pending**: Menunggu untuk dikirim
- **Sent**: Berhasil dikirim
- **Failed**: Gagal dikirim (lihat response untuk detail error)
- **Scheduled**: Terjadwal untuk waktu tertentu

### Response API
Setiap pengingat menyimpan response dari WhatsApp API untuk debugging:
```php
$reminder->response; // JSON format
```

## 🔗 Integrasi dengan WhatsApp Service

Menggunakan `WhatsAppService` yang sudah ada:
```php
$response = $this->whatsappService->sendMessage(
    $receiver_phone,
    $message
);
```

## 📝 Template Pesan Default

### Template 1
```
Selamat ulang tahun! 🎂 Semoga hari istimewamu dipenuhi berkah dan kebahagiaan. 
Terima kasih telah mempercayai kami untuk kesehatan Anda. 💚
```

### Template 2
```
🎉 Happy Birthday! Wishing you a wonderful day filled with joy, good health, 
and happiness! Thank you for trusting us. 🎊
```

### Template 3
```
Selamat ulang tahun! 🎁 Semoga Anda selalu sehat, bahagia, dan bersemangat. 
Jangan lupa untuk rutin kontrol kesehatan dan jaga pola hidup sehat. Salam sehat! 💪
```

## ⚙️ Konfigurasi Queue

File: `.env`
```
QUEUE_CONNECTION=database
```

Pastikan table `jobs` sudah ada:
```bash
php artisan queue:table
php artisan migrate
```

## 🐛 Troubleshooting

### 1. "Foreign Key Constraint Fails"
**Solusi:** Gunakan no_rkm_medis yang valid dari tabel pasien

### 2. "Device tidak terhubung"
**Solusi:** Hubungkan WhatsApp device terlebih dahulu di halaman settings

### 3. Pesan tidak terkirim
**Solusi:** 
- Cek status device
- Cek format nomor telepon
- Lihat response API untuk detail error
- Cek log di `storage/logs/`

### 4. Cron job tidak berjalan
**Solusi:**
- Verifikasi crontab setting
- Test command manual: `php artisan birthday-reminder:send-daily`
- Cek file permission

## 📊 Database Queries

### Query Semua Pengingat Hari Ini
```php
BirthdayReminder::todayBirthday()->get();
```

### Query Pengingat yang Belum Dikirim
```php
BirthdayReminder::where('status', 'pending')->get();
```

### Query Pengingat dengan Pasien Info
```php
BirthdayReminder::with('patient')->get();
```

## 🔐 Keamanan

- Validasi input pada semua form
- Model binding untuk Eloquent (BirthdayReminder)
- CSRF protection pada form
- Authorization dapat ditambahkan di middleware
- Sensitive data di response field disimpan aman

## 📋 Checklist Implementasi

- ✅ Create BirthdayReminder Model
- ✅ Create Migration (birthday_reminders table)
- ✅ Create BirthdayReminderController
- ✅ Create Views (index, create)
- ✅ Setup Routes
- ✅ Create SendBirthdayReminderJob
- ✅ Create SendDailyBirthdayReminders Command
- ⏳ Setup Cron Job (manual setup needed)
- ⏳ Test pengiriman otomatis

## 🚀 Next Steps

1. Setup cron job untuk pengiriman otomatis
2. Test pengiriman ke beberapa pasien
3. Monitor status pengingat di dashboard
4. Customize template pesan sesuai kebutuhan
5. Setup email notification untuk failed messages (optional)

---

**Last Updated:** 2026-01-03
**Version:** 1.0
