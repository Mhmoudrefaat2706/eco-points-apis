<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
        $user = Auth::user();

        $orders = Order::whereHas('items.material', function($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->with(['items' => function($query) use ($user) {
                $query->whereHas('material', function($q) use ($user) {
                    $q->where('user_id', $user->id);
                });
            }])
            ->get();

        return response()->json($orders);
    }


    public function updateOrderStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:approved,rejected,cancelled'
        ]);

        $order = Order::findOrFail($id);
        $order->status = $request->status;
        $order->save();

        return response()->json(['message' => 'Order status updated']);
    }
}
