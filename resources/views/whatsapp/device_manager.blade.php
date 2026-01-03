@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <h1 class="h3 mb-0" style="color: #00897b;">
                        <i class="bi bi-phone"></i> Device Manager
                    </h1>
                    <p class="text-muted small mt-2">Kelola perangkat WhatsApp yang terhubung</p>
                </div>
                <button type="button" class="btn btn-success" onclick="showAddDeviceModal()">
                    <i class="bi bi-plus-circle"></i> Tambah Device
                </button>
            </div>
        </div>
    </div>

    @if($errorMessage)
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle"></i> {{ $errorMessage }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <!-- Server Status Card -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="mb-1 text-muted">Status Server Baileys</h6>
                        <p class="mb-0">
                            @if($serverStatus === 'Online')
                            <span class="badge bg-success">
                                <i class="bi bi-circle-fill"></i> Online
                            </span>
                            @else
                            <span class="badge bg-danger">
                                <i class="bi bi-circle-fill"></i> Offline
                            </span>
                            @endif
                        </p>
                    </div>
                    <button class="btn btn-sm btn-outline-secondary" id="refreshBtn">
                        <i class="bi bi-arrow-repeat"></i> Refresh
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Active Sessions -->
    <div class="row">
        <div class="col-md-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">
                        <i class="bi bi-list-check"></i> Perangkat Aktif
                        <span class="badge bg-secondary">{{ count($sessions) }}</span>
                    </h5>
                </div>
                <div class="card-body">
                    @if(count($sessions) > 0)
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Nama Device</th>
                                    <th>Session ID</th>
                                    <th>Nomor WhatsApp</th>
                                    <th>Status</th>
                                    <th>Terbuat</th>
                                    <th class="text-end">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($sessions as $session)
                                <tr>
                                    <td>
                                        <strong>{{ $session['deviceName'] ?? 'N/A' }}</strong>
                                    </td>
                                    <td>
                                        <code class="text-muted" style="font-size: 0.85rem;">
                                            {{ $session['sessionId'] }}
                                        </code>
                                    </td>
                                    <td>
                                        @if($session['phoneNumber'])
                                        <code>{{ $session['phoneNumber'] }}</code>
                                        @else
                                        <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($session['authenticated'] ?? false)
                                        <span class="badge bg-success">
                                            <i class="bi bi-check-circle"></i> Authenticated
                                        </span>
                                        @else
                                        <span class="badge bg-warning text-dark">
                                            <i class="bi bi-hourglass-split"></i> Connecting
                                        </span>
                                        @endif
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            {{ \Carbon\Carbon::parse($session['createdAt'])->format('d/m/Y H:i') }}
                                        </small>
                                    </td>
                                    <td class="text-end">
                                        <button type="button" class="btn btn-sm btn-outline-danger deleteSessionBtn" data-session-id="{{ $session['sessionId'] }}">
                                            <i class="bi bi-trash"></i> Hapus
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="text-center py-5">
                        <i class="bi bi-inbox" style="font-size: 3rem; color: #ccc;"></i>
                        <p class="text-muted mt-3">Belum ada device yang terhubung</p>
                        <p class="text-muted small">Klik tombol "Tambah Device" untuk membuat session baru</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create Session Modal -->
<div class="modal fade" id="createSessionModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0">
            <div class="modal-header bg-light border-0">
                <h5 class="modal-title">Tambah Device WhatsApp</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="createSessionForm">
                    <div class="mb-3">
                        <label class="form-label">Nama Device</label>
                        <input type="text" class="form-control" name="device_name" 
                               placeholder="Mis: iPhone 12, Device 1" required>
                        <small class="text-muted">Nama unik untuk mengidentifikasi perangkat</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nomor WhatsApp (Opsional)</label>
                        <input type="tel" class="form-control" name="phone_number" 
                               placeholder="Mis: 62812345678">
                        <small class="text-muted">Nomor WhatsApp yang akan digunakan (format: 62...)</small>
                    </div>
                    <div class="alert alert-info" role="alert">
                        <i class="bi bi-info-circle"></i>
                        <strong>Perhatian:</strong> Setelah membuat session, Anda akan melihat QR code. 
                        Scan QR code dengan membuka WhatsApp Settings → Linked Devices di perangkat lain.
                    </div>
                </form>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-success" id="submitCreateSessionBtn">
                    <i class="bi bi-check-circle"></i> Buat Session
                </button>
            </div>
        </div>
    </div>
