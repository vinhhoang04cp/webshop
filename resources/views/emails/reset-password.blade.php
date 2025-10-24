<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đặt lại mật khẩu</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .container {
            background-color: #f4f4f4;
            border-radius: 5px;
            padding: 20px;
        }
        .content {
            background-color: white;
            padding: 30px;
            border-radius: 5px;
        }
        h1 {
            color: #0d6efd;
            margin-top: 0;
        }
        .button {
            display: inline-block;
            padding: 12px 30px;
            background-color: #0d6efd;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
        }
        .button:hover {
            background-color: #0b5ed7;
        }
        .footer {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            font-size: 12px;
            color: #666;
        }
        .warning {
            background-color: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 10px;
            margin: 15px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="content">
            <h1>Đặt lại mật khẩu</h1>
            
            <p>Xin chào,</p>
            
            <p>Bạn nhận được email này vì chúng tôi đã nhận được yêu cầu đặt lại mật khẩu cho tài khoản của bạn.</p>
            
            <p>Vui lòng nhấp vào nút bên dưới để đặt lại mật khẩu:</p>
            
            <a href="{{ $resetLink }}" class="button">Đặt lại mật khẩu</a>
            
            <div class="warning">
                <strong>⚠️ Lưu ý:</strong> Link này sẽ hết hạn sau 24 giờ.
            </div>
            
            <p>Nếu bạn không thể nhấp vào nút, hãy sao chép và dán URL sau vào trình duyệt của bạn:</p>
            <p style="word-break: break-all; color: #0d6efd;">{{ $resetLink }}</p>
            
            <p>Nếu bạn không yêu cầu đặt lại mật khẩu, vui lòng bỏ qua email này.</p>
            
            <div class="footer">
                <p>Trân trọng,<br>{{ config('app.name') }}</p>
                <p style="margin-top: 10px;">
                    Email này được gửi tự động. Vui lòng không trả lời email này.
                </p>
            </div>
        </div>
    </div>
</body>
</html>
