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
use Illuminate\Support\Facades\Mail;
use App\Mail\SellerOrderNotificationMail;

class CartController extends Controller
{
    
    public function addToCart(Request $request)
    {
        $request->validate([
            'material_id' => 'required|exists:materials,id',
        ]);

        $userId = Auth::id();

        $material = Material::find($request->material_id);

        if (!$material->seller_id) {
            return response()->json([
                'message' => 'Material not associated with a seller',
                'available_stock' => $material->quantity,
                'material_id' => $material->id
            ], 400);
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
            $createdOrders = [];

            foreach ($cartItems as $item) {
                $material = $item->material;

                if (!$material || !$material->seller_id) {
                    throw new \Exception('Material not valid or missing seller_id');
                }

                if ($material->quantity < $item->quantity) {
                    throw new \Exception("Not enough stock for material: {$material->name}");
                }

                $shippingCost = 5.00;
                $taxRate = 0.14;
                $subtotal = $material->price * $item->quantity;
                $tax = $subtotal * $taxRate;
                $total = $subtotal;

                $order = Order::create([
                    'user_id' => $user->id,
                    'seller_id' => $material->seller_id,
                    'total_price' => $total,
                    'shipping_cost' => $shippingCost,
                    'tax' => $tax,
                    'status' => 'pending',
                    'estimated_delivery' => now()->addDays(7),
                ]);

                OrderItem::create([
                    'order_id' => $order->id,
                    'material_id' => $material->id,
                    'quantity' => $item->quantity,
                    'price' => $material->price,
                    'seller_id' => $material->seller_id,
                ]);

                // Update material stock
                $material->quantity -= $item->quantity;
                $material->save();

                // Send email to seller
                if ($material->seller && $material->seller->email) {
                    Mail::to($material->seller->email)->send(new SellerOrderNotificationMail($order));
                }

                $createdOrders[] = $order;
            }

            // Clear user cart after creating all orders
            Cart::where('user_id', $user->id)->delete();

            DB::commit();

            return response()->json([
                'message' => 'Checkout completed successfully',
                'orders' => $createdOrders,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Checkout failed: ' . $e->getMessage()], 500);
        }
    }
}
