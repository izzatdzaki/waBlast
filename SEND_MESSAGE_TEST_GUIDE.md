# Panduan Testing Cepat - Form Kirim Pesan WhatsApp ✅

## Pre-requisites
- Backend Node.js sudah running: `npm start` di port 3000
- Laravel sudah running: `php artisan serve` di port 8000
- Device WhatsApp sudah terhubung (status: Terhubung/active)

## Testing Steps

### 1. Buka Form Kirim Pesan
```
URL: http://127.0.0.1:8000/whatsapp/send
```

### 2. Verifikasi Device Dropdown
```
Harusnya muncul:
✅ "-- Pilih Device --" (default option)
✅ Device yang terhubung dengan format: device_name (phone_number)
   Contoh: device_abc123 (628123456789)

❌ JIKA KOSONG/ERROR: 
   - Device tidak aktif
   - Buat QR Code baru di Settings → Perangkat
   - Atau console error di browser (F12)
```

### 3. Test Nomor Manual dengan Format Berbeda
```
Test 1: Format 0812xxx
- Tipe Penerima: Nomor Manual
- Nomor: 0812345678901
- Hasil yang diharapkan: ✅ Auto convert ke 62812345678901

Test 2: Format +628xxx  
- Nomor: +628123456789
- Hasil yang diharapkan: ✅ Auto convert ke 628123456789

Test 3: Format 628xxx (sudah benar)
- Nomor: 628123456789
- Hasil yang diharapkan: ✅ Tetap 628123456789

Test 4: Format INVALID
- Nomor: 123456 atau 081 atau abc
- Hasil yang diharapkan: ❌ Error: "Format nomor telepon tidak valid"
```

### 4. Test Pesan Kustom
```
1. Pilih Device: (dari dropdown)
2. Tipe Penerima: Nomor Manual
3. Nomor: 628123456789 (ganti dengan nomor asli WhatsApp)
4. Tipe Pesan: Pesan Kustom
5. Pesan: "Test Pesan dari WhatsApp"
6. Klik: "Kirim Pesan"

Expected Results:
✅ Loading spinner muncul
✅ Notifikasi: "✅ Pesan berhasil dikirim!"
✅ Halaman redirect ke dashboard
✅ Pesan muncul di message history
✅ Status: "sent"
```

### 5. Test Pesan dari Pasien Database
```
1. Pilih Device: (dari dropdown)
2. Tipe Penerima: Pasien (dari Database)
3. Pilih Pasien: (dropdown terpopulasi dengan nama + nomor)
4. Tipe Pesan: Pesan Kustom
5. Pesan: "Test dari Database"
6. Klik: "Kirim Pesan"

Expected Results:
✅ Nomor dari pasien otomatis diisi
✅ Pesan berhasil dikirim
✅ Database record created dengan device_id
```

### 6. Test Error Cases
```
Test A: Tidak ada device dipilih
- Skip device selection
- Klik submit
- Harusnya error: "Pilih device WhatsApp terlebih dahulu!"

Test B: Nomor kosong
- Pilih device
- Skip nomor
- Klik submit
- Harusnya error: "Masukkan nomor telepon!" (manual)
           atau "Pilih pasien terlebih dahulu!" (database)

Test C: Pesan kosong
- Isi device dan nomor
- Skip pesan
- Klik submit
- Harusnya error: "Masukkan pesan!"

Test D: Device tidak aktif
- Device_id di DB berubah status ke 'inactive'
- Submit form
- Harusnya error: "Device tidak ditemukan atau tidak aktif"
```

## Verifikasi Database

### Check BlastMessage Table
```php
// Di Laravel tinker
php artisan tinker

>>> $msg = \App\Models\BlastMessage::latest()->first();
>>> $msg->device_id  // Should NOT be null
>>> $msg->device->device_name  // Should show device name
>>> $msg->status // Should be 'sent'
```

### Check Device Table
```php
>>> $device = \App\Models\WhatsAppDevice::first();
>>> $device->device_name
>>> $device->status  // Should be 'active'
>>> $device->messages->count()  // Messages sent from this device
```

## Console Validation (Browser F12)

### Check JavaScript Functions
```javascript
// Di browser console (F12 → Console)

// Check if loadDevices ran
typeof loadDevices  // Should return "function"

// Check if device dropdown populated
document.getElementById('device_id').options.length  // Should be > 1

// Check phone formatter
formatPhoneNumber('0812345678901')  // Should return '62812345678901'

// Check error handling
document.querySelectorAll('.alert')  // Check if error alerts display
```

## Expected Behavior Summary

| Test Case | Expected | Pass/Fail |
|-----------|----------|-----------|
| Device dropdown load | Device list populated | ✅ |
| Format 0812xxx | Convert to 628... | ✅ |
| Format +628xxx | Convert to 628... | ✅ |
| Invalid format | Error message shown | ✅ |
| No device selected | Error "Pilih device..." | ✅ |
| Valid form submit | Success notification | ✅ |
| Message saved | device_id NOT null | ✅ |
| Database integrity | Foreign key valid | ✅ |

## Performance Checks

### Load Time
- Page load: < 2 seconds
- Device dropdown: instant (loaded in JS)
- Submit/Send: < 5 seconds

### Network Requests
- GET /api/whatsapp/devices: 1 request at page load
- POST /api/whatsapp/send: 1 request on form submit
- No extra polling or redundant calls

## Troubleshooting Quick Guide

| Problem | Cause | Solution |
|---------|-------|----------|
| Device dropdown kosong | Device tidak aktif | Buat QR pairing baru |
| Form submit gagal | No device selected | Pilih device terlebih dahulu |
| "Invalid phone format" | Format salah | Gunakan 0812 atau 628 |
| Notifikasi tidak muncul | Cache stale | Hard refresh (Ctrl+F5) |
| Database error | Migration belum jalan | `php artisan migrate` |
| Backend error | Node.js tidak running | `cd backend && npm start` |

## Success Criteria

✅ SEMUA test cases berhasil
✅ Tidak ada error di console (F12)
✅ Database BlastMessage terisi dengan device_id
✅ WhatsApp menerima pesan dalam < 5 detik
✅ Form validation bekerja untuk semua field
✅ Phone number format otomatis benar
✅ Device selection wajib (tidak bisa skip)

---

**Estimasi Testing Time**: 10-15 menit
**Backend Status**: Port 3000 (check via browser atau terminal)
**Frontend Status**: Port 8000 (check via browser)
