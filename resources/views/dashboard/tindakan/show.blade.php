@extends('layouts.app')

@section('title', 'Detail Tindakan')

@section('content')

<div class="row mb-4">
    <div class="col-md-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="bi bi-file-earmark-text"></i> Detail Tindakan</h2>
            <a href="{{ route('dashboard.tindakan.index') }}" class="btn btn-sm btn-outline-primary">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
        </div>
    </div>
</div>

<!-- Patient Information -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-person"></i> Informasi Pasien
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Nama Pasien</label>
                    </div>
                    <div class="col-md-8">
                        <p>{{ $tindakan->regPeriksa->pasien->nm_pasien ?? '-' }}</p>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label fw-bold">No. Rekam Medis</label>
                    </div>
                    <div class="col-md-8">
                        <p><span class="badge bg-info">{{ $tindakan->regPeriksa->pasien->no_rkm_medis }}</span></p>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label fw-bold">No. KTP</label>
                    </div>
                    <div class="col-md-8">
                        <p>{{ $tindakan->regPeriksa->pasien->no_ktp ?? '-' }}</p>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Jenis Kelamin</label>
                    </div>
                    <div class="col-md-8">
                        <p>
                            @if($tindakan->regPeriksa->pasien->jk == 'L')
                                <span class="badge bg-primary">Laki-laki</span>
                            @else
                                <span class="badge bg-danger">Perempuan</span>
                            @endif
                        </p>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Tanggal Lahir</label>
                    </div>
                    <div class="col-md-8">
                        <p>{{ $tindakan->regPeriksa->pasien->tgl_lahir ? \Carbon\Carbon::parse($tindakan->regPeriksa->pasien->tgl_lahir)->format('d-m-Y') : '-' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Treatment Information -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-bandaid"></i> Informasi Tindakan
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label fw-bold">No. Rawat</label>
                    </div>
                    <div class="col-md-8">
                        <p><span class="badge bg-primary">{{ $tindakan->no_rawat }}</span></p>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Jenis Tindakan</label>
                    </div>
                    <div class="col-md-8">
                        <p><span class="badge bg-success">{{ $tindakan->jnsPerawatan->nm_perawatan ?? $tindakan->kd_jenis_prw }}</span></p>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Dokter</label>
                    </div>
                    <div class="col-md-8">
                        <p>{{ $tindakan->dokter->nm_dokter ?? '-' }}</p>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Tanggal</label>
                    </div>
                    <div class="col-md-8">
                        <p>{{ \Carbon\Carbon::parse($tindakan->tgl_perawatan)->format('d-m-Y') }}</p>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Jam</label>
                    </div>
                    <div class="col-md-8">
                        <p><span class="badge bg-info">{{ \Carbon\Carbon::parse($tindakan->jam_rawat)->format('H:i') }}</span></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Cost Information -->
<div class="card mb-4">
    <div class="card-header">
        <i class="bi bi-cash-coins"></i> Rincian Biaya Tindakan
    </div>
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-6">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Biaya Material</label>
                    </div>
                    <div class="col-md-6">
                        <p class="text-end">Rp {{ number_format($tindakan->material ?? 0, 0, ',', '.') }}</p>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Biaya BHP</label>
                    </div>
                    <div class="col-md-6">
                        <p class="text-end">Rp {{ number_format($tindakan->bhp ?? 0, 0, ',', '.') }}</p>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Tarif Tindakan Dokter</label>
                    </div>
                    <div class="col-md-6">
                        <p class="text-end">Rp {{ number_format($tindakan->tarif_tindakandr ?? 0, 0, ',', '.') }}</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">KSO</label>
                    </div>
                    <div class="col-md-6">
                        <p class="text-end">Rp {{ number_format($tindakan->kso ?? 0, 0, ',', '.') }}</p>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Manajemen</label>
                    </div>
                    <div class="col-md-6">
                        <p class="text-end">Rp {{ number_format($tindakan->menejemen ?? 0, 0, ',', '.') }}</p>
                    </div>
                </div>
            </div>
        </div>
        <hr>
        <div class="row">
            <div class="col-md-6"></div>
            <div class="col-md-6">
                <div class="row">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Total Biaya</label>
                    </div>
                    <div class="col-md-6">
                        <p class="text-end">
                            <strong style="font-size: 1.3rem; color: #198754;">
                                Rp {{ number_format($tindakan->biaya_rawat ?? 0, 0, ',', '.') }}
                            </strong>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Payment Status -->
<div class="card mb-4">
    <div class="card-header">
        <i class="bi bi-receipt"></i> Status Pembayaran
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-4">
                <p class="text-muted mb-2">Status Pembayaran</p>
                <p>
                    @if($tindakan->stts_bayar == 'Sudah')
                        <span class="badge bg-success p-2" style="font-size: 1rem;">
                            <i class="bi bi-check-circle"></i> Sudah Bayar
                        </span>
                    @elseif($tindakan->stts_bayar == 'Belum')
                        <span class="badge bg-danger p-2" style="font-size: 1rem;">
                            <i class="bi bi-exclamation-circle"></i> Belum Bayar
                        </span>
                    @else
                        <span class="badge bg-warning p-2" style="font-size: 1rem;">
                            <i class="bi bi-question-circle"></i> {{ $tindakan->stts_bayar }}
                        </span>
                    @endif
                </p>
            </div>
        </div>
    </div>
</div>

@endsection
