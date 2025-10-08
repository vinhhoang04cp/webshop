<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'WebShop Admin')</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --primary-color: #667eea;
            --secondary-color: #764ba2;
            --border-radius: 12px;
            --box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            min-height: 100vh;
            font-family: 'Inter', sans-serif;
            color: #333;
        }
        
        /* Auth Pages */
        .auth-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .auth-card {
            background: rgba(255, 255, 255, 0.98);
            border-radius: 20px;
            box-shadow: var(--box-shadow);
            padding: 45px;
            width: 100%;
            max-width: 480px;
        }
        
        .auth-header {
            text-align: center;
            margin-bottom: 35px;
        }
        
        .auth-header h1 {
            color: #1f2937;
            font-weight: 700;
            font-size: 2rem;
            margin-bottom: 10px;
        }
        
        .auth-header p {
            color: #6b7280;
            margin: 0;
        }
        
        /* Forms */
        .form-control {
            border: 2px solid #e5e7eb;
            border-radius: var(--border-radius);
            padding: 13px 16px;
        }
        
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.15);
            outline: none;
        }
        
        /* Buttons */
        .btn {
            border-radius: var(--border-radius);
            padding: 12px 24px;
            font-weight: 600;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            border: none;
            color: white;
        }
        
        .btn-outline-primary {
            border: 2px solid var(--primary-color);
            color: var(--primary-color);
        }
        
        .btn-outline-secondary {
            border: 2px solid #6b7280;
            color: #6b7280;
        }
        
        .btn-outline-danger {
            border: 2px solid #ef4444;
            color: #ef4444;
        }
        
        /* Alerts */
        .alert {
            border-radius: var(--border-radius);
            border: none;
            padding: 14px 18px;
        }
        
        .alert-success {
            background: #d1fae5;
            color: #065f46;
        }
        
        .alert-danger {
            background: #fee2e2;
            color: #991b1b;
        }
        
        /* Dashboard Sidebar */
        .dashboard-sidebar {
            background: linear-gradient(180deg, #1f2937 0%, #111827 100%);
            min-height: 100vh;
            padding: 0;
        }
        
        .sidebar-header {
            padding: 24px 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .sidebar-header h3 {
            color: white;
            font-weight: 700;
            margin: 0;
            font-size: 1.4rem;
        }
        
        .sidebar-menu {
            padding: 20px 0;
        }
        
        .nav-link {
            color: #d1d5db !important;
            padding: 14px 24px;
            margin: 4px 12px;
            border-radius: 10px;
            font-weight: 500;
            display: flex;
            align-items: center;
        }
        
        .nav-link i {
            width: 24px;
            margin-right: 12px;
        }
        
        .nav-link.active {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: #fff !important;
        }
        
        /* Dashboard Content */
        .dashboard-content {
            background: #f9fafb;
            min-height: 100vh;
            padding: 30px;
        }
        
        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        
        .dashboard-header h2 {
            color: #1f2937;
            font-weight: 700;
            margin: 0;
            font-size: 1.8rem;
        }
        
        /* Cards */
        .card {
            border: none;
            border-radius: 16px;
            box-shadow: var(--box-shadow);
            background: white;
        }
        
        .card-header {
            background: white;
            border-bottom: 1px solid #f3f4f6;
            padding: 20px 24px;
            border-radius: 16px 16px 0 0 !important;
        }
        
        .card-header h5 {
            margin: 0;
            font-weight: 600;
            color: #1f2937;
        }
        
        .card-body {
            padding: 24px;
        }
        
        /* Tables */
        .table thead th {
            background: #f9fafb;
            color: #374151;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.8rem;
            border-bottom: 2px solid #e5e7eb;
            padding: 14px 16px;
        }
        
        .table tbody td {
            padding: 16px;
            vertical-align: middle;
            border-bottom: 1px solid #f3f4f6;
        }
        
        .table-hover tbody tr:hover {
            background: #f9fafb;
        }
        
        /* Modals */
        .modal {
            display: none !important;
        }
        
        .modal.show {
            display: block !important;
        }
        
        .modal-content {
            border: none;
            border-radius: 16px;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.2);
        }
        
        .modal-header {
            border-bottom: 1px solid #f3f4f6;
            padding: 20px 24px;
        }
        
        .modal-body {
            padding: 24px;
        }
        
        .modal-footer {
            border-top: 1px solid #f3f4f6;
            padding: 16px 24px;
        }
        
        /* Search */
        .search-box {
            display: flex;
            gap: 8px;
            align-items: center;
        }
        
        .form-control-sm {
            border-radius: 8px;
            border: 2px solid #e5e7eb;
            padding: 8px 12px;
        }
        
        /* User Info */
        .user-info {
            padding: 20px 24px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            margin-top: auto;
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
    </style>
    
    @yield('styles')
</head>
<body>
    @yield('content')
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Simple Modal Control -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const modalButtons = document.querySelectorAll('button[data-bs-toggle="modal"]');
            
            modalButtons.forEach(button => {
                button.removeAttribute('data-bs-toggle');
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    const targetModal = document.querySelector(button.getAttribute('data-bs-target'));
                    if (targetModal) {
                        const modal = new bootstrap.Modal(targetModal);
                        modal.show();
                    }
                });
            });
        });
    </script>
    
    @yield('scripts')
</body>
</html>