import express from 'express';
import cors from 'cors';
import bodyParser from 'body-parser';
import { v4 as uuidv4 } from 'uuid';
import dotenv from 'dotenv';
import QRCode from 'qrcode';
import path from 'path';
import fs from 'fs';
import {
    default as makeWASocket,
    useMultiFileAuthState,
    DisconnectReason,
    fetchLatestBaileysVersion,
} from '@whiskeysockets/baileys';

dotenv.config();

const app = express();
const PORT = process.env.PORT || 3000;

// Middleware
app.use(cors());
app.use(bodyParser.json({ limit: '50mb' }));
app.use(bodyParser.urlencoded({ limit: '50mb', extended: true }));

// Store for WhatsApp connections
const connections = new Map();
const messageQueue = new Map();
const connectionStates = new Map(); // Track actual connection readiness
const sessionDir = process.env.WHATSAPP_STORE_PATH || './sessions';

// Ensure sessions directory exists
if (!fs.existsSync(sessionDir)) {
    fs.mkdirSync(sessionDir, { recursive: true });
}

/**
 * Initialize WhatsApp connection
 */
async function initializeWhatsApp(sessionId) {
    try {
        console.log(`[${sessionId}] Initializing WhatsApp connection...`);

        const sessionPath = path.join(sessionDir, sessionId);
        const { state, saveCreds } = await useMultiFileAuthState(sessionPath);

        const { version } = await fetchLatestBaileysVersion();

        const sock = makeWASocket({
            version,
            auth: state,
            printQRInTerminal: false,
        });

        // Save credentials
        sock.ev.on('creds.update', saveCreds);

        // Handle connection updates
        sock.ev.on('connection.update', async(update) => {
            const { connection, lastDisconnect, qr } = update;

            if (qr) {
                const qrDataUrl = await QRCode.toDataURL(qr);
                console.log(`[${sessionId}] QR Code generated (${qrDataUrl.length} bytes)`);
                // Store QR code with timestamp
                connections.set(sessionId + '_qr_pending', true);
                messageQueue.set(sessionId, {
                    qr: qrDataUrl,
                    timestamp: Date.now()
                });

                // Send webhook for QR event
                try {
                    console.log(`[${sessionId}] Sending QR webhook to Laravel`);
                    const webhookUrl = `${process.env.LARAVEL_SERVER_URL || 'http://wablast.test:88'}/api/whatsapp/webhook/device`;
                    await fetch(webhookUrl, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            event: 'qr',
                            device_id: sessionId,
                            data: {
                                qr_code: qrDataUrl,
                                timestamp: Date.now()
                            }
                        })
                    }).catch(err => console.error(`[${sessionId}] QR Webhook error:`, err.message));
                } catch (err) {
                    console.error(`[${sessionId}] Error sending QR webhook:`, err);
                }
            }

            if (connection === 'open') {
                console.log(`[${sessionId}] Connection opened`);
                messageQueue.delete(sessionId);
                connections.delete(sessionId + '_qr_pending');

                const phoneNumber = sock.user?.id?.replace(/:\d+@.*/, '') || '';
                connectionStates.set(sessionId, {
                    connected: true,
                    authenticated: true,
                    timestamp: Date.now(),
                    phone: phoneNumber
                });

                // Send webhook to Laravel when device is ready - with retry
                const sendDeviceReadyWebhook = async(retries = 0) => {
                    try {
                        console.log(`[${sessionId}] Sending device_ready webhook to Laravel - Phone: ${phoneNumber} (attempt ${retries + 1})`);
                        const webhookUrl = `${process.env.LARAVEL_SERVER_URL || 'http://wablast.test:88'}/api/whatsapp/webhook/device`;

                        const response = await fetch(webhookUrl, {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({
                                event: 'device_ready',
                                device_id: sessionId,
                                data: {
                                    phone_number: phoneNumber,
                                    status: 'connected'
                                }
                            })
                        });

                        if (!response.ok) {
                            throw new Error(`HTTP ${response.status}`);
                        }

                        const data = await response.json();
                        console.log(`[${sessionId}] Webhook sent successfully:`, data);
                    } catch (err) {
                        console.error(`[${sessionId}] Webhook error (attempt ${retries + 1}):`, err.message);

                        // Retry up to 3 times with delay
                        if (retries < 3) {
                            setTimeout(() => {
                                sendDeviceReadyWebhook(retries + 1);
                            }, 2000 * (retries + 1));
                        }
                    }
                };

                // Send webhook immediately
                sendDeviceReadyWebhook();
            }

            if (connection === 'close') {
                const shouldReconnect =
                    lastDisconnect?.error?.output?.statusCode !== DisconnectReason.loggedOut;
                const isLoggedOut = lastDisconnect?.error?.output?.statusCode === DisconnectReason.loggedOut;

                console.log(`[${sessionId}] Connection closed. Reconnect: ${shouldReconnect}, LoggedOut: ${isLoggedOut}`);
                connectionStates.delete(sessionId); // Clear state when disconnected
                messageQueue.delete(sessionId); // Clear message queue
                connections.delete(sessionId + '_qr_pending'); // Clear QR pending flag

                // Send webhook for disconnect
                try {
                    console.log(`[${sessionId}] Sending connection_closed webhook to Laravel`);
                    const webhookUrl = `${process.env.LARAVEL_SERVER_URL || 'http://wablast.test:88'}/api/whatsapp/webhook/device`;
                    await fetch(webhookUrl, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            event: 'connection_closed',
                            device_id: sessionId,
                            data: {
                                reason: isLoggedOut ? 'logged_out' : 'connection_lost',
                                timestamp: Date.now()
                            }
                        })
                    }).catch(err => console.error(`[${sessionId}] Disconnect webhook error:`, err.message));
                } catch (err) {
                    console.error(`[${sessionId}] Error sending disconnect webhook:`, err);
                }

                if (shouldReconnect) {
                    setTimeout(() => initializeWhatsApp(sessionId), 3000);
                }
            }
        });

        // Handle incoming messages
        sock.ev.on('messages.upsert', async(m) => {
            console.log(`[${sessionId}] Message received from`, m.messages[0]?.key?.remoteJid);
        });

        connections.set(sessionId, sock);
        return sock;
    } catch (error) {
        console.error(`[${sessionId}] Error initializing WhatsApp:`, error);
        throw error;
    }
}

