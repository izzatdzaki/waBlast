@extends('layouts.app')

@section('title', 'WhatsApp Dashboard')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="h3 mb-0">
                <i class="bi bi-chat-left-dots"></i> WhatsApp Messaging Dashboard
            </h1>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('whatsapp.send') }}" class="btn btn-success me-2">
                <i class="bi bi-send"></i> Kirim Pesan
            </a>
            <a href="{{ route('whatsapp.schedule') }}" class="btn btn-info me-2">
                <i class="bi bi-calendar-check"></i> Jadwalkan
            </a>
            <a href="{{ route('whatsapp.settings') }}" class="btn btn-secondary">
                <i class="bi bi-gear"></i> Pengaturan
            </a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-left-success">
                <div class="card-body">
                    <h6 class="card-title text-muted mb-1">Terkirim</h6>
                    <h2 class="mb-0 text-success">{{ $stats['total_sent'] }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-left-warning">
                <div class="card-body">
                    <h6 class="card-title text-muted mb-1">Menunggu</h6>
                    <h2 class="mb-0 text-warning">{{ $stats['total_pending'] }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-left-info">
                <div class="card-body">
                    <h6 class="card-title text-muted mb-1">Diterima</h6>
                    <h2 class="mb-0 text-info">{{ $stats['total_delivered'] }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-left-danger">
                <div class="card-body">
                    <h6 class="card-title text-muted mb-1">Gagal</h6>
                    <h2 class="mb-0 text-danger">{{ $stats['total_failed'] }}</h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabs -->
    <div class="row">
        <div class="col-12">
            <ul class="nav nav-tabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="messages-tab" data-bs-toggle="tab" 
                            data-bs-target="#messages" type="button" role="tab">
                        <i class="bi bi-chat-dots"></i> Pesan Terbaru
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="templates-tab" data-bs-toggle="tab" 
                            data-bs-target="#templates" type="button" role="tab">
                        <i class="bi bi-file-text"></i> Template ({{ $templates->count() }})
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="history-tab" data-bs-toggle="tab" 
                            data-bs-target="#history" type="button" role="tab">
                        <i class="bi bi-clock-history"></i> Riwayat Lengkap
                    </button>
                </li>
            </ul>

            <div class="tab-content">
                <!-- Recent Messages Tab -->
                <div class="tab-pane fade show active" id="messages" role="tabpanel">
                    <div class="card mt-3">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">Pesan Terbaru</h5>
                        </div>
                        <div class="card-body p-0">
                            @forelse($recent_messages as $message)
                                <div class="message-item p-3 border-bottom">
                                    <div class="row align-items-center">
                                        <div class="col-md-4">
                                            <div class="fw-bold">
                                                <i class="bi bi-telephone"></i> 
                                                {{ $message->no_tlp ?: ($message->pasien->no_tlp ?? 'N/A') }}
                                            </div>
                                            @if($message->pasien)
                                                <small class="text-muted">
                                                    {{ $message->pasien->nm_pasien }}
                                                </small>
                                            @endif
                                        </div>
                                        <div class="col-md-4">
                                            <small class="text-truncate d-block">
                                                {{ Str::limit($message->message, 1000) }}
                                            </small>
                                        </div>
                                        <div class="col-md-2 text-center">
                                            <span class="badge bg-{{ $message->status === 'sent' ? 'success' : ($message->status === 'failed' ? 'danger' : 'warning') }}">
                                                {{ $message->getStatusLabel() }}
                                            </span>
                                        </div>
                                        <div class="col-md-2 text-end">
                                            <small class="text-muted">
                                                {{ $message->created_at->diffForHumans() }}
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="p-4 text-center text-muted">
                                    <i class="bi bi-inbox" style="font-size: 2rem;"></i>
                                    <p class="mt-2">Belum ada pesan</p>
                                </div>
                            @endforelse
                        </div>
                        <div class="card-footer bg-light text-center">
                            <a href="{{ route('whatsapp.history') }}" class="btn btn-sm btn-outline-primary">
                                Lihat Semua Riwayat
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Templates Tab -->
                <div class="tab-pane fade" id="templates" role="tabpanel">
                    <div class="card mt-3">
                        <div class="card-header bg-light d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Template Pesan</h5>
                            <a href="{{ route('whatsapp.templates') }}" class="btn btn-sm btn-primary">
                                <i class="bi bi-plus"></i> Tambah Template
                            </a>
                        </div>
                        <div class="card-body">
                            @forelse($templates->take(5) as $template)
                                <div class="template-item p-3 border rounded mb-2">
                                    <div class="row">
                                        <div class="col-md-9">
                                            <h6 class="mb-1">{{ $template->nama_template }}</h6>
                                            <p class="mb-1 text-muted small">
                                                {{ Str::limit($template->isi_pesan, 100) }}
                                            </p>
                                            @if(!empty($template->placeholder_variables))
                                                <small class="text-info">
                                                    Variabel: {{ implode(', ', is_array($template->placeholder_variables) ? array_map('strval', $template->placeholder_variables) : []) }}
                                                </small>
                                            @endif
                                        </div>
                                        <div class="col-md-3 text-end">
                                            <a href="{{ route('whatsapp.templates') }}" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-pencil"></i> Edit
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <p class="text-muted text-center">Belum ada template</p>
                            @endforelse
                        </div>
                        <div class="card-footer bg-light text-center">
                            <a href="{{ route('whatsapp.templates') }}" class="btn btn-sm btn-outline-primary">
                                Kelola Semua Template
                            </a>
                        </div>
                    </div>
                </div>

                <!-- History Tab -->
                <div class="tab-pane fade" id="history" role="tabpanel">
                    <div class="card mt-3">
                        <div class="card-body">
                            <p class="text-muted">
                                <i class="bi bi-arrow-right"></i>
                                <a href="{{ route('whatsapp.history') }}">Buka halaman riwayat lengkap</a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .border-left-success {
        border-left: 4px solid #28a745 !important;
    }
    .border-left-warning {
        border-left: 4px solid #ffc107 !important;
    }
    .border-left-info {
        border-left: 4px solid #17a2b8 !important;
    }
    .border-left-danger {
        border-left: 4px solid #dc3545 !important;
    }
</style>
@endsection