</div>

<!-- QR Code Overlay -->
<div id="qrOverlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 10000; justify-content: center; align-items: center;">
    <div style="background: white; border-radius: 12px; padding: 30px; max-width: 500px; width: 90%; max-height: 90vh; overflow-y: auto; box-shadow: 0 20px 60px rgba(0,0,0,0.3);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h5 style="margin: 0; font-weight: 600; font-size: 18px;">Scan QR Code WhatsApp</h5>
            <button type="button" style="background: none; border: none; font-size: 28px; cursor: pointer; color: #666; padding: 0; width: 32px; height: 32px;" onclick="document.getElementById('qrOverlay').style.display='none'">×</button>
        </div>

        <p style="color: #666; font-weight: 500; margin-bottom: 12px;"><strong>Instruksi:</strong></p>
        <ol style="color: #666; font-size: 14px; margin-bottom: 20px; padding-left: 20px;">
            <li>Buka WhatsApp di smartphone Anda</li>
            <li>Tap <strong>Menu</strong> atau <strong>Settings</strong></li>
            <li>Pilih <strong>Linked Devices</strong></li>
            <li>Tap <strong>Link a device</strong></li>
            <li>Arahkan kamera ke QR code di bawah</li>
        </ol>

        <div style="text-align: center; background: #f5f5f5; border: 2px solid #00897b; border-radius: 8px; padding: 20px; margin-bottom: 20px; min-height: 280px; display: flex; align-items: center; justify-content: center;">
            <img id="qrImage" src="" alt="QR Code" style="max-width: 100%; height: auto; border-radius: 4px; display: none;">
            <div id="qrLoading" style="text-align: center;">
                <div style="width: 40px; height: 40px; margin: 0 auto 10px; border: 4px solid #f3f3f3; border-top: 4px solid #00897b; border-radius: 50%; animation: spin 1s linear infinite;"></div>
                <small style="color: #999;">Loading QR Code...</small>
            </div>
        </div>

        <p style="text-align: center; color: #666; font-size: 14px; margin-bottom: 20px;">
            <strong>Session ID:</strong><br>
            <code id="sessionId" style="background: #f5f5f5; padding: 8px 12px; border-radius: 4px; font-size: 12px; display: inline-block; margin-top: 5px;">-</code>
        </p>

        <div style="background: #fff3cd; border: 1px solid #ffc107; border-radius: 6px; padding: 12px; margin-bottom: 20px; font-size: 13px; color: #856404;">
            <strong>⚠ Perhatian:</strong> QR code akan hilang dalam 5 menit. Pastikan untuk scan sebelum waktu habis.
        </div>

        <button type="button" style="width: 100%; padding: 10px; background: #6c757d; color: white; border: none; border-radius: 6px; font-size: 14px; font-weight: 500; cursor: pointer;" onclick="document.getElementById('qrOverlay').style.display='none'">Tutup</button>
    </div>
    <style>
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</div>

@endsection

@section('scripts')
<script>
// Global function untuk membuka modal - HARUS DI SCOPE GLOBAL
function showAddDeviceModal() {
    console.log('✓ showAddDeviceModal called');
    alert('Tombol berhasil di-klik! Function berhasil di-call.');
    
    // Test dengan plain JavaScript dulu
    try {
        const modalEl = document.getElementById('createSessionModal');
        console.log('Modal element found:', !!modalEl);
        
        if (!modalEl) {
            alert('Modal element tidak ditemukan');
            return;
        }
        
        // Check Bootstrap
        console.log('Bootstrap available:', typeof bootstrap !== 'undefined');
        
        if (typeof bootstrap === 'undefined') {
            alert('Bootstrap tidak loaded. Showing modal with plain CSS...');
            // Show dengan plain CSS jika Bootstrap tidak ada
            modalEl.style.display = 'block';
            modalEl.classList.add('show');
            return;
        }
        
        // Gunakan Bootstrap jika tersedia
        console.log('Creating Bootstrap modal...');
        const modal = new bootstrap.Modal(modalEl);
        modal.show();
        console.log('✓ Modal displayed with Bootstrap');
    } catch (e) {
        console.error('Error:', e.message);
        alert('Error: ' + e.message);
    }
}

