@extends('layouts.app')

@section('title', 'Riwayat Pesan WhatsApp')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h3>
                <i class="bi bi-clock-history"></i> Riwayat Pesan WhatsApp
            </h3>
        </div>
        <div class="col-md-4">
            <div class="input-group">
                <input type="text" class="form-control" id="searchInput" placeholder="Cari nomor atau pesan...">
                <button class="btn btn-outline-secondary" type="button" id="searchBtn">
                    <i class="bi bi-search"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <label for="status_filter" class="form-label">Status</label>
                    <select class="form-select" id="status_filter">
                        <option value="">-- Semua Status --</option>
                        <option value="pending">Menunggu</option>
                        <option value="sent">Terkirim</option>
                        <option value="delivered">Diterima</option>
                        <option value="failed">Gagal</option>
                        <option value="read">Dibaca</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="date_from" class="form-label">Dari Tanggal</label>
                    <input type="date" class="form-control" id="date_from">
                </div>
                <div class="col-md-3">
                    <label for="date_to" class="form-label">Sampai Tanggal</label>
                    <input type="date" class="form-control" id="date_to">
                </div>
                <div class="col-md-3">
                    <label class="form-label">&nbsp;</label>
                    <button class="btn btn-primary w-100" id="filterBtn">
                        <i class="bi bi-funnel"></i> Filter
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-start border-5 border-success">
                <div class="card-body">
                    <h6 class="card-title text-muted">Terkirim</h6>
                    <h4 class="text-success" id="stat_sent">0</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-start border-5 border-warning">
                <div class="card-body">
                    <h6 class="card-title text-muted">Menunggu</h6>
                    <h4 class="text-warning" id="stat_pending">0</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-start border-5 border-info">
                <div class="card-body">
                    <h6 class="card-title text-muted">Diterima</h6>
                    <h4 class="text-info" id="stat_delivered">0</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-start border-5 border-danger">
                <div class="card-body">
                    <h6 class="card-title text-muted">Gagal</h6>
                    <h4 class="text-danger" id="stat_failed">0</h4>
                </div>
            </div>
        </div>
    </div>

    <!-- Messages Table -->
    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>No.</th>
                        <th>Nomor Telepon</th>
                        <th>Pesan</th>
                        <th>Status</th>
                        <th>Tanggal</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody id="messages_tbody">
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">
                            <i class="bi bi-inbox"></i> Memuat data...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <div class="card-footer bg-white">
            <nav aria-label="Page navigation">
                <ul class="pagination mb-0" id="pagination_container">
                    <li class="page-item disabled">
                        <span class="page-link">Loading...</span>
                    </li>
                </ul>
            </nav>
        </div>
    </div>
</div>

<!-- Message Detail Modal -->
<div class="modal fade" id="detailModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail Pesan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="modal_body">
                <!-- Content loaded via AJAX -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-danger" id="resendBtn" style="display: none;">
                    <i class="bi bi-arrow-repeat"></i> Kirim Ulang
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let currentPage = 1;
let currentFilters = {};

// Wait for DOM to be ready
document.addEventListener('DOMContentLoaded', function() {
    // Set date filters to today and 7 days ago
    const today = new Date();
    const sevenDaysAgo = new Date(today.getTime() - 7 * 24 * 60 * 60 * 1000);
    document.getElementById('date_from').value = sevenDaysAgo.toISOString().split('T')[0];
    document.getElementById('date_to').value = today.toISOString().split('T')[0];

    // Load messages on page load
    loadMessages();

    // Event listeners
    document.getElementById('filterBtn').addEventListener('click', () => {
        currentPage = 1;
        loadMessages();
    });

    document.getElementById('searchBtn').addEventListener('click', () => {
        currentPage = 1;
        loadMessages();
    });

    document.getElementById('searchInput').addEventListener('keypress', (e) => {
        if (e.key === 'Enter') {
            currentPage = 1;
            loadMessages();
        }
    });

    [document.getElementById('status_filter'), document.getElementById('date_from'), document.getElementById('date_to')].forEach(el => {
        el.addEventListener('change', () => {
            currentPage = 1;
            loadMessages();
        });
    });
});

// Helper function for safe localStorage access
function getSafeLocalStorage(key, defaultValue = '') {
    try {
        return localStorage.getItem(key) || defaultValue;
    } catch (e) {
        console.warn(`localStorage access blocked for ${key}:`, e);
        return defaultValue;
    }
}

