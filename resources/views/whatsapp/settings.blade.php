@extends('layouts.app')

@section('title', 'Pengaturan WhatsApp')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3">
                    <i class="bi bi-gear"></i> Pengaturan WhatsApp
                </h1>
                <a href="{{ route('whatsapp.dashboard') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left"></i> Kembali
                </a>
            </div>
        </div>
    </div>

    <!-- Alert Messages -->
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <h6 class="alert-heading"><i class="bi bi-exclamation-circle"></i> Terjadi Kesalahan!</h6>
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <!-- Left Sidebar - Settings Menu -->
        <div class="col-md-3 mb-4">
            <div class="list-group sticky-top" style="top: 20px;" role="tablist">
                <a class="list-group-item list-group-item-action active" 
                   data-bs-toggle="tab" href="#connection-settings" role="tab" aria-selected="true">
                    <i class="bi bi-wifi"></i> Koneksi
                </a>
                <a class="list-group-item list-group-item-action" 
                   data-bs-toggle="tab" href="#device-settings" role="tab" aria-selected="false">
                    <i class="bi bi-phone"></i> Perangkat
                </a>
                <a class="list-group-item list-group-item-action" 
                   data-bs-toggle="tab" href="#webhook-settings" role="tab" aria-selected="false">
                    <i class="bi bi-link-45deg"></i> Webhook
                </a>
                <a class="list-group-item list-group-item-action" 
                   data-bs-toggle="tab" href="#message-settings" role="tab" aria-selected="false">
                    <i class="bi bi-chat-dots"></i> Pesan
                </a>
                <a class="list-group-item list-group-item-action" 
                   data-bs-toggle="tab" href="#api-settings" role="tab" aria-selected="false">
                    <i class="bi bi-speedometer2"></i> API
                </a>
            </div>
        </div>

        <!-- Right Content - Settings Forms -->
        <div class="col-md-9">
            <div class="tab-content">
                <!-- Connection Settings -->
                <div class="tab-pane fade show active" id="connection-settings" role="tabpanel">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0"><i class="bi bi-wifi"></i> Pengaturan Koneksi</h5>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('whatsapp.settings.update') }}" method="POST">
                                @csrf
                                
                                {{-- Hidden fields untuk settings dari tab lain --}}
                                <input type="hidden" name="device_check_interval" value="{{ old('device_check_interval', $device_check_interval ?? 30) }}">
                                <input type="hidden" name="default_device_id" value="{{ old('default_device_id', $default_device_id ?? '') }}">
                                <input type="hidden" name="webhook_url" value="{{ old('webhook_url', $webhook_url ?? '') }}">
                                <input type="hidden" name="webhook_secret" value="{{ old('webhook_secret', $webhook_secret ?? '') }}">
                                <input type="hidden" name="webhook_enabled" value="{{ old('webhook_enabled', $webhook_enabled ? 1 : 0) }}">
                                <input type="hidden" name="enable_auto_reply" value="{{ old('enable_auto_reply', $enable_auto_reply ? 1 : 0) }}">
                                <input type="hidden" name="auto_reply_message" value="{{ old('auto_reply_message', $auto_reply_message ?? '') }}">
                                <input type="hidden" name="message_retention_days" value="{{ old('message_retention_days', $message_retention_days ?? 30) }}">
                                <input type="hidden" name="max_message_length" value="{{ old('max_message_length', $max_message_length ?? 4096) }}">
                                <input type="hidden" name="api_rate_limit" value="{{ old('api_rate_limit', $api_rate_limit ?? 20) }}">
                                <input type="hidden" name="api_timeout" value="{{ old('api_timeout', $api_timeout ?? 30) }}">
                                <input type="hidden" name="api_retry_attempts" value="{{ old('api_retry_attempts', $api_retry_attempts ?? 3) }}">
                                <input type="hidden" name="api_retry_delay" value="{{ old('api_retry_delay', $api_retry_delay ?? 5) }}">
                                
                                <div class="row mb-4">
                                    <div class="col-md-6">
                                        <h6>Status Baileys Backend</h6>
                                        <div id="baileys-status" class="d-flex align-items-center">
                                            @if ($baileys_status ?? false)
                                                <span class="badge bg-success me-2"><i class="bi bi-check-circle"></i> Online</span>
                                                <small class="text-success">Baileys backend sedang berjalan</small>
                                            @else
                                                <span class="badge bg-danger me-2"><i class="bi bi-x-circle"></i> Offline</span>
                                                <small class="text-danger">Baileys backend tidak tersambung. Pastikan sudah dijalankan: <code>npm start</code></small>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="baileys_url" class="form-label">URL Baileys Backend</label>
                                            <input type="url" class="form-control @error('baileys_url') is-invalid @enderror" 
                                                   id="baileys_url" name="baileys_url" 
                                                   value="{{ old('baileys_url', $baileys_url ?? 'http://localhost:3000') }}"
                                                   placeholder="http://localhost:3000" required>
                                            @error('baileys_url')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <hr>

                                <h6 class="mb-3">Instruksi Koneksi</h6>
                                <div class="alert alert-info">
                                    <ol class="mb-0">
                                        <li>Pastikan Node.js terinstall di sistem Anda</li>
                                        <li>Navigasi ke folder <code>backend/</code></li>
                                        <li>Jalankan <code>npm install</code> (jika belum)</li>
                                        <li>Jalankan <code>npm start</code> untuk menjalankan Baileys backend</li>
                                        <li>Backend akan tersedia di <code>{{ $baileys_url ?? 'http://localhost:3000' }}</code></li>
                                    </ol>
                                </div>

                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-outline-primary" onclick="checkBaileysStatus()">
                                        <i class="bi bi-arrow-clockwise"></i> Cek Status
                                    </button>
                                    <button type="button" class="btn btn-outline-primary" onclick="openBaileysHealth()">
                                        <i class="bi bi-link-45deg"></i> Buka Backend
                                    </button>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-save"></i> Simpan Koneksi
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Device Settings -->
                <div class="tab-pane fade" id="device-settings" role="tabpanel">
                    <div class="card">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0"><i class="bi bi-phone"></i> Manajemen Perangkat WhatsApp</h5>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('whatsapp.settings.update') }}" method="POST" class="mb-4">
                                @csrf
                                
                                {{-- Hidden fields untuk settings dari tab lain --}}
                                <input type="hidden" name="baileys_url" value="{{ old('baileys_url', $baileys_url ?? 'http://localhost:3000') }}">
                                <input type="hidden" name="webhook_url" value="{{ old('webhook_url', $webhook_url ?? '') }}">
                                <input type="hidden" name="webhook_secret" value="{{ old('webhook_secret', $webhook_secret ?? '') }}">
                                <input type="hidden" name="webhook_enabled" value="{{ old('webhook_enabled', $webhook_enabled ? 1 : 0) }}">
                                <input type="hidden" name="enable_auto_reply" value="{{ old('enable_auto_reply', $enable_auto_reply ? 1 : 0) }}">
                                <input type="hidden" name="auto_reply_message" value="{{ old('auto_reply_message', $auto_reply_message ?? '') }}">
                                <input type="hidden" name="message_retention_days" value="{{ old('message_retention_days', $message_retention_days ?? 30) }}">
                                <input type="hidden" name="max_message_length" value="{{ old('max_message_length', $max_message_length ?? 4096) }}">
                                <input type="hidden" name="api_rate_limit" value="{{ old('api_rate_limit', $api_rate_limit ?? 20) }}">
                                <input type="hidden" name="api_timeout" value="{{ old('api_timeout', $api_timeout ?? 30) }}">
                                <input type="hidden" name="api_retry_attempts" value="{{ old('api_retry_attempts', $api_retry_attempts ?? 3) }}">
                                <input type="hidden" name="api_retry_delay" value="{{ old('api_retry_delay', $api_retry_delay ?? 5) }}">
                                
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="default_device_id" class="form-label">Perangkat Default</label>
                                        <input type="text" class="form-control @error('default_device_id') is-invalid @enderror" 
                                               id="default_device_id" name="default_device_id" 
                                               value="{{ old('default_device_id', $default_device_id ?? '') }}"
                                               placeholder="Device ID untuk perangkat default">
                                        <small class="text-muted d-block mt-2">
                                            Perangkat ini akan digunakan sebagai pengirim pesan utama
                                        </small>
                                        @error('default_device_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label for="device_check_interval" class="form-label">Interval Cek Perangkat (Detik)</label>
                                        <input type="number" class="form-control @error('device_check_interval') is-invalid @enderror" 
                                               id="device_check_interval" name="device_check_interval" 
                                               value="{{ old('device_check_interval', $device_check_interval ?? 30) }}"
                                               min="10" max="300" required>
                                        <small class="text-muted d-block mt-2">
                                            Frekuensi sistem mengecek status perangkat
                                        </small>
                                        @error('device_check_interval')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-success me-2">
                                    <i class="bi bi-save"></i> Simpan Pengaturan Perangkat
                                </button>
                            </form>

                            <hr>

                            <p class="text-muted">Kelola perangkat WhatsApp yang terhubung dengan sistem.</p>

                            <div class="mb-3">
                                <button type="button" class="btn btn-success" onclick="showDeviceNameForm()">
                                    <i class="bi bi-qr-code"></i> Tambah Perangkat Baru
                                </button>
                                <button type="button" class="btn btn-outline-secondary" onclick="refreshDevices()">
                                    <i class="bi bi-arrow-clockwise"></i> Refresh
                                </button>
                            </div>

                            <!-- Device Name Form -->
                            <div id="device-name-form" style="display: none;" class="mb-4">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h6 class="card-title">Nama Perangkat Baru</h6>
                                        <div class="input-group">
                                            <input type="text" class="form-control" id="device_name_input" 
                                                   placeholder="Contoh: WhatsApp Server 1" maxlength="100">
                                            <button class="btn btn-success" type="button" onclick="confirmDeviceName()">
                                                <i class="bi bi-check"></i> Lanjut
                                            </button>
                                            <button class="btn btn-secondary" type="button" onclick="cancelDeviceNameForm()">
                                                <i class="bi bi-x"></i> Batal
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- QR Code Display -->
                            <div id="qr-code-container" style="display: none;" class="mb-4">
                                <div class="alert alert-warning">
                                    <p class="mb-3"><strong>Instruksi Pairing:</strong></p>
                                    <ol class="mb-0">
                                        <li>Buka WhatsApp di ponsel Anda</li>
                                        <li>Buka Pengaturan → Perangkat Tertaut → Tautkan Perangkat</li>
                                        <li>Scan QR code di bawah dengan kamera ponsel</li>
                                        <li>Tunggu hingga perangkat terhubung</li>
                                    </ol>
                                </div>
                                <div id="qr-code-image" class="text-center p-3" style="background: #f8f9fa; border-radius: 0.5rem;">
                                    <p class="text-muted">Memuat QR Code...</p>
                                </div>
                                <button type="button" class="btn btn-secondary mt-3 w-100" onclick="cancelQRGeneration()">
                                    Batal
                                </button>
                            </div>

                            <!-- Devices List -->
                            <div id="devices-list">
                                <div class="text-center py-5">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Memuat...</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Webhook Settings -->
                <div class="tab-pane fade" id="webhook-settings" role="tabpanel">
                    <div class="card">
                        <div class="card-header bg-info text-white">
                            <h5 class="mb-0"><i class="bi bi-link-45deg"></i> Pengaturan Webhook</h5>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('whatsapp.settings.update') }}" method="POST">
                                @csrf

                                {{-- Hidden fields untuk settings dari tab lain --}}
                                <input type="hidden" name="baileys_url" value="{{ old('baileys_url', $baileys_url ?? 'http://localhost:3000') }}">
                                <input type="hidden" name="device_check_interval" value="{{ old('device_check_interval', $device_check_interval ?? 30) }}">
                                <input type="hidden" name="default_device_id" value="{{ old('default_device_id', $default_device_id ?? '') }}">
                                <input type="hidden" name="enable_auto_reply" value="{{ old('enable_auto_reply', $enable_auto_reply ? 1 : 0) }}">
                                <input type="hidden" name="auto_reply_message" value="{{ old('auto_reply_message', $auto_reply_message ?? '') }}">
                                <input type="hidden" name="message_retention_days" value="{{ old('message_retention_days', $message_retention_days ?? 30) }}">
                                <input type="hidden" name="max_message_length" value="{{ old('max_message_length', $max_message_length ?? 4096) }}">
                                <input type="hidden" name="api_rate_limit" value="{{ old('api_rate_limit', $api_rate_limit ?? 20) }}">
                                <input type="hidden" name="api_timeout" value="{{ old('api_timeout', $api_timeout ?? 30) }}">
                                <input type="hidden" name="api_retry_attempts" value="{{ old('api_retry_attempts', $api_retry_attempts ?? 3) }}">
                                <input type="hidden" name="api_retry_delay" value="{{ old('api_retry_delay', $api_retry_delay ?? 5) }}">

                                <div class="mb-3">
                                    <label for="webhook_url" class="form-label">URL Webhook</label>
                                    <input type="url" class="form-control @error('webhook_url') is-invalid @enderror" 
                                           id="webhook_url" name="webhook_url" 
                                           value="{{ old('webhook_url', $webhook_url ?? '') }}"
                                           placeholder="https://example.com/webhook/whatsapp">
                                    <small class="text-muted d-block mt-2">
                                        Webhook akan mengirimkan notifikasi untuk setiap event (pesan terkirim, terbaca, gagal, dll)
                                    </small>
                                    @error('webhook_url')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="webhook_enabled" 
                                               name="webhook_enabled" value="1"
                                               @if(old('webhook_enabled', $webhook_enabled ?? false)) checked @endif>
                                        <label class="form-check-label" for="webhook_enabled">
                                            Aktifkan Webhook
                                        </label>
                                    </div>
                                    <small class="text-muted d-block mt-2">
                                        Nonaktifkan webhook saat tidak diperlukan
                                    </small>
                                </div>

                                <div class="mb-3">
                                    <label for="webhook_secret" class="form-label">Secret Key (Optional)</label>
                                    <input type="text" class="form-control @error('webhook_secret') is-invalid @enderror" 
                                           id="webhook_secret" name="webhook_secret" 
                                           value="{{ old('webhook_secret', $webhook_secret ?? '') }}"
                                           placeholder="Secret untuk validasi webhook">
                                    <small class="text-muted d-block mt-2">
                                        Gunakan secret untuk validasi request webhook
                                    </small>
                                    @error('webhook_secret')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="alert alert-secondary">
                                    <strong>Event Webhook yang Dikirim:</strong>
                                    <ul class="mb-0 mt-2">
                                        <li><code>message.sent</code> - Pesan berhasil dikirim</li>
                                        <li><code>message.delivered</code> - Pesan sudah diterima</li>
                                        <li><code>message.read</code> - Pesan sudah dibaca</li>
                                        <li><code>message.failed</code> - Pesan gagal terkirim</li>
                                        <li><code>session.connected</code> - Sesi terhubung</li>
                                        <li><code>session.disconnected</code> - Sesi terputus</li>
                                    </ul>
                                </div>

                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-info">
                                        <i class="bi bi-save"></i> Simpan Webhook
                                    </button>
                                    @if (($webhook_url ?? '') && ($webhook_enabled ?? false))
                                        <button type="button" class="btn btn-outline-info" onclick="testWebhook()">
                                            <i class="bi bi-play-circle"></i> Test Webhook
                                        </button>
                                    @endif
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Message Settings -->
                <div class="tab-pane fade" id="message-settings" role="tabpanel">
                    <div class="card">
                        <div class="card-header bg-warning text-dark">
                            <h5 class="mb-0"><i class="bi bi-chat-dots"></i> Pengaturan Pesan</h5>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('whatsapp.settings.update') }}" method="POST">
                                @csrf

                                {{-- Hidden fields untuk settings dari tab lain --}}
                                <input type="hidden" name="baileys_url" value="{{ old('baileys_url', $baileys_url ?? 'http://localhost:3000') }}">
                                <input type="hidden" name="device_check_interval" value="{{ old('device_check_interval', $device_check_interval ?? 30) }}">
                                <input type="hidden" name="default_device_id" value="{{ old('default_device_id', $default_device_id ?? '') }}">
                                <input type="hidden" name="webhook_url" value="{{ old('webhook_url', $webhook_url ?? '') }}">
                                <input type="hidden" name="webhook_secret" value="{{ old('webhook_secret', $webhook_secret ?? '') }}">
                                <input type="hidden" name="webhook_enabled" value="{{ old('webhook_enabled', $webhook_enabled ? 1 : 0) }}">
                                <input type="hidden" name="api_rate_limit" value="{{ old('api_rate_limit', $api_rate_limit ?? 20) }}">
                                <input type="hidden" name="api_timeout" value="{{ old('api_timeout', $api_timeout ?? 30) }}">
                                <input type="hidden" name="api_retry_attempts" value="{{ old('api_retry_attempts', $api_retry_attempts ?? 3) }}">
                                <input type="hidden" name="api_retry_delay" value="{{ old('api_retry_delay', $api_retry_delay ?? 5) }}">

                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="enable_auto_reply" 
                                               name="enable_auto_reply" value="1"
                                               @if(old('enable_auto_reply', $enable_auto_reply ?? false)) checked @endif>
                                        <label class="form-check-label" for="enable_auto_reply">
                                            Aktifkan Balasan Otomatis
                                        </label>
                                    </div>
                                    <small class="text-muted d-block mt-2">
                                        Sistem akan mengirim balasan otomatis saat menerima pesan
                                    </small>
                                </div>

                                <div class="mb-3">
                                    <label for="auto_reply_message" class="form-label">Pesan Balasan Otomatis</label>
                                    <textarea class="form-control @error('auto_reply_message') is-invalid @enderror" 
                                              id="auto_reply_message" name="auto_reply_message" rows="4"
                                              placeholder="Terima kasih atas pesan Anda. Kami akan merespons sesegera mungkin.">{{ old('auto_reply_message', $auto_reply_message ?? 'Terima kasih atas pesan Anda. Kami akan merespons sesegera mungkin.') }}</textarea>
                                    <small class="text-muted d-block mt-2">
                                        Maksimal {{ $max_message_length ?? 4096 }} karakter
                                    </small>
                                    @error('auto_reply_message')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="message_retention_days" class="form-label">Retensi Data Pesan (Hari)</label>
                                            <input type="number" class="form-control @error('message_retention_days') is-invalid @enderror" 
                                                   id="message_retention_days" name="message_retention_days" 
                                                   value="{{ old('message_retention_days', $message_retention_days ?? 30) }}"
                                                   min="1" max="365" required>
                                            <small class="text-muted d-block mt-2">
                                                Sistem akan menghapus data pesan yang lebih lama dari periode ini
                                            </small>
                                            @error('message_retention_days')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="max_message_length" class="form-label">Panjang Maksimal Pesan (Karakter)</label>
                                            <input type="number" class="form-control @error('max_message_length') is-invalid @enderror" 
                                                   id="max_message_length" name="max_message_length" 
                                                   value="{{ old('max_message_length', $max_message_length ?? 4096) }}"
                                                   min="100" max="4096" required>
                                            <small class="text-muted d-block mt-2">
                                                Batas karakter maksimal untuk setiap pesan
                                            </small>
                                            @error('max_message_length')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-warning">
                                    <i class="bi bi-save"></i> Simpan Pengaturan Pesan
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- API Settings -->
                <div class="tab-pane fade" id="api-settings" role="tabpanel">
                    <div class="card">
                        <div class="card-header bg-danger text-white">
                            <h5 class="mb-0"><i class="bi bi-speedometer2"></i> Pengaturan API</h5>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('whatsapp.settings.update') }}" method="POST">
                                @csrf

                                {{-- Hidden fields untuk settings dari tab lain --}}
                                <input type="hidden" name="baileys_url" value="{{ old('baileys_url', $baileys_url ?? 'http://localhost:3000') }}">
                                <input type="hidden" name="device_check_interval" value="{{ old('device_check_interval', $device_check_interval ?? 30) }}">
                                <input type="hidden" name="default_device_id" value="{{ old('default_device_id', $default_device_id ?? '') }}">
                                <input type="hidden" name="webhook_url" value="{{ old('webhook_url', $webhook_url ?? '') }}">
                                <input type="hidden" name="webhook_secret" value="{{ old('webhook_secret', $webhook_secret ?? '') }}">
                                <input type="hidden" name="webhook_enabled" value="{{ old('webhook_enabled', $webhook_enabled ? 1 : 0) }}">
                                <input type="hidden" name="enable_auto_reply" value="{{ old('enable_auto_reply', $enable_auto_reply ? 1 : 0) }}">
                                <input type="hidden" name="auto_reply_message" value="{{ old('auto_reply_message', $auto_reply_message ?? 'Terima kasih atas pesan Anda.') }}">
                                <input type="hidden" name="message_retention_days" value="{{ old('message_retention_days', $message_retention_days ?? 30) }}">
                                <input type="hidden" name="max_message_length" value="{{ old('max_message_length', $max_message_length ?? 4096) }}">

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="api_rate_limit" class="form-label">Rate Limit (Pesan/Menit)</label>
                                            <input type="number" class="form-control @error('api_rate_limit') is-invalid @enderror" 
                                                   id="api_rate_limit" name="api_rate_limit" 
                                                   value="{{ old('api_rate_limit', $api_rate_limit ?? 20) }}"
                                                   min="1" max="100" required>
                                            <small class="text-muted d-block mt-2">
                                                Maksimal pesan yang dapat dikirim per menit
                                            </small>
                                            @error('api_rate_limit')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="api_timeout" class="form-label">Timeout (Detik)</label>
                                            <input type="number" class="form-control @error('api_timeout') is-invalid @enderror" 
                                                   id="api_timeout" name="api_timeout" 
                                                   value="{{ old('api_timeout', $api_timeout ?? 30) }}"
                                                   min="5" max="300" required>
                                            <small class="text-muted d-block mt-2">
                                                Waktu tunggu maksimal untuk API request
                                            </small>
                                            @error('api_timeout')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="api_retry_attempts" class="form-label">Jumlah Percobaan Ulang</label>
                                            <input type="number" class="form-control @error('api_retry_attempts') is-invalid @enderror" 
                                                   id="api_retry_attempts" name="api_retry_attempts" 
                                                   value="{{ old('api_retry_attempts', $api_retry_attempts ?? 3) }}"
                                                   min="1" max="10" required>
                                            <small class="text-muted d-block mt-2">
                                                Berapa kali sistem mencoba ulang jika request gagal
                                            </small>
                                            @error('api_retry_attempts')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="api_retry_delay" class="form-label">Delay Ulang (Detik)</label>
                                            <input type="number" class="form-control @error('api_retry_delay') is-invalid @enderror" 
                                                   id="api_retry_delay" name="api_retry_delay" 
                                                   value="{{ old('api_retry_delay', $api_retry_delay ?? 5) }}"
                                                   min="1" max="60" required>
                                            <small class="text-muted d-block mt-2">
                                                Waktu tunggu sebelum mencoba ulang
                                            </small>
                                            @error('api_retry_delay')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="alert alert-secondary">
                                    <strong>Informasi API:</strong>
                                    <ul class="mb-0 mt-2">
                                        <li>API Base URL: <code>{{ url('/api/whatsapp') }}</code></li>
                                        <li>Rate Limit Saat Ini: <code>{{ $api_rate_limit }} pesan/menit</code></li>
                                        <li>Timeout: <code>{{ $api_timeout }} detik</code></li>
                                        <li>Percobaan Ulang: <code>{{ $api_retry_attempts }} x dengan delay {{ $api_retry_delay }}s</code></li>
                                    </ul>
                                </div>

                                <button type="submit" class="btn btn-danger">
                                    <i class="bi bi-save"></i> Simpan Pengaturan API
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Check Baileys Status
function checkBaileysStatus() {
    const btn = event.target;
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Mengecek...';

    fetch('{{ route("whatsapp.api.backend-health") }}')
        .then(response => response.json())
        .then(data => {
            const statusDiv = document.getElementById('baileys-status');
            
            if (data.success && data.status === 'online') {
                showNotification('✅ Baileys Backend Online', 'success');
                statusDiv.innerHTML = 
                    '<span class="badge bg-success me-2"><i class="bi bi-check-circle"></i> Online</span>' +
                    '<small class="text-success">Baileys backend sedang berjalan di ' + data.url + '</small>';
            } else {
                showNotification('❌ Baileys Backend Offline', 'danger');
                statusDiv.innerHTML = 
                    '<span class="badge bg-danger me-2"><i class="bi bi-x-circle"></i> Offline</span>' +
                    '<small class="text-danger">Baileys backend tidak dapat dijangkau</small>';
                
                // Show detailed error
                if (data.suggestions) {
                    let suggestionHtml = '<div class="alert alert-warning mt-3 mb-0"><strong>Saran:</strong><ul class="mb-0 ps-3 mt-2">';
                    data.suggestions.forEach(s => {
                        suggestionHtml += '<li>' + s + '</li>';
                    });
                    suggestionHtml += '</ul></div>';
                    statusDiv.insertAdjacentHTML('afterend', suggestionHtml);
                }
            }
        })
        .catch(error => {
            showNotification('Gagal Mengecek Status: ' + error.message, 'danger');
            document.getElementById('baileys-status').innerHTML = 
                '<span class="badge bg-danger me-2"><i class="bi bi-x-circle"></i> Error</span>' +
                '<small class="text-danger">Network error saat mengecek status</small>';
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = originalText;
        });
}

