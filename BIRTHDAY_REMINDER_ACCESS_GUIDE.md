# Birthday Reminder Dashboard - Access Guide

## 🎯 Quick Access

### Primary URL
```
http://localhost/dashboard/birthday-reminder
```

### Menu Navigation
```
Dashboard Menu → Birthday Reminder Dashboard
```

---

## 📱 Dashboard Interface

### Top Section
```
[Header]
Dashboard Pengingat Ulang Tahun
Kelola pengiriman pesan pengingat ulang tahun kepada pasien

[Buttons]
[+ Tambah Pengingat]  [🔄 Sinkronisasi Data]
```

### Statistics Cards
```
┌─────────────────┬──────────────────┬──────────────┬──────────────┐
│ Hari Ini        │ Minggu Ini       │ Pesan Pending│ Pesan Terkirim
│ [Count]         │ [Count]          │ [Count]      │ [Count]
└─────────────────┴──────────────────┴──────────────┴──────────────┘
```

### Filter Section
```
Filter Tanggal:
[Hari Ini] [Minggu Ini] [Bulan Ini] [Semua]

Filter Status:
[Dropdown: Semua Status / Pending / Terkirim / Gagal / Terjadwal]
```

### Data Table
```
No | Pasien | No RM | Tgl Lahir | No WhatsApp | Status | Aksi
──────────────────────────────────────────────────────────────────
1  | [Name] | [ID]  | [Date]   | [Number]   | Badge  | [Buttons]
```

### Table Actions
```
[📤 Kirim] - Send reminder (if pending)
[👁️ Detail] - View detail modal
[🗑️ Hapus] - Delete reminder
```

---

## 🔧 Main Features Access

### 1. View Dashboard
```
Step 1: Click "Dashboard Pengingat Ulang Tahun" in menu
Step 2: See statistics and list of all reminders
Step 3: Use filters to narrow down data
```

### 2. Create New Reminder
```
Step 1: Click [+ Tambah Pengingat] button
Step 2: Fill form fields:
        - Pilih Pasien (dropdown)
        - Nomor WhatsApp (text input)
        - Pesan (textarea with templates)
        - Pengiriman (radio: now or schedule)
Step 3: Click [Simpan Pengingat]
```

### 3. Send Reminder
```
Manual Send:
Step 1: In dashboard table, find reminder with status "Pending"
Step 2: Click [📤 Kirim] button
Step 3: Confirm dialog
Step 4: Status auto update to "Terkirim" ✅

Auto Send (Daily):
Step 1: Setup cron job (or Windows Task Scheduler)
Step 2: Command runs daily: php artisan birthday-reminder:send-daily
Step 3: All pending reminders for today auto sent
```

### 4. View Details
```
Step 1: Click [👁️ Detail] button
Step 2: Modal opens showing:
        - Pasien name
        - No RM
        - Tgl Lahir
        - No WhatsApp
        - Pesan content
        - Status
        - Sent time (if sent)
        - API Response (if failed)
Step 3: Click [Tutup] to close
```

### 5. Delete Reminder
```
Step 1: Click [🗑️ Hapus] button
Step 2: Confirm dialog
Step 3: Reminder deleted from database
Step 4: Dashboard refreshes
```

### 6. Sync Patient Data
```
Step 1: Click [🔄 Sinkronisasi Data] button
Step 2: System queries all pasien with tgl_lahir
Step 3: Auto creates reminders for new patients
Step 4: Success message shows count created
Step 5: Dashboard refreshes with new data
```

---

## 📝 Create Form Fields

### Patient Selection
```
Label: Pilih Pasien *
Type: Dropdown (select)
Options: [no_rkm_medis - nm_pasien]
Required: YES
Action: Auto-populates patient info below
```

### Patient Info (Auto-display)
```
Nama Pasien: [Read-only text]
Tgl Lahir: [Read-only date]
Shows only after patient selected
```

### WhatsApp Number
```
Label: Nomor WhatsApp Penerima *
Type: Text with prefix
Format: +62 [text input]
Pattern: 9-12 digits
Example: 812345678901
Required: YES
Help: Masukkan nomor tanpa 0 di depan
```

### Message
```
Label: Pesan *
Type: Textarea (5 rows)
Min: 5 characters
Max: 1000 characters
Character count: Real-time counter
Required: YES
Features: Template selector buttons above
```

### Template Selector
```
4 Buttons:
[Template 1] - Standard greeting
[Template 2] - English version
[Template 3] - With health reminder
[Custom] - Free text

Clicking button auto-fills textarea with template
```

### Send Option
```
Radio buttons:
○ Kirim Sekarang (default)
○ Jadwalkan untuk Tanggal Tertentu

If Jadwalkan selected:
  └─ Datetime picker shows: [Date] [Time]
     Format: YYYY-MM-DD HH:MM
     Validation: Must be in future
```

### Buttons
```
[💾 Simpan Pengingat] - Submit form
[❌ Batal] - Cancel and back to dashboard
```

---

## 🎨 UI Elements Reference

### Status Badges
```
Pending   → Yellow badge
Terkirim  → Green badge
Gagal     → Red badge
Terjadwal → Blue badge
```

