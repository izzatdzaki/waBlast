# Birthday Reminder Dashboard - Setup Instructions

## ✅ What's Already Done

The Birthday Reminder Dashboard has been **FULLY IMPLEMENTED** and ready to use. Here's what's included:

### Database
- ✅ Table `birthday_reminders` created with proper schema
- ✅ Foreign key constraints configured
- ✅ Indexes for performance optimization
- ✅ 0 rows (empty, ready for data)

### Code
- ✅ Model: `BirthdayReminder` with relationships
- ✅ Controller: `BirthdayReminderController` with 7 methods
- ✅ Views: Index dashboard + Create form (Blade templates)
- ✅ Routes: 5 routes configured under `dashboard` prefix
- ✅ Jobs: `SendBirthdayReminderJob` for queue
- ✅ Commands: `SendDailyBirthdayReminders` for daily execution

### Documentation
- ✅ Full technical documentation
- ✅ Quick start guide
- ✅ Access guide with UI reference
- ✅ Implementation summary

---

## 🚀 How to Start Using

### Step 1: Verify Installation (5 minutes)
```bash
# Open your browser
http://localhost/dashboard/birthday-reminder

# You should see:
- Empty dashboard (no reminders yet)
- Statistics cards
- Buttons: "Tambah Pengingat", "Sinkronisasi Data"
- Empty table with column headers
```

### Step 2: Sync Patient Data (2 minutes)
```bash
# Click: [🔄 Sinkronisasi Data] button
# System will:
1. Query all pasien with tgl_lahir
2. Auto create birthday reminders
3. Show success message with count

# Result: Dashboard now shows patient birthdays
```

### Step 3: Try Manual Send (5 minutes)
```bash
# In dashboard table:
1. Find a reminder with status "Pending"
2. Click [📤 Kirim] button
3. Confirm dialog
4. Message auto sends to WhatsApp
5. Status changes to "Terkirim" ✅

# Note: WhatsApp device must be connected!
```

### Step 4: Create Custom Reminder (10 minutes)
```bash
# Click: [+ Tambah Pengingat] button
# Fill form:
1. Select patient from dropdown
2. Enter WhatsApp number
3. Select template or write custom
4. Choose: "Kirim Sekarang" or "Jadwalkan"
5. Click [Simpan Pengingat]

# Result: New reminder saved, auto-sent if chose "Now"
```

---

## ⚙️ Optional: Setup Automatic Daily Sending

### Option A: Windows Task Scheduler (Windows Users)

**Time Required:** 10 minutes

```
1. Open: Task Scheduler (Task Penjadwal)
   - Win + R → taskschd.msc

2. Create New Task:
   - Name: "Send Birthday Reminders"
   - Description: "Daily birthday reminder sending"

3. Triggers tab:
   - New → Daily
   - Time: 08:00 AM
   - Repeat every 1 day

4. Actions tab:
   - New → Start a program
   - Program: C:\laragon\bin\php\php8.1.0-Win32-x64-portable\php.exe
   - Arguments: C:\laragon\www\waBlast\artisan birthday-reminder:send-daily
   - Start in: C:\laragon\www\waBlast

5. Conditions tab:
   - Uncheck "Stop task if running longer than 3 hours"
   
6. Click OK and save
```

### Option B: Cron Job (Linux/Mac Users)

**Time Required:** 5 minutes

```bash
# Edit crontab
crontab -e

# Add line (runs daily at 08:00 AM)
0 8 * * * cd /path/to/waBlast && php artisan birthday-reminder:send-daily >> /var/log/birthday-reminders.log 2>&1

# Save and exit
```

### Option C: Manual Command (For Testing)

```bash
# Run manually anytime
cd C:\laragon\www\waBlast
php artisan birthday-reminder:send-daily

# Output:
# "Berhasil mengirim/menjadwalkan X pengingat ulang tahun"
```

---

## 📋 Verification Checklist

Before using in production, verify these items:

```
Database:
☐ Table birthday_reminders exists
  Command: SHOW TABLES LIKE 'birthday_reminders'
☐ Table has correct columns (12 columns)
☐ Pasien table has tgl_lahir field
  Command: SHOW COLUMNS FROM pasien WHERE Field='tgl_lahir'

Code:
☐ Model exists at app/Models/BirthdayReminder.php
☐ Controller exists at app/Http/Controllers/BirthdayReminderController.php
☐ Routes visible: php artisan route:list | grep birthday
☐ Views exist (check with file explorer)

WhatsApp:
☐ At least one device connected
  Go to: /whatsapp/settings
☐ Device status shows "connected"
☐ Phone number saved

Features:
☐ Dashboard loads without errors
☐ Create form has all fields
☐ Patient dropdown populates (has data)
☐ Templates display correctly
☐ Filter buttons work
☐ Pagination works (15 per page)
```

---

## 🔧 Configuration Options

### Default Settings (Already Configured)

```php
// Queue connection
QUEUE_CONNECTION=database  // in .env

// Template languages
Available: Indonesian, English, Custom

// Phone format
Supported: 62xxxxxxxxxx (no +62, no 0)

// Message limit
Max: 1000 characters per message

// Pagination
15 items per page

// Table indexes
status, birthday_date, no_rkm_medis
```

### Customizable Settings (Optional)

