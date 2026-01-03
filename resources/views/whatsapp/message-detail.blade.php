@extends('layouts.app')

@section('title', 'Detail Pesan WhatsApp')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <!-- Header -->
            <div class="mb-4">
                <a href="{{ route('whatsapp.history') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left"></i> Kembali
                </a>
            </div>

            <!-- Main Card -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-chat-left-text"></i> Detail Pesan
                    </h5>
                </div>
                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
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

                    <!-- Status Information -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6 class="text-muted">Status</h6>
                            <h4 id="message_status">
                                <span class="badge bg-secondary">Loading...</span>
                            </h4>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted">Waktu</h6>
                            <p class="h6" id="message_time">-</p>
                        </div>
                    </div>

                    <!-- Recipient Information -->
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">
                                <i class="bi bi-person"></i> Informasi Penerima
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <label class="text-muted small">Nomor Telepon</label>
                                    <p class="h6" id="recipient_phone">-</p>
                                </div>
                                <div class="col-md-6">
                                    <label class="text-muted small">Nama Pasien</label>
                                    <p class="h6" id="recipient_name">-</p>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <label class="text-muted small">No. Rekam Medis</label>
                                    <p class="small" id="recipient_rkm">-</p>
                                </div>
                                <div class="col-md-6">
                                    <label class="text-muted small">Email</label>
                                    <p class="small" id="recipient_email">-</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Message Content -->
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">
                                <i class="bi bi-chat-dots"></i> Isi Pesan
                            </h6>
                        </div>
                        <div class="card-body">
                            <p id="message_content" class="lead" style="line-height: 1.8;">-</p>
                        </div>
                    </div>

                    <!-- Delivery Timeline -->
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">
                                <i class="bi bi-clock"></i> Timeline Pengiriman
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="timeline">
                                <div class="timeline-item">
                                    <div class="timeline-marker bg-primary"></div>
                                    <div class="timeline-content">
                                        <h6>Dikirim</h6>
                                        <p class="text-muted" id="timeline_sent">-</p>
                                    </div>
                                </div>
                                <div class="timeline-item" id="delivered_item" style="display: none;">
                                    <div class="timeline-marker bg-success"></div>
                                    <div class="timeline-content">
                                        <h6>Diterima</h6>
                                        <p class="text-muted" id="timeline_delivered">-</p>
                                    </div>
                                </div>
                                <div class="timeline-item" id="read_item" style="display: none;">
                                    <div class="timeline-marker bg-info"></div>
                                    <div class="timeline-content">
                                        <h6>Dibaca</h6>
                                        <p class="text-muted" id="timeline_read">-</p>
                                    </div>
                                </div>
                                <div class="timeline-item" id="failed_item" style="display: none;">
                                    <div class="timeline-marker bg-danger"></div>
                                    <div class="timeline-content">
                                        <h6>Gagal</h6>
                                        <p class="text-muted" id="timeline_failed">-</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Message Metadata -->
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">
                                <i class="bi bi-info-circle"></i> Metadata
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="text-muted small">ID Pesan Internal</label>
                                    <p class="small"><code id="message_id">-</code></p>
                                </div>
                                <div class="col-md-6">
                                    <label class="text-muted small">ID Pesan WhatsApp</label>
                                    <p class="small"><code id="external_message_id">-</code></p>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <label class="text-muted small">Template Digunakan</label>
                                    <p class="small" id="template_used">-</p>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <label class="text-muted small">Keterangan</label>
                                    <p class="small" id="description">-</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Error Information (if failed) -->
                    <div id="error_section" class="alert alert-danger" style="display: none;">
                        <h6 class="alert-heading">
                            <i class="bi bi-exclamation-triangle"></i> Informasi Error
                        </h6>
                        <p class="mb-0" id="error_message">-</p>
                    </div>

                    <!-- Action Buttons -->
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <a href="{{ route('whatsapp.history') }}" class="btn btn-secondary">
                            <i class="bi bi-x"></i> Tutup
                        </a>
                        <button type="button" class="btn btn-warning" id="resendBtn" style="display: none;">
                            <i class="bi bi-arrow-repeat"></i> Kirim Ulang
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const messageId = new URLSearchParams(window.location.search).get('id') || '{{ $messageId ?? null }}';

if (messageId) {
    loadMessageDetail(messageId);
}

async function loadMessageDetail(id) {
    try {
        const response = await fetch(`{{ route('whatsapp.show', '') }}/${id}`, {
            headers: {
                'Authorization': 'Bearer ' + (localStorage.getItem('api_token') || ''),
                'Accept': 'application/json',
            }
        });

        const data = await response.json();

        if (data.success) {
            displayMessage(data.data);
        } else {
            showError('Pesan tidak ditemukan');
        }
    } catch (error) {
        showError('Gagal memuat detail: ' + error.message);
    }
}

