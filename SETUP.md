# 📖 PANDUAN SETUP waBlast - WhatsApp Reminder System

## 🎯 Prerequisites

Pastikan system sudah punya:
- **PHP 7.4 atau lebih tinggi** (project ini PHP 7.4)
- **Node.js v14+** (untuk Baileys backend)
- **MySQL 5.7+** atau **MariaDB**
- **Composer** (PHP dependency manager)
- **Laragon** atau Web Server lainnya (Apache/Nginx)

---

## 📦 STEP 1: Clone & Install Dependencies

```bash
# 1. Navigate ke folder web project
cd C:\laragon\www

# 2. Clone atau copy project
git clone <repository-url> waBlast
cd waBlast

# 3. Install PHP dependencies
composer install

# 4. Install Node.js dependencies untuk backend
cd backend
npm install
cd ..
```

---

## ⚙️ STEP 2: Environment Configuration

### 📄 Copy & Setup `.env` Laravel

```bash
# Copy file .env
cp .env.example .env

# Generate APP_KEY
php artisan key:generate
```

### 📝 Edit `.env` dengan database credentials:

```env
APP_NAME=waBlast
APP_ENV=local
APP_DEBUG=true
APP_URL=http://wablast.test:88

# Database Configuration
DB_CONNECTION=mysql
DB_HOST=172.23.227.178          # IP db server
DB_PORT=3306
DB_DATABASE=sikmasyita          # Nama database
DB_USERNAME=client              # Username db
DB_PASSWORD=Masyita@123         # Password db

# WhatsApp Configuration
BAILEYS_API_URL=http://localhost:3000
BAILEYS_TIMEOUT=30
WA_RETRY_ATTEMPTS=3
WA_RETRY_DELAY=60
WA_TIMEOUT=30
```

### 📄 Setup `.env` Backend (Node.js)

Edit file `backend/.env`:

```env
NODE_ENV=development
PORT=3000

# Laravel Server (untuk webhook)
LARAVEL_SERVER_URL=http://wablast.test:88

# WhatsApp Session Storage
WHATSAPP_STORE_PATH=./sessions
MAX_RECONNECT_RETRIES=5
```

---

## 🗄️ STEP 3: Database Setup

### 1️⃣ Create Database (jika belum ada)

```bash
# Via MySQL client
mysql -h 172.23.227.178 -u client -p
mysql> CREATE DATABASE sikmasyita;
mysql> exit;
```

### 2️⃣ Run Laravel Migrations

```bash
# Generate tables untuk WhatsApp settings & devices
php artisan migrate

# Atau jika tables sudah ada, seeding saja:
php artisan db:seed
```

### 3️⃣ Initialize WhatsApp Settings (opsional, auto-create jika kosong)

```bash
# Settings akan auto-create saat pertama kali akses
# Default values:
# - Baileys URL: http://localhost:3000
# - Message Retention: 30 hari
# - Max Message: 4096 karakter
# - API Rate Limit: 20 req/menit
```

---

## 🚀 STEP 4: Run Project

### Terminal 1: Laravel Development Server

```bash
cd C:\laragon\www\waBlast

# Opsi A: Pakai artisan serve
php artisan serve --host=0.0.0.0 --port=8088

# Opsi B: Pakai Laragon built-in (jika sudah config)
# Akses: http://wablast.test:88 di browser
```

### Terminal 2: Baileys Backend (WhatsApp)

```bash
cd C:\laragon\www\waBlast\backend

# Start Node.js server
node server.js

# Atau pakai npm
npm start

# Backend akan running di http://localhost:3000
```

---

## ✅ STEP 5: Verify Installation

Buka browser dan akses:

```
http://wablast.test:88/dashboard/tindakan
```

Atau jika pakai php artisan serve:
```
http://localhost:8088/dashboard/tindakan
```

### ✅ Test Checklist:

- [ ] Dashboard Tindakan load tanpa error
- [ ] Data pasien terlihat dari database
- [ ] Klik tombol WhatsApp → Modal muncul
- [ ] Dropdown perangkat WhatsApp terlihat

---

## 🔧 STEP 6: WhatsApp Device Setup

### 1️⃣ Buka Settings Page

```
http://wablast.test:88/whatsapp/settings
```

### 2️⃣ Tab "Koneksi" - Verify Baileys Backend

- Status seharusnya **Online** (jika backend running)
- Klik tombol **"Cek Status"** untuk test connection

### 3️⃣ Tab "Perangkat" - Tambah Device Baru

1. Klik **"Tambah Perangkat Baru"**
2. Masukkan nama device (contoh: "WhatsApp Server 1")
3. Klik **"Lanjut"**
4. **QR Code akan muncul**
5. Buka WhatsApp di smartphone → Settings → **Linked Devices**
6. **Scan QR code** dengan camera
7. Tunggu hingga status berubah menjadi **"✓ Terhubung"**

---

## 📋 Project Structure