/**
 * Get or create connection
 */
async function getConnection(sessionId = 'default') {
    if (!connections.has(sessionId)) {
        await initializeWhatsApp(sessionId);
    }
    return connections.get(sessionId);
}

/**
 * Format phone number to WhatsApp format
 */
function formatPhoneToWA(phone) {
    // Remove all non-digits
    let cleaned = phone.replace(/\D/g, '');

    // If starts with 0, replace with 62
    if (cleaned.startsWith('0')) {
        cleaned = '62' + cleaned.substring(1);
    }

    // If doesn't start with 62, add it
    if (!cleaned.startsWith('62')) {
        cleaned = '62' + cleaned;
    }

    // Add @s.whatsapp.net suffix
    return cleaned + '@s.whatsapp.net';
}

/**
 * API Routes
 */

// Health check
app.get('/health', (req, res) => {
    res.json({
        success: true,
        status: 'healthy',
        timestamp: new Date().toISOString(),
    });
});

// Check connection status for a session
app.get('/connection-status/:sessionId', async(req, res) => {
    try {
        const { sessionId } = req.params;
        const sock = connections.get(sessionId);
        const state = connectionStates.get(sessionId);

        if (!sock) {
            return res.json({
                success: false,
                status: 'not_initialized',
                message: 'Session not initialized'
            });
        }

        res.json({
            success: true,
            status: state?.authenticated ? 'authenticated' : 'pending',
            authenticated: state?.authenticated || false,
            hasUser: !!sock.user,
            phone: sock.user?.id?.replace(/:\d+@.*/, '') || null,
            connectedAt: state?.timestamp,
            isReady: state?.authenticated && !!sock.user
        });
    } catch (error) {
        res.status(500).json({
            success: false,
            error: error.message
        });
    }
});

// Get QR code
app.get('/qr', async(req, res) => {
    try {
        const sessionId = req.query.sessionId || 'default';
        const sock = await getConnection(sessionId);

        const qrData = messageQueue.get(sessionId);
        if (qrData?.qr) {
            return res.json({
                success: true,
                qr: qrData.qr,
                sessionId,
            });
        }

        // Check if already connected
        if (sock.user) {
            return res.json({
                success: true,
                connected: true,
                phone: sock.user.id,
                sessionId,
            });
        }

        res.status(202).json({
            success: false,
            message: 'Generating QR code, please wait...',
            sessionId,
        });
    } catch (error) {
        console.error('Error getting QR:', error);
        res.status(500).json({
            success: false,
            error: error.message,
        });
    }
});

