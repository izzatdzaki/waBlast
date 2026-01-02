@extends('layouts.app')

@section('title', 'Dashboard Mobile BPJS')

@section('content')
<div class="row mb-4">
    <div class="col-md-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="bi bi-phone"></i> Pasien Mobile BPJS</h2>
            <a href="{{ route('dashboard.mobile_bpjs.index') }}" class="btn btn-sm btn-outline-primary">
                <i class="bi bi-arrow-clockwise"></i> Refresh
            </a>
        </div>
    </div>
</div>

<!-- Statistics -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="stat-card stat-card-total">
            <h3>{{ $stats['total'] }}</h3>
            <p>Total Antrian Hari Ini</p>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card text-center" style="padding: 12px;">
            <small class="text-muted">Tanggal:</small>
            <strong>{{ \Carbon\Carbon::parse($date)->format('d M Y') }}</strong>
        </div>
    </div>
</div>

<!-- Date Filter -->
<div class="card mb-4">
    <div class="card-header">
        <i class="bi bi-calendar"></i> Filter
    </div>
    <div class="card-body">
        <form action="{{ route('dashboard.mobile_bpjs.index') }}" method="GET" class="row g-2">
            <div class="col-md-3">
                <input type="date" name="date" class="form-control" value="{{ $date }}">
            </div>
            <div class="col-md-3">
                <input type="text" name="search" class="form-control" placeholder="Cari nama, No. KTP, No. RM, No. Referensi" value="{{ $search }}">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-search"></i> Cari
                </button>
            </div>
            <div class="col-md-2">
                <a href="{{ route('dashboard.mobile_bpjs.index') }}" class="btn btn-secondary w-100">
                    <i class="bi bi-arrow-clockwise"></i> Reset
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Mobile BPJS Table -->
<div class="card">
    <div class="card-header">
        <i class="bi bi-table"></i> Data Pasien Mobile BPJS
        <span class="badge bg-secondary float-end">{{ $mobile_bpjs_data->total() }} Pasien</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>No. Referensi</th>
                    <th>No. RM</th>
                    <th>Nama Pasien</th>
                    <th>Jenis Kunjungan</th>
                    <th>No. Telepon</th>
                    <th>Tanggal Periksa</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($mobile_bpjs_data as $index => $item)
                    <tr>
                        <td>{{ $mobile_bpjs_data->firstItem() + $index }}</td>
                        <td>
                            <span class="badge bg-info">{{ $item->nomorreferensi }}</span>
                        </td>
                        <td>{{ $item->no_rkm_medis ?? '-' }}</td>
                        <td>
                            <strong>{{ $item->nm_pasien ?? '-' }}</strong>
                        </td>
                        <td>
                            @if($item->jeniskunjungan)
                                <span class="badge bg-secondary">{{ $item->jeniskunjungan }}</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            @if($item->no_tlp)
                                <a href="https://wa.me/{{ preg_replace('/^0/', '62', $item->no_tlp) }}" target="_blank" class="btn btn-sm btn-outline-success" title="Chat WhatsApp">
                                    <i class="bi bi-whatsapp"></i>
                                </a>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            <small>{{ \Carbon\Carbon::parse($item->tanggalperiksa)->format('d M Y') }}</small>
                        </td>
                        <td>
                            @if($item->status)
                                @if(strtolower($item->status) == 'batal')
                                    <span class="badge bg-danger">{{ $item->status }}</span>
                                @else
                                    <span class="badge bg-success">{{ $item->status }}</span>
                                @endif
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        <td>
                            <a href="{{ route('dashboard.mobile_bpjs.show', $item->nomorreferensi) }}" class="btn btn-sm btn-outline-primary" title="Lihat Detail">
                                <i class="bi bi-eye"></i>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">
                            <i class="bi bi-inbox"></i> Tidak ada data pasien Mobile BPJS untuk tanggal ini
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if($mobile_bpjs_data->hasPages())
        <div class="card-footer">
            {{ $mobile_bpjs_data->links('vendor.pagination.bootstrap-5') }}
        </div>
    @endif
</div>
@endsection
