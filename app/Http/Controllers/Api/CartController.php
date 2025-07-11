<?php
namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Material;
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
}

