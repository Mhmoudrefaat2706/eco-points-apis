<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Order Status Changed</title>
</head>
<body>
    <h2>Hello {{ $order->user->name ?? 'Customer' }},</h2>

    <p>Your order (ID: {{ $order->id }}) status has been updated to:</p>
    <p><strong>{{ ucfirst($status) }}</strong></p>

    <p>Thank you for using our service.</p>
</body>
</html>