// Open Baileys Health Check
function openBaileysHealth() {
    const baileysUrl = '{{ $baileys_url }}';
    window.open(baileysUrl + '/health', '_blank');
}

// Show Device Name Form
function showDeviceNameForm() {
    document.getElementById('device-name-form').style.display = 'block';
    document.getElementById('device_name_input').focus();
}

// Cancel Device Name Form
function cancelDeviceNameForm() {
    document.getElementById('device-name-form').style.display = 'none';
    document.getElementById('device_name_input').value = '';
}

// Confirm Device Name
function confirmDeviceName() {
    const deviceName = document.getElementById('device_name_input').value.trim();
    
    if (!deviceName) {
        showNotification('Nama perangkat tidak boleh kosong', 'warning');
        return;
    }
    
    generateQRCode(deviceName);
    cancelDeviceNameForm();
}

// Check Device Status (polling)
let qrPollingInterval = null;
let qrPollingAttempts = 0;

function checkDeviceStatus(deviceId) {
    if (!deviceId) return;
    
    // Check via Laravel endpoint (which queries Baileys backend)
    const laravelEndpoint = '{{ route("whatsapp.device.connection-status", ":device") }}'.replace(':device', deviceId);
    
    fetch(laravelEndpoint)
        .then(response => response.json())
        .then(data => {
            console.log('Device status check:', deviceId, data);
            
            if (data.success && data.authenticated && data.ready) {
                // Device is fully authenticated and ready!
                console.log('✅ Device is ready!', deviceId);
                
                // Stop polling
                if (qrPollingInterval) {
                    clearInterval(qrPollingInterval);
                    qrPollingInterval = null;
                }
                
                // Show success message
                showNotification('✅ Perangkat berhasil terhubung!', 'success');
                
                // Update UI
                const qrDiv = document.getElementById('qr-code-image');
                if (qrDiv) {
                    qrDiv.innerHTML = `
                        <div class="alert alert-success">
                            <i class="bi bi-check-circle"></i> <strong>Perangkat Terhubung!</strong>
                            <p class="mb-2 mt-2">Device ID: <code>${deviceId}</code></p>
                            <p class="mb-0 small">Nomor WhatsApp: <strong>${data.phone || 'N/A'}</strong></p>
                        </div>
                    `;
                }
                
                // Refresh devices list
                setTimeout(() => {
                    refreshDevices();
                    document.getElementById('qr-code-container').style.display = 'none';
                }, 1500);
                
            } else if (data.status === 'pending' || qrPollingAttempts < 45) {
                // Still waiting for QR scan or authentication
                qrPollingAttempts++;
                console.log('Device still pending... attempt', qrPollingAttempts);
            } else {
                // Timeout after ~90 seconds (45 * 2 second polls)
                console.log('Device polling timeout');
                clearInterval(qrPollingInterval);
                qrPollingInterval = null;
                
                const qrDiv = document.getElementById('qr-code-image');
                if (qrDiv) {
                    qrDiv.innerHTML = `
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-circle"></i> <strong>QR Scan Timeout</strong>
                            <p class="mb-2 mt-2">QR code belum di-scan dalam 90 detik.</p>
                            <p class="mb-0">Silakan <button type="button" class="btn btn-link btn-sm" onclick="document.getElementById('qr-code-container').style.display='none'">buat QR baru</button>.</p>
                        </div>
                    `;
                }
            }
        })
        .catch(error => {
            // Connection error - continue polling (backend might not be fully ready yet)
            console.log('Device status check error:', error);
        });
}