async function loadMessages(page = 1) {
    const filters = {
        status: document.getElementById('status_filter').value || '',
        from_date: document.getElementById('date_from').value || '',
        to_date: document.getElementById('date_to').value || '',
        search: document.getElementById('searchInput').value || '',
        page: page,
        per_page: 15,
    };

    try {
        const queryString = new URLSearchParams(filters).toString();
        
        // Safe localStorage access with fallback
        let authHeader = '';
        try {
            const apiToken = localStorage.getItem('api_token');
            if (apiToken) {
                authHeader = 'Bearer ' + apiToken;
            }
        } catch (e) {
            console.warn('localStorage not available:', e.message);
        }
        
        const response = await fetch(`{{ route('whatsapp.history') }}?${queryString}`, {
            headers: {
                ...(authHeader ? { 'Authorization': authHeader } : {}),
                'Accept': 'application/json',
            }
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const data = await response.json();
        console.log('Response data:', data);

        if (data.success) {
            try {
                displayMessages(data.data || []);
                if (data.statistics) {
                    updateStatistics(data.statistics);
                }
                if (data.pagination) {
                    updatePagination(data.pagination);
                }
                currentPage = page;
            } catch (renderError) {
                console.error('Render error:', renderError);
                showError('Error displaying messages: ' + renderError.message);
            }
        } else {
            showError('Gagal memuat data pesan: ' + (data.message || 'Unknown error'));
        }
    } catch (error) {
        console.error('Fetch error:', error);
        showError('Terjadi kesalahan: ' + error.message);
    }
}

function displayMessages(messages) {
    const tbody = document.getElementById('messages_tbody');
    
    if (messages.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="6" class="text-center py-4 text-muted">
                    <i class="bi bi-inbox"></i> Tidak ada pesan
                </td>
            </tr>
        `;
        return;
    }

    tbody.innerHTML = messages.map((msg, index) => `
        <tr>
            <td>${index + 1}</td>
            <td>
                <strong>${msg.no_tlp}</strong>
            </td>
            <td>
                <small>${msg.message ? msg.message.substring(0, 50) : '(tidak ada pesan)'}${msg.message && msg.message.length > 50 ? '...' : ''}</small>
            </td>
            <td>
                ${getStatusBadge(msg.status)}
            </td>
            <td>
                <small>${new Date(msg.created_at).toLocaleString('id-ID')}</small>
            </td>
            <td>
                <button class="btn btn-sm btn-info" onclick="showDetail('${msg.id}')">
                    <i class="bi bi-eye"></i> Detail
                </button>
                ${msg.status === 'failed' ? `
                    <button class="btn btn-sm btn-warning" onclick="resendMessage('${msg.id}')">
                        <i class="bi bi-arrow-repeat"></i> Ulang
                    </button>
                ` : ''}
            </td>
        </tr>
    `).join('');
}

function updateStatistics(stats) {
    document.getElementById('stat_sent').textContent = stats.sent || 0;
    document.getElementById('stat_pending').textContent = stats.pending || 0;
    document.getElementById('stat_delivered').textContent = stats.delivered || 0;
    document.getElementById('stat_failed').textContent = stats.failed || 0;
}

function updatePagination(pagination) {
    const container = document.getElementById('pagination_container');
    let html = '';

    // Previous button
    if (pagination.current_page > 1) {
        html += `<li class="page-item"><a class="page-link" href="#" onclick="loadMessages(${pagination.current_page - 1}); return false;">← Sebelumnya</a></li>`;
    } else {
        html += `<li class="page-item disabled"><span class="page-link">← Sebelumnya</span></li>`;
    }

    // Page numbers
    for (let i = Math.max(1, pagination.current_page - 2); i <= Math.min(pagination.last_page, pagination.current_page + 2); i++) {
        if (i === pagination.current_page) {
            html += `<li class="page-item active"><span class="page-link">${i}</span></li>`;
        } else {
            html += `<li class="page-item"><a class="page-link" href="#" onclick="loadMessages(${i}); return false;">${i}</a></li>`;
        }
    }

    // Next button
    if (pagination.current_page < pagination.last_page) {
        html += `<li class="page-item"><a class="page-link" href="#" onclick="loadMessages(${pagination.current_page + 1}); return false;">Selanjutnya →</a></li>`;
    } else {
        html += `<li class="page-item disabled"><span class="page-link">Selanjutnya →</span></li>`;
    }

    container.innerHTML = html;
}

function getStatusBadge(status) {
    const badges = {
        'sent': '<span class="badge bg-success">Terkirim</span>',
        'pending': '<span class="badge bg-warning">Menunggu</span>',
        'delivered': '<span class="badge bg-info">Diterima</span>',
        'read': '<span class="badge bg-primary">Dibaca</span>',
        'failed': '<span class="badge bg-danger">Gagal</span>',
    };
    return badges[status] || '<span class="badge bg-secondary">Unknown</span>';
}

async function showDetail(messageId) {
    try {
        const response = await fetch(`/whatsapp/message/${messageId}`, {
            headers: {
                'Accept': 'application/json',
            }
        });

        // Check if response is ok
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        // Check content type before parsing JSON
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            const text = await response.text();
            throw new Error('Invalid response type: ' + contentType);
        }

        const data = await response.json();

        if (data.success) {
            const msg = data.data;
            const modal = document.getElementById('detailModal');
            
            let html = `
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="text-muted small">Nomor Telepon</label>
                        <p class="h6">${msg.no_tlp}</p>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small">Status</label>
                        <p class="h6">${getStatusBadge(msg.status)}</p>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="text-muted small">Pesan</label>
                    <p>${msg.message || msg.pesan || '(tidak ada pesan)'}</p>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="text-muted small">Dikirim</label>
                        <p class="small">${new Date(msg.created_at).toLocaleString('id-ID')}</p>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small">Diterima</label>
                        <p class="small">${msg.delivered_at ? new Date(msg.delivered_at).toLocaleString('id-ID') : '-'}</p>
                    </div>
                </div>

                ${msg.error_message ? `
                    <div class="alert alert-danger">
                        <strong>Error:</strong> ${msg.error_message}
                    </div>
                ` : ''}

                ${msg.external_message_id ? `
                    <div class="row">
                        <div class="col-md-12">
                            <label class="text-muted small">ID Pesan</label>
                            <p class="small"><code>${msg.external_message_id}</code></p>
                        </div>
                    </div>
                ` : ''}
            `;

            document.getElementById('modal_body').innerHTML = html;

            // Show resend button only if failed
            const resendBtn = document.getElementById('resendBtn');
            if (msg.status === 'failed') {
                resendBtn.style.display = 'inline-block';
                resendBtn.onclick = () => resendMessage(msg.id);
            } else {
                resendBtn.style.display = 'none';
            }

            // Show modal
            const modalElement = document.getElementById('detailModal');
            const modalInstance = new bootstrap.Modal(modalElement);
            modalInstance.show();
            
            // Fix aria-hidden issue by removing focus from buttons before hiding
            modalElement.addEventListener('hide.bs.modal', function(e) {
                // Move focus to a safe place
                if (document.activeElement && modalElement.contains(document.activeElement)) {
                    document.body.focus();
                }
            });
        } else {
            showError('Gagal memuat detail: ' + (data.message || 'Unknown error'));
        }
    } catch (error) {
        console.error('Detail error:', error);
        showError('Gagal memuat detail: ' + error.message);
    }
}

async function resendMessage(messageId) {
    if (!confirm('Yakin ingin mengirim ulang pesan ini?')) return;

    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
        const response = await fetch(`/whatsapp/message/${messageId}/resend`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            }
        });

        // Check if response is ok
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        // Check content type before parsing JSON
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            const text = await response.text();
            throw new Error('Invalid response type: ' + contentType + '. Response: ' + text.substring(0, 100));
        }

        const data = await response.json();

        if (data.success) {
            showSuccess('Pesan berhasil dikirim ulang');
            
            // Close modal safely
            const detailModal = document.getElementById('detailModal');
            const modal = bootstrap.Modal.getInstance(detailModal);
            if (modal) {
                // Move focus before hiding modal
                if (document.activeElement && detailModal.contains(document.activeElement)) {
                    document.body.focus();
                }
                modal.hide();
            }
            
            // Reload data langsung karena pesan sudah terkirim
            loadMessages(currentPage);
        } else {
            showError(data.message || 'Gagal mengirim ulang');
        }
    } catch (error) {
        console.error('Resend error:', error);
        showError('Terjadi kesalahan: ' + error.message);
    }
}

function showSuccess(message) {
    console.log('Success:', message);
    
    const alert = document.createElement('div');
    alert.className = 'alert alert-success alert-dismissible fade show';
    alert.role = 'alert';
    alert.innerHTML = `
        <i class="bi bi-check-circle"></i> ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    try {
        // Try to insert at the top of body
        const body = document.body;
        if (body) {
            body.insertBefore(alert, body.firstChild);
        }
    } catch (e) {
        console.error('Could not insert alert:', e);
        // As fallback, just append to body
        try {
            document.body.appendChild(alert);
        } catch (e2) {
            alert('Success: ' + message);
        }
    }
    
    setTimeout(() => {
        try {
            if (alert.parentNode) {
                alert.parentNode.removeChild(alert);
            }
        } catch (e) {
            console.error('Could not remove alert:', e);
        }
    }, 3000);
}

function showError(message) {
    console.error('Error:', message);
    
    const alert = document.createElement('div');
    alert.className = 'alert alert-danger alert-dismissible fade show';
    alert.role = 'alert';
    alert.innerHTML = `
        <i class="bi bi-exclamation-circle"></i> ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    try {
        // Try to insert at the top of body
        const body = document.body;
        if (body) {
            body.insertBefore(alert, body.firstChild);
        }
    } catch (e) {
        console.error('Could not insert alert:', e);
        // As fallback, just append to body
        try {
            document.body.appendChild(alert);
        } catch (e2) {
            alert('Error: ' + message);
        }
    }
    
    setTimeout(() => {
        try {
            if (alert.parentNode) {
                alert.parentNode.removeChild(alert);
            }
        } catch (e) {
            console.error('Could not remove alert:', e);
        }
    }, 3000);
}
</script>
@endsection