// Send message with retry logic
app.post('/send-message', async(req, res) => {
    try {
        const { phone, message, sessionId = 'default', metadata = {} } = req.body;

        if (!phone || !message) {
            return res.status(400).json({
                success: false,
                error: 'phone and message are required',
            });
        }

        const sock = await getConnection(sessionId);

        // Check connection with retry logic
        const maxRetries = 5;
        let connected = false;

        for (let attempt = 1; attempt <= maxRetries; attempt++) {
            const state = connectionStates.get(sessionId);

            // Check if truly authenticated
            if (sock.user && state?.authenticated) {
                connected = true;
                console.log(`[${sessionId}] Connection verified on attempt ${attempt}`);
                break;
            }

            if (attempt < maxRetries) {
                console.log(`[${sessionId}] Waiting for connection ready... (attempt ${attempt}/${maxRetries})`);
                await new Promise(resolve => setTimeout(resolve, 1000)); // Wait 1 second

                // Force reconnect check
                try {
                    await sock.socket?.emit?.('user-present');
                } catch (e) {
                    // Ignore
                }
            }
        }

        if (!connected || !sock.user) {
            return res.status(503).json({
                success: false,
                error: 'WhatsApp session not connected. Please scan QR code and wait for device to fully authenticate.',
                details: {
                    sessionId,
                    hasUser: !!sock.user,
                    isAuthenticated: connectionStates.get(sessionId)?.authenticated || false,
                    status: connectionStates.get(sessionId) || 'unknown'
                }
            });
        }

        const jid = formatPhoneToWA(phone);
        const messageId = uuidv4();

        try {
            const result = await sock.sendMessage(jid, { text: message });
            console.log(`[${sessionId}] Message sent to ${phone}`, result.key.id);

            res.json({
                success: true,
                message_id: result.key.id || messageId,
                phone: formatPhoneToWA(phone),
                timestamp: new Date().toISOString(),
                metadata,
            });
        } catch (sendError) {
            console.error(`[${sessionId}] Send message error:`, sendError.message);
            res.status(500).json({
                success: false,
                error: 'Failed to send message: ' + sendError.message,
                sessionId,
            });
        }
    } catch (error) {
        console.error('Error sending message:', error);
        res.status(500).json({
            success: false,
            error: error.message,
        });
    }
});

// Send scheduled message (simulated - Laravel will handle actual scheduling)
app.post('/send-scheduled', async(req, res) => {
    try {
        const { phone, message, scheduled_at, sessionId = 'default', metadata = {} } = req.body;

        if (!phone || !message || !scheduled_at) {
            return res.status(400).json({
                success: false,
                error: 'phone, message, and scheduled_at are required',
            });
        }

        const messageId = uuidv4();
        const scheduledTime = new Date(scheduled_at);

        res.json({
            success: true,
            message_id: messageId,
            phone,
            scheduled_at: scheduledTime.toISOString(),
            status: 'scheduled',
            note: 'Message scheduled in Laravel queue. Baileys will receive it when Laravel processes the job.',
            metadata,
        });
    } catch (error) {
        console.error('Error scheduling message:', error);
        res.status(500).json({
            success: false,
            error: error.message,
        });
    }
});

// Get message status
app.get('/message-status/:messageId', (req, res) => {
    try {
        const { messageId } = req.params;

        // Baileys doesn't provide real-time status via messageId lookup
        // Status is handled through webhooks from Laravel
        res.json({
            success: true,
            message_id: messageId,
            status: 'unknown',
            note: 'Status tracking is handled via webhook callbacks from Laravel',
        });
    } catch (error) {
        console.error('Error getting status:', error);
        res.status(500).json({
            success: false,
            error: error.message,
        });
    }
});

// Get messages history (mock endpoint)
app.get('/messages', (req, res) => {
    try {
        const { limit = 50, offset = 0 } = req.query;

        res.json({
            success: true,
            messages: [],
            total: 0,
            limit: parseInt(limit),
            offset: parseInt(offset),
            note: 'Message history is stored in Laravel database',
        });
    } catch (error) {
        console.error('Error getting messages:', error);
        res.status(500).json({
            success: false,
            error: error.message,
        });
    }
});

// Get session info
app.get('/session-info', async(req, res) => {
    try {
        const sessionId = req.query.sessionId || 'default';
        const sock = await getConnection(sessionId);

        if (sock.user) {
            res.json({
                success: true,
                connected: true,
                phone: sock.user.id,
                name: sock.user.name,
                sessionId,
            });
        } else {
            res.json({
                success: false,
                connected: false,
                message: 'Session not connected. Scan QR code first.',
                sessionId,
            });
        }
    } catch (error) {
        console.error('Error getting session info:', error);
        res.status(500).json({
            success: false,
            error: error.message,
        });
    }
});

