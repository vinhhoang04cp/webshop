<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thanh toán thành công</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
        
        .success-icon {
            width: 80px;
            height: 80px;
            background: #4CAF50;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            animation: scaleIn 0.5s ease-out;
        }
        
        .success-icon svg {
            width: 50px;
            height: 50px;
            fill: white;
        }
        
        @keyframes scaleIn {
            0% {
                transform: scale(0);
            }
            50% {
                transform: scale(1.1);
            }
            100% {
                transform: scale(1);
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
            font-weight: bold;
            font-size: 18px;
            color: #4CAF50;
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
            background: #667eea;
            color: white;
        }
        
        .btn-primary:hover {
            background: #5568d3;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
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
    </style>
</head>
<body>
    <div class="container">
        <div class="success-icon">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/>
            </svg>
        </div>
        
        <h1>Thanh toán thành công!</h1>
        
        <p class="message">
            Cảm ơn bạn đã mua hàng. Đơn hàng của bạn đã được thanh toán thành công và đang được xử lý.
        </p>
        
        <div class="order-info">
            <div class="order-row">
                <span class="order-label">Mã đơn hàng:</span>
                <span class="order-value">#{{ $order->order_id }}</span>
            </div>
            <div class="order-row">
                <span class="order-label">Phương thức thanh toán:</span>
                <span class="order-value">VNPay</span>
            </div>
            <div class="order-row">
                <span class="order-label">Mã giao dịch:</span>
                <span class="order-value">{{ $order->transaction_id ?? 'N/A' }}</span>
            </div>
            <div class="order-row">
                <span class="order-label">Thời gian:</span>
                <span class="order-value">{{ $order->paid_at ? $order->paid_at->format('d/m/Y H:i:s') : now()->format('d/m/Y H:i:s') }}</span>
            </div>
            <div class="order-row">
                <span class="order-label">Tổng tiền:</span>
                <span class="order-value">{{ number_format($order->total_amount, 0, ',', '.') }} ₫</span>
            </div>
        </div>
        
        <div class="btn-container">
            <a href="{{ route('home') }}" class="btn btn-primary">Về trang chủ</a>
            <a href="{{ route('profile.index') }}" class="btn btn-secondary">Xem đơn hàng</a>
        </div>
    </div>
</body>
</html>
