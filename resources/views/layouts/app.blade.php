<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'WebShop Admin')</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #667eea;
            --secondary: #764ba2;
        }
        body {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            min-height: 100vh;
            font-family: system-ui, -apple-system, sans-serif;
        }
        .auth-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .auth-card {
            background: white;
            border-radius: 12px;
            padding: 40px;
            max-width: 450px;
            width: 100%;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .auth-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .auth-header h1 {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 8px;
        }
        .form-control, .form-select {
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            padding: 10px 14px;
        }
        .form-control:focus, .form-select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(102,126,234,0.1);
        }
        .btn {
            border-radius: 8px;
            padding: 10px 20px;
            font-weight: 600;
        }
        .btn-primary {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border: none;
        }
        .alert {
            border-radius: 8px;
            border: none;
            padding: 12px 16px;
        }
        .dashboard-sidebar {
            background: linear-gradient(180deg, #1f2937, #111827);
            min-height: 100vh;
        }
        .sidebar-header {
            padding: 20px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        .sidebar-header h3 {
            color: white;
            font-size: 1.3rem;
            margin: 0;
        }
        .sidebar-menu {
            padding: 15px 0;
        }
        .nav-link {
            color: #d1d5db !important;
            padding: 12px 20px;
            margin: 4px 10px;
            border-radius: 8px;
            display: flex;
            align-items: center;
        }
        .nav-link i {
            width: 20px;
            margin-right: 10px;
        }
        .nav-link.active {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white !important;
        }
        .dashboard-content {
            background: #f9fafb;
            min-height: 100vh;
            padding: 25px;
        }
        .dashboard-header {
            margin-bottom: 25px;
        }
        .dashboard-header h2 {
            font-size: 1.6rem;
            font-weight: 700;
            margin: 0;
        }
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.08);
        }
        .card-header {
            background: #f9fafb;
            border-bottom: 1px solid #e5e7eb;
            padding: 18px 20px;
        }
        .card-header h5 {
            margin: 0;
            font-weight: 600;
        }
        .card-body {
            padding: 20px;
        }
        .table thead th {
            background: #f9fafb;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
            padding: 12px;
        }
        .table tbody td {
            padding: 12px;
            vertical-align: middle;
        }
        .table-hover tbody tr:hover {
            background: #f9fafb;
        }
        .user-info {
            padding: 20px;
            border-top: 1px solid rgba(255,255,255,0.1);
        }
        .user-info .user-name {
            color: white;
            font-weight: 600;
            margin-bottom: 4px;
        }
        .user-info .user-role {
            color: #9ca3af;
            font-size: 0.85rem;
        }
        .stat-card {
            border-radius: 12px;
            padding: 25px 20px;
            color: white;
            position: relative;
        }
        .stat-card .stat-icon {
            position: absolute;
            top: 20px;
            right: 20px;
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(255,255,255,0.2);
            border-radius: 10px;
        }
        .stat-card .stat-icon i {
            font-size: 20px;
        }
        .stat-card h3 {
            font-size: 2rem;
            font-weight: 700;
            margin: 0 0 5px 0;
        }
        .stat-card p {
            margin: 0;
            font-weight: 500;
            text-transform: uppercase;
            font-size: 0.9rem;
        }
        .badge {
            padding: 6px 12px;
            font-size: 0.75rem;
            font-weight: 600;
            border-radius: 12px;
        }
        .search-box {
            display: flex;
            gap: 8px;
        }
    </style>
    @yield('styles')
</head>
<body>
    @yield('content')
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    @yield('scripts')
</body>
</html>