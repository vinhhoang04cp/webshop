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
    <!-- Custom CSS -->
    <link href="{{ asset('css/auth.css') }}" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #667eea;
            --secondary-color: #764ba2;
            --dark-color: #2c3e50;
            --light-color: #f8f9fa;
            --success-color: #10b981;
            --danger-color: #ef4444;
            --warning-color: #f59e0b;
            --info-color: #3b82f6;
            --border-radius: 12px;
            --box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
            --box-shadow-lg: 0 10px 25px rgba(0, 0, 0, 0.1);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            min-height: 100vh;
            font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
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
            box-shadow: var(--box-shadow-lg);
            padding: 45px;
            width: 100%;
            max-width: 480px;
            backdrop-filter: blur(10px);
            animation: slideUp 0.4s ease-out;
        }
        
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
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
            font-size: 0.95rem;
        }
        
        /* Forms */
        .form-floating label {
            color: #6b7280;
            font-size: 0.9rem;
        }
        
        .form-control {
            border: 2px solid #e5e7eb;
            border-radius: var(--border-radius);
            padding: 13px 16px;
            transition: all 0.3s ease;
            font-size: 0.95rem;
        }
        
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.15);
            outline: none;
        }
        
        .form-control.is-invalid {
            border-color: var(--danger-color);
        }
        
        .form-control.is-invalid:focus {
            box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.15);
        }
        
        /* Buttons */
        .btn {
            border-radius: var(--border-radius);
            padding: 12px 24px;
            font-weight: 600;
            transition: all 0.3s ease;
            font-size: 0.95rem;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            border: none;
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 16px rgba(102, 126, 234, 0.3);
            background: linear-gradient(135deg, #7c93f0 0%, #8659b0 100%);
        }
        
        .btn-outline-primary {
            border: 2px solid var(--primary-color);
            color: var(--primary-color);
        }
        
        .btn-outline-primary:hover {
            background: var(--primary-color);
            color: white;
        }
        
        .btn-outline-secondary {
            border: 2px solid #6b7280;
            color: #6b7280;
        }
        
        .btn-outline-secondary:hover {
            background: #6b7280;
            color: white;
        }
        
        .btn-outline-danger {
            border: 2px solid var(--danger-color);
            color: var(--danger-color);
        }
        
        .btn-outline-danger:hover {
            background: var(--danger-color);
            color: white;
        }
        
        /* Alerts */
        .alert {
            border-radius: var(--border-radius);
            border: none;
            padding: 14px 18px;
            font-size: 0.9rem;
        }
        
        .alert-success {
            background: #d1fae5;
            color: #065f46;
        }
        
        .alert-danger {
            background: #fee2e2;
            color: #991b1b;
        }
        
        .alert-info {
            background: #dbeafe;
            color: #1e40af;
        }
        
        /* Links */
        .auth-links {
            text-align: center;
            margin-top: 24px;
        }
        
        .auth-links a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 600;
            transition: color 0.2s;
        }
        
        .auth-links a:hover {
            color: var(--secondary-color);
        }
        
        /* Dashboard Sidebar */
        .dashboard-sidebar {
            background: linear-gradient(180deg, #1f2937 0%, #111827 100%);
            min-height: 100vh;
            padding: 0;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
        }
        
        .sidebar-header {
            padding: 24px 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            background: rgba(0, 0, 0, 0.2);
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
            transition: all 0.2s ease;
            font-size: 0.95rem;
            font-weight: 500;
            display: flex;
            align-items: center;
        }
        
        .nav-link i {
            width: 24px;
            margin-right: 12px;
            font-size: 1.1rem;
        }
        
        .nav-link:hover {
            background: rgba(102, 126, 234, 0.15);
            color: #fff !important;
            transform: translateX(4px);
        }
        
        .nav-link.active {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: #fff !important;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
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
        
        /* Cards - Modified to prevent conflicts */
        .card {
            border: none;
            border-radius: 16px;
            box-shadow: var(--box-shadow);
            transition: box-shadow 0.3s ease;
            background: white;
        }
        
        .card:hover {
            box-shadow: var(--box-shadow-lg);
            /* Removed transform to prevent modal conflicts */
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
            font-size: 1.1rem;
        }
        
        .card-body {
            padding: 24px;
        }
        
        /* Stats Cards */
        .stat-card {
            border-radius: 16px;
            padding: 24px;
            color: white;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            border: none;
            height: 100%;
            transition: all 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 24px rgba(102, 126, 234, 0.3);
        }
        
        .stat-card .stat-icon {
            font-size: 2.5rem;
            opacity: 0.9;
            margin-bottom: 12px;
        }
        
        .stat-card h3 {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 8px;
        }
        
        .stat-card p {
            margin: 0;
            opacity: 0.95;
            font-size: 0.95rem;
        }
        
        /* Tables */
        .table {
            margin: 0;
        }
        
        .table thead th {
            background: #f9fafb;
            color: #374151;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.8rem;
            letter-spacing: 0.5px;
            border-bottom: 2px solid #e5e7eb;
            padding: 14px 16px;
        }
        
        .table tbody td {
            padding: 16px;
            vertical-align: middle;
            border-bottom: 1px solid #f3f4f6;
            font-size: 0.9rem;
        }
        
        .table-hover tbody tr:hover {
            background: #f9fafb;
        }
        
        /* Fix modal flickering issue */
        .modal {
            pointer-events: none;
            z-index: -1;
        }
        
        .modal.show {
            pointer-events: auto;
            z-index: 1055;
        }
        
        .modal-backdrop {
            pointer-events: none;
            z-index: -1;
        }
        
        .modal-backdrop.show {
            pointer-events: auto;
            z-index: 1050;
        }
        
        /* Prevent table hover from interfering */
        .table-hover tbody tr {
            position: relative;
            z-index: 1;
        }
        
        .table-hover tbody tr:hover {
            background: #f9fafb;
            z-index: 1;
        }
        
        /* Pagination */
        .pagination {
            margin: 0;
        }
        
        .page-link {
            border: 2px solid #e5e7eb;
            color: #6b7280;
            padding: 8px 14px;
            margin: 0 4px;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.2s;
        }
        
        .page-link:hover {
            background: var(--primary-color);
            border-color: var(--primary-color);
            color: white;
        }
        
        .page-item.active .page-link {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            border-color: var(--primary-color);
            color: white;
        }
        
        .page-item.disabled .page-link {
            background: #f3f4f6;
            border-color: #e5e7eb;
        }
        
        /* Modals - Complete fix for flickering */
        .modal {
            display: none !important;
            pointer-events: none;
            opacity: 0;
            visibility: hidden;
            transition: none !important;
        }
        
        .modal.show {
            display: block !important;
            pointer-events: auto;
            opacity: 1;
            visibility: visible;
        }
        
        .modal.show .modal-dialog {
            transform: none !important;
        }
        
        .modal-content {
            border: none;
            border-radius: 16px;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.2);
            will-change: auto;
        }
        
        .modal-header {
            border-bottom: 1px solid #f3f4f6;
            padding: 20px 24px;
        }
        
        .modal-header h5 {
            font-weight: 600;
            color: #1f2937;
        }
        
        .modal-body {
            padding: 24px;
        }
        
        .modal-footer {
            border-top: 1px solid #f3f4f6;
            padding: 16px 24px;
        }
        
        /* Ensure buttons work properly */
        button[data-bs-target] {
            cursor: pointer;
            user-select: none;
            transition: opacity 0.2s ease;
        }
        
        button[data-bs-target]:hover {
            opacity: 0.8;
        }
        
        /* Prevent any accidental triggers */
        .modal-backdrop {
            display: none !important;
        }
        
        .modal.show + .modal-backdrop {
            display: block !important;
        }
        
        /* Badges */
        .badge {
            padding: 6px 12px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.8rem;
        }
        
        /* Search & Filters */
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
            background: rgba(0, 0, 0, 0.2);
            margin-top: auto;
        }
        
        .user-info .user-name {
            color: white;
            font-weight: 600;
            margin-bottom: 4px;
            font-size: 0.95rem;
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
    <!-- Custom JS -->
    <script src="{{ asset('js/auth.js') }}"></script>
    
    <!-- Fix modal flickering - Complete solution -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Disable all automatic Bootstrap modal triggers
            const modalButtons = document.querySelectorAll('button[data-bs-toggle="modal"]');
            
            modalButtons.forEach(button => {
                // Remove Bootstrap's automatic modal trigger
                button.removeAttribute('data-bs-toggle');
                
                // Add manual click handler
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    const targetModalId = button.getAttribute('data-bs-target');
                    const targetModal = document.querySelector(targetModalId);
                    
                    if (targetModal) {
                        // Ensure modal is properly hidden first
                        targetModal.style.display = 'none';
                        targetModal.classList.remove('show');
                        
                        // Small delay then show modal
                        setTimeout(() => {
                            const modal = new bootstrap.Modal(targetModal, {
                                backdrop: true,
                                keyboard: true,
                                focus: true
                            });
                            modal.show();
                        }, 10);
                    }
                });
                
                // Prevent any hover effects on modal buttons
                button.addEventListener('mouseenter', function(e) {
                    e.stopPropagation();
                });
                
                button.addEventListener('mouseleave', function(e) {
                    e.stopPropagation();
                });
            });
            
            // Force hide all modals on page load
            const allModals = document.querySelectorAll('.modal');
            allModals.forEach(modal => {
                modal.style.display = 'none';
                modal.classList.remove('show');
                modal.setAttribute('aria-hidden', 'true');
            });
            
            // Prevent any automatic modal triggers
            document.addEventListener('mousemove', function(e) {
                // Hide any accidentally shown modals
                const visibleModals = document.querySelectorAll('.modal.show');
                visibleModals.forEach(modal => {
                    if (!modal.querySelector('.modal-dialog:hover')) {
                        // Only hide if mouse is not over the modal content
                        const rect = modal.getBoundingClientRect();
                        if (e.clientX < rect.left || e.clientX > rect.right || 
                            e.clientY < rect.top || e.clientY > rect.bottom) {
                            // Mouse is outside modal, but don't auto-hide
                        }
                    }
                });
            });
        });
    </script>
    
    @yield('scripts')
</body>
</html>