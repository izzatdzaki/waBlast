@extends('layouts.app')

@section('title', 'Tracking Kehadiran')

@section('content')
<div class="row mb-4">
    <div class="col-md-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="bi bi-person-check"></i> Tracking Kehadiran Pasien</h2>
            <div>
                <a href="{{ route('attendance.export', ['date' => $date]) }}" class="btn btn-sm btn-outline-success">
                    <i class="bi bi-download"></i> Export CSV
                </a>
                <a href="{{ route('attendance.index') }}" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-arrow-clockwise"></i> Refresh
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Statistics -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="stat-card stat-card-total">
            <h3>{{ $stats['total'] }}</h3>
            <p>Total Jadwal</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card stat-card-sent">
            <h3>{{ $stats['sudah_datang'] }}</h3>
            <p>Sudah Datang</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card stat-card-pending">
            <h3>{{ $stats['belum_datang'] }}</h3>
            <p>Belum Datang</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center" style="padding: 12px;">
            <small class="text-muted">Tanggal:</small>
            <strong>{{ \Carbon\Carbon::parse($date)->format('d M Y') }}</strong>
        </div>
    </div>
</div>

<!-- Date Filter -->
<div class="card mb-4">
    <div class="card-header">
        <i class="bi bi-calendar"></i> Pilih Tanggal
    </div>
    <div class="card-body">
        <form action="{{ route('attendance.index') }}" method="GET" class="row g-2">
            <div class="col-md-4">
                <input type="date" name="date" class="form-control" value="{{ $date }}">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-search"></i> Cari
                </button>
            </div>
            <div class="col-md-2">
                <a href="{{ route('attendance.index') }}" class="btn btn-secondary w-100">
                    <i class="bi bi-arrow-clockwise"></i> Reset
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Attendance Table -->
<div class="card">
    <div class="card-header">
        <i class="bi bi-table"></i> Data Kehadiran
        <span class="badge bg-secondary float-end">{{ $stats['total'] }} Pasien</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>No. Surat</th>
                    <th>No. RM</th>
                    <th>Nama Pasien</th>
                    <th>Status Kehadiran</th>
                    <th>Asal Kehadiran</th>
                    <th>Waktu Datang</th>
                </tr>
            </thead>
            <tbody>
                @forelse($attendance_data as $index => $item)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>
                            <span class="badge bg-info">{{ substr($item['no_surat'], -6) }}</span>
                        </td>
                        <td>{{ $item['no_rkm_medis'] }}</td>
                        <td>
                            <strong>{{ $item['nama_pasien'] }}</strong>
                        </td>
                        <td>
                            @if($item['status_kehadiran'] == 'sudah_datang')
                                <span class="badge bg-success"><i class="bi bi-check-circle"></i> Sudah Datang</span>
                            @else
                                <span class="badge bg-warning"><i class="bi bi-exclamation-circle"></i> Belum Datang</span>
                            @endif
                        </td>
                        <td>
                            @if($item['asal_kehadiran'] == 'Mobile JKN')
                                <span class="badge bg-primary"><i class="bi bi-phone"></i> Mobile JKN</span>
                            @elseif($item['asal_kehadiran'] == 'ONSITE')
                                <span class="badge bg-info"><i class="bi bi-building"></i> ONSITE</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            @if($item['waktu_datang'])
                                <small>{{ $item['waktu_datang'] }}</small>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">
                            <i class="bi bi-inbox"></i> Tidak ada data kehadiran untuk tanggal ini
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
