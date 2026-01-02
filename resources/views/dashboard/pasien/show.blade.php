@extends('layouts.app')

@section('title', 'Detail Pasien - ' . $pasien->nm_pasien)

@section('content')
<div class="row mb-4">
    <div class="col-md-12">
        <div class="d-flex justify-content-between align-items-center">
            <h2>
                <i class="bi bi-person-card"></i> Detail Pasien
            </h2>
            <a href="{{ route('dashboard.pasien.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
        </div>
    </div>
</div>

<!-- Patient Info Card -->
<div class="row mb-4">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-info-circle"></i> Informasi Pasien
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label text-muted">No. Rekam Medis</label>
                        <p><strong>{{ $pasien->no_rkm_medis }}</strong></p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted">Nama Pasien</label>
                        <p><strong>{{ $pasien->nm_pasien }}</strong></p>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label text-muted">No. KTP</label>
                        <p><strong>{{ $pasien->no_ktp ?? '-' }}</strong></p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted">Jenis Kelamin</label>
                        <p>
                            @if($pasien->jk == 'L')
                                <span class="badge bg-primary">Laki-laki</span>
                            @else
                                <span class="badge bg-danger">Perempuan</span>
                            @endif
                        </p>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label text-muted">Tanggal Lahir</label>
                        <p><strong>{{ $pasien->tgl_lahir ? \Carbon\Carbon::parse($pasien->tgl_lahir)->format('d M Y') : '-' }}</strong></p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted">Umur</label>
                        <p><strong>{{ $pasien->umur ?? '-' }} tahun</strong></p>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label text-muted">No. Telepon</label>
                        <p>
                            @if($pasien->no_tlp)
                                @php
                                    $phone = preg_replace('/[^0-9]/', '', $pasien->no_tlp);
                                    $phone = preg_replace('/^0/', '62', $phone);
                                @endphp
                                <a href="https://wa.me/{{ $phone }}" target="_blank" class="text-decoration-none">
                                    <i class="bi bi-whatsapp" style="color: #25D366;"></i> {{ $pasien->no_tlp }}
                                </a>
                            @else
                                <span>-</span>
                            @endif
                        </p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted">Email</label>
                        <p><strong>{{ $pasien->email ?? '-' }}</strong></p>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-12">
                        <label class="form-label text-muted">Alamat</label>
                        <p><strong>{{ $pasien->alamat ?? '-' }}</strong></p>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label text-muted">Status Pernikahan</label>
                        <p><strong>{{ $pasien->stts_nikah ?? '-' }}</strong></p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted">Agama</label>
                        <p><strong>{{ $pasien->agama ?? '-' }}</strong></p>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label text-muted">No. Peserta Asuransi</label>
                        <p><strong>{{ $pasien->no_peserta ?? '-' }}</strong></p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted">Golongan Darah</label>
                        <p>
                            @if($pasien->gol_darah && $pasien->gol_darah != '-')
                                <span class="badge bg-warning">{{ $pasien->gol_darah }}</span>
                            @else
                                <span>-</span>
                            @endif
                        </p>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label text-muted">Tanggal Daftar</label>
                        <p><strong>{{ $pasien->tgl_daftar ? \Carbon\Carbon::parse($pasien->tgl_daftar)->format('d M Y') : '-' }}</strong></p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted">Pendidikan</label>
                        <p><strong>{{ $pasien->pnd ?? '-' }}</strong></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="col-md-4">
        <div class="stat-card stat-card-total">
            <h3>{{ $regPeriksa->total() }}</h3>
            <p>Total Kunjungan</p>
        </div>
    </div>
</div>

<!-- Medical Visits -->
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-file-medical"></i> Riwayat Kunjungan Medis
                <span class="badge bg-secondary float-end">{{ $regPeriksa->total() }} Kunjungan</span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>No. Rawat</th>
                            <th>Tanggal</th>
                            <th>Poliklinik</th>
                            <th>Dokter</th>
                            <th>Asuransi</th>
                            <th>Status</th>
                            <th>Status Bayar</th>
                            <th>BPJS SEP</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($regPeriksa as $visit)
                            <tr>
                                <td><span class="badge bg-info">{{ $visit->no_rawat }}</span></td>
                                <td>{{ \Carbon\Carbon::parse($visit->tgl_registrasi)->format('d M Y H:i') }}</td>
                                <td>{{ optional($visit->poliklinik)->nm_poli ?? $visit->kd_poli ?? '-' }}</td>
                                <td>{{ optional($visit->dokter)->nm_dokter ?? $visit->kd_dokter ?? '-' }}</td>
                                <td>
                                    @if($visit->kd_pj)
                                        <span class="badge bg-info">{{ $visit->kd_pj }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $statusColor = [
                                            'Belum' => 'warning',
                                            'Sudah' => 'success',
                                            'Batal' => 'danger',
                                            'Berkas Diterima' => 'info',
                                            'Dirujuk' => 'primary',
                                            'Meninggal' => 'dark',
                                            'Dirawat' => 'secondary',
                                            'Pulang Paksa' => 'danger',
                                        ];
                                        $color = $statusColor[$visit->stts] ?? 'secondary';
                                    @endphp
                                    <span class="badge bg-{{ $color }}">{{ $visit->stts }}</span>
                                </td>
                                <td>
                                    @if($visit->status_bayar == 'Sudah Bayar')
                                        <span class="badge bg-success">Sudah Bayar</span>
                                    @else
                                        <span class="badge bg-warning">Belum Bayar</span>
                                    @endif
                                </td>
                                <td>
                                    @if($visit->bridgingSep && $visit->bridgingSep->count() > 0)
                                        <span class="badge bg-info">Ada SEP</span>
                                    @else
                                        <span class="badge bg-secondary">Tidak Ada</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    <i class="bi bi-inbox"></i> Tidak ada riwayat kunjungan
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($regPeriksa->hasPages())
                <div class="card-footer">
                    <nav aria-label="Pagination">
                        {{ $regPeriksa->links() }}
                    </nav>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
