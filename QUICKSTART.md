# 🚀 QUICK START - waBlast

## 5 Menit Setup

### Prerequisites
```
✅ PHP 7.4+
✅ Node.js v14+
✅ MySQL
✅ Composer installed
```

---

## 1️⃣ Install Dependencies

```bash
cd C:\laragon\www\waBlast

# PHP
composer install

# Node.js Backend
cd backend && npm install && cd ..
```

---

## 2️⃣ Configure .env

**Laravel (.env):**
```env
DB_HOST=172.23.227.178
DB_DATABASE=sikmasyita
DB_USERNAME=client
DB_PASSWORD=Masyita@123
```

**Backend (backend/.env):**
```env
LARAVEL_SERVER_URL=http://wablast.test:88
PORT=3000
```

---

## 3️⃣ Database

```bash
php artisan migrate
```

---

## 4️⃣ Run 2 Terminals

**Terminal 1: Laravel**
```bash
php artisan serve --host=0.0.0.0 --port=8088
# Atau gunakan Laragon: http://wablast.test:88
```

**Terminal 2: Backend**
```bash
cd backend
node server.js
```

---

## 5️⃣ Test

```
Browser: http://wablast.test:88/dashboard/tindakan
```

---

## 🔌 Connect WhatsApp

1. Buka: `http://wablast.test:88/whatsapp/settings`
2. Tab Perangkat → **Tambah Perangkat**
3. **Scan QR** dengan WhatsApp Linked Devices
4. Status berubah menjadi **Terhubung** ✓

---

## 💬 Send Message

1. Dashboard Tindakan
2. Klik icon **WhatsApp** pada pasien
3. Pilih **Template**
4. **Kirim**

---

## ❓ Issues?

See `SETUP.md` untuk troubleshooting detail
