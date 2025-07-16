<?php
namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Payment;
use App\Services\PayPalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Order;

class PayPalController extends Controller
{
 public function createOrder(Request $request)
{
    $request->validate([
        'order_id' => 'required|exists:orders,id',
    ]);

    $order = Order::find($request->order_id);
    $seller = $order->items()->first()->material->user;

    if (!$seller->paypal_client_id || !$seller->paypal_client_secret) {
        return response()->json(['error' => 'لم يتم العثور على بيانات PayPal الخاصة بالتاجر'], 422);
    }

    $paypal = new PayPalService($seller->paypal_client_id, $seller->paypal_client_secret);

    try {
        $paypalOrder = $paypal->createOrder($order->total_price);

        if (!empty($paypalOrder['id'])) {
            Payment::create([
                'user_id' => Auth::id(),
                'order_id' => $order->id,
                'paypal_order_id' => $paypalOrder['id'],
                'amount' => $order->total_price,
                'status' => 'pending',
                'currency' => 'USD',
            ]);

            $approvalUrl = collect($paypalOrder['links'])->firstWhere('rel', 'approve')['href'] ?? null;

            return response()->json([
                'approval_url' => $approvalUrl
            ]);
        }

        return response()->json(['error' => 'فشل في إنشاء طلب PayPal'], 500);

    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
}
    public function captureOrder(Request $request)
    {
        $request->validate([
            'orderId' => 'required|string'
        ]);

        $payment = Payment::where('order_id', $request->orderId)->first();
        if (!$payment) {
            return response()->json(['error' => 'Payment not found'], 404);
        }

        $seller = $payment->user;

        $paypal = new PayPalService($seller->paypal_client_id, $seller->paypal_client_secret);

        try {
            $response = $paypal->capturePayment($request->orderId);

            if (!empty($response['id'])) {
                $payment->update([
                    'payment_id' => $response['id'],
                    'status' => $response['status'] ?? 'completed',
                ]);
            }

            return response()->json($response);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
