# Birthday Reminder Dashboard - Quick Start Guide

## 🚀 Quick Start dalam 5 Menit

### Step 1: Akses Dashboard
```
URL: http://localhost/dashboard/birthday-reminder
```

### Step 2: Lihat Statistik
Dashboard menampilkan:
- 📅 Ulang tahun hari ini
- 📆 Ulang tahun minggu ini  
- ⏳ Pesan yang pending
- ✅ Pesan yang terkirim

### Step 3: Tambah Pengingat Manual

**Klik tombol "Tambah Pengingat"**

1. **Pilih Pasien**: Dropdown berisi daftar pasien dengan tanggal lahir
2. **No WhatsApp**: Masukkan nomor penerima (cth: 81234567890)
3. **Pesan**: Pilih template atau tulis custom
4. **Pengiriman**: 
   - Kirim Sekarang → Langsung terkirim
   - Jadwalkan → Set tanggal & waktu pengiriman
5. **Submit**: Klik "Simpan Pengingat"

### Step 4: Sinkronisasi Data Pasien (Optional)

**Klik tombol "Sinkronisasi Data"**

Ini akan:
- Membaca semua pasien yang punya tanggal lahir
- Otomatis membuat pengingat untuk pasien yang belum ada
- Mengextract nomor WhatsApp dari data pasien

### Step 5: Monitoring Pengingat

**Tabel menampilkan:**
- Nama Pasien
- No RM (Medical Record)
- Tanggal Lahir
- No WhatsApp
- Status (Pending/Terkirim/Gagal)
- Action Buttons

**Klik tombol action:**
- 📤 **Kirim** - Kirim pesan sekarang (jika status pending)
- 👁️ **Detail** - Lihat detail lengkap pengingat
- 🗑️ **Hapus** - Hapus pengingat

## 📱 Template Pesan Siap Pakai

### Template 1: Standard
```
Selamat ulang tahun! 🎂 Semoga hari istimewamu dipenuhi berkah dan kebahagiaan. 
Terima kasih telah mempercayai kami untuk kesehatan Anda. 💚
```

### Template 2: English
```
🎉 Happy Birthday! Wishing you a wonderful day filled with joy, good health, 
and happiness! Thank you for trusting us. 🎊
```

### Template 3: With Health Reminder
```
Selamat ulang tahun! 🎁 Semoga Anda selalu sehat, bahagia, dan bersemangat. 
Jangan lupa untuk rutin kontrol kesehatan dan jaga pola hidup sehat. Salam sehat! 💪
```

## ⚙️ Setup Pengiriman Otomatis (30 Menit)

### Opsi 1: Windows Task Scheduler

1. **Buka Task Scheduler** (Task Penjadwal)
   ```
   Win + R → taskschd.msc
   ```

2. **Buat New Task**
   - Name: `Send Birthday Reminders`
   - Trigger: Daily at 08:00 AM
   - Action:
     ```
     Program: C:\laragon\bin\php\php8.1.0-Win32-x64-portable\php.exe
     Arguments: C:\laragon\www\waBlast\artisan birthday-reminder:send-daily
     Start in: C:\laragon\www\waBlast
     ```

### Opsi 2: Laragon Built-in Scheduler

1. **Edit file di root project**
   ```
   file: .env
   tambahkan/pastikan:
   QUEUE_CONNECTION=database
   ```

2. **Run Laragon scheduler** (jika sudah built-in)
   ```
   php artisan schedule:work
   ```

3. **Atau manual test:**
   ```
   php artisan birthday-reminder:send-daily
   ```

### Opsi 3: Manual Check di Dashboard

Setiap hari cek dashboard dan klik tombol "Sync" untuk trigger pengiriman

## 🔍 Troubleshooting Cepat

### ❌ Pesan Tidak Terkirim?

**Cek 1: Device WhatsApp Terhubung?**
```
Ke: Pengaturan WhatsApp → Status Device
Pastikan ada 1+ device dengan status "connected"
```

**Cek 2: Nomor WhatsApp Valid?**
- Format: 62812345678 (tanpa +, tanpa 0)
- Minimal 10 digit setelah 62
- Maksimal 14 digit

**Cek 3: Pesan Terlalu Panjang?**
- Maksimal 1000 karakter per pesan
- Pastikan tidak ada karakter spesial yang error

