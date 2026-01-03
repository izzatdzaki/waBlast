# 🚀 Quick Start - Menjalankan Baileys Backend untuk QR Code

## ⚡ Mulai Cepat (3 Langkah)

### Langkah 1: Buka PowerShell Terminal
```powershell
# Buka PowerShell baru (tekan Win+R, ketik PowerShell)
```

### Langkah 2: Navigasi ke Folder Backend
```powershell
cd C:\laragon\www\waBlast\backend
```

### Langkah 3: Jalankan Server
```powershell
npm start
```

Tunggu hingga muncul:
```
╔════════════════════════════════════════╗
║   WhatsApp Baileys API Server         ║
║   Running on http://localhost:3000    ║
╚════════════════════════════════════════╝
```

---

## ✅ Selesai!

Sekarang buka halaman settings WhatsApp dan coba buat QR code:
```
http://127.0.0.1:8000/whatsapp/settings
```

Tab "Perangkat" → Klik "Tambah Perangkat Baru"

---

## 🐛 Jika Ada Error

### ❌ "npm: command not found"
**Solusi:** Instal Node.js dari https://nodejs.org/

### ❌ "ENOENT: no such file or directory"
**Solusi:** Jalankan:
```powershell
npm install
npm start
```

### ❌ "Port 3000 already in use"
**Solusi:** Kill process yang menggunakan port:
```powershell
netstat -ano | findstr :3000
taskkill /PID <PID> /F
npm start
```

### ❌ "Cannot connect to backend"
**Solusi:**
1. Pastikan backend terminal masih terbuka
2. Cek apakah muncul error di console backend
3. Buka http://localhost:3000/health di browser untuk tes
4. Update URL di settings jika berbeda

---

## 📋 Checklist

- [ ] Terminal backend berjalan di port 3000
- [ ] Muncul message "Running on http://localhost:3000"
- [ ] Halaman settings bisa dibuka
- [ ] Bisa generate QR code
- [ ] Bisa scan QR dengan WhatsApp

---

## 🆘 Butuh Bantuan Lebih?

Baca file lengkap: `BAILEYS_QR_TROUBLESHOOTING.md`

---

**Status:** ✅ Siap digunakan  
**Last Updated:** 2026-01-03