// Generate QR Code
function generateQRCode(deviceName = null) {
    const container = document.getElementById('qr-code-container');
    const imageDiv = document.getElementById('qr-code-image');
    
    if (!imageDiv) {
        // Create imageDiv if it doesn't exist
        const html = `
            <div class="alert alert-warning">
                <p class="mb-3"><strong>Instruksi Pairing:</strong></p>
                <ol class="mb-0">
                    <li>Buka WhatsApp di ponsel Anda</li>
                    <li>Buka Pengaturan → Perangkat Tertaut → Tautkan Perangkat</li>
                    <li>Scan QR code di bawah dengan kamera ponsel</li>
                    <li>Tunggu hingga perangkat terhubung</li>
                </ol>
            </div>
            <div id="qr-code-image" class="text-center p-3" style="background: #f8f9fa; border-radius: 0.5rem;">
                <p class="text-muted"><span class="spinner-border spinner-border-sm me-2"></span>Memuat QR Code...</p>
            </div>
            <div class="mt-3">
                <button type="button" class="btn btn-secondary w-100" onclick="cancelQRGeneration()">
                    Batal
                </button>
            </div>
        `;
        container.innerHTML = html;
    } else {
        imageDiv.innerHTML = '<p class="text-muted"><span class="spinner-border spinner-border-sm me-2"></span>Memuat QR Code...</p>';
    }
    
    container.style.display = 'block';

    fetch('{{ route("whatsapp.qr.generate") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('[name="_token"]').value,
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            device_name: deviceName || 'Default Device'
        })
    })
    .then(response => response.json())
    .then(data => {
        console.log('QR Response:', data);
        const qrDiv = document.getElementById('qr-code-image');
        
        if (data.success && (data.data?.qr || data.qr)) {
            const qrCode = data.data?.qr || data.qr;
            const deviceId = data.data?.device_id || data.device_id;
            
            if (qrDiv) {
                qrDiv.innerHTML = `
                    <img src="${qrCode}" alt="QR Code" class="img-fluid" style="max-width: 300px;">
                    <p class="text-muted mt-2">Device ID: <code>${deviceId}</code></p>
                    <p class="text-muted small">
                        <span class="spinner-border spinner-border-sm me-2"></span>
                        Menunggu scan QR code... (status di-update otomatis)
                    </p>
                `;
            }
            
            // START AUTO-POLLING untuk cek status device setelah QR di-scan
            console.log('🔄 Starting device status polling for:', deviceId);
            
            qrPollingInterval = setInterval(() => {
                checkDeviceStatus(deviceId);
            }, 2000); // Poll every 2 seconds
        } else {
            // Show detailed error message
            if (qrDiv) {
                let errorHtml = `
                    <div class="alert alert-danger mt-3" role="alert">
                        <h6 class="alert-heading">
                            <i class="bi bi-exclamation-triangle"></i> Gagal Membuat QR Code
                        </h6>
                        <p class="mb-2"><strong>Alasan:</strong> ${data.error || 'Koneksi ke Baileys backend gagal'}</p>
                `;
                
                if (data.suggestions && Array.isArray(data.suggestions)) {
                    errorHtml += '<p class="mb-2"><strong>Saran Perbaikan:</strong></p><ul class="mb-0">';
                    data.suggestions.forEach(suggestion => {
                        errorHtml += `<li>${suggestion}</li>`;
                    });
                    errorHtml += '</ul>';
                }
                
                errorHtml += `
                        <hr class="my-2">
                        <p class="mb-0 small">
                            <strong>Troubleshooting:</strong>
                            <ol class="mb-0 ps-3">
                                <li>Pastikan backend Node.js berjalan: <code>cd backend && npm start</code></li>
                                <li>Cek port 3000 tidak tertutup firewall</li>
                                <li>Lihat console backend untuk error messages</li>
                                <li>Update URL Baileys di tab "Koneksi"</li>
                                <li><a href="/whatsapp/settings" target="_blank">Refresh halaman</a></li>
                            </ol>
                        </p>
                    </div>
                `;
                
                qrDiv.innerHTML = errorHtml;
            }
        }
    })
    .catch(error => {
        const qrDiv = document.getElementById('qr-code-image');
        if (qrDiv) {
            qrDiv.innerHTML = `
                <div class="alert alert-danger" role="alert">
                    <h6 class="alert-heading">
                        <i class="bi bi-exclamation-triangle"></i> Network Error
                    </h6>
                    <p class="mb-2"><strong>Error:</strong> ${error.message}</p>
                    <p class="mb-0 small">
                        Pastikan backend Node.js berjalan di port 3000.
                        <br>
                        Jalankan: <code>cd backend && npm start</code>
                    </p>
                </div>
            `;
        }
    });
}

