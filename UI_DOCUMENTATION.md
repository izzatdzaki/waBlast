# UI WhatsApp Messaging System Documentation

## Overview

Sistem UI untuk WhatsApp Messaging Service telah berhasil dibuat dengan interface yang user-friendly dan intuitif menggunakan Bootstrap 5.

## Components & Views Created

### 1. Dashboard (`resources/views/whatsapp/dashboard.blade.php`)
**Lokasi**: `/whatsapp`
**Fitur**:
- Statistics cards menampilkan:
  - **Terkirim (Hijau)**: Total pesan sukses dikirim
  - **Menunggu (Kuning)**: Total pesan dalam antrian
  - **Diterima (Biru)**: Total pesan terkirim ke WhatsApp
  - **Gagal (Merah)**: Total pesan gagal
- Tabbed interface dengan 3 tab:
  1. **Pesan Terbaru**: Menampilkan 10 pesan terbaru dengan status badges
  2. **Template**: Daftar template aktif yang tersimpan
  3. **Riwayat Lengkap**: Link ke halaman riwayat lengkap
- Action buttons untuk "Kirim Pesan" dan "Jadwalkan"
- Responsive Bootstrap grid layout
- Real-time status updates

### 2. Send Message Form (`resources/views/whatsapp/send-message.blade.php`)
**Lokasi**: `/whatsapp/send`
**Fitur**:
- **Recipient Selection**:
  - Nomor manual (freetext input dengan format validation)
  - Pilih dari database pasien (autocomplete)
- **Message Type**:
  - Custom message (textarea dengan character counter)
  - Template-based (dropdown dengan variable substitution)
- **Template Variables**:
  - Otomatis deteksi placeholder {variable}
  - Dynamic input fields untuk setiap variable
  - Live preview update
- **Character Count**: Display real-time karakter usage
- **Message Preview**: Visual preview sebelum pengiriman
- **Error Handling**: Validasi form dan error display
- **AJAX Submission**: Non-blocking form submission dengan loader

### 3. Schedule Message Form (`resources/views/whatsapp/schedule-message.blade.php`)
**Lokasi**: `/whatsapp/schedule`
**Fitur**:
- **Bulk Recipient Support**:
  - Manual: Textarea untuk multiple nomor (satu per baris)
  - Database: Multi-select dari pasien dengan Ctrl/Cmd support
- **Schedule Time**:
  - Date picker dengan minimum date = today
  - Time picker untuk jam pengiriman
  - Date warning jika waktu sudah berlalu
- **Message Type**: Custom atau template (sama seperti send form)
- **Schedule Summary**:
  - Menampilkan waktu penjadwalan
  - Total penerima
  - Total pesan yang akan dikirim
- **Recipient Count**: Auto-update berdasarkan pilihan
- **Jam Server**: Menggunakan server time untuk konsistensi

### 4. Message History (`resources/views/whatsapp/message-history.blade.php`)
**Lokasi**: `/whatsapp/history`
**Fitur**:
- **Statistics Dashboard**:
  - 4 kartu statistik (Terkirim, Menunggu, Diterima, Gagal)
  - Auto-update berdasarkan filter
- **Advanced Filtering**:
  - Status filter (pending, sent, delivered, failed, read)
  - Date range picker (from - to)
  - Search box (nomor atau pesan preview)
- **Paginated Table**:
  - Kolom: No, Nomor, Pesan, Status, Tanggal, Aksi
  - Status badges dengan warna berbeda
  - Per-page: 15 messages
- **Action Buttons**:
  - Detail button (modal dengan full message info)
  - Resend button (untuk failed messages only)
- **Modal Detail**:
  - Full message content
  - Phone & patient info
  - Status timeline
  - Delivery timestamps
  - Error details (jika ada)
  - Resend functionality
- **Responsive Pagination**: Prev/Next dengan page numbers

### 5. Templates Management (`resources/views/whatsapp/templates.blade.php`)
**Lokasi**: `/whatsapp/templates`
**Fitur**:
- **Template Grid View**: Responsive card layout (3/4 columns)
- **Create Template**:
  - Modal form dengan fields:
    - Nama template
    - Isi pesan (textarea)
    - Character counter
    - Variable detection
- **Edit Template**: Load existing data ke form modal
- **Template Preview**:
  - Visual preview dalam modal
  - Variable highlighting sebagai badges
  - Live preview update saat edit
- **Delete Template**: Confirmation modal sebelum delete
- **Card Features**:
  - Preview text (first 100 chars)
  - Edit, Preview, Delete buttons
  - Hover effect (transform & shadow)
  - Button group styling
