@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="h3 mb-0">
                <i class="fas fa-birthday-cake text-primary"></i> Dashboard Pengingat Ulang Tahun
            </h1>
            <p class="text-muted mt-2">Kelola pengiriman pesan pengingat ulang tahun kepada pasien</p>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('dashboard.birthday-reminder.create') }}" class="btn btn-primary me-2">
                <i class="fas fa-plus"></i> Tambah Pengingat
            </a>
            <form action="{{ route('dashboard.birthday-reminder.sync') }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-secondary">
                    <i class="fas fa-sync"></i> Sinkronisasi Data
                </button>
            </form>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-left-primary">
                <div class="card-body">
                    <div class="text-primary font-weight-bold text-lg">{{ $todayCount }}</div>
                    <div class="text-muted small">Ulang Tahun Hari Ini</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-left-info">
                <div class="card-body">
                    <div class="text-info font-weight-bold text-lg">{{ $weekCount }}</div>
                    <div class="text-muted small">Ulang Tahun Minggu Ini</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-left-warning">
                <div class="card-body">
                    <div class="text-warning font-weight-bold text-lg">{{ $pendingCount }}</div>
                    <div class="text-muted small">Pesan Pending</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-left-success">
                <div class="card-body">
                    <div class="text-success font-weight-bold text-lg">{{ $sentCount }}</div>
                    <div class="text-muted small">Pesan Terkirim</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Alerts -->
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle"></i> <strong>Error!</strong>
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (session('warning'))
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <i class="fas fa-info-circle"></i> {{ session('warning') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Filter Section -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-bold">Filter Tanggal</label>
                    <div class="btn-group w-100" role="group">
                        <a href="{{ route('dashboard.birthday-reminder.index', ['filter' => 'today']) }}"
                            class="btn btn-outline-primary {{ $filter === 'today' ? 'active' : '' }}">
                            Hari Ini
                        </a>
                        <a href="{{ route('dashboard.birthday-reminder.index', ['filter' => 'week']) }}"
                            class="btn btn-outline-primary {{ $filter === 'week' ? 'active' : '' }}">
                            Minggu Ini
                        </a>
                        <a href="{{ route('dashboard.birthday-reminder.index', ['filter' => 'month']) }}"
                            class="btn btn-outline-primary {{ $filter === 'month' ? 'active' : '' }}">
                            Bulan Ini
                        </a>
                        <a href="{{ route('dashboard.birthday-reminder.index', ['filter' => 'all']) }}"
                            class="btn btn-outline-primary {{ $filter === 'all' ? 'active' : '' }}">
                            Semua
                        </a>
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Filter Status</label>
                    <select class="form-select" id="statusFilter" onchange="filterByStatus(this.value)">
                        <option value="">Semua Status</option>
                        <option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="sent" {{ $status === 'sent' ? 'selected' : '' }}>Terkirim</option>
                        <option value="failed" {{ $status === 'failed' ? 'selected' : '' }}>Gagal</option>
                        <option value="scheduled" {{ $status === 'scheduled' ? 'selected' : '' }}>Terjadwal</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Table Section -->
    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 5%">No</th>
                        <th style="width: 20%">Pasien</th>
                        <th style="width: 15%">No RM</th>
                        <th style="width: 15%">Tanggal Lahir</th>
                        <th style="width: 20%">No WhatsApp</th>
                        <th style="width: 10%">Status</th>
                        <th style="width: 15%">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @if ($reminders->count() > 0)
                        @foreach ($reminders as $index => $reminder)
                            <tr>
                                <td>{{ ($reminders->currentPage() - 1) * $reminders->perPage() + $index + 1 }}</td>
                                <td>
                                    <strong>{{ $reminder->patient->nm_pasien ?? 'N/A' }}</strong>
                                </td>
                                <td>{{ $reminder->no_rkm_medis }}</td>
                                <td>
                                    <span class="badge bg-light text-dark">
                                        {{ $reminder->birthday_date->format('d M Y') }}
                                    </span>
                                </td>
                                <td>
                                    <code>{{ $reminder->receiver_phone }}</code>
                                </td>
                                <td>
                                    @if ($reminder->status === 'pending')
                                        <span class="badge bg-warning">Pending</span>
                                    @elseif ($reminder->status === 'sent')
                                        <span class="badge bg-success">Terkirim</span>
                                    @elseif ($reminder->status === 'failed')
                                        <span class="badge bg-danger">Gagal</span>
                                    @elseif ($reminder->status === 'scheduled')
                                        <span class="badge bg-info">Terjadwal</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($reminder->status === 'pending')
                                        <form action="{{ route('dashboard.birthday-reminder.send', $reminder) }}"
                                            method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success"
                                                title="Kirim Sekarang"
                                                onclick="return confirm('Kirim pesan pengingat sekarang?')">
                                                <i class="fas fa-paper-plane"></i> Kirim
                                            </button>
                                        </form>
                                    @endif

                                    <!-- Detail Button -->
                                    <button type="button" class="btn btn-sm btn-info"
                                        data-bs-toggle="modal" data-bs-target="#detailModal{{ $reminder->id }}"
                                        title="Lihat Detail">
                                        <i class="fas fa-eye"></i>
                                    </button>

                                    <!-- Delete Button -->
                                    <form action="{{ route('dashboard.birthday-reminder.destroy', $reminder) }}"
                                        method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger"
                                            title="Hapus"
                                            onclick="return confirm('Hapus pengingat ini?')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>

                            <!-- Detail Modal -->
                            <div class="modal fade" id="detailModal{{ $reminder->id }}" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">
                                                <i class="fas fa-birthday-cake"></i>
                                                Detail Pengingat Ulang Tahun
                                            </h5>
                                            <button type="button" class="btn-close"
                                                data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Nama Pasien</label>
                                                <p class="form-control-plaintext">
                                                    {{ $reminder->patient->nm_pasien ?? 'N/A' }}
                                                </p>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">No RM</label>
                                                <p class="form-control-plaintext">{{ $reminder->no_rkm_medis }}</p>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Tanggal Lahir</label>
                                                <p class="form-control-plaintext">
                                                    {{ \Carbon\Carbon::parse($reminder->birthday_date)->translatedFormat('d F Y') }}
                                                </p>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">No WhatsApp</label>
                                                <p class="form-control-plaintext">{{ $reminder->receiver_phone }}</p>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Pesan</label>
                                                <p class="form-control-plaintext">
                                                    <em>{{ $reminder->message }}</em>
                                                </p>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Status</label>
                                                <p class="form-control-plaintext">
                                                    @if ($reminder->status === 'pending')
                                                        <span class="badge bg-warning">Pending</span>
                                                    @elseif ($reminder->status === 'sent')
                                                        <span class="badge bg-success">Terkirim</span>
                                                    @elseif ($reminder->status === 'failed')
                                                        <span class="badge bg-danger">Gagal</span>
                                                    @elseif ($reminder->status === 'scheduled')
                                                        <span class="badge bg-info">Terjadwal</span>
                                                    @endif
                                                </p>
                                            </div>
                                            @if ($reminder->sent_at)
                                                <div class="mb-3">
                                                    <label class="form-label fw-bold">Waktu Pengiriman</label>
                                                    <p class="form-control-plaintext">
                                                        {{ $reminder->sent_at->format('d M Y H:i:s') }}
                                                    </p>
                                                </div>
                                            @endif
                                            @if ($reminder->response)
                                                <div class="mb-3">
                                                    <label class="form-label fw-bold">Response API</label>
                                                    <pre
                                                        class="form-control-plaintext bg-light p-2 rounded"><small>{{ json_encode(json_decode($reminder->response), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</small></pre>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary"
                                                data-bs-dismiss="modal">Tutup</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                <i class="fas fa-inbox text-muted" style="font-size: 2em;"></i>
                                <p class="text-muted mt-2">Tidak ada data pengingat ulang tahun</p>
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-light">
            {{ $reminders->links() }}
        </div>
    </div>
</div>

<style>
    .border-left-primary {
        border-left: 4px solid #007bff;
    }

    .border-left-info {
        border-left: 4px solid #17a2b8;
    }

    .border-left-warning {
        border-left: 4px solid #ffc107;
    }

    .border-left-success {
        border-left: 4px solid #28a745;
    }

    .card-body {
        padding: 1.5rem;
    }

    .text-lg {
        font-size: 1.5rem;
    }

    .font-weight-bold {
        font-weight: 600;
    }
</style>

<script>
    function filterByStatus(status) {
        const params = new URLSearchParams(window.location.search);
        params.set('status', status);
        window.location.href = window.location.pathname + '?' + params.toString();
    }
</script>
@endsection