// Disconnect session
app.post('/disconnect', async(req, res) => {
    try {
        const sessionId = req.body.sessionId || 'default';
        const sock = connections.get(sessionId);

        if (sock) {
            await sock.logout();
            connections.delete(sessionId);
            messageQueue.delete(sessionId);
        }

        res.json({
            success: true,
            message: 'Session disconnected',
            sessionId,
        });
    } catch (error) {
        console.error('Error disconnecting:', error);
        res.status(500).json({
            success: false,
            error: error.message,
        });
    }
});

// Get all sessions/devices
app.get('/sessions', (req, res) => {
    try {
        const devices = [];
        for (const [sessionId, sock] of connections) {
            const isConnected = sock.user ? true : false;
            devices.push({
                id: sessionId,
                phone: sock.user?.id || null,
                status: isConnected ? 'connected' : 'disconnected',
                connected: isConnected,
            });
        }

        res.json({
            success: true,
            devices,
        });
    } catch (error) {
        console.error('Error getting sessions:', error);
        res.status(500).json({
            success: false,
            error: error.message,
        });
    }
});

// Create new session for QR code
app.post('/sessions/new', async(req, res) => {
    try {
        const device_id = req.body.device_id || 'device_' + Date.now();

        // Check if session already exists
        if (connections.has(device_id)) {
            return res.json({
                success: true,
                message: 'Session already exists',
                device_id,
                connected: !!connections.get(device_id).user,
            });
        }

        // Initialize new connection
        const sock = await getConnection(device_id);

        res.status(202).json({
            success: true,
            device_id,
            message: 'Generating QR code...',
            status: 'generating',
        });
    } catch (error) {
        console.error('Error creating session:', error);
        res.status(500).json({
            success: false,
            error: error.message,
        });
    }
});

// Get session status
app.get('/sessions/:sessionId', async(req, res) => {
    try {
        const { sessionId } = req.params;
        const sock = connections.get(sessionId);

        if (!sock) {
            return res.status(404).json({
                success: false,
                error: 'Session not found',
            });
        }

        const isConnected = sock.user ? true : false;

        res.json({
            success: true,
            device_id: sessionId,
            phone: sock.user?.id || null,
            status: isConnected ? 'connected' : 'disconnected',
            connected: isConnected,
        });
    } catch (error) {
        console.error('Error getting session:', error);
        res.status(500).json({
            success: false,
            error: error.message,
        });
    }
});

// Delete session
app.delete('/sessions/:sessionId', (req, res) => {
    try {
        const { sessionId } = req.params;
        const sock = connections.get(sessionId);

        if (!sock) {
            return res.status(404).json({
                success: false,
                error: 'Session not found',
            });
        }

        // Disconnect and remove
        sock.end();
        connections.delete(sessionId);
        messageQueue.delete(sessionId);

        // Clean up session files
        const sessionPath = path.join(sessionDir, sessionId);
        if (fs.existsSync(sessionPath)) {
            fs.rmSync(sessionPath, { recursive: true, force: true });
        }

        res.json({
            success: true,
            message: 'Session deleted',
        });
    } catch (error) {
        console.error('Error deleting session:', error);
        res.status(500).json({
            success: false,
            error: error.message,
        });
    }
});

// Error handling middleware
app.use((err, req, res, next) => {
    console.error('Unhandled error:', err);
    res.status(500).json({
        success: false,
        error: err.message,
    });
});

// 404 handler
app.use((req, res) => {
    res.status(404).json({
        success: false,
        error: 'Endpoint not found',
    });
});

// Start server
app.listen(PORT, () => {
    console.log(`
╔════════════════════════════════════════╗
║   WhatsApp Baileys API Server         ║
║   Running on http://localhost:${PORT}    ║
╚════════════════════════════════════════╝

Available endpoints:
  GET    /health                    - Health check
  GET    /qr                        - Get QR code
  POST   /send-message              - Send message
  POST   /send-scheduled            - Schedule message
  GET    /message-status/:id        - Get message status
  GET    /messages                  - Get messages history
  GET    /session-info              - Get session info
  POST   /disconnect                - Disconnect session

Sessions directory: ${sessionDir}
    `);
});

// Graceful shutdown
process.on('SIGTERM', () => {
    console.log('SIGTERM received, closing connections...');
    for (const [sessionId, sock] of connections) {
        sock.end();
    }
    process.exit(0);
});