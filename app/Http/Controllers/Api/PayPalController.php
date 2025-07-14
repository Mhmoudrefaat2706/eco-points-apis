<?php
namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Payment;
use App\Services\PayPalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PayPalController extends Controller
{
    public function createOrder(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.5',
            'seller_id' => 'required|exists:users,id',
        ]);

        $seller = User::find($request->seller_id);

        if (!$seller->paypal_client_id || !$seller->paypal_client_secret) {
            return response()->json(['error' => 'Seller PayPal credentials not found'], 422);
        }

        $paypal = new PayPalService($seller->paypal_client_id, $seller->paypal_client_secret);

        try {
            $order = $paypal->createOrder($request->amount);

            if (!empty($order['id'])) {
                Payment::create([
                    'user_id' => Auth::id(),
                    'order_id' => $order['id'],
                    'amount' => $request->amount,
                    'status' => 'pending',
                    'currency' => 'USD',
                ]);

                $approvalUrl = collect($order['links'])->firstWhere('rel', 'approve')['href'] ?? null;

                return response()->json([
                    'order_id' => $order['id'],
                    'approval_url' => $approvalUrl
                ]);
            }

            return response()->json(['error' => 'Failed to create PayPal order'], 500);

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
