@extends('layouts.app')

@section('title', 'Dashboard Kontrol BPJS')

@section('content')


<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="stat-card stat-card-total">
            <h3>{{ $stats['total'] }}</h3>
            <p>Total Jadwal Kontrol</p>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card stat-card-pending">
            <h3>{{ $stats['upcoming'] }}</h3>
            <p>Jadwal Mendatang</p>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card stat-card-sent">
            <h3>{{ $stats['past'] }}</h3>
            <p>Jadwal Lalu</p>
        </div>
    </div>
</div>


<!-- Search and Filter -->
<div class="card mb-4">
    <div class="card-header">
        <i class="bi bi-funnel"></i> Pencarian & Filter
    </div>
    <div class="card-body">
        <form action="{{ route('dashboard.kontrol.index') }}" method="GET" class="row g-3">
            <div class="col-md-4">
                <div class="search-box">
                    <i class="bi bi-search search-icon"></i>
                    <input type="text" name="search" class="form-control" placeholder="Cari nama pasien atau no rekam medis..."
                        value="{{ request('search') }}">
                </div>
            </div>

            <div class="col-md-3">
                <input type="date" name="start_date" class="form-control" placeholder="Dari Tanggal"
                    value="{{ $start_date }}">
            </div>

            <div class="col-md-3">
                <input type="date" name="end_date" class="form-control" placeholder="Sampai Tanggal"
                    value="{{ $end_date }}">
            </div>

            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-search"></i> Filter
                </button>
            </div>

            <div class="col-md-12">
                <a href="{{ route('dashboard.kontrol.index') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-clockwise"></i> Reset
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Table -->
<div class="card">
    <div class="card-header">
        <i class="bi bi-table"></i> Data Jadwal Kontrol
        <span class="badge bg-secondary float-end">{{ $kontrols->total() }} Jadwal</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>No. Surat</th>
                    <th>No. Rekam Medis</th>
                    <th>Nama Pasien</th>
                    <th>No. Telepon</th>
                    <th>Tanggal Rencana</th>
                    <th>Poliklinik BPJS</th>
                    <th>Dokter BPJS</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($kontrols as $index => $kontrol)
                    <tr>
                        <td>{{ ($kontrols->currentPage() - 1) * $kontrols->perPage() + $loop->iteration }}</td>
                        <td>
                            <span class="badge bg-info">{{ $kontrol->no_surat }}</span>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark">{{ $kontrol->no_rkm_medis }}</span>
                        </td>
                        <td>
                            <strong>{{ $kontrol->nm_pasien ?? '-' }}</strong>
                        </td>
                        <td>
                            @if($kontrol->no_tlp)
                                @php
                                    $phone = preg_replace('/[^0-9]/', '', $kontrol->no_tlp);
                                    $phone = preg_replace('/^0/', '62', $phone);
                                @endphp
                                <a href="https://wa.me/{{ $phone }}" target="_blank" class="text-decoration-none">
                                    <i class="bi bi-whatsapp" style="color: #25D366;"></i> {{ $kontrol->no_tlp }}
                                </a>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            <strong>{{ $kontrol->tgl_rencana ? \Carbon\Carbon::parse($kontrol->tgl_rencana)->format('d M Y') : '-' }}</strong>
                        </td>
                        <td>
                            <small>{{ $kontrol->nm_poli_bpjs ?? '-' }}</small>
                        </td>
                        <td>
                            <small>{{ $kontrol->nm_dokter_bpjs ?? '-' }}</small>
                        </td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary" title="Kirim Reminder WA" data-bs-toggle="modal" data-bs-target="#sendWAModal" data-nomor-surat="{{ $kontrol->no_surat }}" data-nama="{{ $kontrol->nm_pasien }}" data-nomor-tlp="{{ $kontrol->no_tlp }}">
                                <i class="bi bi-send"></i> Kirim
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted py-4">
                            <i class="bi bi-inbox"></i> Tidak ada data jadwal kontrol
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if($kontrols->hasPages())
        <div class="card-footer">
            <nav aria-label="Pagination">
                {{ $kontrols->links('vendor.pagination.bootstrap-5') }}
            </nav>
        </div>
    @endif
</div>

