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

        /* Stat Cards */
        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 20px;
            padding: 30px 25px;
            color: white;
            position: relative;
            overflow: hidden;
            box-shadow: 0 8px 32px rgba(102, 126, 234, 0.3);
            transition: all 0.3s ease;
            border: none;
            backdrop-filter: blur(10px);
        }

        .stat-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 40px rgba(102, 126, 234, 0.4);
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(255,255,255,0.2) 0%, rgba(255,255,255,0.05) 100%);
            border-radius: 20px;
        }

        .stat-card * {
            position: relative;
            z-index: 2;
        }

        .stat-card .stat-icon {
            position: absolute;
            top: 20px;
            right: 20px;
            width: 60px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(255, 255, 255, 0.15);
            border-radius: 15px;
            backdrop-filter: blur(10px);
        }

        .stat-card .stat-icon i {
            font-size: 24px;
            color: white;
            opacity: 0.9;
        }

        .stat-card h3 {
            font-size: 2.5rem;
            font-weight: 700;
            margin: 0 0 8px 0;
            color: white;
            text-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .stat-card p {
            font-size: 1rem;
            margin: 0;
            color: rgba(255, 255, 255, 0.9);
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Gradient variations for different cards */
        .stat-card[style*="667eea"] {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            box-shadow: 0 8px 32px rgba(102, 126, 234, 0.3);
        }

        .stat-card[style*="f093fb"] {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            box-shadow: 0 8px 32px rgba(240, 147, 251, 0.3);
        }

        .stat-card[style*="4facfe"] {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            box-shadow: 0 8px 32px rgba(79, 172, 254, 0.3);
        }

        .stat-card[style*="43e97b"] {
            background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
            box-shadow: 0 8px 32px rgba(67, 233, 123, 0.3);
        }

        /* Enhanced card animations */
        @keyframes statCardPulse {
            0% {
                box-shadow: 0 8px 32px rgba(102, 126, 234, 0.3);
            }
            50% {
                box-shadow: 0 12px 40px rgba(102, 126, 234, 0.5);
            }
            100% {
                box-shadow: 0 8px 32px rgba(102, 126, 234, 0.3);
            }
        }

        .stat-card:nth-child(1) {
            animation: statCardPulse 3s ease-in-out infinite;
            animation-delay: 0s;
        }

        .stat-card:nth-child(2) {
            animation: statCardPulse 3s ease-in-out infinite;
            animation-delay: 0.5s;
        }

        .stat-card:nth-child(3) {
            animation: statCardPulse 3s ease-in-out infinite;
            animation-delay: 1s;
        }

        .stat-card:nth-child(4) {
            animation: statCardPulse 3s ease-in-out infinite;
            animation-delay: 1.5s;
        }

        /* Recent orders table styling */
        .card .table-responsive {
            border-radius: 12px;
            overflow: hidden;
        }

        .table {
            margin-bottom: 0;
        }

        .badge {
            padding: 8px 16px;
            font-size: 0.75rem;
            font-weight: 600;
            border-radius: 20px;
        }

        /* Enhanced card styling */
        .card {
            border: none;
            border-radius: 20px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.08);
            background: white;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.12);
        }

        .card-header {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            border-bottom: 1px solid #e2e8f0;
            padding: 24px 30px;
            border-radius: 20px 20px 0 0 !important;
        }

        .card-body {
            padding: 30px;
        }

        /* System activity styling */
        .card .d-flex.align-items-center {
            padding: 20px;
            border-radius: 12px;
            background: linear-gradient(135deg, #f8fafc 0%, #ffffff 100%);
            border: 1px solid #e2e8f0;
            transition: all 0.3s ease;
        }

        .card .d-flex.align-items-center:hover {
            background: linear-gradient(135deg, #f1f5f9 0%, #ffffff 100%);
            border-color: #cbd5e1;
            transform: translateX(5px);
        }

        .card .flex-shrink-0 i {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
            font-size: 18px;
        }

        .text-success {
            background: rgba(34, 197, 94, 0.1) !important;
            color: #059669 !important;
        }

        .text-primary {
            background: rgba(59, 130, 246, 0.1) !important;
            color: #2563eb !important;
        }

        .text-warning {
            background: rgba(245, 158, 11, 0.1) !important;
            color: #d97706 !important;
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