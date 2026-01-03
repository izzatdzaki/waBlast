# WhatsApp UI Setup Guide

## Step 1: Run Migrations

To create all necessary database tables, run:

```bash
php artisan migrate
```

This will create:
- `blast_messages` - Store all messages (sent, pending, scheduled, etc.)
- `blast_templates` - Store reusable message templates
- `whatsapp_devices` - Store WhatsApp device information

## Step 2: Start the Queue Worker

The system uses Laravel Queue with database driver. Start the queue worker:

```bash
php artisan queue:work
```

This will process pending messages in the background.

## Step 3: Start the Baileys Backend (WhatsApp API)

In a separate terminal, start the Node.js backend:

```bash
cd backend
npm install (if not already installed)
node server.js
```

The backend will run on `http://localhost:3000`

## Step 4: Access the Dashboard

Open your browser and navigate to:

```
http://localhost:8000/whatsapp
```

Or if using Laragon, use your configured domain:

```
http://wablast.local/whatsapp
```

## Available Routes

- `/whatsapp` - Main Dashboard
- `/whatsapp/send` - Send immediate messages
- `/whatsapp/schedule` - Schedule messages for later
- `/whatsapp/history` - View message history
- `/whatsapp/templates` - Manage message templates
- `/whatsapp/message/{id}` - View single message details

## API Endpoints

All API endpoints are available at `/api/whatsapp/`:

- `GET /api/whatsapp/health` - Health check
- `POST /api/whatsapp/send` - Send immediate message
- `POST /api/whatsapp/send-scheduled` - Schedule message
- `GET /api/whatsapp/messages` - Get message history
- `GET /api/whatsapp/messages/{id}` - Get single message
- `PUT /api/whatsapp/messages/{id}/resend` - Resend failed message
- `POST /api/whatsapp/webhook/status` - Delivery status webhook

## Features

### Send Immediate Messages
- Send to manual phone number
- Send to patients from database
- Use custom message or template
- With template variables support

### Schedule Messages
- Schedule for specific date and time
- Send to multiple recipients
- Batch sending support

### Message History
- View all sent/pending/failed messages
- Filter by status, date range
- Search functionality
- Pagination support

### Templates Management
- Create reusable templates
- Use variables (e.g., {nama_pasien}, {no_rkm_medis})
- Template preview
- Category organization

### Tracking
- Real-time message status tracking
- Message delivery confirmation
- Failed message retry mechanism
- Message history export

## Troubleshooting

### "Route [login] not defined" error
✅ Fixed - Routes no longer require authentication

### Form submission fails
- Check browser console for errors
- Ensure CSRF token is present in meta tag
- Verify API endpoints are accessible at `/api/whatsapp/`

### Queue worker not processing messages
- Start queue worker: `php artisan queue:work`
- Check queue jobs table: `select * from jobs;`
- Check failed jobs: `php artisan queue:failed`

### Baileys backend not connecting
- Start backend: `cd backend && node server.js`
- Check if it's running on port 3000
- Check backend logs for connection issues