<!-- Send WA Modal -->
<div class="modal fade" id="sendWAModal" tabindex="-1" aria-labelledby="sendWAModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="sendWAModalLabel">
                    <i class="bi bi-whatsapp" style="color: #25D366;"></i> Kirim Reminder WhatsApp
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="sendWAForm" action="" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama Pasien</label>
                        <input type="text" class="form-control" id="namaPasien" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">No. Telepon</label>
                        <input type="text" class="form-control" id="nomorTlp" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Jadwal Pengiriman</label>
                        <div class="row g-2">
                            <div class="col-md-6">
                                <input type="date" class="form-control" id="tanggalKirim" required>
                            </div>
                            <div class="col-md-6">
                                <input type="time" class="form-control" id="jamKirim" required>
                            </div>
                        </div>
                        <small class="text-muted">Biarkan kosong untuk kirim sekarang</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Pesan</label>
                        <textarea class="form-control" id="pesan" rows="4" placeholder="Ketik pesan reminder...">Salam Hormat, kami ingin mengingatkan jadwal kontrol Anda. Pastikan hadir tepat waktu. Terima kasih.</textarea>
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="kirimSekarang">
                            <label class="form-check-label" for="kirimSekarang">
                                Kirim Sekarang (tanpa jadwal)
                            </label>
                        </div>
                    </div>
                    <div class="alert alert-info" role="alert">
                        <i class="bi bi-info-circle"></i> Pesan akan dikirim ke WhatsApp pasien sesuai jadwal yang ditentukan
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-success" id="btnKirimSekarang">
                        <i class="bi bi-send"></i> Kirim Sekarang
                    </button>
                    <button type="button" class="btn btn-warning" id="btnJadwalkanBesok">
                        <i class="bi bi-calendar"></i> Jadwalkan Besok
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-send"></i> <span id="btnText">Jadwalkan Pengiriman</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('extra_js')
<script>
    // Handle modal for sending WA
    document.getElementById('sendWAModal').addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget;
        const nomorSurat = button.getAttribute('data-nomor-surat');
        const nama = button.getAttribute('data-nama');
        const nomorTlp = button.getAttribute('data-nomor-tlp');

        document.getElementById('namaPasien').value = nama;
        document.getElementById('nomorTlp').value = nomorTlp;

        // Set default date to tomorrow
        const tomorrow = new Date();
        tomorrow.setDate(tomorrow.getDate() + 1);
        document.getElementById('tanggalKirim').value = tomorrow.toISOString().split('T')[0];
        document.getElementById('jamKirim').value = '09:00';

        // Update form action
        document.getElementById('sendWAForm').action = '/dashboard/kontrol/' + nomorSurat + '/send-reminder';
        
        // Store nomor surat for later use
        document.getElementById('sendWAForm').dataset.nomorSurat = nomorSurat;
        
        // Uncheck "Kirim Sekarang"
        document.getElementById('kirimSekarang').checked = false;
    });

    // Handle "Kirim Sekarang" button
    document.getElementById('btnKirimSekarang').addEventListener('click', function() {
        const pesan = document.getElementById('pesan').value;
        
        if (!pesan.trim()) {
            alert('Mohon isi pesan');
            return;
        }
        
        const form = document.getElementById('sendWAForm');
        const nomorSurat = form.dataset.nomorSurat;
        
        // Send immediately
        const now = new Date();
        const date = now.toISOString().split('T')[0];
        const time = now.getHours().toString().padStart(2, '0') + ':' + now.getMinutes().toString().padStart(2, '0');
        
        submitForm(form, date, time, pesan, nomorSurat);
    });

    // Handle "Jadwalkan Besok" button
    document.getElementById('btnJadwalkanBesok').addEventListener('click', function() {
        const pesan = document.getElementById('pesan').value;
        
        if (!pesan.trim()) {
            alert('Mohon isi pesan');
            return;
        }
        
        const form = document.getElementById('sendWAForm');
        const nomorSurat = form.dataset.nomorSurat;
        
        // Schedule for tomorrow at 08:00
        const tomorrow = new Date();
        tomorrow.setDate(tomorrow.getDate() + 1);
        const date = tomorrow.toISOString().split('T')[0];
        const time = '08:00';
        
        submitForm(form, date, time, pesan, nomorSurat);
    });

    // Helper function to submit form
    function submitForm(form, date, time, pesan, nomorSurat) {
        // Remove existing hidden fields if any
        let existingDate = form.querySelector('input[name="schedule_date"]');
        let existingTime = form.querySelector('input[name="schedule_time"]');
        let existingPesan = form.querySelector('input[name="pesan"]');
        if (existingDate) existingDate.remove();
        if (existingTime) existingTime.remove();
        if (existingPesan) existingPesan.remove();
        
        // Add new hidden fields
        let inputDate = document.createElement('input');
        inputDate.type = 'hidden';
        inputDate.name = 'schedule_date';
        inputDate.value = date;
        form.appendChild(inputDate);
        
        let inputTime = document.createElement('input');
        inputTime.type = 'hidden';
        inputTime.name = 'schedule_time';
        inputTime.value = time;
        form.appendChild(inputTime);
        
        let inputPesan = document.createElement('input');
        inputPesan.type = 'hidden';
        inputPesan.name = 'pesan';
        inputPesan.value = pesan;
        form.appendChild(inputPesan);
        
        // Submit form
        form.submit();
    }

    // Handle form submission with validation
    document.getElementById('sendWAForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const tanggal = document.getElementById('tanggalKirim').value;
        const jam = document.getElementById('jamKirim').value;
        const pesan = document.getElementById('pesan').value;
        
        if (!tanggal || !jam) {
            alert('Mohon isi jadwal pengiriman');
            return;
        }
        
        if (!pesan.trim()) {
            alert('Mohon isi pesan');
            return;
        }
        
        submitForm(this, tanggal, jam, pesan);
    });
</script>
@endsection
