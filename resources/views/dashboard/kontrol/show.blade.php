@extends('layouts.app')

@section('title', 'Detail Jadwal Kontrol - ' . $kontrol->no_surat)

@section('content')
<div class="row mb-4">
    <div class="col-md-12">
        <div class="d-flex justify-content-between align-items-center">
            <h2>
                <i class="bi bi-calendar-check"></i> Detail Jadwal Kontrol
            </h2>
            <a href="{{ route('dashboard.kontrol.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
        </div>
    </div>
</div>

<!-- Control Schedule Info -->
<div class="row mb-4">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-file-earmark-check"></i> Informasi Jadwal Kontrol
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label text-muted">No. Surat Kontrol</label>
                        <p><strong>{{ $kontrol->no_surat }}</strong></p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted">No. SEP</label>
                        <p><strong>{{ $kontrol->no_sep ?? '-' }}</strong></p>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label text-muted">Tanggal Surat</label>
                        <p><strong>{{ $kontrol->tgl_surat ? \Carbon\Carbon::parse($kontrol->tgl_surat)->format('d M Y') : '-' }}</strong></p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted">Tanggal Rencana Kontrol</label>
                        <p><strong>{{ $kontrol->tgl_rencana ? \Carbon\Carbon::parse($kontrol->tgl_rencana)->format('d M Y') : '-' }}</strong></p>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label text-muted">Kode Dokter BPJS</label>
                        <p><strong>{{ $kontrol->kd_dokter_bpjs ?? '-' }}</strong></p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted">Nama Dokter BPJS</label>
                        <p><strong>{{ $kontrol->nm_dokter_bpjs ?? '-' }}</strong></p>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label text-muted">Kode Poliklinik BPJS</label>
                        <p><strong>{{ $kontrol->kd_poli_bpjs ?? '-' }}</strong></p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted">Nama Poliklinik BPJS</label>
                        <p><strong>{{ $kontrol->nm_poli_bpjs ?? '-' }}</strong></p>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label text-muted">Kode Poliklinik BPJS</label>
                        <p><strong>{{ $kontrol->kd_poli_bpjs ?? '-' }}</strong></p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted">Nama Poliklinik BPJS</label>
                        <p><strong>{{ $kontrol->nm_poli_bpjs ?? '-' }}</strong></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Patient Info Summary -->
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-person"></i> Informasi Pasien
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label text-muted small">No. Rekam Medis</label>
                    <p class="mb-0"><strong>{{ $kontrol->bridgingSep->regPeriksa->no_rkm_medis ?? '-' }}</strong></p>
                </div>

                <div class="mb-3">
                    <label class="form-label text-muted small">Nama Pasien</label>
                    <p class="mb-0"><strong>{{ $kontrol->bridgingSep->nama_pasien ?? '-' }}</strong></p>
                </div>

                <div class="mb-3">
                    <label class="form-label text-muted small">No. Kartu BPJS</label>
                    <p class="mb-0"><strong>{{ $kontrol->bridgingSep->no_kartu ?? '-' }}</strong></p>
                </div>

                <div class="mb-3">
                    <label class="form-label text-muted small">Jenis Kelamin</label>
                    <p class="mb-0">
                        @if($kontrol->bridgingSep->jkel == 'L')
                            <span class="badge bg-primary">Laki-laki</span>
                        @else
                            <span class="badge bg-danger">Perempuan</span>
                        @endif
                    </p>
                </div>

                <div class="mb-3">
                    <label class="form-label text-muted small">Tanggal Lahir</label>
                    <p class="mb-0"><strong>{{ $kontrol->bridgingSep->tanggal_lahir ? \Carbon\Carbon::parse($kontrol->bridgingSep->tanggal_lahir)->format('d M Y') : '-' }}</strong></p>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="card mt-3">
            <div class="card-header">
                <i class="bi bi-lightning"></i> Aksi
            </div>
            <div class="card-body">
                <button class="btn btn-primary w-100 mb-2" data-bs-toggle="modal" data-bs-target="#sendWAModal">
                    <i class="bi bi-whatsapp"></i> Kirim Reminder WA
                </button>
                <button class="btn btn-outline-secondary w-100">
                    <i class="bi bi-file-pdf"></i> Cetak Surat
                </button>
            </div>
        </div>
    </div>
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
                        <label class="form-label">No. Surat Kontrol</label>
                        <input type="text" class="form-control" value="{{ $kontrol->no_surat }}" readonly>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Nama Pasien</label>
                        <input type="text" class="form-control" value="{{ $kontrol->bridgingSep->nama_pasien ?? '-' }}" readonly>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Pesan</label>
                        <textarea class="form-control" id="pesan" rows="4" placeholder="Ketik pesan reminder...">Salam Hormat, kami ingin mengingatkan jadwal kontrol Anda pada {{ $kontrol->tgl_rencana ? \Carbon\Carbon::parse($kontrol->tgl_rencana)->format('d M Y') : '-' }} di {{ $kontrol->nm_poli_bpjs ?? 'Poliklinik' }}. Pastikan hadir tepat waktu. Terima kasih.</textarea>
                    </div>

                    <div class="alert alert-info" role="alert">
                        <i class="bi bi-info-circle"></i> Pesan akan dikirim ke nomor WhatsApp pasien
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-send"></i> Kirim Sekarang
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
