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

        $groupedCartItems = $cartItems->groupBy('material.seller_id');



        foreach ($groupedCartItems as $sellerId => $items) {
                        if (!$sellerId) {
                throw new \Exception('One or more materials are missing seller_id');
            }
            $subtotal = $items->sum(function ($item) {
                return $item->material->price * $item->quantity;
            });

            $shippingCost = 5.00;
            $taxRate = 0.14;
            $tax = $subtotal * $taxRate;
            $total = $subtotal + $shippingCost + $tax;


            $order = Order::create([
                'user_id' => $user->id,
                'seller_id' => $sellerId,
                'total_price' => $total,
                'shipping_cost' => $shippingCost,
                'tax' => $tax,
                'status' => 'pending',
                'estimated_delivery' => now()->addDays(7),
            ]);


            foreach ($items as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'material_id' => $item->material_id,
                    'quantity' => $item->quantity,
                    'price' => $item->material->price,
                    'seller_id' => $sellerId,
                ]);


                $material = Material::find($item->material_id);
                $material->quantity -= $item->quantity;
                $material->save();
            }

            $createdOrders[] = $order;
        }

        Cart::where('user_id', $user->id)->delete();
        DB::commit();

        return response()->json([
            'message' => 'Orders created successfully',
            'orders' => $createdOrders,
        ], 201);

    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json(['message' => 'Checkout failed: ' . $e->getMessage()], 500);
    }
}
}




