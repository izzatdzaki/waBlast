@extends('layouts.app')

@section('title', 'Dashboard Pasien')

@section('content')
<div class="row mb-4">
    <div class="col-md-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="bi bi-people"></i> Dashboard Data Pasien</h2>
            <a href="{{ route('dashboard.pasien.index') }}" class="btn btn-sm btn-outline-primary">
                <i class="bi bi-arrow-clockwise"></i> Refresh
            </a>
        </div>
    </div>
</div>

<!-- Search and Filter -->
<div class="card mb-4">
    <div class="card-header">
        <i class="bi bi-funnel"></i> Pencarian & Filter
    </div>
    <div class="card-body">
        <form action="{{ route('dashboard.pasien.index') }}" method="GET" class="row g-3">
            <div class="col-md-5">
                <div class="search-box">
                    <i class="bi bi-search search-icon"></i>
                    <input type="text" name="search" class="form-control" placeholder="Cari nama, no rekam medis, no KTP, atau no telepon..."
                        value="{{ request('search') }}">
                </div>
            </div>

            <div class="col-md-3">
                <select name="jk" class="form-select">
                    <option value="">-- Pilih Jenis Kelamin --</option>
                    <option value="L" @if(request('jk') == 'L') selected @endif>Laki-laki</option>
                    <option value="P" @if(request('jk') == 'P') selected @endif>Perempuan</option>
                </select>
            </div>

            <div class="col-md-4">
                <select name="stts_nikah" class="form-select">
                    <option value="">-- Pilih Status Pernikahan --</option>
                    <option value="BELUM MENIKAH" @if(request('stts_nikah') == 'BELUM MENIKAH') selected @endif>Belum Menikah</option>
                    <option value="MENIKAH" @if(request('stts_nikah') == 'MENIKAH') selected @endif>Menikah</option>
                    <option value="JANDA" @if(request('stts_nikah') == 'JANDA') selected @endif>Janda</option>
                    <option value="DUDHA" @if(request('stts_nikah') == 'DUDHA') selected @endif>Dudha</option>
                    <option value="JOMBLO" @if(request('stts_nikah') == 'JOMBLO') selected @endif>Jomblo</option>
                </select>
            </div>

            <div class="col-md-12">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-search"></i> Cari
                </button>
                <a href="{{ route('dashboard.pasien.index') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-clockwise"></i> Reset
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Table -->
<div class="card">
    <div class="card-header">
        <i class="bi bi-table"></i> Data Pasien
        <span class="badge bg-secondary float-end">{{ $pasiens->total() }} Pasien</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>No. Rekam Medis</th>
                    <th>Nama Pasien</th>
                    <th>No. KTP</th>
                    <th>Jenis Kelamin</th>
                    <th>No. Telepon</th>
                    <th>Alamat</th>
                    <th>Asuransi</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pasiens as $index => $pasien)
                    <tr>
                        <td>{{ ($pasiens->currentPage() - 1) * $pasiens->perPage() + $loop->iteration }}</td>
                        <td>
                            <span class="badge bg-info">{{ $pasien->no_rkm_medis }}</span>
                        </td>
                        <td>
                            <strong>{{ $pasien->nm_pasien ?? '-' }}</strong>
                        </td>
                        <td>{{ $pasien->no_ktp ?? '-' }}</td>
                        <td>
                            @if($pasien->jk == 'L')
                                <span class="badge bg-primary">Laki-laki</span>
                            @else
                                <span class="badge bg-danger">Perempuan</span>
                            @endif
                        </td>
                        <td>
                            @if($pasien->no_tlp)
                                @php
                                    $phone = preg_replace('/[^0-9]/', '', $pasien->no_tlp);
                                    $phone = preg_replace('/^0/', '62', $phone);
                                @endphp
                                <a href="https://wa.me/{{ $phone }}" target="_blank" class="text-decoration-none">
                                    <i class="bi bi-whatsapp"></i> {{ $pasien->no_tlp }}
                                </a>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            <small>{{ substr($pasien->alamat ?? '-', 0, 30) }}{{ strlen($pasien->alamat ?? '') > 30 ? '...' : '' }}</small>
                        </td>
                        <td>
                            <span class="badge bg-success">{{ $pasien->png_jawab ?? $pasien->kd_pj ?? '-' }}</span>
                        </td>
                        <td>
                            <a href="{{ route('dashboard.pasien.show', $pasien->no_rkm_medis) }}" class="btn btn-sm btn-outline-primary" title="Detail Pasien">
                                <i class="bi bi-eye"></i> Detail
                            </a>
                            @if($pasien->no_tlp)
                                <button type="button" class="btn btn-sm btn-outline-success" onclick="openSendWAModal('{{ $pasien->no_rkm_medis }}', '{{ $pasien->nm_pasien }}', '{{ $pasien->no_tlp }}')" title="Kirim WhatsApp">
                                    <i class="bi bi-whatsapp"></i> WA
                                </button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted py-4">
                            <i class="bi bi-inbox"></i> Tidak ada data pasien
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if($pasiens->hasPages())
        <div class="card-footer">
            <nav aria-label="Pagination">
                {{ $pasiens->links('vendor.pagination.bootstrap-5') }}
            </nav>
        </div>
    @endif
</div>