function displayMessage(msg) {
    // Status
    document.getElementById('message_status').innerHTML = getStatusBadge(msg.status);
    document.getElementById('message_time').textContent = new Date(msg.created_at).toLocaleString('id-ID');

    // Recipient
    document.getElementById('recipient_phone').textContent = msg.no_tlp;
    document.getElementById('recipient_name').textContent = msg.nm_pasien || '-';
    document.getElementById('recipient_rkm').textContent = msg.no_rkm_medis || '-';
    document.getElementById('recipient_email').textContent = msg.email || '-';

    // Message Content
    document.getElementById('message_content').textContent = msg.isi_pesan;

    // Timeline
    document.getElementById('timeline_sent').textContent = new Date(msg.created_at).toLocaleString('id-ID');

    if (msg.delivered_at) {
        document.getElementById('delivered_item').style.display = 'block';
        document.getElementById('timeline_delivered').textContent = new Date(msg.delivered_at).toLocaleString('id-ID');
    }

    if (msg.read_at) {
        document.getElementById('read_item').style.display = 'block';
        document.getElementById('timeline_read').textContent = new Date(msg.read_at).toLocaleString('id-ID');
    }

    if (msg.status === 'failed') {
        document.getElementById('failed_item').style.display = 'block';
        document.getElementById('timeline_failed').textContent = msg.error_message || 'Tidak diketahui';
    }

    // Metadata
    document.getElementById('message_id').textContent = msg.id;
    document.getElementById('external_message_id').textContent = msg.external_message_id || '-';
    document.getElementById('template_used').textContent = msg.nama_template || 'Tidak menggunakan template';
    document.getElementById('description').textContent = msg.keterangan || '-';

    // Error section
    if (msg.error_message) {
        document.getElementById('error_section').style.display = 'block';
        document.getElementById('error_message').textContent = msg.error_message;
    }

    // Resend button
    if (msg.status === 'failed') {
        const resendBtn = document.getElementById('resendBtn');
        resendBtn.style.display = 'inline-block';
        resendBtn.addEventListener('click', () => resendMessage(msg.id));
    }
}

async function resendMessage(messageId) {
    if (!confirm('Yakin ingin mengirim ulang pesan ini?')) return;

    const btn = document.getElementById('resendBtn');
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Mengirim...';

    try {
        const response = await fetch(`{{ route('whatsapp.resend', '') }}/${messageId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Authorization': 'Bearer ' + (localStorage.getItem('api_token') || ''),
            }
        });

        const data = await response.json();

        if (data.success) {
            showSuccess('Pesan berhasil dikirim ulang');
            setTimeout(() => loadMessageDetail(messageId), 1500);
        } else {
            showError(data.message || 'Gagal mengirim ulang');
        }
    } catch (error) {
        showError('Terjadi kesalahan: ' + error.message);
    } finally {
        btn.disabled = false;
        btn.innerHTML = originalText;
    }
}

function getStatusBadge(status) {
    const badges = {
        'sent': '<span class="badge bg-success">Terkirim</span>',
        'pending': '<span class="badge bg-warning">Menunggu</span>',
        'delivered': '<span class="badge bg-info">Diterima</span>',
        'read': '<span class="badge bg-primary">Dibaca</span>',
        'failed': '<span class="badge bg-danger">Gagal</span>',
    };
    return badges[status] || '<span class="badge bg-secondary">Unknown</span>';
}

function showSuccess(message) {
    console.log('Success:', message);
    const alert = document.createElement('div');
    alert.className = 'alert alert-success alert-dismissible fade show';
    alert.role = 'alert';
    alert.innerHTML = `
        <i class="bi bi-check-circle"></i> ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    try {
        document.body.insertBefore(alert, document.body.firstChild);
    } catch (e) {
        document.body.appendChild(alert);
    }
    setTimeout(() => {
        try { alert.remove(); } catch(e) {}
    }, 3000);
}

function showError(message) {
    console.error('Error:', message);
    const alert = document.createElement('div');
    alert.className = 'alert alert-danger alert-dismissible fade show';
    alert.role = 'alert';
    alert.innerHTML = `
        <i class="bi bi-exclamation-circle"></i> ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    try {
        document.body.insertBefore(alert, document.body.firstChild);
    } catch (e) {
        document.body.appendChild(alert);
    }
    setTimeout(() => {
        try { alert.remove(); } catch(e) {}
    }, 3000);
}
</script>

<style>
    .timeline {
        position: relative;
        padding-left: 30px;
    }

    .timeline-item {
        margin-bottom: 30px;
        position: relative;
    }

    .timeline-marker {
        position: absolute;
        left: -30px;
        top: 5px;
        width: 12px;
        height: 12px;
        border-radius: 50%;
        border: 3px solid #fff;
    }

    .timeline-content h6 {
        margin-bottom: 5px;
    }

    .timeline::before {
        content: '';
        position: absolute;
        left: -24px;
        top: 20px;
        width: 2px;
        height: calc(100% - 20px);
        background: #dee2e6;
    }

    .timeline-item:last-child::after {
        content: '';
        position: absolute;
        left: -24px;
        top: 20px;
        width: 2px;
        height: 0;
        background: white;
    }
</style>
@endsection
