@extends('layouts.app')

@section('title', 'Jadwalkan Pesan WhatsApp')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-calendar-event"></i> Jadwalkan Pesan WhatsApp
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

                    <form action="{{ route('whatsapp.send-scheduled') }}" method="POST" id="scheduleForm" class="needs-validation">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="recipient_type" class="form-label">Tipe Penerima</label>
                                    <select class="form-select" id="recipient_type" name="recipient_type" required>
                                        <option value="">-- Pilih Tipe Penerima --</option>
                                        <option value="manual">Nomor Manual</option>
                                        <option value="patient">Pasien (dari Database)</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="recipient_count" class="form-label">Jumlah Penerima</label>
                                    <input type="number" class="form-control" id="recipient_count" 
                                           placeholder="Otomatis terbaca" disabled>
                                </div>
                            </div>
                        </div>

                        <!-- Manual Numbers -->
                        <div id="manual_section" class="mb-3" style="display: none;">
                            <label for="phone_list" class="form-label">Nomor Telepon (Satu per baris)</label>
                            <textarea class="form-control" id="phone_list" name="phone_list" rows="4"
                                      placeholder="0812xxxxxxxx&#10;0813xxxxxxxx&#10;+6281x..."></textarea>
                            <small class="text-muted">Masukkan nomor telepon, satu nomor per baris</small>
                        </div>

                        <!-- Patient Selection -->
                        <div id="patient_section" class="mb-3" style="display: none;">
                            <label for="patient_ids" class="form-label">Pilih Pasien (Bisa multiple)</label>
                            <select class="form-select" id="patient_ids" name="patient_ids[]" multiple size="8">
                                @foreach($patients as $patient)
                                    <option value="{{ $patient->no_rkm_medis }}">
                                        {{ $patient->nm_pasien }} ({{ $patient->no_tlp }})
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">Tekan Ctrl/Cmd untuk memilih multiple pasien</small>
                        </div>

                        <!-- Schedule Time -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="scheduled_date" class="form-label">Tanggal</label>
                                    <input type="date" class="form-control" id="scheduled_date" 
                                           name="scheduled_date" required>
                                    <small class="text-muted" id="date_warning"></small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="scheduled_time" class="form-label">Jam</label>
                                    <input type="time" class="form-control" id="scheduled_time" 
                                           name="scheduled_time" required>
                                </div>
                            </div>
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
                                    <option value="{{ $template->id }}" data-content="{{ $template->isi_pesan }}">
                                        {{ $template->nama_template }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Preview -->
                        <div id="preview_section" class="mb-3" style="display: none;">
                            <div class="alert alert-info">
                                <strong>Preview Pesan:</strong>
                                <p id="preview_text" class="mb-0 mt-2"></p>
                            </div>
                        </div>

                        <!-- Schedule Summary -->
                        <div id="summary_section" class="mb-3" style="display: none;">
                            <div class="alert alert-warning">
                                <strong>Ringkasan Jadwal:</strong>
                                <ul class="mb-0 mt-2">
                                    <li>Waktu: <span id="summary_datetime"></span></li>
                                    <li>Penerima: <span id="summary_recipients"></span></li>
                                    <li>Total Pesan: <span id="summary_total"></span></li>
                                </ul>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="{{ route('whatsapp.dashboard') }}" class="btn btn-secondary">
                                <i class="bi bi-x"></i> Batal
                            </a>
                            <button type="submit" class="btn btn-info text-white" id="submitBtn">
                                <i class="bi bi-calendar-check"></i> Jadwalkan Pesan
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Info Card -->
            <div class="card mt-3">
                <div class="card-body">
                    <h6 class="card-title">
                        <i class="bi bi-info-circle"></i> Informasi Penjadwalan
                    </h6>
                    <ul class="small mb-0">
                        <li>Pesan akan dikirim otomatis pada waktu yang ditentukan</li>
                        <li>Jam server digunakan untuk penjadwalan</li>
                        <li>Anda dapat menjadwalkan hingga 1000 pesan sekaligus</li>
                        <li>Status dapat dipantau di halaman Riwayat</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Set minimum date to today
const today = new Date().toISOString().split('T')[0];
document.getElementById('scheduled_date').setAttribute('min', today);
document.getElementById('scheduled_date').value = today;

document.getElementById('recipient_type').addEventListener('change', function() {
    document.getElementById('manual_section').style.display = this.value === 'manual' ? 'block' : 'none';
    document.getElementById('patient_section').style.display = this.value === 'patient' ? 'block' : 'none';
    updateRecipientCount();
});

document.getElementById('message_type').addEventListener('change', function() {
    document.getElementById('custom_message_section').style.display = this.value === 'custom' ? 'block' : 'none';
    document.getElementById('template_message_section').style.display = this.value === 'template' ? 'block' : 'none';
    updatePreview();
});

document.getElementById('phone_list').addEventListener('input', updateRecipientCount);
document.getElementById('patient_ids').addEventListener('change', updateRecipientCount);
document.getElementById('message').addEventListener('input', function() {
    document.getElementById('char_count').textContent = this.value.length;
    updatePreview();
});

document.getElementById('template_id').addEventListener('change', updatePreview);
document.getElementById('scheduled_date').addEventListener('change', updateSummary);
document.getElementById('scheduled_time').addEventListener('change', updateSummary);

function updateRecipientCount() {
    const recipientType = document.getElementById('recipient_type').value;
    let count = 0;

    if (recipientType === 'manual') {
        const phones = document.getElementById('phone_list').value.split('\n').filter(p => p.trim());
        count = phones.length;
    } else if (recipientType === 'patient') {
        const selected = document.getElementById('patient_ids').selectedOptions;
        count = selected.length;
    }

    document.getElementById('recipient_count').value = count || 0;
    updateSummary();
}

function updatePreview() {
    const messageType = document.getElementById('message_type').value;
    const preview = document.getElementById('preview_text');
    let text = '';

    if (messageType === 'custom') {
        text = document.getElementById('message').value;
    } else if (messageType === 'template') {
        const templateOption = document.getElementById('template_id').options[document.getElementById('template_id').selectedIndex];
        text = templateOption.getAttribute('data-content') || '';
    }

    if (text) {
        preview.textContent = text;
        document.getElementById('preview_section').style.display = 'block';
    } else {
        document.getElementById('preview_section').style.display = 'none';
    }
}

function updateSummary() {
    const date = document.getElementById('scheduled_date').value;
    const time = document.getElementById('scheduled_time').value;
    const count = document.getElementById('recipient_count').value;

    if (date && time) {
        const datetime = new Date(date + 'T' + time);
        document.getElementById('summary_datetime').textContent = datetime.toLocaleString('id-ID');
        document.getElementById('summary_recipients').textContent = document.getElementById('recipient_type').options[document.getElementById('recipient_type').selectedIndex].text;
        document.getElementById('summary_total').textContent = count + ' pesan';
        document.getElementById('summary_section').style.display = 'block';

        // Date warning
        const warningEl = document.getElementById('date_warning');
        const now = new Date();
        if (datetime < now) {
            warningEl.textContent = 'Perhatian: Waktu yang dipilih sudah berlalu';
            warningEl.className = 'text-danger';
        } else {
            const diffMs = datetime - now;
            const diffHours = Math.floor(diffMs / (1000 * 60 * 60));
            warningEl.textContent = `Pesan akan dikirim dalam ${diffHours} jam`;
            warningEl.className = 'text-muted';
        }
    } else {
        document.getElementById('summary_section').style.display = 'none';
    }
}

// Form submission
document.getElementById('scheduleForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    const submitBtn = document.getElementById('submitBtn');
    const originalText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Menjadwalkan...';

    const recipientType = document.getElementById('recipient_type').value;
    const messageType = document.getElementById('message_type').value;
    const date = document.getElementById('scheduled_date').value;
    const time = document.getElementById('scheduled_time').value;

    let recipients = [];
    if (recipientType === 'manual') {
        recipients = document.getElementById('phone_list').value.split('\n').map(p => p.trim()).filter(p => p);
    } else {
        recipients = Array.from(document.getElementById('patient_ids').selectedOptions).map(opt => opt.value);
    }

    let message = '';
    if (messageType === 'custom') {
        message = document.getElementById('message').value;
    } else {
        const templateOption = document.getElementById('template_id').options[document.getElementById('template_id').selectedIndex];
        message = templateOption.getAttribute('data-content');
    }

    try {
        const response = await fetch('/api/whatsapp/send-scheduled', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify({
                recipients: recipients,
                message: message,
                recipient_type: recipientType,
                scheduled_date: date,
                scheduled_time: time,
            })
        });

        const data = await response.json();

        if (data.success) {
            showAlert('success', `Pesan berhasil dijadwalkan untuk ${recipients.length} penerima!`);
            setTimeout(() => window.location.href = '{{ route("whatsapp.dashboard") }}', 2000);
        } else {
            showAlert('danger', data.message || 'Gagal menjadwalkan pesan!');
        }
    } catch (error) {
        showAlert('danger', 'Terjadi kesalahan: ' + error.message);
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
        border-color: #0dcaf0;
        box-shadow: 0 0 0 0.2rem rgba(13, 202, 240, 0.25);
    }
</style>
@endsection