document.addEventListener('DOMContentLoaded', function() {
    console.log('✓ Device Manager Script Loaded');
    
    const submitBtn = document.getElementById('submitCreateSessionBtn');
    if (submitBtn) {
        submitBtn.addEventListener('click', function() {
            console.log('✓ Create Session button clicked');
            handleCreateSession();
        });
    }
    
    const refreshBtn = document.getElementById('refreshBtn');
    if (refreshBtn) {
        refreshBtn.addEventListener('click', function() {
            location.reload();
        });
    }
    
    document.querySelectorAll('.deleteSessionBtn').forEach(btn => {
        btn.addEventListener('click', function() {
            const sessionId = this.dataset.sessionId;
            if (confirm('Yakin hapus device ini?')) {
                handleDeleteSession(sessionId);
            }
        });
    });
});

function handleCreateSession() {
    console.log('=== handleCreateSession called ===');
    
    const form = document.getElementById('createSessionForm');
    const deviceName = form.elements['device_name'].value.trim();
    const phoneNumber = form.elements['phone_number'].value.trim();
    
    if (!deviceName) {
        alert('Nama device tidak boleh kosong');
        return;
    }
    
    console.log('Device:', deviceName, 'Phone:', phoneNumber);
    
    const submitBtn = document.getElementById('submitCreateSessionBtn');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Membuat...';
    
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
    
    fetch('{{ route("whatsapp.create_session") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            device_name: deviceName,
            phone_number: phoneNumber || null
        })
    })
    .then(response => {
        console.log('Response status:', response.status);
        if (!response.ok) throw new Error('HTTP ' + response.status);
        return response.json();
    })
    .then(data => {
        console.log('✓ Response received:', data);
        
        if (data.success) {
            console.log('✓ Session created successfully!');
            
            const modal = bootstrap.Modal.getInstance(document.getElementById('createSessionModal'));
            if (modal) modal.hide();
            
            showQRCode(data.qrCode, data.sessionId);
            form.reset();
            
            setTimeout(() => {
                console.log('Auto refreshing page...');
                location.reload();
            }, 30000);
        } else {
            alert('Error: ' + (data.message || 'Gagal membuat session'));
        }
    })
    .catch(error => {
        console.error('✗ Error:', error);
        alert('Error: ' + error.message);
    })
    .finally(() => {
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="bi bi-check-circle"></i> Buat Session';
    });
}

function showQRCode(qrCodeData, sessionId) {
    console.log('=== showQRCode called ===');
    console.log('QR Code length:', qrCodeData ? qrCodeData.length : 0);
    
    const overlay = document.getElementById('qrOverlay');
    const qrImage = document.getElementById('qrImage');
    const qrLoading = document.getElementById('qrLoading');
    const sessionIdSpan = document.getElementById('sessionId');
    
    if (sessionIdSpan) sessionIdSpan.textContent = sessionId;
    
    qrLoading.style.display = 'block';
    qrImage.style.display = 'none';
    
    qrImage.src = qrCodeData;
    
    qrImage.onload = function() {
        console.log('✓ QR Image loaded');
        qrLoading.style.display = 'none';
        qrImage.style.display = 'block';
    };
    
    qrImage.onerror = function() {
        console.error('✗ QR Image failed to load');
        qrLoading.innerHTML = '<div style="color: #d32f2f;">Failed to load QR code</div>';
    };
    
    overlay.style.display = 'flex';
    console.log('✓ QR Overlay displayed');
}

function handleDeleteSession(sessionId) {
    console.log('Deleting session:', sessionId);
    
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
    
    fetch(`{{ route("whatsapp.delete_session", ":id") }}`.replace(':id', sessionId), {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Error: ' + (data.message || 'Gagal menghapus session'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error: ' + error.message);
    });
}
</script>
@endsection
