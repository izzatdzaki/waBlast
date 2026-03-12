# 📚 Technology Stack & Requirements

## 🖥️ System Requirements

### Development Machine
- **OS:** Windows 10/11 atau Linux/Mac
- **RAM:** Minimum 4GB (recommend 8GB)
- **Disk:** 2GB free space

### Software Requirements

| Component | Version | Purpose |
|-----------|---------|---------|
| **PHP** | 7.4+ | Backend framework (Laravel) |
| **Node.js** | v14+ LTS | WhatsApp Baileys backend |
| **MySQL** | 5.7+ | Database |
| **Composer** | Latest | PHP package manager |
| **npm** | 6+ | Node package manager |

---

## 🛠️ Tech Stack Details

### Backend - Laravel

```
Framework: Laravel 8.x
Language: PHP 7.4
Database: MySQL 5.7+
ORM: Eloquent
Authentication: Session-based
Validation: Laravel Validation Rules
```

**Key Packages:**
```
maatwebsite/excel - Excel export (if needed)
guzzlehttp/guzzle - HTTP requests
```

### Frontend - Bootstrap

```
Framework: Bootstrap 5.3.0
CSS: Bootstrap native
Icons: Bootstrap Icons 1.11.0
JS: Vanilla JavaScript (ES6)
Library: None (lightweight)
```

### Backend Services - Node.js

```
Framework: Express.js
WhatsApp: Baileys (@whiskeysockets/baileys)
QR Generator: qrcode
Environment: dotenv
CORS: cors middleware
```

**Backend Dependencies:**
```
express - Web framework
@whiskeysockets/baileys - WhatsApp bot
qrcode - QR code generation
dotenv - Environment config
body-parser - Request parsing
cors - Cross-origin support
uuid - ID generation
```

---

## 📁 Project Structure

```
waBlast/
│
├── app/                          # Laravel application
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── DashboardTindakanController.php      (Treatment dashboard)
│   │   │   ├── WhatsAppDashboardController.php      (WA settings & device mgmt)
│   │   │   ├── WhatsAppMessageController.php        (Message sending)
│   │   │   └── ...
│   │   └── Middleware/
│   │
│   ├── Models/
│   │   ├── RawatJlDr.php                (Treatment data)
│   │   ├── RegPeriksa.php               (Patient registration)
│   │   ├── Pasien.php                   (Patient master)
│   │   ├── WhatsAppSettings.php         (WA configuration)
│   │   ├── WhatsAppDevice.php           (Connected devices)
│   │   ├── BlastTemplate.php            (Message templates)
│   │   ├── BlastMessage.php             (Message history)
│   │   └── ...
│   │
│   ├── Services/                        # Business logic
│   │   └── ...
│   │
│   └── Exceptions/
│
├── backend/                      # Node.js Baileys backend
│   ├── server.js                 (Main server file)
│   ├── .env                      (Environment config)
│   ├── sessions/                 (WhatsApp session storage)
│   ├── package.json
│   └── node_modules/
│
├── routes/
│   ├── web.php                   (Web routes)
│   └── api.php                   (API routes)
│
├── resources/
│   ├── views/
│   │   ├── layouts/
│   │   ├── dashboard/
│   │   │   └── tindakan/
│   │   │       ├── index.blade.php       (Treatment list + WA modal)
│   │   │       └── ...
│   │   ├── whatsapp/
│   │   │   ├── settings.blade.php        (Settings + device mgmt + QR)
│   │   │   ├── dashboard.blade.php
│   │   │   └── ...
│   │   └── ...
│   │
│   ├── css/
│   ├── js/
│   └── ...
│
├── database/
│   ├── migrations/               (Schema definitions)
│   │   ├── *_create_whatsapp_settings_table.php
│   │   ├── *_create_whatsapp_devices_table.php
│   │   ├── *_create_blast_templates_table.php
│   │   ├── *_create_blast_messages_table.php
│   │   └── ...
│   └── seeders/                  (Sample data)
│
├── storage/
│   ├── logs/                     (Application logs)
│   ├── app/                      (File storage)
│   └── ...
│
├── config/
│   ├── app.php
│   ├── database.php
│   ├── mail.php
│   └── ...
│
├── public/
│   ├── index.php                 (Laravel entry point)
│   ├── css/
│   ├── js/
│   └── ...
│
├── composer.json                 (PHP dependencies)
├── package.json                  (Root npm config)
├── .env                          (Laravel environment)
├── artisan                       (Laravel CLI)
├── SETUP.md                      (Full setup guide)
├── QUICKSTART.md                 (Quick start guide)
└── README.md                     (Project overview)
```

---

## 🗄️ Database Schema

### Key Tables

**whatsapp_settings**
```sql
- id (PK)
- baileys_url
- baileys_status
- default_device_id
- device_check_interval
- webhook_url
- webhook_enabled
- webhook_secret
- enable_auto_reply
- auto_reply_message
- message_retention_days
- max_message_length
- api_rate_limit
- api_timeout
- api_retry_attempts
- api_retry_delay
- timestamps
```

**whatsapp_devices**
```sql
- id (PK)
- device_name (UNIQUE)
- phone_number (NOT NULL, DEFAULT='')
- status (active/connecting/disconnected/error)
- device_info (JSON)
- last_connected_at
- created_at, updated_at
```

**blast_templates**
```sql
- id (PK)
- nama_template
- isi_pesan
- category
- placeholder_variables (JSON)
- is_active
- created_by
- timestamps
```