// Cancel QR Generation
function cancelQRGeneration() {
    // Stop polling
    if (qrPollingInterval) {
        clearInterval(qrPollingInterval);
        qrPollingInterval = null;
    }
    qrPollingAttempts = 0;
    
    document.getElementById('qr-code-container').style.display = 'none';
}

// Refresh Devices
function refreshDevices() {
    const container = document.getElementById('devices-list');
    container.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Memuat...</span></div></div>';

    fetch('{{ route("whatsapp.devices") }}')
        .then(response => response.json())
        .then(data => {
            console.log('Devices Response:', data);
            const devices = data.devices || data.data || [];
            
            if (data.success && devices.length > 0) {
                let html = '<div class="table-responsive"><table class="table table-hover"><thead class="table-light"><tr><th>Device ID</th><th>Status</th><th>Nomor WhatsApp</th><th>Aksi</th></tr></thead><tbody>';
                
                devices.forEach(device => {
                    const isConnected = device.status === 'active' || device.connected;
                    const statusBadge = isConnected ? 
                        '<span class="badge bg-success"><i class="bi bi-check-circle"></i> ✓ Terhubung</span>' : 
                        '<span class="badge bg-warning"><i class="bi bi-clock"></i> ⏳ Menunggu</span>';
                    const phone = device.phone || device.phone_number || '-';
                    const deviceId = device.id || device.device_id;
                    const deviceName = device.device_name || deviceId;
                    
                    html += `
                        <tr>
                            <td><code>${deviceName}</code></td>
                            <td>${statusBadge}</td>
                            <td>${phone}</td>
                            <td>
                                <button type="button" class="btn btn-sm btn-outline-info" onclick="checkDeviceConnectionStatus('${deviceName}')">
                                    <i class="bi bi-arrow-repeat"></i> Cek
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteDevice('${deviceName}')">
                                    <i class="bi bi-trash"></i> Hapus
                                </button>
                            </td>
                        </tr>
                    `;
                });
                
                html += '</tbody></table></div>';
                container.innerHTML = html;
            } else {
                container.innerHTML = '<div class="alert alert-info"><i class="bi bi-info-circle"></i> Belum ada perangkat yang terhubung. Klik tombol "Tambah Perangkat Baru" untuk memulai.</div>';
            }
        })
        .catch(error => {
            container.innerHTML = '<div class="alert alert-danger"><i class="bi bi-exclamation-triangle"></i> Error: ' + error.message + '</div>';
        });
}

