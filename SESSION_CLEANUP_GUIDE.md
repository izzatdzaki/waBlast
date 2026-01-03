# 🧹 Session Cleanup Guide

## Masalah: "QR refs attempts ended"

Error ini terjadi ketika:
- ⏱️ QR code timeout (tidak di-scan dalam waktu 3 menit)
- 🔄 Multiple session attempts dengan QR yang sama
- 💾 Session lama tersimpan dan corrupt
- ❌ Failed pairing attempts

## ✅ Solusi: Bersihkan Session

### **Opsi 1: PowerShell Script (Recommended)**

```powershell
cd C:\laragon\www\waBlast\backend
.\cleanup-sessions.ps1
```

Output:
```
🧹 Cleaning WhatsApp Sessions...
✅ Cleanup complete!
   Removed sessions: 25
   Sessions remaining: 0

📝 Next steps:
   1. Restart the backend server: npm start
   2. Refresh the browser
   3. Generate a fresh QR code
```

### **Opsi 2: Manual Cleanup (PowerShell)**

```powershell
cd C:\laragon\www\waBlast\backend\sessions
Get-ChildItem -Directory | Remove-Item -Recurse -Force
```

### **Opsi 3: Manual Cleanup (Explorer)**

1. Buka: `C:\laragon\www\waBlast\backend\sessions\`
2. Hapus semua folder kecuali `.gitkeep`
3. Restart backend server

---

## 🚀 Langkah Setelah Cleanup

### **1. Restart Backend Server**
```powershell
# Terminal tempat backend berjalan: Ctrl+C untuk stop
# Kemudian jalankan ulang:
npm start
```

Tunggu sampai muncul:
```
Running on http://localhost:3000
```

### **2. Refresh Browser**
```
http://127.0.0.1:8000/whatsapp/settings
```

### **3. Generate QR Code Fresh**
1. Tab "Perangkat"
2. Klik "Tambah Perangkat Baru"
3. Masukkan nama device
4. Scan QR dengan WhatsApp
5. **Pastikan scan dalam 3 menit sebelum timeout**

---

## ⚠️ Important Notes

### **QR Code Timeout**
- QR code valid hanya **3 menit**
- Jika tidak di-scan dalam waktu itu, akan error
- Solusi: Generate QR baru dan scan langsung

### **Session Files**
- Tersimpan di: `backend/sessions/`
- Setiap device pairing = 1 folder session
- Old sessions bisa menyebabkan conflict
- Cleanup secara berkala jika ada banyak percobaan

### **Best Practice**
```
1. Generate QR Code
   ↓
2. Langsung scan dengan WhatsApp (jangan tunggu lama)
   ↓
3. Confirm pairing di WhatsApp
   ↓
4. Device status: "Terhubung"
   ↓
5. Baru gunakan untuk send messages
```

---

## 🔍 Cek Session Status

### **Lihat Semua Session yang Aktif**
```powershell
# Di backend terminal, lihat log untuk device yang connect
# Output: [device_xxx] Connection opened
```

### **Lihat Session Folder**
```powershell
# PowerShell
cd C:\laragon\www\waBlast\backend\sessions
Get-ChildItem -Directory | ForEach-Object { 
    Write-Host "$($_.Name) - $(Get-Item $_.FullName -Force).CreationTime"
}
```

---

## 🐛 Troubleshooting

### **❌ "Error: QR refs attempts ended"**

**Penyebab:**
- QR code expired (> 3 menit)
- Device tidak mencoba connect
- Network issue

**Solusi:**
1. Cleanup sessions
2. Generate QR baru
3. Scan langsung (jangan delay)

### **❌ "Permission denied" saat cleanup**

**Penyebab:**
- Backend masih berjalan (file terkunci)

**Solusi:**
1. Stop backend server (Ctrl+C)
2. Jalankan cleanup script
3. Restart backend (`npm start`)

### **❌ ".gitkeep" file hilang**

**Tidak ada masalah!**
- File tersebut hanya untuk Git tracking
- Sistem akan buat folder otomatis saat ada session baru

---

## 📊 Session Cleanup Frequency

### **Cleanup Kapan:**
- ✅ Setiap kali gagal scanning QR
- ✅ Sebelum setup device baru
- ✅ Jika ada multiple error attempts
- ✅ Setiap minggu jika intensive use

### **Tidak Perlu Cleanup Jika:**
- ✅ Device sudah terhubung (status: "Terhubung")
- ✅ Messages sudah bisa dikirim
- ✅ Hanya jarang pairing device baru

---

## 🎯 Prevention Tips

1. **Scan QR Immediately**
   - Jangan menunda setelah generate QR
   - Waktu timeout hanya 3 menit

2. **Keep Network Stable**
   - Pastikan WiFi/Internet stabil
   - Jangan disconnect saat scanning

3. **Use Fresh Device**
   - Pastikan WhatsApp di ponsel is up-to-date
   - Clear cache WhatsApp jika ada masalah

4. **Monitor Logs**
   - Check backend console untuk error
   - Laravel logs di `storage/logs/laravel.log`

5. **Regular Cleanup**
   - Cleanup sessions setiap 2-3 minggu
   - Terutama jika banyak percobaan pairing

---

## 📋 Automated Cleanup

### **Weekly Cleanup Script** (Windows Scheduler)

Buat file: `cleanup-weekly.bat`
```batch
@echo off
cd C:\laragon\www\waBlast\backend
powershell -ExecutionPolicy Bypass -File cleanup-sessions.ps1
echo Cleanup complete at %date% %time% >> cleanup.log
```

### **Schedule di Windows**
1. Press `Win+R` → type `taskschd.msc`
2. Create Basic Task
3. Set trigger: Weekly (Sunday, 2 AM)
4. Set action: Run `cleanup-weekly.bat`

---

## ✨ After Cleanup Checklist

- [ ] Backend server restarted
- [ ] Browser refreshed
- [ ] Settings page loads OK
- [ ] Status shows "Online"
- [ ] Can generate QR code
- [ ] QR code can be scanned
- [ ] Device shows as "Terhubung"

---

**Last Updated:** 2026-01-03  
**Status:** ✅ Production Ready
