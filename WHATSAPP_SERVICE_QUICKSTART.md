# WhatsApp Service - Quick Start Guide

## 📋 Setup Checklist

### ✅ Implementation Complete
All files have been created and configured:

- ✅ `app/Services/WhatsAppService.php` - Core service layer
- ✅ `app/Jobs/SendWhatsAppMessage.php` - Queue job handler
- ✅ `app/Http/Controllers/WhatsAppMessageController.php` - API controller
- ✅ `app/Http/Requests/SendWhatsAppMessageRequest.php` - Form validator
- ✅ `app/Http/Requests/SendScheduledWhatsAppMessageRequest.php` - Scheduled validator
- ✅ `app/Models/BlastMessage.php` - Updated model with new methods
- ✅ `app/Models/BlastTemplate.php` - Updated model with preview methods
- ✅ `app/Models/Pasien.php` - Added relationships
- ✅ `routes/api.php` - API routes configured
- ✅ `config/services.php` - Baileys configuration
- ✅ `.env` - Environment variables updated

### 🚀 Getting Started

#### 1. Start the Baileys API Server
```bash
cd backend
npm install
npm start
# Server should be running on http://localhost:3000
```

#### 2. Start the Laravel Queue Worker
In a new terminal:
```bash
cd c:\laragon\www\waBlast
php artisan queue:work --queue=default
# Leave this running in the background
```

#### 3. Test the Health Check
```bash
curl http://localhost:8000/api/whatsapp/health
```

Expected response:
```json
{
  "success": true,
  "status": "healthy",
  "message": "WhatsApp API is running"
}
```

#### 4. Generate API Token (if needed)
```bash
# Create a token for testing
php artisan tinker
>>> $user = App\Models\User::first();
>>> $token = $user->createToken('test-token')->plainTextToken;
>>> echo $token;
```

#### 5. Send Your First Message
```bash
curl -X POST http://localhost:8000/api/whatsapp/send \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_API_TOKEN" \
  -d '{
    "phone": "08123456789",
    "message": "Halo dari waBlast!"
  }'
```

## 📊 API Reference

### Send Immediate Message
```
POST /api/whatsapp/send
Authorization: Bearer {token}

{
  "phone": "08123456789",
  "message": "Your message here",
  "template_id": null,
  "template_variables": {},
  "pasien_id": null
}
```

### Send Scheduled Message
```
POST /api/whatsapp/send-scheduled
Authorization: Bearer {token}

{
  "phone": "08123456789",
  "message": "Your scheduled message",
  "scheduled_at": "2024-01-10 14:30:00",
  "template_id": 1,
  "template_variables": {
    "nama_pasien": "Budi",
    "tanggal_jadwal": "10-01-2024"
  }
}
```

### Get Message History
```
GET /api/whatsapp/messages?status=sent&per_page=50&page=1
Authorization: Bearer {token}
```

### Get Single Message
```
GET /api/whatsapp/messages/{id}
Authorization: Bearer {token}
```

### Resend Failed Message
```
PUT /api/whatsapp/messages/{id}/resend
Authorization: Bearer {token}
```

### Health Check (No Auth Required)
```
GET /api/whatsapp/health
```

## 🔍 Database Tables

### blast_messages
Stores all sent and scheduled messages

Key columns:
- `id` - Message ID
- `no_telp` - Recipient phone number
- `message` - Message content
- `status` - pending|scheduled|sent|delivered|read|failed
- `external_message_id` - Baileys API message ID
- `sent_at` - Delivery timestamp
- `delivered_at` - Delivery confirmation timestamp
- `read_at` - Read timestamp

### blast_templates
Stores message templates with placeholder support

Example template:
```
Halo {nama_pasien}, 
Jadwal check-up Anda: {tanggal_jadwal} jam {jam_jadwal}
Poliklinik: {poliklinik}
Dokter: {dokter}
```

## 🛠️ Troubleshooting

### Queue Not Processing
```bash
# Check if worker is running
php artisan queue:work

# Monitor queue
php artisan queue:monitor

# Restart queue worker
php artisan queue:restart
```

### Health Check Returns Unhealthy
- Ensure Baileys backend is running on `http://localhost:3000`
- Check `.env` file: `BAILEYS_API_URL=http://localhost:3000`
- Verify no firewall blocking localhost:3000

### Message Status Stuck on "Pending"
- Ensure queue worker is running
- Check storage/logs/laravel.log for errors
- Verify phone number format is valid (0812... or +62...)

### Invalid Phone Number Format
Phone numbers must be:
- Indonesian format: `0812xxx` or `+6281xxx` or `6281xxx`
- Valid mobile carriers: 8, 9, or 10 (second digit)
- Example: `0812345678` → auto-converts to `628123456789`

## 📝 Message Template Variables

Available placeholders for templates:
- `{nama_pasien}` - Patient name
- `{tanggal_jadwal}` - Appointment date
- `{jam_jadwal}` - Appointment time
- `{poliklinik}` - Clinic/Department
- `{dokter}` - Doctor name
- `{no_surat}` - SEP/Letter number
- `{no_rkm_medis}` - Medical record number
- `{tanggal_pesan}` - Message send date
- `{jam_pesan}` - Message send time

## 🔐 Security Notes

1. **API Tokens**: Generate and manage tokens for different services
2. **Phone Numbers**: Never log full phone numbers in production
3. **Rate Limiting**: Consider implementing rate limits on endpoints
4. **Webhook Verification**: Verify webhook signatures if adding callback authentication
5. **Queue Encryption**: Sensitive data is logged, use encryption if needed

## 📚 Useful Commands

```bash
# Clear configuration cache
php artisan config:clear

# Clear application cache
php artisan cache:clear

# List all routes
php artisan route:list | grep whatsapp

# Tail logs in real-time
tail -f storage/logs/laravel.log

# Check queue jobs
php artisan queue:failed

# Retry failed jobs
php artisan queue:retry all
```

## 🎯 Common Use Cases

### Send Reminder to Patient
```bash
curl -X POST http://localhost:8000/api/whatsapp/send \
  -H "Authorization: Bearer TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "phone": "08123456789",
    "message": "Reminder: Jadwal check-up Anda besok jam 14:00"
  }'
```

### Schedule Appointment Confirmation
```bash
curl -X POST http://localhost:8000/api/whatsapp/send-scheduled \
  -H "Authorization: Bearer TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "phone": "08123456789",
    "message": "Appointment confirmed for tomorrow",
    "scheduled_at": "2024-01-09 09:00:00",
    "template_id": 1,
    "template_variables": {
      "nama_pasien": "Budi",
      "tanggal_jadwal": "09-01-2024"
    }
  }'
```

### Send Bulk Messages (Using Template)
```php
// In your controller or service
$patients = Pasien::whereNotNull('no_tlp')->get();

foreach ($patients as $patient) {
    SendWhatsAppMessage::dispatch([
        'phone' => $patient->no_tlp,
        'message' => 'Pengingat check-up bulanan',
        'template_id' => 1,
        'template_variables' => [
            'nama_pasien' => $patient->nm_pasien,
        ]
    ]);
}
```

## 📞 Support

For issues or questions:
1. Check logs: `storage/logs/laravel.log`
2. Review API responses for error messages
3. Verify Baileys API connectivity
4. Check queue worker status

---

**Status**: Ready for Production ✅  
**Last Updated**: January 3, 2026
