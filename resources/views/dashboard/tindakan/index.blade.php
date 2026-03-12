@extends('layouts.app')

@section('title', 'Dashboard Tindakan')

@section('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endsection

@section('content')

<!-- Search and Filter -->
<div class="card mb-4">
    <div class="card-header">
        <i class="bi bi-funnel"></i> Pencarian & Filter
    </div>
    <div class="card-body">
        <form action="{{ route('dashboard.tindakan.index') }}" method="GET" class="row g-3">
            <div class="col-md-3">
                <label class="form-label" style="font-size: 0.8rem;">Dari Tanggal</label>
                <input type="date" name="start_date" class="form-control" 
                    value="{{ $start_date }}">
            </div>

            <div class="col-md-3">
                <label class="form-label" style="font-size: 0.8rem;">Sampai Tanggal</label>
                <input type="date" name="end_date" class="form-control" 
                    value="{{ $end_date }}">
            </div>



            <div class="col-md-2">
                <select name="kd_jenis_prw" class="form-select select2" id="kd_jenis_prw">
                    <option value="">-- Jenis Tindakan --</option>
                    @foreach($jnsPerawatans as $jenis)
                        <option value="{{ $jenis->kd_jenis_prw }}" @if(request('kd_jenis_prw') == $jenis->kd_jenis_prw) selected @endif>
                            {{ $jenis->nm_perawatan ?? $jenis->kd_jenis_prw }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-2">
                <select name="kd_dokter" class="form-select select2" id="kd_dokter">
                    <option value="">-- Dokter --</option>
                    @foreach($dokters as $dokter)
                        <option value="{{ $dokter->kd_dokter }}" @if(request('kd_dokter') == $dokter->kd_dokter) selected @endif>
                            {{ $dokter->nm_dokter ?? $dokter->kd_dokter }}
                        </option>
                    @endforeach
                </select>
            </div>



            <div class="col-md-12">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-search"></i> Cari
                </button>
                <a href="{{ route('dashboard.tindakan.index') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-clockwise"></i> Reset
                </a>
                <a href="{{ route('dashboard.tindakan.export', request()->query()) }}" class="btn btn-success">
                    <i class="bi bi-file-earmark-excel"></i> Export Excel
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Table -->
<div class="card">
    <div class="card-header">
        <i class="bi bi-table"></i> Data Tindakan 
        <small class="text-muted" style="color: rgba(255,255,255,0.8); font-size: 0.85rem;">
            ({{ \Carbon\Carbon::parse($start_date)->format('d-m-Y') }} s/d {{ \Carbon\Carbon::parse($end_date)->format('d-m-Y') }})
        </small>
        <span class="badge bg-light text-dark float-end">{{ $tindakans->total() }} Tindakan</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Tanggal</th>
                    <th>Waktu</th>
                    <th>No. Rawat</th>
                    <th>Nama Pasien</th>
                    <th>No. RM</th>
                    <th>Jenis Tindakan</th>
                    <th>Dokter</th>
                    <th>Biaya</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tindakans as $index => $tindakan)
                    <tr>
                        <td>{{ ($tindakans->currentPage() - 1) * $tindakans->perPage() + $loop->iteration }}</td>
                        <td>
                            <span class="badge bg-secondary">{{ \Carbon\Carbon::parse($tindakan->tgl_perawatan)->format('d-m-Y') }}</span>
                        </td>
                        <td>
                            <span class="badge bg-info">{{ \Carbon\Carbon::parse($tindakan->jam_rawat)->format('H:i') }}</span>
                        </td>
                        <td>
                            <span class="badge bg-primary">{{ $tindakan->no_rawat }}</span>
                        </td>
                        <td>
                            <strong>{{ $tindakan->regPeriksa->pasien->nm_pasien ?? '-' }}</strong>
                        </td>
                        <td>
                            <span class="text-muted">{{ $tindakan->regPeriksa->pasien->no_rkm_medis ?? '-' }}</span>
                        </td>
                        <td>
                            <span class="badge bg-success">
                                {{ $tindakan->jnsPerawatan->nm_perawatan ?? $tindakan->kd_jenis_prw }}
                            </span>
                        </td>
                        <td>
                            {{ $tindakan->dokter->nm_dokter ?? '-' }}
                        </td>
                        <td>
                            <strong>Rp {{ number_format($tindakan->biaya_rawat ?? 0, 0, ',', '.') }}</strong>
                        </td>
                        <td>
                            @if($tindakan->regPeriksa->pasien->no_tlp)
                                <button type="button" class="btn btn-sm btn-outline-success" onclick="openSendWAModal('{{ $tindakan->regPeriksa->pasien->no_rkm_medis }}', '{{ $tindakan->regPeriksa->pasien->nm_pasien }}', '{{ $tindakan->regPeriksa->pasien->no_tlp }}')" title="Kirim WhatsApp">
                                    <i class="bi bi-whatsapp"></i> WA
                                </button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="11" class="text-center text-muted py-4">
                            <i class="bi bi-inbox"></i> Tidak ada data tindakan untuk periode ini
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if($tindakans->hasPages())
        <div class="card-footer">
            <nav aria-label="Pagination">
                {{ $tindakans->links('vendor.pagination.bootstrap-5') }}
            </nav>
        </div>
    @endif
</div>

<style>
    .stat-card-info {
        background: linear-gradient(135deg, #0dcaf0 0%, #14b8a6 100%);
        color: white;
        padding: 25px;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        text-align: center;
    }

    .stat-card-info h3 {
        font-size: 2.5rem;
        font-weight: bold;
        margin: 10px 0;
    }

    .stat-card-info p {
        margin: 0;
        font-size: 0.95rem;
        opacity: 0.95;
    }


    .search-icon {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: #999;
    }

    .search-box input {
        padding-left: 35px;
    }

</style>

<!-- Modal Kirim WhatsApp -->
<div class="modal fade" id="sendWAModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
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

                <!-- Template Selector -->
                <div class="mb-3">
                    <label class="form-label">Template Pesan</label>
                    <select id="wa_template" class="form-select" onchange="loadTemplateMessage()">
                        <option value="">-- Pilih Template atau Buat Custom --</option>
                        <option value="template_1">1. Follow Up Kelahiran</option>
                        <option value="template_2">2. Reminder USG Kontrol</option>
                        <option value="template_3">3. Reminder HPL</option>
                    </select>
                </div>

                <!-- Dynamic Placeholder Fields -->
                <div id="wa_placeholder_fields" class="mb-3" style="display:none;">
                    <h6 class="mb-2">Isi Data Pesan:</h6>
                    <div id="wa_fields_container"></div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Pesan</label>
                    <textarea id="wa_message" class="form-control" rows="5" placeholder="Masukkan pesan..." required></textarea>
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
    document.getElementById('wa_template').value = '';
    document.getElementById('char_count').textContent = '0';
    document.getElementById('wa_error_msg').classList.add('d-none');
    document.getElementById('wa_placeholder_fields').style.display = 'none';
    
    const modal = new bootstrap.Modal(document.getElementById('sendWAModal'));
    modal.show();
}

// Template messages
const templates = {
    template_1: {
        nama: 'Follow Up Kelahiran',
        pesan: 'Assalamualaikum bunda ijin Follow Up mengingatkan ya untuk kelahiran di bulan ini Bunda tetap memilih Rumah Sakit Ibu dan Anak Masyita dalam persalinanya sesuai dengan Dokter Kandungan pada Surat Pengantar di Buku KIA, Terimakasih 🙏',
        variables: {}
    },
    template_2: {
        nama: 'Reminder USG Kontrol',
        pesan: 'Halo Ibu {nama_pasien} 😊\n\nKami dari RSIA Masyita ingin mengingatkan bahwa Ibu memiliki jadwal USG kontrol kehamilan.\n\n📅 Tanggal : {tanggal_usg}\n⏰ Waktu : {jam_poli}\n📍 Tempat : RSIA Masyita\n\nUSG rutin penting untuk memantau perkembangan dan kesehatan bayi.\n\nJika ada perubahan jadwal atau ingin konfirmasi, silakan menghubungi kami melalui WhatsApp ini.\n\nTerima kasih\nRSIA Masyita',
        variables: {
            nama_pasien: 'Nama lengkap pasien',
            tanggal_usg: 'Tanggal jadwal USG (dd-mm-yyyy)',
            jam_poli: 'Jam dan nama poli'
        }
    },
    template_3: {
        nama: 'Reminder HPL',
        pesan: 'Halo Ibu {nama_pasien}\n\nKami dari RSIA Masyita ingin mengingatkan bahwa Hari Perkiraan Lahir (HPL) Ibu semakin dekat.\n\n📅 Perkiraan HPL : {tanggal_hpl}\n\nKami menyarankan Ibu untuk melakukan pemeriksaan kontrol / USG untuk memastikan kondisi ibu dan bayi dalam keadaan baik.\n\nSilakan melakukan pemeriksaan di RSIA Masyita sesuai anjuran dokter.\n\nTerima kasih atas kepercayaannya 🙏',
        variables: {
            nama_pasien: 'Nama lengkap pasien',
            tanggal_hpl: 'Tanggal perkiraan lahir (dd-mm-yyyy)'
        }
    }
};

function loadTemplateMessage() {
    const templateSelect = document.getElementById('wa_template');
    const messageEl = document.getElementById('wa_message');
    const placeholderDiv = document.getElementById('wa_placeholder_fields');
    const fieldsContainer = document.getElementById('wa_fields_container');
    const pasienName = document.getElementById('wa_pasien_name').value;
    
    const selectedTemplate = templateSelect.value;
    
    if (!selectedTemplate) {
        messageEl.value = '';
        placeholderDiv.style.display = 'none';
        fieldsContainer.innerHTML = '';
        return;
    }
    
    const template = templates[selectedTemplate];
    if (!template) return;
    
    // Auto-fill nama_pasien if available
    let messageText = template.pesan;
    const variables = { ...template.variables };
    
    // Pre-fill nama_pasien
    if (variables.nama_pasien && pasienName) {
        messageText = messageText.replace('{nama_pasien}', pasienName);
        delete variables.nama_pasien;
    }
    
    messageEl.value = messageText;
    document.getElementById('char_count').textContent = messageText.length;
    
    // Show/hide placeholder fields based on remaining variables
    if (Object.keys(variables).length > 0) {
        placeholderDiv.style.display = 'block';
        fieldsContainer.innerHTML = '';
        
        // Create input fields for each variable
        Object.entries(variables).forEach(([key, label]) => {
            const div = document.createElement('div');
            div.className = 'mb-2';
            div.innerHTML = `
                <label class="form-label">${label}</label>
                <input type="text" class="form-control form-control-sm placeholder-input" 
                       data-placeholder="{${key}}" placeholder="Masukkan ${label.toLowerCase()}">
            `;
            fieldsContainer.appendChild(div);
        });
        
        // Add listener untuk input fields
        document.querySelectorAll('.placeholder-input').forEach(input => {
            input.addEventListener('input', function() {
                updateMessageWithPlaceholders();
            });
        });
    } else {
        placeholderDiv.style.display = 'none';
        fieldsContainer.innerHTML = '';
    }
}

function updateMessageWithPlaceholders() {
    const templateSelect = document.getElementById('wa_template');
    const selectedTemplate = templateSelect.value;
    const template = templates[selectedTemplate];
    
    if (!template) return;
    
    let messageText = template.pesan;
    const pasienName = document.getElementById('wa_pasien_name').value;
    
    // Replace nama_pasien
    if (pasienName) {
        messageText = messageText.replace('{nama_pasien}', pasienName);
    }
    
    // Replace other placeholders
    document.querySelectorAll('.placeholder-input').forEach(input => {
        const placeholder = input.getAttribute('data-placeholder');
        const value = input.value;
        if (value) {
            messageText = messageText.replaceAll(placeholder, value);
        }
    });
    
    document.getElementById('wa_message').value = messageText;
    document.getElementById('char_count').textContent = messageText.length;
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

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
$(document).ready(function() {
    // Initialize Select2 for searchable dropdowns
    $('.select2').select2({
        placeholder: 'Cari...',
        allowClear: false,
        width: '100%',
        minimumInputLength: 0
    });
});
</script>

@endsection
