<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Feedback;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FeedbackController extends Controller
{
    //  Add new feedback
    public function store(Request $request)
    {
        if (Auth::user()->role !== 'buyer') {
    return response()->json(['message' => 'Only buyers can submit feedback'], 403);
}

        $request->validate([
            'material_id' => 'required|exists:materials,id',
            'seller_id' => 'required|exists:users,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string',
        ]);

        $feedback = Feedback::create([
            'material_id' => $request->material_id,
            'seller_id' => $request->seller_id,
            'buyer_id' => Auth::id(),
            'rating' => $request->rating,
            'comment' => $request->comment,
        ]);

        return response()->json(['message' => 'Feedback added', 'data' => $feedback], 201);
    }
    //  Get feedback for specific seller
    public function getSellerFeedback($seller_id)
    {
        $feedbacks = Feedback::with('buyer')
            ->where('seller_id', $seller_id)
            ->orderByDesc('created_at')
            ->get();

        return response()->json($feedbacks);
    }

    //  Update feedback (only by buyer who created it)
    public function update(Request $request, $id)
    {
        $feedback = Feedback::findOrFail($id);

        if ($feedback->buyer_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string',
        ]);

        $feedback->update([
            'rating' => $request->rating,
            'comment' => $request->comment,
        ]);

        return response()->json(['message' => 'Feedback updated', 'data' => $feedback]);
    }

    //  Delete feedback (only by buyer who created it)
    public function destroy($id)
    {
        $feedback = Feedback::findOrFail($id);

        if ($feedback->buyer_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $feedback->delete();

        return response()->json(['message' => 'Feedback deleted']);
    }
}