// Check Device Connection Status (Manual)
function checkDeviceConnectionStatus(deviceName) {
    // Show loading notification
    showNotification(`Mengecek status ${deviceName}...`, 'info');
    
    // Use Laravel endpoint to check connection status
    const laravelEndpoint = '{{ route("whatsapp.device.connection-status", ":device") }}'.replace(':device', deviceName);
    
    fetch(laravelEndpoint)
        .then(response => response.json())
        .then(data => {
            console.log('Connection status:', data);
            
            if (data.success && data.authenticated && data.ready) {
                showNotification(`✅ ${deviceName} Terhubung! Phone: ${data.phone}`, 'success');
                
                // Update device status in database
                updateDeviceStatusFromCheck(deviceName, 'active', data.phone);
            } else if (data.authenticated) {
                showNotification(`✓ ${deviceName} Authenticated tapi belum siap (${data.status})`, 'warning');
            } else {
                showNotification(`⏳ ${deviceName} Belum ter-authenticate atau sedang connecting`, 'warning');
                
                // Update status to connecting/disconnected
                updateDeviceStatusFromCheck(deviceName, 'connecting', null);
            }
            
            // Refresh devices list after 2 seconds
            setTimeout(() => refreshDevices(), 2000);
        })
        .catch(error => {
            console.error('Status check error:', error);
            showNotification(`❌ Gagal cek status: ${error.message}`, 'danger');
        });
}