### ⚠️ Error: "Tidak ada device WhatsApp yang terhubung"

**Solusi:**
1. Ke halaman WhatsApp Settings
2. Scan QR code untuk connect device
3. Tunggu device connected
4. Coba kirim ulang

### 🔄 Status Masih Pending?

**Alasan:**
- Device tidak terhubung saat pengiriman
- Nomor WhatsApp tidak valid
- Pesan berisi format tidak support

**Solusi:**
1. Check device connection
2. Cek nomor WhatsApp
3. Klik tombol "Kirim" lagi untuk retry
4. Lihat detail modal untuk error message

## 📊 Filter & Search

### Filter Tanggal:
- ✅ **Hari Ini** - Hanya ulang tahun hari ini
- ✅ **Minggu Ini** - 7 hari ke depan
- ✅ **Bulan Ini** - Sampai akhir bulan
- ✅ **Semua** - Tampilkan semua

### Filter Status:
- **Pending** - Belum dikirim
- **Terkirim** - Berhasil dikirim
- **Gagal** - Error saat pengiriman
- **Terjadwal** - Menunggu waktu jadwal

## 💡 Tips & Tricks

### 1. Bulk Create dari Excel
- Siapkan file dengan kolom: no_rkm_medis, no_hp
- Import via sinkronisasi (field nomor dari pasien master)

### 2. Customize Pesan
- Edit template di create form
- Gunakan emoji untuk lebih menarik
- Character count terhitung real-time

### 3. Schedule untuk Besok
- Jika mau kirim besok pagi jam 08:00
- Pilih "Jadwalkan"
- Set: [Besok] 08:00 AM
- Pastikan cron job running

### 4. Resend Gagal
- Jika status "Gagal", bisa langsung re-send
- Klik tombol "Kirim" di tabel
- Auto update ke "Terkirim" jika sukses

## 🎯 Use Cases

### Scenario 1: Pasien Ulang Tahun Hari Ini
```
1. Buka Dashboard
2. Filter: "Hari Ini"
3. Lihat pasien yang ulang tahun
4. Klik "Kirim" untuk setiap pasien
5. Status berubah ke "Terkirim" ✅
```

### Scenario 2: Buat Pengingat Pasien Baru
```
1. Klik "Tambah Pengingat"
2. Pilih pasien dari dropdown
3. Cek nomor WA sudah benar
4. Pilih template atau custom
5. Klik "Kirim Sekarang" atau jadwalkan
6. Kembali ke dashboard, lihat status
```

### Scenario 3: Sinkronisasi Data Pasien
```
1. Klik tombol "Sinkronisasi Data"
2. System query semua pasien + tgl lahir
3. Auto create pengingat yang belum ada
4. Success message + jumlah created
5. Dashboard refresh, lihat data baru
```

## 📈 Analytics

**Dashboard menampilkan KPI:**
- Jumlah ulang tahun hari ini
- Jumlah ulang tahun minggu ini
- Total pesan pending
- Total pesan terkirim

**Untuk advanced analytics:**
- Export data: `SELECT * FROM birthday_reminders WHERE DATE(created_at) = CURDATE()`
- Dashboard: Check status distribution

## 🔐 Security Notes

- Form protection: CSRF token auto included
- Input validation: Server-side + client-side
- Model binding: Auto prevent SQL injection
- Access: Open (bisa add middleware AUTH)

## 📞 Support

### Common Issues & Solutions

| Issue | Solution |
|-------|----------|
| Form tidak submit | Refresh page, clear cache |
| Data pasien tidak muncul | Cek pasien punya tgl_lahir |
| Nomor WA invalid | Format: 62812345678 (no +62) |
| Device offline | Reconnect di settings WA |
| Message too long | Max 1000 char, edit message |

### Logs Location
```
storage/logs/laravel-YYYY-MM-DD.log
```

Check logs untuk detail error saat send fail.

---

## 🚀 Next: Advanced Features

Untuk setup lebih lanjut:
- Lihat [BIRTHDAY_REMINDER_DOCUMENTATION.md](BIRTHDAY_REMINDER_DOCUMENTATION.md)
- Setup database queue monitoring
- Email notification untuk failed sends
- Custom template management

---

**Last Updated:** 2026-01-03  
**Version:** 1.0
