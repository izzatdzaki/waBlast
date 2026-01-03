# Troubleshooting QR Code Generation - Baileys Backend

## 🔴 Error: "Gagal membuat QR Code. Pastikan Baileys backend berjalan di port 3000."

### ✅ Solusi Lengkap

#### **1. Pastikan Backend Node.js Berjalan**

```bash
# Buka PowerShell terminal baru
cd C:\laragon\www\waBlast\backend
npm start
```

**Output yang benar:**
```
╔════════════════════════════════════════╗
║   WhatsApp Baileys API Server         ║
║   Running on http://localhost:3000    ║
╚════════════════════════════════════════╝
```

#### **2. Cek Konfigurasi Backend**

**File:** `backend/.env`
```env
PORT=3000
WHATSAPP_STORE_PATH=./sessions
```

Jika file tidak ada, buat dengan konfigurasi di atas.

#### **3. Cek Port 3000 Tidak Digunakan**

```powershell
# Cek port 3000
netstat -ano | findstr :3000

# Jika ada yang menggunakan, kill process
taskkill /PID <PID> /F
```

#### **4. Cek Koneksi dari Laravel**

Buka browser dan test endpoint:
- **Health Check:** http://localhost:3000/health
- **Get Sessions:** http://localhost:3000/sessions

Jika mendapat response JSON ✅ backend berjalan baik.

#### **5. Update Baileys URL di Settings**

1. Buka: http://127.0.0.1:8000/whatsapp/settings
2. Tab **"Koneksi"**
3. Update **"URL Baileys Backend"** ke `http://localhost:3000`
4. Klik **"Simpan Koneksi"**

#### **6. Cek Log untuk Detail Error**

**Laravel logs:**
```
storage/logs/laravel.log
```

**Backend logs:**
Lihat console tempat `npm start` berjalan

### 🔍 Debugging Checklist

- [ ] Backend sudah di-start dengan `npm start`
- [ ] Port 3000 terbuka dan tidak digunakan program lain
- [ ] URL di settings sudah benar: `http://localhost:3000`
- [ ] Backend tidak punya error di console
- [ ] Firewall/Antivirus tidak memblok localhost:3000
- [ ] Dependencies sudah install: `npm install`

### 📝 Langkah demi Langkah (Step by Step)

**Step 1: Buka Terminal PowerShell Baru**
```powershell
# Pastikan Anda di folder backend
cd C:\laragon\www\waBlast\backend
```

**Step 2: Install Dependencies (Jika Belum)**
```powershell
npm install
```

**Step 3: Jalankan Backend Server**
```powershell
npm start
```

Tunggu hingga muncul pesan:
```
Running on http://localhost:3000
```

**Step 4: Buka Tab Browser Baru**
```
http://127.0.0.1:8000/whatsapp/settings
```

**Step 5: Ke Tab "Perangkat" → Klik "Tambah Perangkat Baru"**

### 🚨 Error Messages & Solutions

| Error | Penyebab | Solusi |
|-------|---------|--------|
| Connection Refused | Backend tidak jalan | Jalankan `npm start` |
| Timeout | Backend lambat/error | Lihat console backend |
| Port already in use | Port 3000 sudah pakai | Kill process lain di port 3000 |
| Cannot find module | npm install belum | Jalankan `npm install` |
| ENOENT sessions folder | Folder sessions error | Backend akan buat otomatis |

### 🐛 Advanced Debugging

**Test Backend Endpoints dari PowerShell:**

```powershell
# Health check
Invoke-WebRequest -Uri "http://localhost:3000/health" -Method Get

# Get sessions
Invoke-WebRequest -Uri "http://localhost:3000/sessions" -Method Get

# Create new session
Invoke-WebRequest -Uri "http://localhost:3000/sessions/new" -Method Post -ContentType "application/json" -Body '{"device_id":"test_device"}'
```

### 📞 Kontak Support Information

**Backend Jika Error di Console:**

Cari pesan error yang bermula dengan `[` diikuti device ID, contoh:
```
[device_abc123] Error initializing WhatsApp: ...
```

Salin pesan ini untuk debugging lebih lanjut.

### ✨ After QR Code Works

Setelah berhasil scanning QR code:
1. Tunggu device status berubah jadi "Terhubung"
2. Perangkat akan muncul di daftar devices
3. Gunakan perangkat untuk mengirim pesan WhatsApp

---

**Last Updated:** 2026-01-03  
**Status:** Aktif & Tested
