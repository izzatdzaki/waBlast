# WEBHOOK CONFIGURATION

Untuk menghubungkan backend Baileys dengan Laravel, Anda perlu mengatur webhook di backend Node.js.

## Langkah Setup:

### 1. Edit file backend/server.js 

Tambahkan code ini untuk mengirim webhook saat device terhubung:

```javascript
// Setelah device terhubung
socket.on('connection_ready', (data) => {
    console.log('Device connected:', data);
    
    // Kirim webhook ke Laravel
    fetch('http://127.0.0.1:8000/api/whatsapp/webhook/device', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            event: 'device_ready',
            device_id: data.sessionId,
            data: {
                phone_number: data.phoneNumber,
                status: 'connected',
            }
        })
    }).catch(err => console.error('Webhook error:', err));
});
```

### 2. Webhook Endpoint

Endpoint yang sudah dibuat di Laravel:
- **URL:** http://127.0.0.1:8000/api/whatsapp/webhook/device
- **Method:** POST
- **Payload:**
```json
{
    "event": "device_ready",
    "device_id": "device_xxxxx",
    "data": {
        "phone_number": "628xxxx",
        "status": "connected"
    }
}
```

### 3. Setelah konfigurasi webhook:

1. Restart backend Node.js
2. Scan ulang QR code di http://127.0.0.1:8000/whatsapp/devices
3. Jika berhasil, device akan muncul di database dengan status 'active'
4. Pesan yang pending akan otomatis terkirim

### 4. Verifikasi:

Jalankan:
```bash
php check_all_devices.php
```

Harus menampilkan device dengan status 'active'.