```php
// Change pagination items per page
// In: app/Http/Controllers/BirthdayReminderController.php
// Line: paginate(15) → paginate(20)

// Add more template messages
// In: resources/views/dashboard/birthday-reminder/create.blade.php
// Add new <option> in template selector

// Change default status
// In: database/migrations/...birthday_reminders_table.php
// Change: ->default('pending') to any status

// Modify date filters
// In: app/Http/Controllers/BirthdayReminderController.php
// Edit: thisWeekBirthday() scope (default 7 days)
```

---

## 📱 Access Points

### Main Dashboard
```
URL: http://localhost/dashboard/birthday-reminder
Route Name: dashboard.birthday-reminder.index
Access: Open (no auth required by default)
```

### Create Form
```
URL: http://localhost/dashboard/birthday-reminder/create
Route Name: dashboard.birthday-reminder.create
Access: Open
```

### API Routes (For Integration)
```
POST /dashboard/birthday-reminder
POST /dashboard/birthday-reminder/{id}/send
DELETE /dashboard/birthday-reminder/{id}
POST /dashboard/birthday-reminder/sync
```

---

## 🆘 Quick Troubleshooting

### Issue: Dashboard shows error
**Solution:**
```bash
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

### Issue: Patient dropdown empty
**Solution:**
```
Check: Do pasien records have tgl_lahir?
Command: SELECT COUNT(*) FROM pasien WHERE tgl_lahir IS NOT NULL
If 0: Patients don't have birthday data
Action: Add tgl_lahir data to pasien table
```

### Issue: Message not sending
**Solution:**
1. Check WhatsApp device connection
2. Verify phone number format (62xxxxxxxxxx)
3. Check message content (no unsupported chars)
4. Review error in detail modal

### Issue: Cron job not running
**Solution:**
1. Test manual: `php artisan birthday-reminder:send-daily`
2. Check logs: `storage/logs/laravel-*.log`
3. Verify permissions on artisan file
4. Test with manual command first

---

## 📊 Database Backup

### Before Going Live

```bash
# Backup database
mysqldump -u root -p sik > sik_backup_$(date +%Y%m%d).sql

# Restore if needed
mysql -u root -p sik < sik_backup_20260103.sql

# Or via phpMyAdmin:
1. Select database 'sik'
2. Export tab
3. Choose format: SQL
4. Click 'Go'
```

---

## 🔐 Security Recommendations

### For Production Environment

```php
// 1. Add authentication middleware
// In: routes/web.php
Route::middleware('auth')->group(function() {
    Route::prefix('dashboard')->name('dashboard.')->group(function() {
        Route::resource('birthday-reminder', BirthdayReminderController::class);
    });
});

// 2. Add authorization checks
// In: BirthdayReminderController.php
public function index() {
    $this->authorize('viewAny', BirthdayReminder::class);
    // ...
}

// 3. Add rate limiting
// In: api.php or routes
Route::middleware('throttle:60,1')->group(function() {
    // routes here
});

// 4. Enable SQL query logging
// In: config/logging.php
'single' => [
    'driver' => 'single',
    'path' => storage_path('logs/laravel.log'),
]
```

---

## 📈 Monitoring & Maintenance

### Daily Tasks
```
☐ Check dashboard for pending reminders
☐ Verify successful sends (count in stat cards)
☐ Review any failed messages (status = failed)
☐ Check error logs if any issues
```

### Weekly Tasks
```
☐ Backup database
☐ Review send success rate
☐ Check for any pattern in failures
☐ Update templates if needed
```

### Monthly Tasks
```
☐ Archive old records (optional)
☐ Analyze sending statistics
☐ Review and update contact info
☐ Performance optimization if needed
```

---

## 📞 Support Resources

### Documentation Files (In Project Root)
1. **BIRTHDAY_REMINDER_QUICK_START.md** - Quick reference
2. **BIRTHDAY_REMINDER_DOCUMENTATION.md** - Complete reference
3. **BIRTHDAY_REMINDER_ACCESS_GUIDE.md** - UI guide
4. **BIRTHDAY_REMINDER_IMPLEMENTATION_SUMMARY.md** - Technical details

### Log Files
```
storage/logs/laravel-YYYY-MM-DD.log
```

### Database
```
Table: birthday_reminders
Rows: SELECT * FROM birthday_reminders;
Failed: SELECT * FROM birthday_reminders WHERE status='failed';
Today: SELECT * FROM birthday_reminders WHERE DATE(birthday_date) = CURDATE();
```

---

## ✨ Next Steps

### Immediate (Day 1)
- [ ] Verify installation works
- [ ] Sync patient data
- [ ] Test send one reminder
- [ ] Review UI and features

### Short Term (Week 1)
- [ ] Setup automatic daily sending
- [ ] Test with actual WhatsApp messages
- [ ] Customize templates
- [ ] Train users

### Long Term (Month 1+)
- [ ] Monitor success rate
- [ ] Gather feedback for improvements
- [ ] Plan feature enhancements
- [ ] Setup backup strategy

---

## 🎉 You're All Set!

Birthday Reminder Dashboard is ready to use. Start by:

1. **Open dashboard:** http://localhost/dashboard/birthday-reminder
2. **Sync data:** Click "Sinkronisasi Data" button
3. **Send reminder:** Find a reminder and click "Kirim"
4. **Create custom:** Click "Tambah Pengingat" for manual entry

For questions, refer to the documentation files or check logs for technical issues.

---

**Installation Date:** 2026-01-03  
**Status:** ✅ Ready to Use  
**Version:** 1.0

Happy sending! 🚀
