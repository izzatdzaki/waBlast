@extends('layouts.app')

@section('title', 'Manajemen Template Pesan WhatsApp')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h3>
                <i class="bi bi-file-text"></i> Template Pesan WhatsApp
            </h3>
            <small class="text-muted">Kelola template pesan untuk pengiriman cepat dengan variabel dinamis</small>
        </div>
        <div class="col-md-4 text-end">
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTemplateModal">
                <i class="bi bi-plus-circle"></i> Tambah Template
            </button>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <h6 class="alert-heading">Terjadi Kesalahan!</h6>
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Templates Grid -->
    <div class="row" id="templates_container">
        <!-- Loaded via JavaScript -->
    </div>
</div>

<!-- Add/Edit Template Modal -->
<div class="modal fade" id="addTemplateModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Tambah Template Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="templateForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="template_name" class="form-label">Nama Template</label>
                        <input type="text" class="form-control" id="template_name" name="nama_template" required>
                        <small class="text-muted">Contoh: Reminder Jadwal Konsultasi</small>
                    </div>

                    <div class="mb-3">
                        <label for="template_content" class="form-label">Isi Pesan</label>
                        <textarea class="form-control" id="template_content" name="isi_pesan" rows="6" required></textarea>
                        <small class="text-muted">
                            Gunakan <code>{nama}</code>, <code>{tanggal}</code>, <code>{jam}</code> untuk variabel dinamis.
                            <br>Placeholder akan otomatis terdeteksi.
                        </small>
                    </div>

                    <!-- Detected Variables -->
                    <div id="detected_variables_section" style="display: none;">
                        <label class="form-label">Variabel Terdeteksi</label>
                        <div id="detected_variables" class="mb-3">
                            <!-- Populated by JavaScript -->
                        </div>
                    </div>

                    <!-- Character Count -->
                    <div class="mb-3">
                        <small class="text-muted">
                            <strong id="char_count">0</strong> / 4096 karakter
                        </small>
                    </div>

                    <!-- Preview -->
                    <div id="preview_section" class="alert alert-info" style="display: none;">
                        <strong>Preview:</strong>
                        <p id="preview_text" class="mb-0 mt-2"></p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="submitBtn">
                        <i class="bi bi-check"></i> Simpan Template
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Hapus Template</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Apakah Anda yakin ingin menghapus template ini?</p>
                <p class="text-muted small" id="delete_template_name"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">
                    <i class="bi bi-trash"></i> Hapus
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let currentEditingId = null;
let currentDeletingId = null;

// Load templates on page load
loadTemplates();

document.getElementById('template_content').addEventListener('input', function() {
    document.getElementById('char_count').textContent = this.value.length;
    detectVariables();
    updatePreview();
});

document.getElementById('addTemplateModal').addEventListener('show.bs.modal', function() {
    document.getElementById('templateForm').reset();
    document.getElementById('modalTitle').textContent = 'Tambah Template Baru';
    currentEditingId = null;
    document.getElementById('detected_variables_section').style.display = 'none';
    document.getElementById('preview_section').style.display = 'none';
});

document.getElementById('templateForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const name = document.getElementById('template_name').value;
    const content = document.getElementById('template_content').value;
    
    if (!name || !content) {
        alert('Mohon isi semua field');
        return;
    }

    const submitBtn = document.getElementById('submitBtn');
    const originalText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Menyimpan...';

    const url = currentEditingId 
        ? `{{ route('whatsapp.template.update', '') }}/${currentEditingId}`
        : '{{ route("whatsapp.template.create") }}';
    
    const method = currentEditingId ? 'PUT' : 'POST';

    try {
        const response = await fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Authorization': 'Bearer ' + (localStorage.getItem('api_token') || ''),
            },
            body: JSON.stringify({
                nama_template: name,
                isi_pesan: content,
            })
        });

        const data = await response.json();

        if (data.success) {
            showSuccess(currentEditingId ? 'Template berhasil diperbarui' : 'Template berhasil ditambahkan');
            bootstrap.Modal.getInstance(document.getElementById('addTemplateModal')).hide();
            loadTemplates();
        } else {
            showError(data.message || 'Gagal menyimpan template');
        }
    } catch (error) {
        showError('Terjadi kesalahan: ' + error.message);
    } finally {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    }
});

