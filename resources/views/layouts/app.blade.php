<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - waBlast Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        :root {
            --primary: #0d6efd;
            --secondary: #6c757d;
            --success: #198754;
            --danger: #dc3545;
            --warning: #ffc107;
            --info: #0dcaf0;
        }

        body {
            background-color: #f5f7fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 0.9rem;
        }

        .navbar {
            background: linear-gradient(135deg, #00897b 0%, #00695c 100%);
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 0.5rem 1rem !important;
        }

        .navbar-brand {
            font-weight: 700;
            font-size: 1.1rem;
            color: white !important;
        }

        .nav-link {
            color: rgba(255,255,255,0.9) !important;
            margin: 0 3px;
            padding: 0.3rem 0.6rem !important;
            font-size: 0.85rem;
            transition: all 0.3s ease;
        }

        .nav-link:hover {
            color: white !important;
            background: rgba(255,255,255,0.1);
            border-radius: 4px;
        }

        .nav-link.active {
            color: white !important;
            background: rgba(255,255,255,0.2);
            border-radius: 4px;
        }

        .sidebar {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            padding: 12px;
            margin-bottom: 15px;
        }

        .card {
            border: none;
            border-radius: 6px;
            box-shadow: 0 1px 4px rgba(0,0,0,0.08);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            margin-bottom: 12px;
        }

        .card:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 8px rgba(0,0,0,0.12);
        }

        .card-header {
            background: linear-gradient(135deg, #00897b 0%, #00695c 100%);
            color: white;
            border: none;
            border-radius: 6px 6px 0 0;
            font-weight: 600;
            padding: 0.6rem 0.9rem;
            font-size: 0.85rem;
        }

        .card-body {
            padding: 0.9rem;
        }

        .card-footer {
            padding: 0.6rem 0.9rem;
            background-color: #f8f9fa;
            border-top: 1px solid #dee2e6;
            font-size: 0.85rem;
        }

        .stat-card {
            padding: 12px;
            border-radius: 6px;
            color: white;
            text-align: center;
            margin-bottom: 10px;
        }

        .stat-card-total {
            background: linear-gradient(135deg, #00897b 0%, #00695c 100%);
        }

        .stat-card-pending {
            background: linear-gradient(135deg, #00897b 0%, #00695c 100%);
        }

        .stat-card-sent {
            background: linear-gradient(135deg, #00897b 0%, #00695c 100%);
        }

        .stat-card-failed {
            background: linear-gradient(135deg, #00897b 0%, #00695c 100%);
        }

        .stat-card h3 {
            font-size: 1.6rem;
            font-weight: 700;
            margin: 0;
        }

        .stat-card p {
            font-size: 0.75rem;
            margin: 3px 0 0 0;
            opacity: 0.9;
        }

        table {
            font-size: 0.9rem;
            margin-bottom: 0;
        }

        thead {
            background-color: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
        }

        th {
            font-weight: 600;
            color: #333;
            padding: 8px 10px;
            border-bottom: 1px solid #dee2e6;
            font-size: 0.85rem;
        }

        td {
            padding: 8px 10px;
            vertical-align: middle;
        }

        /* Highlight nama pasien */
        td strong {
            font-size: 1rem;
            color: #333;
        }

        tbody tr {
            transition: background-color 0.3s ease;
        }

        tbody tr:hover {
            background-color: #f8f9fa;
        }

        .btn-sm {
            padding: 3px 6px;
            font-size: 0.7rem;
        }

        .badge {
            padding: 3px 6px;
            font-weight: 500;
            font-size: 0.75rem;
        }

        .pagination {
            margin-top: 10px;
            margin-bottom: 0;
            gap: 1px;
            flex-wrap: wrap;
        }

        .page-item {
            margin: 0;
        }

        .page-link {
            padding: 0.08rem 0.25rem;
            font-size: 0.6rem;
            line-height: 1;
            min-width: 1.4rem;
            text-align: center;
            border: 1px solid #dee2e6;
        }

        .page-item.active .page-link {
            background-color: #00897b;
            border-color: #00897b;
        }

        .form-control, .form-select {
            border-radius: 3px;
            border: 1px solid #dee2e6;
            padding: 0.25rem 0.4rem;
            font-size: 0.75rem;
            height: auto;
        }

        .form-control:focus, .form-select:focus {
            border-color: #00897b;
            box-shadow: 0 0 0 0.08rem rgba(0, 137, 123, 0.2);
        }

        .btn, .btn-primary, .btn-secondary, .btn-outline-primary, .btn-outline-secondary {
            padding: 0.15rem 0.35rem;
            font-size: 0.65rem;
            border-radius: 2px;
            line-height: 1;
            height: auto;
        }

        .btn-primary {
            background: linear-gradient(135deg, #00897b 0%, #00695c 100%);
            border: none;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #007068 0%, #00574e 100%);
        }

        .btn-outline-primary {
            color: #00897b;
            border: 1px solid #00897b;
        }

        .btn-outline-primary:hover {
            background: #00897b;
            border-color: #00897b;
            color: white;
        }

        .btn-outline-secondary {
            color: #6c757d;
            border: 1px solid #6c757d;
        }

        .btn-outline-secondary:hover {
            background: #6c757d;
            border-color: #6c757d;
            color: white;
        }

        .btn-secondary {
            background-color: #6c757d;
            border-color: #6c757d;
        }

        .btn-secondary:hover {
            background-color: #5a6268;
            border-color: #545b62;
        }

        .w-100 {
            width: 100%;
        }

        .footer {
            margin-top: 20px;
            padding: 10px 0;
            text-align: center;
            color: #6c757d;
            border-top: 1px solid #dee2e6;
            font-size: 0.75rem;
        }

        .search-box {
            position: relative;
        }

        .search-box input {
            padding-left: 28px;
        }

        .search-icon {
            position: absolute;
            left: 8px;
            top: 50%;
            transform: translateY(-50%);
            color: #999;
            font-size: 0.85rem;
        }

        h1, h2 {
            font-size: 1.3rem;
            margin-bottom: 0.8rem;
        }

        .row {
            margin-bottom: 10px;
        }

        .mb-4 {
            margin-bottom: 1rem !important;
        }

        .mb-3 {
            margin-bottom: 0.8rem !important;
        }

        @media (max-width: 768px) {
            .stat-card h3 {
                font-size: 1.2rem;
            }

            table {
                font-size: 0.85rem;
            }

            th, td {
                padding: 10px 8px;
            }
        }
    </style>
    @yield('extra_css')
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark sticky-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="/">
                <i class="bi bi-chat-dots"></i> waBlast Notification Sistem
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link @if(Route::currentRouteName() == 'dashboard.pasien.index') active @endif" href="{{ route('dashboard.pasien.index') }}">
                            <i class="bi bi-people"></i> Pasien
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link @if(Route::currentRouteName() == 'dashboard.kontrol.index') active @endif" href="{{ route('dashboard.kontrol.index') }}">
                            <i class="bi bi-calendar-check"></i> Kontrol BPJS
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link @if(Route::currentRouteName() == 'dashboard.mobile_bpjs.index') active @endif" href="{{ route('dashboard.mobile_bpjs.index') }}">
                            <i class="bi bi-phone"></i> Mobile BPJS
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container-fluid py-2">
        @yield('content')
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="container-fluid">
            <p>&copy; 2026 waBlast - WhatsApp Reminder System </p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    @yield('extra_js')
</body>
</html>