- **Variable Detection**:
  - Auto-detect {variable} patterns
  - Display sebagai badges
  - Automatic dari content textarea

### 6. Message Detail (`resources/views/whatsapp/message-detail.blade.php`)
**Lokasi**: `/whatsapp/message/{id}`
**Fitur**:
- **Recipient Information**:
  - Nomor telepon, nama pasien
  - No. rekam medis, email
- **Message Content**:
  - Full pesan dengan formatting
- **Delivery Timeline**:
  - Timeline visual dengan markers
  - Sent → Delivered → Read → (atau Failed)
  - Timestamps untuk setiap status
- **Metadata**:
  - Message ID (internal & external)
  - Template digunakan
  - Keterangan
- **Error Information**: Detailed error message (jika failed)
- **Resend Functionality**: Button untuk kirim ulang (failed only)
- **Back Navigation**: Link kembali ke history

## Routes

Semua routes protected dengan `auth` middleware:

```
GET  /whatsapp                      → Dashboard
GET  /whatsapp/send                 → Send Message Form
GET  /whatsapp/schedule             → Schedule Message Form
GET  /whatsapp/history              → Message History
GET  /whatsapp/templates            → Templates Management
GET  /whatsapp/message/{id}         → Message Detail
```

## Styling & Design

### Color Scheme
- **Primary**: Bootstrap blue (#0d6efd)
- **Success**: Green (#198754) - untuk Terkirim
- **Warning**: Yellow (#ffc107) - untuk Menunggu
- **Info**: Cyan (#0dcaf0) - untuk Diterima
- **Danger**: Red (#dc3545) - untuk Gagal
- **Secondary**: Gray (#6c757d) - untuk buttons

### Components Used
- Bootstrap 5 cards, tables, forms, modals
- Bootstrap badges untuk status
- Bootstrap spinners untuk loading
- Bootstrap alerts untuk notifications
- Bootstrap grid system untuk responsive layout

### Custom Styling
- Left-border accent pada cards (4px colored borders)
- Hover effects pada cards (transform & shadow)
- Timeline visualization untuk delivery status
- Responsive button groups
- Character counter display

## Features & Functionality

### JavaScript Features
1. **Form Validation**:
   - HTML5 validation
   - Real-time character counting
   - Phone number format validation

2. **Dynamic Content**:
   - Template variable substitution
   - Live preview updates
   - Conditional field display (manual vs patient selection)

3. **AJAX Integration**:
   - Non-blocking form submission
   - API token handling
   - Error/success notifications
   - Modal detail loading

4. **User Interactions**:
   - Multi-select support
   - Date/time pickers
   - Search functionality
   - Pagination
   - Modal dialogs

### API Integration
Views berinteraksi dengan API endpoints:
- `POST /api/whatsapp/send` - Send message
- `POST /api/whatsapp/send-scheduled` - Schedule message
- `GET /api/whatsapp/history` - Fetch messages dengan filters
- `GET /api/whatsapp/show/{id}` - Get message detail
- `POST /api/whatsapp/resend/{id}` - Resend failed message
- `GET /api/whatsapp/template/list` - Get all templates
- `POST /api/whatsapp/template/store` - Create template
- `PUT /api/whatsapp/template/update/{id}` - Update template
- `DELETE /api/whatsapp/template/delete/{id}` - Delete template

## Data Loading

### Dashboard Data
```php
$stats = [
    'sent' => BlastMessage::sent()->count(),
    'pending' => BlastMessage::pending()->count(),
    'delivered' => BlastMessage::whereNotNull('delivered_at')->count(),
    'failed' => BlastMessage::failed()->count(),
];
$recent_messages = BlastMessage::latest()->take(10)->get();
$templates = BlastTemplate::where('is_active', true)->get();
```

### History Data
- Paginated messages: 15 per page
- Filters: status, date range, search
- Statistics aggregation per filter

### Templates Data
- All active templates
- Variable extraction from content
- Usage count tracking

## User Workflows

### Workflow 1: Send Immediate Message
1. User klik "Kirim Pesan" di dashboard
2. Pilih tipe penerima (manual/database)
3. Pilih tipe pesan (custom/template)
4. Isi message content
5. Preview pesan
6. Klik "Kirim Pesan"
7. Success notification → kembali ke dashboard

### Workflow 2: Schedule Bulk Messages
1. User klik "Jadwalkan"
2. Pilih multiple recipients
3. Set schedule date & time
4. Pilih message type
5. Isi message
6. Review summary
7. Klik "Jadwalkan Pesan"
8. Success notification → kembali ke dashboard

### Workflow 3: Manage Templates
1. User buka halaman Templates
2. Klik "Tambah Template"
3. Isi nama & isi template
4. System auto-detect variables
5. Klik "Simpan"
6. Template tersedia di send/schedule forms

### Workflow 4: Monitor Messages
1. User buka History
2. Filter berdasarkan status/date/search
3. Klik message untuk detail
4. Jika gagal, bisa kirim ulang
5. View delivery timeline

## Mobile Responsiveness

Semua views fully responsive:
- Desktop: Multi-column layouts (3-4 cols)
- Tablet: 2 column layouts
- Mobile: Single column (stacked)
- Touch-friendly buttons dan inputs
- Readable font sizes

## Accessibility

- Semantic HTML structure
- Form labels properly associated
- Alt text untuk icons (via Bootstrap)
- Color not only indicator (badges + text)
- Keyboard navigation support
- ARIA labels dimana diperlukan

## Installation & Setup

### Prerequisites
1. Laravel 8+ dengan Blade templating
2. Bootstrap 5 (already included)
3. jQuery atau vanilla JavaScript
4. Database dengan tables: `blast_messages`, `blast_templates`, `pasien`

### File Structure
```
resources/views/whatsapp/
├── dashboard.blade.php
├── send-message.blade.php
├── schedule-message.blade.php
├── message-history.blade.php
├── templates.blade.php
└── message-detail.blade.php
```

### Routes Configuration
Tambahkan di `routes/web.php` (sudah dilakukan):
```php
Route::middleware(['auth'])->prefix('whatsapp')->name('whatsapp.')->group(function () {
    Route::get('/', [WhatsAppDashboardController::class, 'index'])->name('dashboard');
    Route::get('send', [WhatsAppDashboardController::class, 'showSendForm'])->name('send');
    Route::get('schedule', [WhatsAppDashboardController::class, 'showScheduleForm'])->name('schedule');
    Route::get('history', [WhatsAppDashboardController::class, 'showHistory'])->name('history');
    Route::get('templates', [WhatsAppDashboardController::class, 'showTemplates'])->name('templates');
    Route::get('message/{id}', [WhatsAppDashboardController::class, 'showMessageDetail'])->name('detail');
});
```

## Usage Examples

### Accessing Dashboard
```
http://localhost/whatsapp
```

### Sending Message
```
http://localhost/whatsapp/send
```

### Scheduling Messages
```
http://localhost/whatsapp/schedule
```

### Viewing History
```
http://localhost/whatsapp/history
```

### Managing Templates
```
http://localhost/whatsapp/templates
```

### Viewing Message Detail
```
http://localhost/whatsapp/message/1?id=123
```

## Browser Compatibility

- Chrome/Chromium (latest)
- Firefox (latest)
- Safari (latest)
- Edge (latest)
- Mobile browsers (iOS Safari, Chrome Mobile)

## Performance Considerations

1. **Lazy Loading**: Messages loaded on-demand via pagination
2. **AJAX**: Forms submitted without full page reload
3. **Caching**: Template lists cached at controller level
4. **Database Indexes**: Status, created_at columns indexed
5. **API Rate Limiting**: Consider implementing for production

## Security Features

1. **CSRF Protection**: All forms include @csrf token
2. **Authentication**: Auth middleware on all routes
3. **Authorization**: User dapat hanya melihat data mereka sendiri
4. **Sanitization**: HTML entities escaped di output
5. **Input Validation**: Server-side validation via Form Requests

## Future Enhancements

1. **Bulk Operations**: Select multiple messages untuk bulk action
2. **Export**: Download history sebagai CSV/Excel
3. **Reports**: Analytics dan charts untuk message delivery
4. **Webhooks**: Real-time status updates via WebSockets
5. **Rich Media**: Support untuk image/video messages
6. **Scheduled Tasks**: UI untuk manage cron jobs
7. **User Permissions**: Role-based access control
8. **Audit Logs**: Track semua message history changes
9. **A/B Testing**: Compare different message templates
10. **Integration**: Connect dengan external services (CRM, etc)

## Troubleshooting

### Messages not loading in history
- Check API token in localStorage
- Verify auth middleware
- Check database connection
- Review API response in browser console

### Template variables not detected
- Ensure format: `{variable_name}`
- Variables must start with letter or underscore
- No spaces inside braces

### Schedule form not working
- Check server timezone configuration
- Verify date is not in past
- Check queue is running (php artisan queue:listen)

### Images not showing in modals
- Check Bootstrap version compatibility
- Verify icon library (Bootstrap Icons) is included

## Support & Maintenance

- Regular updates untuk Bootstrap versions
- Monitor API compatibility
- Security patches untuk Laravel updates
- Performance monitoring

---

**Last Updated**: 2024
**Version**: 1.0
**Status**: Production Ready
