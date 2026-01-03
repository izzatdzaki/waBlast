@extends('layouts.app')

@section('title', 'Kirim Pesan WhatsApp')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-send"></i> Kirim Pesan WhatsApp
                    </h5>
                </div>
                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <h6 class="alert-heading">Terjadi Kesalahan!</h6>
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

                    <form id="sendForm" class="needs-validation">
                        @csrf
                        <!-- Device Selection -->
                        <div class="mb-3">
                            <label for="device_id" class="form-label">
                                <i class="bi bi-phone"></i> Pilih Device WhatsApp
                            </label>
                            <select class="form-select" id="device_id" name="device_id" required>
                                <option value="">-- Memuat device yang terhubung --</option>
                            </select>
                            <div id="device_status" class="alert alert-info mt-2" style="display: none;">
                                <div>
                                    <strong id="device_status_text"></strong>
                                    <small class="d-block text-muted mt-1">
                                        <span id="device_phone"></span>
                                        <span id="device_ready_badge" class="badge bg-success ms-2" style="display: none;">✓ Siap Digunakan</span>
                                        <span id="device_not_ready_badge" class="badge bg-warning ms-2" style="display: none;">⏳ Tunggu Koneksi</span>
                                    </small>
                                </div>
                            </div>
                        </div>

                        <hr>

                        <div class="mb-3">
                            <label for="recipient_type" class="form-label">Tipe Penerima</label>
                            <select class="form-select" id="recipient_type" name="recipient_type" required>
                                <option value="">-- Pilih Tipe Penerima --</option>
                                <option value="manual">Nomor Manual</option>
                                <option value="patient">Pasien (dari Database)</option>
                            </select>
                        </div>

                        <!-- Manual Number -->
                        <div id="manual_section" class="mb-3" style="display: none;">
                            <label for="phone" class="form-label">Nomor Telepon</label>
                            <input type="tel" class="form-control" id="phone" name="phone" 
                                   placeholder="0812xxxxxxxx atau +628xx..." maxlength="20">
                            <small class="text-muted">Format: 0812... atau +628... atau 628...</small>
                        </div>

                        <!-- Patient Selection -->
                        <div id="patient_section" class="mb-3" style="display: none;">
                            <label for="patient_id" class="form-label">Pilih Pasien</label>
                            <select class="form-select" id="patient_id" name="patient_id">
                                <option value="">-- Cari Pasien --</option>
                                @foreach($patients as $patient)
                                    <option value="{{ $patient->no_rkm_medis }}" data-phone="{{ $patient->no_tlp }}">
                                        {{ $patient->nm_pasien }} ({{ $patient->no_tlp }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Message Type -->
                        <div class="mb-3">
                            <label for="message_type" class="form-label">Tipe Pesan</label>
                            <select class="form-select" id="message_type" name="message_type" required>
                                <option value="">-- Pilih Tipe Pesan --</option>
                                <option value="custom">Pesan Kustom</option>
                                <option value="template">Gunakan Template</option>
                            </select>
                        </div>

                        <!-- Custom Message -->
                        <div id="custom_message_section" class="mb-3" style="display: none;">
                            <label for="message" class="form-label">Pesan</label>
                            <textarea class="form-control" id="message" name="message" rows="5" 
                                      placeholder="Tulis pesan Anda di sini..." maxlength="4096"></textarea>
                            <div class="form-text">
                                <small id="char_count">0</small> / 4096 karakter
                            </div>
                        </div>

                        <!-- Template Message -->
                        <div id="template_message_section" class="mb-3" style="display: none;">
                            <label for="template_id" class="form-label">Pilih Template</label>
                            <select class="form-select" id="template_id" name="template_id">
                                <option value="">-- Pilih Template --</option>
                                @foreach($templates as $template)
                                    <option value="{{ $template->id }}" data-content="{{ $template->isi_pesan }}"
                                            data-variables="{{ json_encode($template->placeholder_variables) }}">
                                        {{ $template->nama_template }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Template Variables -->
                        <div id="template_variables_section" style="display: none;">
                            <label class="form-label">Variabel Template</label>
                            <div id="variables_container"></div>
                        </div>

                        <!-- Preview -->
                        <div id="preview_section" class="mb-3" style="display: none;">
                            <div class="alert alert-info">
                                <strong>Preview Pesan:</strong>
                                <p id="preview_text" class="mb-0 mt-2"></p>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="{{ route('whatsapp.dashboard') }}" class="btn btn-secondary">
                                <i class="bi bi-x"></i> Batal
                            </a>
                            <button type="submit" class="btn btn-success" id="submitBtn">
                                <i class="bi bi-send"></i> Kirim Pesan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Load devices on page load
document.addEventListener('DOMContentLoaded', async function() {
    await loadDevices();
});

async function loadDevices() {
    try {
        const response = await fetch('/api/whatsapp/devices');
        const data = await response.json();
        
        const deviceSelect = document.getElementById('device_id');
        deviceSelect.innerHTML = '';

        if (!data.success || !data.devices || data.devices.length === 0) {
            deviceSelect.innerHTML = '<option value="">-- Tidak ada device yang siap digunakan --</option>';
            showAlert('warning', '⚠️ <strong>Tidak ada device yang siap!</strong><br>Silakan buka Settings → Perangkat dan tunggu status device berubah menjadi "Terhubung" sebelum mengirim pesan.');
            return;
        }

        deviceSelect.innerHTML = '<option value="">-- Pilih Device --</option>';
        data.devices.forEach(device => {
            const option = document.createElement('option');
            option.value = device.id;
            option.dataset.phone = device.phone_number || 'N/A';
            option.dataset.ready = device.ready ? 'true' : 'false';
            
            const readyIcon = device.ready ? '✓' : '⏳';
            const readyText = device.ready ? '(Siap)' : '(Menunggu)';
            option.textContent = `${readyIcon} ${device.device_name} ${readyText}`;
            
            deviceSelect.appendChild(option);
        });
    } catch (error) {
        console.error('Error loading devices:', error);
        showAlert('danger', `❌ Error loading devices: ${error.message}`);
        document.getElementById('device_id').innerHTML = '<option value="">-- Error loading devices --</option>';
    }
}

document.getElementById('device_id').addEventListener('change', async function() {
    const deviceStatus = document.getElementById('device_status');
    const statusText = document.getElementById('device_status_text');
    const phoneText = document.getElementById('device_phone');
    const readyBadge = document.getElementById('device_ready_badge');
    const notReadyBadge = document.getElementById('device_not_ready_badge');
    
    if (this.value) {
        const selectedOption = this.options[this.selectedIndex];
        const isReady = selectedOption.dataset.ready === 'true';
        const phone = selectedOption.dataset.phone;
        
        statusText.textContent = `Device: ${selectedOption.text}`;
        phoneText.textContent = phone !== 'N/A' ? `Nomor: ${phone}` : '';
        
        if (isReady) {
            readyBadge.style.display = 'inline-block';
            notReadyBadge.style.display = 'none';
        } else {
            readyBadge.style.display = 'none';
            notReadyBadge.style.display = 'inline-block';
        }
        
        deviceStatus.style.display = 'block';
    } else {
        deviceStatus.style.display = 'none';
    }
});

document.getElementById('recipient_type').addEventListener('change', function() {
    document.getElementById('manual_section').style.display = this.value === 'manual' ? 'block' : 'none';
    document.getElementById('patient_section').style.display = this.value === 'patient' ? 'block' : 'none';
});

document.getElementById('message_type').addEventListener('change', function() {
    document.getElementById('custom_message_section').style.display = this.value === 'custom' ? 'block' : 'none';
    document.getElementById('template_message_section').style.display = this.value === 'template' ? 'block' : 'none';
    document.getElementById('preview_section').style.display = 'none';
});

document.getElementById('message').addEventListener('input', function() {
    document.getElementById('char_count').textContent = this.value.length;
    updatePreview();
});

document.getElementById('template_id').addEventListener('change', function() {
    const option = this.options[this.selectedIndex];
    const variables = JSON.parse(option.getAttribute('data-variables') || '[]');
    const container = document.getElementById('variables_container');
    container.innerHTML = '';

    variables.forEach(variable => {
        const div = document.createElement('div');
        div.className = 'mb-2';
        div.innerHTML = `
            <label class="form-label text-muted small">{${variable}}</label>
            <input type="text" class="form-control" name="template_variables[${variable}]" 
                   placeholder="Masukkan nilai untuk ${variable}">
        `;
        container.appendChild(div);
    });

    document.getElementById('template_variables_section').style.display = variables.length > 0 ? 'block' : 'none';
    updatePreview();
});

function updatePreview() {
    const messageType = document.getElementById('message_type').value;
    const preview = document.getElementById('preview_text');
    let text = '';

    if (messageType === 'custom') {
        text = document.getElementById('message').value;
    } else if (messageType === 'template') {
        const templateOption = document.getElementById('template_id').options[document.getElementById('template_id').selectedIndex];
        text = templateOption.getAttribute('data-content') || '';
        
        // Replace variables with input values
        const inputs = document.querySelectorAll('#variables_container input');
        inputs.forEach(input => {
            const variable = input.name.match(/\[([^\]]+)\]/)[1];
            text = text.replace('{' + variable + '}', input.value || '{' + variable + '}');
        });
    }

    if (text) {
        preview.textContent = text;
        document.getElementById('preview_section').style.display = 'block';
    }
}

// Add input event to template variables for live preview
document.addEventListener('input', function(e) {
    if (e.target.closest('#variables_container input')) {
        updatePreview();
    }
}, true);

// Format phone number
function formatPhoneNumber(phone) {
    // Remove any non-digit characters except +
    phone = phone.replace(/[^\d+]/g, '');
    
    // If starts with +62, convert to 62
    if (phone.startsWith('+62')) {
        phone = phone.replace('+62', '62');
    }
    // If starts with 0, convert to 62
    else if (phone.startsWith('0')) {
        phone = '62' + phone.substring(1);
    }
    // If doesn't start with 62, prepend it
    else if (!phone.startsWith('62')) {
        phone = '62' + phone;
    }
    
    return phone;
}

// Form submission
document.getElementById('sendForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    const submitBtn = document.getElementById('submitBtn');
    const originalText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Mengirim...';

    const deviceId = document.getElementById('device_id').value;
    const recipientType = document.getElementById('recipient_type').value;
    const messageType = document.getElementById('message_type').value;

    // Validate device
    if (!deviceId) {
        showAlert('danger', 'Pilih device WhatsApp terlebih dahulu!');
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
        return;
    }

    // Validate recipient type
    if (!recipientType) {
        showAlert('danger', 'Pilih tipe penerima terlebih dahulu!');
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
        return;
    }

    // Validate message type
    if (!messageType) {
        showAlert('danger', 'Pilih tipe pesan terlebih dahulu!');
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
        return;
    }

    // Get phone number
    let phone = '';
    if (recipientType === 'manual') {
        phone = document.getElementById('phone').value?.trim() || '';
        if (!phone) {
            showAlert('danger', 'Masukkan nomor telepon!');
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
            return;
        }
    } else if (recipientType === 'patient') {
        const patientId = document.getElementById('patient_id').value;
        if (!patientId) {
            showAlert('danger', 'Pilih pasien terlebih dahulu!');
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
            return;
        }
        const patient = document.getElementById('patient_id').options[document.getElementById('patient_id').selectedIndex];
        phone = patient.getAttribute('data-phone') || '';
        if (!phone) {
            showAlert('danger', 'Pasien tidak memiliki nomor telepon!');
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
            return;
        }
    }

    // Format phone number to 62xxx format
    phone = formatPhoneNumber(phone);
    
    // Validate phone format
    if (!/^62\d{9,}$/.test(phone)) {
        showAlert('danger', 'Format nomor telepon tidak valid. Gunakan format 0812xxx atau 628xxx');
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
        return;
    }

    let message = '';
    const variables = {};

    if (messageType === 'custom') {
        message = document.getElementById('message').value?.trim() || '';
        if (!message) {
            showAlert('danger', 'Masukkan pesan!');
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
            return;
        }
    } else if (messageType === 'template') {
        const templateId = document.getElementById('template_id').value;
        if (!templateId) {
            showAlert('danger', 'Pilih template terlebih dahulu!');
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
            return;
        }
        // Get template variables from inputs
        const inputs = document.querySelectorAll('#variables_container input');
        inputs.forEach(input => {
            const match = input.name.match(/\[([^\]]+)\]/);
            if (match) {
                const variable = match[1];
                variables[variable] = input.value;
            }
        });

        // Get template content
        const templateOption = document.getElementById('template_id').options[document.getElementById('template_id').selectedIndex];
        message = templateOption.getAttribute('data-content') || '';
        if (!message) {
            showAlert('danger', 'Template tidak memiliki konten!');
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
            return;
        }
    }

    try {
        const response = await fetch('/api/whatsapp/send', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify({
                device_id: deviceId,
                phone: phone,
                message: message,
                template_id: messageType === 'template' ? document.getElementById('template_id').value : undefined,
                template_variables: Object.keys(variables).length > 0 ? variables : undefined,
            })
        });

        const data = await response.json();

        if (data.success) {
            showAlert('success', '✅ Pesan berhasil dikirim!');
            setTimeout(() => window.location.href = '{{ route("whatsapp.dashboard") }}', 2000);
        } else {
            let errorMsg = data.message || data.error || 'Kesalahan tidak diketahui';
            
            // Better error handling for connection issues
            if (errorMsg.includes('not connected') || errorMsg.includes('WhatsApp session')) {
                errorMsg = '❌ <strong>Device belum siap!</strong><br>' +
                    'Device mungkin belum sepenuhnya terhubung. Silakan:<br>' +
                    '1. Buka Settings → Perangkat<br>' +
                    '2. Tunggu sampai status berubah menjadi "Terhubung" (hijau)<br>' +
                    '3. Tunggu 10-15 detik setelah QR di-scan<br>' +
                    '4. Coba kirim pesan lagi';
            } else if (errorMsg.includes('timeout') || errorMsg.includes('Timeout')) {
                errorMsg = '❌ <strong>Koneksi timeout!</strong><br>' +
                    'Device sedang tidak responsif. Silakan reload halaman dan coba lagi.';
            }
            
            showAlert('danger', errorMsg);
        }
    } catch (error) {
        showAlert('danger', `❌ Terjadi kesalahan: ${error.message}`);
    } finally {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    }
});

function showAlert(type, message) {
    const alert = document.createElement('div');
    alert.className = `alert alert-${type} alert-dismissible fade show`;
    alert.role = 'alert';
    alert.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    document.querySelector('.card-body').insertBefore(alert, document.querySelector('form'));
    setTimeout(() => alert.remove(), 5000);
}
</script>

<style>
    .form-control:focus, .form-select:focus {
        border-color: #28a745;
        box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25);
    }
</style>
@endsection
