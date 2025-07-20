<h2>You've received a new order!</h2>
<p>Order: {{ $order->id }}</p>
<p>Total Price: ${{ $order->total_price }}</p>
<p>Status: {{ $order->status }}</p>
<p>Delivery Date: {{ $order->estimated_delivery }}</p>