// Update Device Status from Check
function updateDeviceStatusFromCheck(deviceName, status, phone) {
    fetch('{{ route("whatsapp.device.update-status") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('[name="_token"]').value,
        },
        body: JSON.stringify({
            device_name: deviceName,
            status: status,
            phone_number: phone
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            console.log('Device status updated successfully');
        }
    })
    .catch(error => {
        console.error('Update status error:', error);
    });
}

// Delete Device
function deleteDevice(deviceName) {
    if (!confirm('Yakin ingin menghapus perangkat ini?')) return;

    fetch('{{ route("whatsapp.device.delete", ":id") }}'.replace(':id', deviceName), {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('[name="_token"]').value,
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Perangkat berhasil dihapus', 'success');
            refreshDevices();
        } else {
            showNotification('Gagal menghapus perangkat: ' + data.error, 'danger');
        }
    })
    .catch(error => {
        showNotification('Error: ' + error.message, 'danger');
    });
}

// Test Webhook
function testWebhook() {
    const webhookUrl = document.getElementById('webhook_url').value;
    
    if (!webhookUrl) {
        showNotification('Masukkan URL Webhook terlebih dahulu', 'warning');
        return;
    }

    const btn = event.target;
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Testing...';

    fetch('{{ route("whatsapp.webhook.test") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('[name="_token"]').value
        },
        body: JSON.stringify({ webhook_url: webhookUrl })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Webhook berhasil dikirim! (Status: ' + data.status_code + ')', 'success');
        } else {
            showNotification('Gagal mengirim webhook: ' + data.error, 'danger');
        }
    })
    .catch(error => {
        showNotification('Error: ' + error.message, 'danger');
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = originalText;
    });
}

