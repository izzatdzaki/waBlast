@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="h3 mb-0">
                <i class="fas fa-plus-circle text-primary"></i> Tambah Pengingat Ulang Tahun
            </h1>
            <p class="text-muted mt-2">Buat pesan pengingat baru untuk dikirim kepada pasien</p>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('dashboard.birthday-reminder.index', ['filter' => request('filter', 'today')]) }}"
                class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>
    </div>

    <!-- Form Card -->
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('dashboard.birthday-reminder.store') }}" method="POST">
                        @csrf

                        <!-- Pilih Pasien -->
                        <div class="mb-3">
                            <label for="no_rkm_medis" class="form-label fw-bold">
                                <i class="fas fa-user-injured"></i> Pilih Pasien
                                <span class="text-danger">*</span>
                            </label>
                            <select class="form-select @error('no_rkm_medis') is-invalid @enderror"
                                id="no_rkm_medis" name="no_rkm_medis" required onchange="updatePatientInfo()">
                                <option value="">-- Pilih Pasien --</option>
                                @foreach ($patients as $patient)
                                    <option value="{{ $patient->no_rkm_medis }}"
                                        data-name="{{ $patient->nm_pasien }}"
                                        data-birth="{{ $patient->tgl_lahir->format('Y-m-d') }}"
                                        {{ old('no_rkm_medis') === $patient->no_rkm_medis ? 'selected' : '' }}>
                                        {{ $patient->no_rkm_medis }} - {{ $patient->nm_pasien }}
                                    </option>
                                @endforeach
                            </select>
                            @error('no_rkm_medis')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Informasi Pasien -->
                        <div class="row" id="patientInfo" style="display: none;">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nama Pasien</label>
                                <input type="text" class="form-control" id="patientName" readonly>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tanggal Lahir</label>
                                <input type="text" class="form-control" id="patientBirth" readonly>
                            </div>
                        </div>

                        <!-- Nomor WhatsApp Penerima -->
                        <div class="mb-3">
                            <label for="receiver_phone" class="form-label fw-bold">
                                <i class="fas fa-mobile-alt"></i> Nomor WhatsApp Penerima
                                <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">+62</span>
                                <input type="text" class="form-control @error('receiver_phone') is-invalid @enderror"
                                    id="receiver_phone" name="receiver_phone" placeholder="812345678901" required
                                    value="{{ old('receiver_phone') }}"
                                    pattern="[0-9]{9,12}">
                            </div>
                            <small class="form-text text-muted">
                                Masukkan nomor tanpa 0 di depan, cth: 812345678901
                            </small>
                            @error('receiver_phone')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Template Pesan -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">Template Pesan</label>
                            <div class="btn-group w-100 mb-3" role="group">
                                <input type="radio" class="btn-check" name="template" id="template1"
                                    value="template1" onchange="useTemplate(1)">
                                <label class="btn btn-outline-primary" for="template1">Template 1</label>

                                <input type="radio" class="btn-check" name="template" id="template2"
                                    value="template2" onchange="useTemplate(2)">
                                <label class="btn btn-outline-primary" for="template2">Template 2</label>

                                <input type="radio" class="btn-check" name="template" id="template3"
                                    value="template3" onchange="useTemplate(3)">
                                <label class="btn btn-outline-primary" for="template3">Template 3</label>

                                <input type="radio" class="btn-check" name="template" id="template4"
                                    value="template4" onchange="useTemplate(4)">
                                <label class="btn btn-outline-primary" for="template4">Custom</label>
                            </div>
                        </div>

                        <!-- Pesan -->
                        <div class="mb-3">
                            <label for="message" class="form-label fw-bold">
                                <i class="fas fa-message"></i> Pesan
                                <span class="text-danger">*</span>
                            </label>
                            <textarea class="form-control @error('message') is-invalid @enderror" id="message"
                                name="message" rows="5" required
                                placeholder="Ketik pesan pengingat ulang tahun di sini...">{{ old('message') }}</textarea>
                            <small class="form-text text-muted">
                                Minimal 5 karakter, maksimal 1000 karakter. Saat ini: <span id="charCount">0</span>
                            </small>
                            @error('message')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Opsi Pengiriman -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">
                                <i class="fas fa-clock"></i> Opsi Pengiriman
                            </label>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="send_now" id="sendNow"
                                    value="1" checked onchange="toggleScheduleDate()">
                                <label class="form-check-label" for="sendNow">
                                    Kirim Sekarang
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="send_now" id="sendScheduled"
                                    value="0" onchange="toggleScheduleDate()">
                                <label class="form-check-label" for="sendScheduled">
                                    Jadwalkan untuk Tanggal Tertentu
                                </label>
                            </div>
                        </div>

                        <!-- Tanggal Jadwal -->
                        <div class="mb-3" id="scheduleDateGroup" style="display: none;">
                            <label for="scheduled_date" class="form-label fw-bold">
                                <i class="fas fa-calendar"></i> Tanggal & Waktu Pengiriman
                            </label>
                            <input type="datetime-local"
                                class="form-control @error('scheduled_date') is-invalid @enderror"
                                id="scheduled_date" name="scheduled_date" value="{{ old('scheduled_date') }}">
                            @error('scheduled_date')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Submit Buttons -->
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Simpan Pengingat
                            </button>
                            <a href="{{ route('dashboard.birthday-reminder.index', ['filter' => request('filter', 'today')]) }}"
                                class="btn btn-secondary">
                                <i class="fas fa-times"></i> Batal
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Help Card -->
        <div class="col-lg-4">
            <div class="card bg-light">
                <div class="card-header bg-primary text-white">
                    <i class="fas fa-info-circle"></i> Bantuan
                </div>
                <div class="card-body">
                    <h6 class="fw-bold mb-3">Cara Penggunaan:</h6>
                    <ol class="small">
                        <li>Pilih pasien dari daftar</li>
                        <li>Masukkan nomor WhatsApp penerima</li>
                        <li>Pilih template atau buat pesan custom</li>
                        <li>Pilih opsi pengiriman (sekarang atau terjadwal)</li>
                        <li>Klik "Simpan Pengingat"</li>
                    </ol>

                    <hr>

                    <h6 class="fw-bold mb-2">Template Pesan:</h6>
                    <div class="mb-2">
                        <small class="fw-bold">Template 1:</small>
                        <p class="small">
                            Selamat ulang tahun! Semoga hari istimewamu dipenuhi berkah dan kebahagiaan. Terima kasih
                            telah mempercayai kami.
                        </p>
                    </div>
                    <div class="mb-2">
                        <small class="fw-bold">Template 2:</small>
                        <p class="small">
                            🎉 Happy Birthday! Wishing you a wonderful day filled with joy and good health!
                        </p>
                    </div>
                    <div class="mb-2">
                        <small class="fw-bold">Template 3:</small>
                        <p class="small">
                            Selamat ulang tahun! Kami berharap Anda selalu sehat dan bahagia. Jangan lupa kontrol
                            kesehatan secara berkala. 😊
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Hitung karakter pesan
    document.getElementById('message').addEventListener('input', function() {
        document.getElementById('charCount').textContent = this.value.length;
    });

    // Update informasi pasien
    function updatePatientInfo() {
        const select = document.getElementById('no_rkm_medis');
        const option = select.options[select.selectedIndex];
        const info = document.getElementById('patientInfo');

        if (option.value) {
            document.getElementById('patientName').value = option.dataset.name;
            document.getElementById('patientBirth').value = option.dataset.birth;
            info.style.display = 'flex';
        } else {
            info.style.display = 'none';
        }
    }

    // Toggle tanggal jadwal
    function toggleScheduleDate() {
        const scheduleGroup = document.getElementById('scheduleDateGroup');
        if (document.getElementById('sendScheduled').checked) {
            scheduleGroup.style.display = 'block';
            document.getElementById('scheduled_date').required = true;
        } else {
            scheduleGroup.style.display = 'none';
            document.getElementById('scheduled_date').required = false;
        }
    }

    // Gunakan template
    function useTemplate(num) {
        const textarea = document.getElementById('message');
        let template = '';

        switch (num) {
            case 1:
                template = 'Selamat ulang tahun! 🎂 Semoga hari istimewamu dipenuhi berkah dan kebahagiaan. Terima kasih telah mempercayai kami untuk kesehatan Anda. 💚';
                break;
            case 2:
                template = '🎉 Happy Birthday! Wishing you a wonderful day filled with joy, good health, and happiness! Thank you for trusting us. 🎊';
                break;
            case 3:
                template = 'Selamat ulang tahun! 🎁 Semoga Anda selalu sehat, bahagia, dan bersemangat. Jangan lupa untuk rutin kontrol kesehatan dan jaga pola hidup sehat. Salam sehat! 💪';
                break;
            case 4:
                template = '';
                break;
        }

        textarea.value = template;
        document.getElementById('charCount').textContent = template.length;
        textarea.focus();
    }
</script>

<style>
    .form-label {
        margin-bottom: 0.5rem;
        font-size: 0.95rem;
    }

    .btn-group {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
    }

    .btn-group .btn {
        flex: 1;
        min-width: 100px;
    }

    .card-header {
        border-bottom: 1px solid rgba(0, 0, 0, 0.125);
    }
</style>
@endsection