document.getElementById('confirmDeleteBtn').addEventListener('click', async function() {
    if (!currentDeletingId) return;

    const btn = this;
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Menghapus...';

    try {
        const response = await fetch(`{{ route('whatsapp.template.delete', '') }}/${currentDeletingId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Authorization': 'Bearer ' + (localStorage.getItem('api_token') || ''),
            }
        });

        const data = await response.json();

        if (data.success) {
            showSuccess('Template berhasil dihapus');
            bootstrap.Modal.getInstance(document.getElementById('deleteModal')).hide();
            loadTemplates();
        } else {
            showError(data.message || 'Gagal menghapus template');
        }
    } catch (error) {
        showError('Terjadi kesalahan: ' + error.message);
    } finally {
        btn.disabled = false;
        btn.innerHTML = originalText;
    }
});

async function loadTemplates() {
    try {
        const response = await fetch('/api/whatsapp/templates', {
            headers: {
                'Authorization': 'Bearer ' + (localStorage.getItem('api_token') || ''),
                'Accept': 'application/json',
            }
        });

        const data = await response.json();

        if (data.success) {
            displayTemplates(data.data);
        }
    } catch (error) {
        console.error('Error loading templates:', error);
        document.getElementById('templates_container').innerHTML = `
            <div class="col-12">
                <div class="alert alert-danger">Gagal memuat template: ${error.message}</div>
            </div>
        `;
    }
}

function displayTemplates(templates) {
    const container = document.getElementById('templates_container');
    
    if (templates.length === 0) {
        container.innerHTML = `
            <div class="col-12">
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i> Belum ada template. Klik "Tambah Template" untuk membuat template baru.
                </div>
            </div>
        `;
        return;
    }

    container.innerHTML = templates.map(template => `
        <div class="col-md-6 col-lg-4 mb-3">
            <div class="card h-100">
                <div class="card-body">
                    <h6 class="card-title">${escapeHtml(template.nama_template)}</h6>
                    <p class="card-text small text-muted">
                        ${escapeHtml(template.isi_pesan.substring(0, 100))}${template.isi_pesan.length > 100 ? '...' : ''}
                    </p>
                </div>
                <div class="card-footer bg-light">
                    <div class="btn-group btn-group-sm w-100" role="group">
                        <button type="button" class="btn btn-outline-primary" onclick="editTemplate('${template.id}')">
                            <i class="bi bi-pencil"></i> Edit
                        </button>
                        <button type="button" class="btn btn-outline-info" onclick="showPreview('${template.id}')">
                            <i class="bi bi-eye"></i> Preview
                        </button>
                        <button type="button" class="btn btn-outline-danger" onclick="confirmDelete('${template.id}', '${escapeHtml(template.nama_template)}')">
                            <i class="bi bi-trash"></i> Hapus
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `).join('');
}

async function editTemplate(templateId) {
    try {
        const response = await fetch(`{{ route('whatsapp.template.show', '') }}/${templateId}`, {
            headers: {
                'Authorization': 'Bearer ' + (localStorage.getItem('api_token') || ''),
                'Accept': 'application/json',
            }
        });

        const data = await response.json();

        if (data.success) {
            const template = data.data;
            currentEditingId = template.id;
            
            document.getElementById('template_name').value = template.nama_template;
            document.getElementById('template_content').value = template.isi_pesan;
            document.getElementById('char_count').textContent = template.isi_pesan.length;
            document.getElementById('modalTitle').textContent = 'Edit Template';
            
            detectVariables();
            updatePreview();
            
            new bootstrap.Modal(document.getElementById('addTemplateModal')).show();
        }
    } catch (error) {
        showError('Gagal memuat template: ' + error.message);
    }
}

function showPreview(templateId) {
    const template = {{ json_encode($templates ?? []) }};
    // This would typically fetch the template data
    alert('Fitur preview akan segera tersedia');
}

function confirmDelete(templateId, templateName) {
    currentDeletingId = templateId;
    document.getElementById('delete_template_name').textContent = 'Template: ' + templateName;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}

function detectVariables() {
    const content = document.getElementById('template_content').value;
    const regex = /\{([a-zA-Z_][a-zA-Z0-9_]*)\}/g;
    const variables = [...new Set([...content.matchAll(regex)].map(m => m[1]))];
    
    const section = document.getElementById('detected_variables_section');
    const container = document.getElementById('detected_variables');
    
    if (variables.length > 0) {
        container.innerHTML = variables.map(v => `
            <span class="badge bg-info me-2 mb-2">{${v}}</span>
        `).join('');
        section.style.display = 'block';
    } else {
        section.style.display = 'none';
    }
}

function updatePreview() {
    const content = document.getElementById('template_content').value;
    if (content) {
        document.getElementById('preview_text').textContent = content;
        document.getElementById('preview_section').style.display = 'block';
    } else {
        document.getElementById('preview_section').style.display = 'none';
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
        document.body.insertBefore(alert, document.body.firstChild);
    } catch (e) {
        document.body.appendChild(alert);
    }
    setTimeout(() => {
        try { alert.remove(); } catch(e) {}
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
        document.body.insertBefore(alert, document.body.firstChild);
    } catch (e) {
        document.body.appendChild(alert);
    }
    setTimeout(() => {
        try { alert.remove(); } catch(e) {}
    }, 3000);
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
</script>

<style>
    .card {
        transition: transform 0.2s, box-shadow 0.2s;
    }
    
    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }
    
    .btn-group-sm .btn {
        padding: 0.25rem 0.5rem;
        font-size: 0.75rem;
    }
</style>
@endsection
