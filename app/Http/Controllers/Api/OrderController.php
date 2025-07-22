<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Mail\OrderStatusChangedMail;

class OrderController extends Controller
{
    // OrderController.php
    public function getUserOrders()
    {
        $user = Auth::user();
        $orders = Order::with(['items.material'])
            ->where('user_id', $user->id)
            ->get()
            ->map(function ($order) {
                return [
                    'id' => $order->id,
                    'total_price' => $order->total_price,
                    'shipping_cost' => $order->shipping_cost,
                    'tax' => $order->tax,
                    'status' => $order->status,
                    'created_at' => $order->created_at,
                    'items' => $order->items->map(function ($item) {
                        return [
                            'material' => $item->material,
                            'quantity' => $item->quantity,
                            'price' => $item->price
                        ];
                    })
                ];
            });

        return response()->json(['orders' => $orders]);
    }
    public function getSellerOrders()
    {
        $seller = Auth::user();

        $orders = Order::with(['items.material'])
            ->where('seller_id', $seller->id)
            ->get()
            ->map(function ($order) {
                return [
                    'id' => $order->id,
                    'total_price' => $order->total_price,
                    'shipping_cost' => $order->shipping_cost,
                    'tax' => $order->tax,
                    'status' => $order->status,
                    'payment_status' => $order->payment_status,
                    'estimated_delivery' => $order->estimated_delivery,
                    'created_at' => $order->created_at,
                    'items' => $order->items->map(function ($item) {
                        return [
                            'material' => $item->material,
                            'quantity' => $item->quantity,
                            'price' => $item->price
                        ];
                    })
                ];
            });

        return response()->json(['orders' => $orders]);
    }

    public function updateOrderStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:approved,rejected,cancelled'
        ]);

        $order = Order::with('user')->findOrFail($id);

        if ($order->seller_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $order->status = $request->status;
        $order->save();

        // إرسال الإيميل للمشتري
        if ($order->user && $order->user->email) {
            Mail::to($order->user->email)->send(new OrderStatusChangedMail($order, $request->status));
        }

        return response()->json(['message' => 'Order status updated and email sent']);
    }

    // Add this method to OrderController.php
    public function cancelOrder($id)
    {
        $order = Order::findOrFail($id);

        // Check if the authenticated user owns this order
        if ($order->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Only allow cancellation if order is pending
        if ($order->status !== 'pending') {
            return response()->json(['error' => 'Order cannot be cancelled at this stage'], 400);
        }

        $order->status = 'cancelled';
        $order->save();

        return response()->json(['message' => 'Order cancelled successfully']);
    }
}
