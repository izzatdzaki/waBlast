@extends('layouts.app')

@section('title', 'Dashboard Utama')

@section('content')


<!-- Main Statistics -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="stat-card stat-card-total">
            <h3>{{ $stats['total_pasien'] }}</h3>
            <p>Total Pasien</p>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card stat-card-pending">
            <h3>{{ $stats['jadwal_hari_ini'] }}</h3>
            <p>Kontrol Hari Ini</p>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card stat-card-sent">
            <h3>{{ $stats['jadwal_mendatang'] }}</h3>
            <p>Kontrol Mendatang</p>
        </div>
    </div>
</div>

<!-- Summary Statistics -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-envelope"></i> Pesan WhatsApp
            </div>
            <div class="card-body text-center">
                <h3 style="font-size: 2.5rem; color: #667eea;">{{ $stats['pesan_terkirim'] }}</h3>
                <p class="text-muted">Pesan Terkirim</p>
                <small class="text-muted">Fitur WhatsApp akan diaktifkan</small>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-person-check"></i> Pasien Datang
            </div>
            <div class="card-body text-center">
                <h3 style="font-size: 2.5rem; color: #198754;">{{ $stats['pasien_datang'] }}</h3>
                <p class="text-muted">Pasien Sudah Datang</p>
                <a href="{{ route('attendance.index') }}" class="btn btn-sm btn-outline-primary mt-2">
                    <i class="bi bi-arrow-right"></i> Lihat Detail
                </a>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-calendar-check"></i> Total Jadwal
            </div>
            <div class="card-body text-center">
                <h3 style="font-size: 2.5rem; color: #0dcaf0;">{{ $stats['total_jadwal_kontrol'] }}</h3>
                <p class="text-muted">Total Jadwal Kontrol</p>
                <a href="{{ route('dashboard.kontrol.index') }}" class="btn btn-sm btn-outline-primary mt-2">
                    <i class="bi bi-arrow-right"></i> Lihat Detail
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Jadwal Hari Ini -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-calendar-event"></i> Jadwal Kontrol Hari Ini
                <span class="badge bg-danger float-end">{{ count($jadwal_hari_ini) }}</span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>No. Surat</th>
                            <th>Nama Pasien</th>
                            <th>Status</th>
                            <th>Asal</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($jadwal_hari_ini as $jadwal)
                            <tr>
                                <td>
                                    <span class="badge bg-info">{{ substr($jadwal->no_surat, -6) }}</span>
                                </td>
                                <td>
                                    <strong>{{ $jadwal->nm_pasien ?? '-' }}</strong>
                                </td>
                                <td>
                                    @if($jadwal->status_kehadiran == 'sudah_datang')
                                        <span class="badge bg-success"><i class="bi bi-check-circle"></i> Sudah</span>
                                    @else
                                        <span class="badge bg-warning"><i class="bi bi-exclamation-circle"></i> Belum</span>
                                    @endif
                                </td>
                                <td>
                                    @if($jadwal->asal_kehadiran == 'Mobile JKN')
                                        <span class="badge bg-primary"><i class="bi bi-phone"></i> Mobile</span>
                                    @elseif($jadwal->asal_kehadiran == 'ONSITE')
                                        <span class="badge bg-info"><i class="bi bi-building"></i> Onsite</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('dashboard.kontrol.show', $jadwal->no_surat) }}" class="btn btn-sm btn-outline-primary" title="Lihat Detail">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-3">
                                    <i class="bi bi-inbox"></i> Tidak ada jadwal kontrol hari ini
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Jadwal Minggu Depan -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-calendar-range"></i> Jadwal Kontrol 7 Hari Ke Depan
                <span class="badge bg-warning float-end">{{ count($jadwal_minggu_depan) }}</span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>No. Surat</th>
                            <th>Nama Pasien</th>
                            <th>Tanggal</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($jadwal_minggu_depan as $jadwal)
                            <tr>
                                <td>
                                    <span class="badge bg-info">{{ substr($jadwal->no_surat, -6) }}</span>
                                </td>
                                <td>
                                    <strong>{{ $jadwal->bridgingSep->regPeriksa->pasien->nm_pasien ?? '-' }}</strong>
                                </td>
                                <td>
                                    {{ $jadwal->tgl_rencana ? \Carbon\Carbon::parse($jadwal->tgl_rencana)->format('d M Y') : '-' }}
                                </td>
                                <td>
                                    <a href="{{ route('dashboard.kontrol.show', $jadwal->no_surat) }}" class="btn btn-sm btn-outline-primary" title="Lihat Detail">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-3">
                                    <i class="bi bi-inbox"></i> Tidak ada jadwal untuk 7 hari ke depan
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