// Show Notification
function showNotification(message, type = 'info') {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
    alertDiv.role = 'alert';
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    const cardBody = document.querySelector('.card-body');
    if (cardBody) {
        cardBody.parentElement.insertBefore(alertDiv, cardBody);
    }
    
    setTimeout(() => alertDiv.remove(), 5000);
}

// Load devices on page load and tab switch
document.addEventListener('DOMContentLoaded', function() {
    // Load devices when page loads
    refreshDevices();
    
    // Reload devices when switching to device tab
    const deviceTab = document.querySelector('button[href="#device-settings"]');
    if (deviceTab) {
        deviceTab.addEventListener('shown.bs.tab', function() {
            setTimeout(() => refreshDevices(), 300);
        });
    }
});

// Periodically check for new connected devices (every 5 seconds)
setInterval(function() {
    if (document.getElementById('devices-list') && !document.getElementById('qr-code-container').style.display === 'none') {
        // Only refresh if device settings is visible
        const deviceTab = document.querySelector('button[href="#device-settings"]');
        if (deviceTab && deviceTab.classList.contains('active')) {
            refreshDevices();
        }
    }
}, 5000);
</script>

<style>
    .list-group-item {
        border: none;
        border-left: 3px solid transparent;
        transition: all 0.3s ease;
    }

    .list-group-item.active {
        background-color: #f8f9fa;
        border-left-color: #0d6efd;
        font-weight: 500;
    }

    .list-group-item:hover {
        background-color: #f8f9fa;
        border-left-color: #0d6efd;
    }

    .card {
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    }

    .card-header {
        border-bottom: 2px solid rgba(0, 0, 0, 0.125);
    }
</style>
@endsection
