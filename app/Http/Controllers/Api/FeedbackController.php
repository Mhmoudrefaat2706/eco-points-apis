<?php


namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Feedback;

class FeedbackController extends Controller
{
    public function getSellerFeedback($id)
    {
        $feedbacks = Feedback::with('buyer')
            ->where('seller_id', $id)
            ->orderByDesc('created_at')
            ->get();

        return response()->json($feedbacks);
    }
}
