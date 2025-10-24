<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thanh toán thất bại</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            max-width: 600px;
            width: 100%;
            padding: 40px;
            text-align: center;
        }
        
        .error-icon {
            width: 80px;
            height: 80px;
            background: #f44336;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            animation: shake 0.5s ease-out;
        }
        
        .error-icon svg {
            width: 50px;
            height: 50px;
            fill: white;
        }
        
        @keyframes shake {
            0%, 100% {
                transform: translateX(0);
            }
            10%, 30%, 50%, 70%, 90% {
                transform: translateX(-10px);
            }
            20%, 40%, 60%, 80% {
                transform: translateX(10px);
            }
        }
        
        h1 {
            color: #333;
            font-size: 28px;
            margin-bottom: 15px;
        }
        
        .order-info {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 25px;
            margin: 25px 0;
            text-align: left;
        }
        
        .order-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #dee2e6;
        }
        
        .order-row:last-child {
            border-bottom: none;
        }
        
        .order-label {
            color: #666;
        }
        
        .order-value {
            color: #333;
            font-weight: 500;
        }
        
        .btn-container {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-top: 30px;
        }
        
        .btn {
            padding: 12px 30px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
            border: none;
            cursor: pointer;
            font-size: 16px;
        }
        
        .btn-primary {
            background: #f5576c;
            color: white;
        }
        
        .btn-primary:hover {
            background: #e74c61;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(245, 87, 108, 0.4);
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #5a6268;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(108, 117, 125, 0.4);
        }
        
        .message {
            color: #666;
            margin: 20px 0;
            line-height: 1.6;
        }
        
        .error-message {
            background: #fff3cd;
            border: 1px solid #ffc107;
            border-radius: 8px;
            padding: 15px;
            margin: 20px 0;
            color: #856404;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="error-icon">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                <path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/>
            </svg>
        </div>
        
        <h1>Thanh toán thất bại!</h1>
        
        <p class="message">
            Rất tiếc, giao dịch của bạn không thành công. Vui lòng thử lại hoặc sử dụng phương thức thanh toán khác.
        </p>
        
        @if(session('error'))
        <div class="error-message">
            <strong>Lý do:</strong> {{ session('error') }}
        </div>
        @endif
        
        <div class="order-info">
            <div class="order-row">
                <span class="order-label">Mã đơn hàng:</span>
                <span class="order-value">#{{ $order->order_id }}</span>
            </div>
            <div class="order-row">
                <span class="order-label">Trạng thái:</span>
                <span class="order-value" style="color: #f44336;">Chưa thanh toán</span>
            </div>
            <div class="order-row">
                <span class="order-label">Tổng tiền:</span>
                <span class="order-value">{{ number_format($order->total_amount, 0, ',', '.') }} ₫</span>
            </div>
        </div>
        
        <div class="btn-container">
            <a href="{{ route('cart.index') }}" class="btn btn-primary">Thử lại</a>
            <a href="{{ route('home') }}" class="btn btn-secondary">Về trang chủ</a>
        </div>
    </div>
</body>
</html>
