@extends('layouts.app')

@section('title', 'Detail Mobile BPJS')

@section('content')
<div class="row mb-4">
    <div class="col-md-12">
        <a href="{{ route('dashboard.mobile_bpjs.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>
</div>

<!-- Detail Pasien -->
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-person"></i> Informasi Pasien
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Nama Pasien:</strong></p>
                        <p>{{ $mobile_bpjs->nm_pasien ?? 'Tidak ditemukan' }}</p>

                        <p><strong>No. RM:</strong></p>
                        <p>{{ $mobile_bpjs->no_rkm_medis ?? '-' }}</p>

                        <p><strong>No. KTP:</strong></p>
                        <p>{{ $mobile_bpjs->no_ktp ?? '-' }}</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>No. Telepon:</strong></p>
                        <p>
                            @if($mobile_bpjs->no_tlp)
                                <a href="https://wa.me/{{ preg_replace('/^0/', '62', $mobile_bpjs->no_tlp) }}" target="_blank" class="btn btn-sm btn-outline-success">
                                    <i class="bi bi-whatsapp"></i> {{ $mobile_bpjs->no_tlp }}
                                </a>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </p>

                        <p><strong>Alamat:</strong></p>
                        <p>{{ $mobile_bpjs->alamat ?? '-' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Detail Antrian Mobile BPJS -->
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-phone"></i> Detail Antrian Mobile BPJS
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>No. Referensi:</strong></p>
                        <p><span class="badge bg-info">{{ $mobile_bpjs->nomorreferensi }}</span></p>

                        <p><strong>Tanggal Periksa:</strong></p>
                        <p>{{ \Carbon\Carbon::parse($mobile_bpjs->tanggalperiksa)->format('d M Y H:i:s') }}</p>

                        <p><strong>Jenis Kunjungan:</strong></p>
                        <p>
                            @if($mobile_bpjs->jeniskunjungan)
                                <span class="badge bg-secondary">{{ $mobile_bpjs->jeniskunjungan }}</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Status:</strong></p>
                        <p>
                            @if($mobile_bpjs->status)
                                <span class="badge bg-success">{{ $mobile_bpjs->status }}</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Jadwal Kontrol Terkait -->
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-calendar-check"></i> Jadwal Kontrol Terkait
                <span class="badge bg-secondary float-end">{{ count($jadwal_terkait) }} Jadwal</span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>No. Surat</th>
                            <th>Tanggal Rencana</th>
                            <th>Nama Pasien</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($jadwal_terkait as $jadwal)
                            <tr>
                                <td>
                                    <span class="badge bg-info">{{ substr($jadwal->no_surat, -6) }}</span>
                                </td>
                                <td>
                                    {{ \Carbon\Carbon::parse($jadwal->tgl_rencana)->format('d M Y') }}
                                </td>
                                <td>
                                    <strong>{{ $jadwal->nm_pasien ?? '-' }}</strong>
                                </td>
                                <td>
                                    @if($jadwal->stts === 'Sudah')
                                        <span class="badge bg-success"><i class="bi bi-check-circle"></i> Sudah</span>
                                    @else
                                        <span class="badge bg-warning"><i class="bi bi-exclamation-circle"></i> Belum</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('dashboard.kontrol.show', $jadwal->no_surat) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-3">
                                    <i class="bi bi-inbox"></i> Tidak ada jadwal kontrol terkait
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
