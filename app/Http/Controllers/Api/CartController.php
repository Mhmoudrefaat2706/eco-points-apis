<?php
namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Material;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;
class CartController extends Controller
{


public function addToCart(Request $request)
{
    $request->validate([
        'material_id' => 'required|exists:materials,id',
    ]);

    $userId = Auth::id();

    $material = Material::find($request->material_id);

    if (!$material) {
        return response()->json(['message' => 'Material not found'], 404);
    }

    $cartItem = Cart::where('user_id', $userId)
                    ->where('material_id', $request->material_id)
                    ->first();

    if ($cartItem) {

        return response()->json([
            'message' => 'Item already in cart',
            'cart' => $cartItem,
            'available_stock' => $material->quantity
        ]);
    }

    if ($material->quantity < 1) {
        return response()->json([
            'message' => 'Material out of stock',
            'available_stock' => $material->quantity
        ], 400);
    }

    $cartItem = Cart::create([
        'user_id' => $userId,
        'material_id' => $request->material_id,
        'quantity' => 1
    ]);

    return response()->json([
        'message' => 'Item added to cart with quantity = 1',
        'cart' => $cartItem,
        'available_stock' => $material->quantity
    ]);
}


    public function removeFromCart($id)
    {
        $cartItem = Cart::where('id', $id)->where('user_id', Auth::id())->first();
        if (!$cartItem) {
            return response()->json(['message' => 'Item not found'], 404);
        }
        $cartItem->delete();

        return response()->json(['message' => 'Item removed from cart']);
    }

    public function clearCart()
    {
        Cart::where('user_id', Auth::id())->delete();
        return response()->json(['message' => 'Cart cleared']);
    }

    public function viewCart()
    {
        $cart = Cart::with('material')->where('user_id', Auth::id())->get();
        return response()->json($cart);
    }

public function checkout(Request $request)
{
    $user = Auth::user();
    $cartItems = Cart::with('material')->where('user_id', $user->id)->get();

    if ($cartItems->isEmpty()) {
        return response()->json(['message' => 'Cart is empty'], 400);
    }

    DB::beginTransaction();

    try {
        // Calculate order totals
        $subtotal = $cartItems->sum(function ($item) {
            return $item->material->price * $item->quantity;
        });

        $shippingCost = 5.00; // Fixed shipping cost
        $taxRate = 0.14; // 14% tax
        $tax = $subtotal * $taxRate;
        $total = $subtotal + $shippingCost + $tax;

        // Create order
        $order = Order::create([
            'user_id' => $user->id,
            'total_price' => $total,
            'shipping_cost' => $shippingCost,
            'tax' => $tax,
            'status' => 'pending',
            'estimated_delivery' => now()->addDays(7),
        ]);

        // Create order items
        foreach ($cartItems as $cartItem) {
            OrderItem::create([
                'order_id' => $order->id,
                'material_id' => $cartItem->material_id,
                'quantity' => $cartItem->quantity,
                'price' => $cartItem->material->price,
            ]);

            // Update material stock
            $material = Material::find($cartItem->material_id);
            $material->quantity -= $cartItem->quantity;
            $material->save();
        }

        // Clear cart
        Cart::where('user_id', $user->id)->delete();

        DB::commit();

        return response()->json([
            'message' => 'Order created successfully',
            'order_id' => $order->id,
            'total' => $total
        ], 201);

    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json(['message' => 'Checkout failed: ' . $e->getMessage()], 500);
    }
}

}