<!-- Modal Kirim WhatsApp -->
<div class="modal fade" id="sendWAModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-whatsapp"></i> Kirim Pesan WhatsApp</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Nama Pasien</label>
                    <input type="text" id="wa_pasien_name" class="form-control" readonly>
                </div>
                <div class="mb-3">
                    <label class="form-label">No. Telepon</label>
                    <input type="text" id="wa_phone" class="form-control" readonly>
                </div>
                <div class="mb-3">
                    <label class="form-label">Perangkat WhatsApp</label>
                    <select id="wa_device" class="form-select" required>
                        <option value="">-- Pilih Perangkat --</option>
                    </select>
                    <small class="text-muted">Pastikan perangkat sudah terhubung di Settings</small>
                </div>
                <div class="mb-3">
                    <label class="form-label">Pesan</label>
                    <textarea id="wa_message" class="form-control" rows="4" placeholder="Masukkan pesan..." required></textarea>
                    <small class="text-muted d-block mt-1">Sisa karakter: <span id="char_count">0</span>/4096</small>
                </div>
                <div id="wa_error_msg" class="alert alert-danger d-none"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-success" onclick="sendWhatsAppMessage()">
                    <i class="bi bi-send"></i> Kirim
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Load WhatsApp devices saat halaman load
document.addEventListener('DOMContentLoaded', function() {
    loadWhatsAppDevices();
    
    // Character counter
    document.getElementById('wa_message').addEventListener('input', function() {
        document.getElementById('char_count').textContent = this.value.length;
    });
});

function loadWhatsAppDevices() {
    fetch('{{ route("whatsapp.devices") }}')
        .then(response => response.json())
        .then(data => {
            const deviceSelect = document.getElementById('wa_device');
            
            if (data.success && data.devices && data.devices.length > 0) {
                const activeDevices = data.devices.filter(d => d.status === 'active');
                
                activeDevices.forEach(device => {
                    const option = document.createElement('option');
                    option.value = device.id || device.device_id;
                    option.textContent = `${device.device_name || device.id} (${device.phone_number || 'Unknown'})`;
                    deviceSelect.appendChild(option);
                });
                
                if (activeDevices.length === 0) {
                    deviceSelect.innerHTML = '<option value="">-- Tidak ada perangkat aktif --</option>';
                    deviceSelect.disabled = true;
                }
            } else {
                deviceSelect.innerHTML = '<option value="">-- Tidak ada perangkat --</option>';
                deviceSelect.disabled = true;
            }
        })
        .catch(error => {
            console.error('Error loading devices:', error);
            document.getElementById('wa_device').innerHTML = '<option value="">Error loading devices</option>';
        });
}

function openSendWAModal(noRKM, namaPasien, noTelp) {
    document.getElementById('wa_pasien_name').value = namaPasien;
    document.getElementById('wa_phone').value = noTelp;
    document.getElementById('wa_message').value = '';
    document.getElementById('char_count').textContent = '0';
    document.getElementById('wa_error_msg').classList.add('d-none');
    
    const modal = new bootstrap.Modal(document.getElementById('sendWAModal'));
    modal.show();
}

function sendWhatsAppMessage() {
    try {
        // Validate elements exist
        const deviceEl = document.getElementById('wa_device');
        const messageEl = document.getElementById('wa_message');
        const phoneEl = document.getElementById('wa_phone');
        const errorDiv = document.getElementById('wa_error_msg');
        
        if (!deviceEl || !messageEl || !phoneEl || !errorDiv) {
            console.error('Form elements not found');
            alert('❌ Form tidak valid. Silakan refresh halaman dan coba lagi.');
            return;
        }
        
        const deviceId = deviceEl.value;
        const message = messageEl.value;
        const phone = phoneEl.value;
        
        // Validate inputs
        if (!deviceId) {
            errorDiv.textContent = 'Silakan pilih perangkat WhatsApp';
            errorDiv.classList.remove('d-none');
            return;
        }
        
        if (!message || !message.trim()) {
            errorDiv.textContent = 'Pesan tidak boleh kosong';
            errorDiv.classList.remove('d-none');
            return;
        }
        
        if (message.length > 4096) {
            errorDiv.textContent = 'Pesan terlalu panjang (max 4096 karakter)';
            errorDiv.classList.remove('d-none');
            return;
        }
        
        if (!phone) {
            errorDiv.textContent = 'Nomor telepon tidak valid';
            errorDiv.classList.remove('d-none');
            return;
        }
        
        // Format phone number
        let formattedPhone = phone.replace(/[^0-9]/g, '');
        if (formattedPhone.startsWith('0')) {
            formattedPhone = '62' + formattedPhone.substring(1);
        }
        
        // Get button and show loading
        const btn = event.target;
        const originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Mengirim...';
        
        // Get CSRF token
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        
        // Send message
        fetch('{{ route("whatsapp.send.post") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({
                device_id: deviceId,
                phone: formattedPhone,
                message: message
            })
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                alert('✅ Pesan berhasil dikirim!');
                // Hide modal
                const modalEl = document.getElementById('sendWAModal');
                if (modalEl) {
                    const modal = bootstrap.Modal.getInstance(modalEl);
                    if (modal) {
                        modal.hide();
                    }
                }
            } else {
                errorDiv.textContent = data.error || 'Gagal mengirim pesan';
                errorDiv.classList.remove('d-none');
            }
        })
        .catch(error => {
            console.error('Send error:', error);
            errorDiv.textContent = 'Terjadi kesalahan: ' + error.message;
            errorDiv.classList.remove('d-none');
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = originalText;
        });
    } catch (error) {
        console.error('Function error:', error);
        alert('❌ Terjadi kesalahan: ' + error.message);
    }
}
</script>

@endsection