**blast_messages**
```sql
- id (PK)
- template_id (FK)
- device_id (FK)
- no_telp
- pesan
- status (pending/sent/delivered/failed)
- error_message
- created_at, updated_at
```

**rawat_jl_dr** (From existing DB)
```sql
- kd_rawat (PK)
- tgl_registrasi
- no_rkm_medis
- kd_jenis_prw
- kd_dokter
- total_biaya
- ... (other fields)
```

---

## 🔌 API Endpoints

### Web Routes (Laravel)

```
GET  /dashboard/tindakan           - List treatments
POST /dashboard/tindakan/export    - Export to Excel

GET  /whatsapp/settings            - Settings page
POST /whatsapp/settings            - Update settings

GET  /whatsapp/devices             - Get devices list
POST /whatsapp/devices/qr-code     - Generate QR
GET  /whatsapp/devices/{name}/status - Check device status
DELETE /whatsapp/devices/{id}      - Delete device
```

### API Routes (JSON)

```
POST /api/whatsapp/webhook/device  - Device webhook (from Baileys)
POST /api/whatsapp/send            - Send message
GET  /api/whatsapp/devices         - List devices (API)
```

### Baileys Backend Endpoints

```
GET  http://localhost:3000/health
POST http://localhost:3000/sessions/new
GET  http://localhost:3000/sessions/{id}
GET  http://localhost:3000/connection-status/{id}
POST http://localhost:3000/messages/send
```

---

## 🔄 Data Flow

### Sending WhatsApp Message

```
1. User clicks WhatsApp button
   ↓
2. Modal opens with template selector
   ↓
3. User fills template fields
   ↓
4. POST /api/whatsapp/send
   ↓
5. Laravel validates & formats message
   ↓
6. Call Baileys backend API
   ↓
7. Baileys sends via connected WhatsApp device
   ↓
8. Webhook callback → update status in DB
   ↓
9. History logged in blast_messages table
```

### Device Connection Flow

```
1. User enters device name
   ↓
2. Laravel request to Baileys: POST /sessions/new
   ↓
3. Baileys generates QR code
   ↓
4. Baileys sends webhook to Laravel with QR
   ↓
5. Laravel stores QR in session & marks status='connecting'
   ↓
6. User scans QR with WhatsApp Linked Devices
   ↓
7. WhatsApp authenticates on Baileys
   ↓
8. Baileys sends device_ready webhook
   ↓
9. Laravel updates DB: status='active', fills phone_number
   ↓
10. Device ready for sending messages
```

---

## 📊 Key Features Implementation

### 1. Treatment Dashboard
- **Query:** `RawatJlDr` with filters (date, type, doctor)
- **Export:** Native .xlsx via ZipArchive + XML
- **WhatsApp Integration:** Direct button in table rows

### 2. WhatsApp Settings
- **Multi-tab UI:** Connection, Device, Webhook, Message, API
- **Baileys Health Check:** Periodic status polling
- **QR Code Display:** Dynamic generation & modal display

### 3. Message Templates
- **3 Built-in Templates:** Follow-up, USG Reminder, HPL Reminder
- **Custom Fields:** Placeholder substitution with {variable} syntax
- **Auto-fill:** Patient name auto-populated from context

### 4. Device Management
- **Linked Devices:** WhatsApp Linked Devices protocol
- **Status Tracking:** Real-time connection monitoring
- **Multiple Devices:** Support multiple WhatsApp accounts

---

## 🔐 Security Considerations

- ✅ CSRF protection (Laravel tokens)
- ✅ Input validation & sanitization
- ✅ Rate limiting (API_RATE_LIMIT setting)
- ✅ Environment secrets (.env)
- ✅ Database credentials encrypted at rest
- ✅ Session isolation per device
- ✅ Webhook signature validation (optional)

---

## ⚡ Performance Optimization

- **Caching:** Redis optional for session management
- **Database Indexing:** Indexed on frequently queried fields
- **Pagination:** 15 items per page by default
- **Lazy Loading:** Template selector loads on demand
- **Async Webhooks:** Background processing via queue
- **Connection Pooling:** MySQL connection pooling recommended

---

## 🧪 Testing

### Manual Testing Checklist
- [ ] Dashboard loads successfully
- [ ] Filters work correctly
- [ ] Excel export generates valid file
- [ ] WhatsApp modal opens with correct data
- [ ] Template selector populates fields
- [ ] QR code generates without errors
- [ ] Device connection status updates
- [ ] Message sends successfully
- [ ] Phone number auto-fills after connection

### Local Testing
```bash
# Test Laravel
php artisan serve

# Test Backend
cd backend && node server.js

# Test Database
php artisan tinker
> DB::table('users')->count();
```

---

## 📞 Maintenance & Support

### Regular Maintenance
- ✅ Clear old webhook logs (storage/logs)
- ✅ Archive old messages (blast_messages) > 30 days
- ✅ Backup database weekly
- ✅ Update dependencies quarterly

### Monitoring
- Monitor `storage/logs/laravel.log` for errors
- Check backend console for Baileys issues
- Track message delivery rates
- Monitor database connection health

---

## 📖 Documentation

- `README.md` - Project overview
- `SETUP.md` - Detailed setup guide
- `QUICKSTART.md` - Quick start (5 min)
- `TECH_STACK.md` - This file

---

**Last Updated: March 12, 2026**
