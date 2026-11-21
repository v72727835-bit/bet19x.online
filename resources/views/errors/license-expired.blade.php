<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>License Expired</title>
    <style>
        body { font-family: Arial, sans-serif; background: #0F1023; color: #F4F3FA; min-height: 100vh; display: flex; align-items: center; justify-content: center; margin: 0; padding: 20px; }
        .box { max-width: 460px; text-align: center; background: #1D1E3D; border-radius: 14px; padding: 40px 32px; border: 1px solid #2B2C55; }
        h1 { font-size: 22px; margin-bottom: 10px; }
        p { color: #8A8CB3; font-size: 14px; line-height: 1.6; }
    </style>
</head>
<body>
    <div class="box">
        <h1>⚠ License Expired</h1>
        <p>Is platform ka license expire ho gaya hai ya invalid hai. Kripya apne provider se contact karein renewal ke liye.</p>
        @if($supportEmail)
            <p>Email: <strong>{{ $supportEmail }}</strong></p>
        @endif
        @if($supportPhone)
            <p>Phone: <strong>{{ $supportPhone }}</strong></p>
        @endif
    </div>
</body>
</html>
