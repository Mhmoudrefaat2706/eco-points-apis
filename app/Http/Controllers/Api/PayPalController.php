<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Payment;
use App\Services\PayPalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Order;
use Illuminate\Support\Facades\Log;

class PayPalController extends Controller
{

    public function createOrder(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
        ]);

        $order = Order::with('items.material')->find($request->order_id);

        if (!$order) {
            return response()->json(['error' => 'Order not found'], 404);
        }

        if ($order->items->isEmpty()) {
            return response()->json(['error' => 'Order does not contain any items'], 422);
        }

        if (!$order->seller_id) {
            return response()->json(['error' => 'No seller found for this order'], 422);
        }

        $seller = User::find($order->seller_id);
        if (!$seller) {
            return response()->json(['error' => 'Seller data not found'], 422);
        }

        if (!$seller->paypal_client_id || !$seller->paypal_client_secret) {
            return response()->json(['error' => 'Seller PayPal credentials not found'], 422);
        }

        $paypal = new PayPalService($seller->paypal_client_id, $seller->paypal_client_secret);

        try {
            $response = $paypal->createOrder($order->total_price, 'USD');
            Log::info('PayPal response', $response);

            Payment::create([
                'user_id' => $order->user_id,
                'order_id' => $order->id,
                'paypal_order_id' => $response['id'],
                'amount' => $order->total_price,
                'status' => $response['status'],
            ]);
            Log::info('PayPal response', $response);

            return response()->json([
                'approval_url' => $this->findApprovalUrl($response['links'])
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    private function findApprovalUrl(array $links): string
    {
        foreach ($links as $link) {
            if ($link['rel'] === 'approve') {
                return $link['href'];
            }
        }
        throw new \Exception('Approval URL not found in PayPal response');
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

    public function success(Request $request)
    {
        $token = $request->query('token');
        $payment = Payment::where('paypal_order_id', $token)->first();

        if (!$payment) {
            return response()->json(['error' => 'Payment not found'], 404);
        }

        $payment->status = 'completed';
        $payment->save();

        $order = Order::find($payment->order_id);
        $order->status = 'paid';
        $order->save();

        return redirect(config('app.frontend_url') . '/my-orders?payment=success');
    }

    public function cancel(Request $request)
    {
        $token = $request->query('token');
        $payment = Payment::where('paypal_order_id', $token)->first();

        if ($payment) {
            $payment->status = 'cancelled';
            $payment->save();
        }
        return redirect(config('app.frontend_url') . '/my-orders?payment=cancelled');
    }
}