```
waBlast/
├── app/
│   ├── Http/Controllers/
│   │   ├── DashboardTindakanController.php    # Treatment dashboard
│   │   ├── WhatsAppDashboardController.php    # WhatsApp settings
│   │   └── ...
│   ├── Models/
│   │   ├── RawatJlDr.php                      # Treatment data
│   │   ├── WhatsAppSettings.php               # WA configuration
│   │   ├── WhatsAppDevice.php                 # Connected devices
│   │   ├── BlastTemplate.php                  # Message templates
│   │   └── ...
│   └── ...
├── backend/
│   ├── server.js                              # Baileys API server
│   ├── .env                                   # Backend config
│   ├── sessions/                              # WhatsApp sessions
│   └── package.json
├── routes/
│   ├── web.php                                # Web routes
│   └── api.php                                # API routes
├── resources/
│   ├── views/
│   │   ├── dashboard/tindakan/
│   │   ├── whatsapp/settings.blade.php
│   │   └── ...
│   └── ...
├── database/
│   ├── migrations/                            # Database schema
│   └── seeders/
└── .env                                       # Laravel config
```

---

## 🎨 Available Features

### 1. **Dashboard Tindakan**
- ✅ List perawatan rawat jalan
- ✅ Filter: tanggal, jenis tindakan, dokter
- ✅ Export ke Excel (.xlsx)
- ✅ Kirim WhatsApp langsung dari dashboard

### 2. **WhatsApp Settings**
- ✅ Manage Baileys backend connection
- ✅ Add/remove WhatsApp devices
- ✅ Configure webhook, API settings
- ✅ Message templates (3 built-in templates)

### 3. **Message Templates**
Template otomatis tersedia:

1. **Follow Up Kelahiran** - Reminder kelahiran
2. **Reminder USG Kontrol** - Reminder jadwal USG
3. **Reminder HPL** - Reminder Hari Perkiraan Lahir

---

## 🐛 Troubleshooting

### ❌ Error: "Connection refused :3000"
**Penyebab:** Backend Baileys tidak running

**Solusi:**
```bash
# Terminal baru, go to backend folder
cd backend
node server.js

# cek port 3000 tidak terpakai
netstat -ano | findstr :3000
```

### ❌ Error: "Unknown database 'sikmasyita'"
**Penyebab:** Database tidak exist atau credentials salah

**Solusi:**
```bash
# Verify koneksi database
php artisan tinker
# Di dalam tinker:
DB::connection()->getPdo();  # Cek koneksi
```

### ❌ QR Code tidak muncul
**Penyebab:** Backend tidak running atau webhook URL salah

**Solusi:**
1. Pastikan backend running: `node backend/server.js`
2. Cek `.env` backend: `LARAVEL_SERVER_URL=http://wablast.test:88`
3. Check browser console untuk error messages

### ❌ "Column 'phone_number' cannot be null"
**Penyebab:** Database constraint error pada update device

**Solusi:**
```sql
-- Update table schema
ALTER TABLE whatsapp_devices MODIFY phone_number VARCHAR(255) DEFAULT '';
```

---

## 📱 Sending WhatsApp Messages

### 1️⃣ Via Dashboard Tindakan

1. Buka: `http://wablast.test:88/dashboard/tindakan`
2. Klik icon **WhatsApp** pada pasien yang dituju
3. Pilih **Template Pesan** (atau custom)
4. Isi placeholder fields jika ada
5. Pilih **Perangkat WhatsApp**
6. Klik **Kirim**

### 2️⃣ Message Template Variables

```
{nama_pasien}     → Auto-filled dengan nama pasien
{tanggal_usg}     → Input manually
{jam_poli}        → Input manually
{tanggal_hpl}     → Input manually
```

---

## 📊 Database Tables Reference

### `whatsapp_settings`
Configuration untuk WhatsApp integration

### `whatsapp_devices`
Connected WhatsApp devices (Linked Devices)

### `blast_templates`
Message templates untuk kirim pesan

### `blast_messages`
History pesan yang telah dikirim

### `rawat_jl_dr`
Data perawatan rawat jalan (dari database lama)

---

## 🔐 Security Notes

- ✅ CSRF token protection pada semua form
- ✅ Request validation untuk semua input
- ✅ Database credentials di .env (tidak di git)
- ✅ WhatsApp session di local folder `backend/sessions/`

---

## 📞 Support & Debugging

### Lihat Logs

```bash
# Laravel logs
tail -f storage/logs/laravel.log

# Backend logs
# Lihat di terminal saat backend running

# Database logs
# Check MySQL error log sesuai OS
```

### Enable Debug Mode

Edit `.env`:
```env
APP_DEBUG=true          # Laravel debug
NODE_ENV=development    # Backend debug
```

---

## ✨ Next Steps

1. ✅ Setup project seperti panduan ini
2. ✅ Connect WhatsApp device via QR code
3. ✅ Test kirim pesan dari dashboard
4. ✅ Monitor message history
5. ✅ Customize templates sesuai kebutuhan

---

**Happy coding! 🚀**