### Action Buttons
```
Primary: [+ Tambah Pengingat] [Simpan Pengingat]
Secondary: [🔄 Sinkronisasi Data] [Batal]
Danger: [🗑️ Hapus]
Info: [👁️ Detail]
Success: [📤 Kirim]
```

### Alert Messages
```
Success (Green): Operasi berhasil
Warning (Yellow): Peringatan/info
Error (Red): Terjadi kesalahan
```

---

## 🔍 Dashboard Filters

### Date Filter (Buttons)
```
[Hari Ini]   - Only today's birthdays
[Minggu Ini] - Next 7 days
[Bulan Ini]  - Current month
[Semua]      - All records
```

### Status Filter (Dropdown)
```
[All Status]
- Pending
- Terkirim
- Gagal
- Terjadwal
```

### Pagination
```
Shows 15 items per page
[< Previous] [1] [2] [3] [Next >]
```

---

## 📋 Modal Details

### Detail Modal Structure
```
[Header]
🎂 Detail Pengingat Ulang Tahun
[Close button]

[Body]
Nama Pasien: [value]
No RM: [value]
Tgl Lahir: [date formatted]
No WhatsApp: [phone]
Pesan: [message text - italic]
Status: [badge]
[Sent time - if applicable]
[API Response - if error - pre-formatted JSON]

[Footer]
[Tutup] button
```

---

## 🚀 Workflow Examples

### Example 1: Send Birthday Message Now
```
1. Open: /dashboard/birthday-reminder
2. Find: Patient name in table
3. Check: Status is "Pending"
4. Click: [📤 Kirim] button
5. Confirm: Dialog "Kirim pesan pengingat sekarang?"
6. Wait: Message sending...
7. Refresh: Table auto-updates
8. Result: Status changes to "Terkirim" ✅
```

### Example 2: Create & Send Later
```
1. Click: [+ Tambah Pengingat]
2. Select: Patient dari dropdown
3. Enter: No WhatsApp
4. Select: Template 3 (with health reminder)
5. Choose: "Jadwalkan untuk Tanggal Tertentu"
6. Set: Date = 2026-02-01, Time = 08:00 AM
7. Click: [Simpan Pengingat]
8. Result: Saved with status "Scheduled"
9. Later: Cron job triggers send automatically
```

### Example 3: View & Understand Error
```
1. Find: Reminder with status "Gagal"
2. Click: [👁️ Detail]
3. Modal opens showing:
   - Error message in "Response API"
   - JSON format with error details
4. Possible issues:
   - Device offline: Reconnect in settings
   - Invalid number: Edit and resend
   - Message error: Check content
5. Fix: Make changes and click [📤 Kirim] again
```

---

## 💡 Tips for Best Experience

### 1. Format Phone Numbers Correctly
```
✓ CORRECT: 081234567890 or 6281234567890
✗ WRONG: +6281234567890 or 0081234567890

Convert:
081234567890 → 6281234567890 (remove 0, add 62)
```

### 2. Use Templates for Consistency
```
Instead of: Free-typing every message
Use: Pick relevant template + minor edit
Saves: Time, ensures consistency, proper format
```

### 3. Schedule for Optimal Time
```
Medical reminders: 08:00 AM (morning)
Wish reminders: 00:00 AM (midnight start of day)
Consider: Patient timezone
```

### 4. Backup Important Data
```
Export: Use database export for backup
Schedule: Regular backups via MySQL
Monitor: Check logs for failed sends
```

### 5. Track Success Rate
```
Watch: Stat cards for sent/pending counts
Review: Modal responses for failure reasons
Adjust: Templates or timing based on results
```

---

## 🆘 Troubleshooting Quick Access

### "Tidak ada device WhatsApp yang terhubung"
```
Go to: /whatsapp/settings
Action: Scan QR code to connect device
Wait: Device status becomes "connected"
Then: Retry send
```

### "Form tidak submit"
```
Check: All required fields filled (marked with *)
Check: Browser console for JavaScript errors
Try: Refresh page with Ctrl+F5
Try: Clear browser cache
```

### "Nomor WhatsApp tidak valid"
```
Format: 62812345678 (no +, no 0)
Length: 10-14 digits after 62
Check: No spaces or special characters
Example: 6281234567890 ✓
```

### "Pesan terlalu panjang"
```
Limit: 1000 characters maximum
Current: See counter in form (real-time)
Solution: Shorten message or split in 2
```

---

## 📞 Support Contacts

For issues or questions:
1. Check documentation files
2. Review error messages carefully
3. Check API response in detail modal
4. Check logs in `storage/logs/laravel-*.log`
5. Contact development team with:
   - Error message exact text
   - API response (if available)
   - Steps to reproduce
   - Browser/OS information

---

## 📚 Related Documentation

- [BIRTHDAY_REMINDER_DOCUMENTATION.md](BIRTHDAY_REMINDER_DOCUMENTATION.md) - Full reference
- [BIRTHDAY_REMINDER_QUICK_START.md](BIRTHDAY_REMINDER_QUICK_START.md) - Quick start guide
- [BIRTHDAY_REMINDER_IMPLEMENTATION_SUMMARY.md](BIRTHDAY_REMINDER_IMPLEMENTATION_SUMMARY.md) - Technical details

---

**Last Updated:** 2026-01-03  
**Version:** 1.0
