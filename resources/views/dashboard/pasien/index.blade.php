@extends('layouts.app')

@section('title', 'Dashboard Pasien')

@section('content')
<div class="row mb-4">
    <div class="col-md-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="bi bi-people"></i> Dashboard Data Pasien</h2>
            <a href="{{ route('dashboard.pasien.index') }}" class="btn btn-sm btn-outline-primary">
                <i class="bi bi-arrow-clockwise"></i> Refresh
            </a>
        </div>
    </div>
</div>

<!-- Search and Filter -->
<div class="card mb-4">
    <div class="card-header">
        <i class="bi bi-funnel"></i> Pencarian & Filter
    </div>
    <div class="card-body">
        <form action="{{ route('dashboard.pasien.index') }}" method="GET" class="row g-3">
            <div class="col-md-5">
                <div class="search-box">
                    <i class="bi bi-search search-icon"></i>
                    <input type="text" name="search" class="form-control" placeholder="Cari nama, no rekam medis, no KTP, atau no telepon..."
                        value="{{ request('search') }}">
                </div>
            </div>

            <div class="col-md-3">
                <select name="jk" class="form-select">
                    <option value="">-- Pilih Jenis Kelamin --</option>
                    <option value="L" @if(request('jk') == 'L') selected @endif>Laki-laki</option>
                    <option value="P" @if(request('jk') == 'P') selected @endif>Perempuan</option>
                </select>
            </div>

            <div class="col-md-4">
                <select name="stts_nikah" class="form-select">
                    <option value="">-- Pilih Status Pernikahan --</option>
                    <option value="BELUM MENIKAH" @if(request('stts_nikah') == 'BELUM MENIKAH') selected @endif>Belum Menikah</option>
                    <option value="MENIKAH" @if(request('stts_nikah') == 'MENIKAH') selected @endif>Menikah</option>
                    <option value="JANDA" @if(request('stts_nikah') == 'JANDA') selected @endif>Janda</option>
                    <option value="DUDHA" @if(request('stts_nikah') == 'DUDHA') selected @endif>Dudha</option>
                    <option value="JOMBLO" @if(request('stts_nikah') == 'JOMBLO') selected @endif>Jomblo</option>
                </select>
            </div>

            <div class="col-md-12">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-search"></i> Cari
                </button>
                <a href="{{ route('dashboard.pasien.index') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-clockwise"></i> Reset
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Table -->
<div class="card">
    <div class="card-header">
        <i class="bi bi-table"></i> Data Pasien
        <span class="badge bg-secondary float-end">{{ $pasiens->total() }} Pasien</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>No. Rekam Medis</th>
                    <th>Nama Pasien</th>
                    <th>No. KTP</th>
                    <th>Jenis Kelamin</th>
                    <th>No. Telepon</th>
                    <th>Alamat</th>
                    <th>Asuransi</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pasiens as $index => $pasien)
                    <tr>
                        <td>{{ ($pasiens->currentPage() - 1) * $pasiens->perPage() + $loop->iteration }}</td>
                        <td>
                            <span class="badge bg-info">{{ $pasien->no_rkm_medis }}</span>
                        </td>
                        <td>
                            <strong>{{ $pasien->nm_pasien ?? '-' }}</strong>
                        </td>
                        <td>{{ $pasien->no_ktp ?? '-' }}</td>
                        <td>
                            @if($pasien->jk == 'L')
                                <span class="badge bg-primary">Laki-laki</span>
                            @else
                                <span class="badge bg-danger">Perempuan</span>
                            @endif
                        </td>
                        <td>
                            @if($pasien->no_tlp)
                                @php
                                    $phone = preg_replace('/[^0-9]/', '', $pasien->no_tlp);
                                    $phone = preg_replace('/^0/', '62', $phone);
                                @endphp
                                <a href="https://wa.me/{{ $phone }}" target="_blank" class="text-decoration-none">
                                    <i class="bi bi-whatsapp"></i> {{ $pasien->no_tlp }}
                                </a>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            <small>{{ substr($pasien->alamat ?? '-', 0, 30) }}{{ strlen($pasien->alamat ?? '') > 30 ? '...' : '' }}</small>
                        </td>
                        <td>
                            <span class="badge bg-success">{{ $pasien->png_jawab ?? $pasien->kd_pj ?? '-' }}</span>
                        </td>
                        <td>
                            <a href="{{ route('dashboard.pasien.show', $pasien->no_rkm_medis) }}" class="btn btn-sm btn-outline-primary" title="Detail Pasien">
                                <i class="bi bi-eye"></i> Detail
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted py-4">
                            <i class="bi bi-inbox"></i> Tidak ada data pasien
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if($pasiens->hasPages())
        <div class="card-footer">
            <nav aria-label="Pagination">
                {{ $pasiens->links('vendor.pagination.bootstrap-5') }}
            </nav>
        </div>
    @endif
</div>
@endsection
