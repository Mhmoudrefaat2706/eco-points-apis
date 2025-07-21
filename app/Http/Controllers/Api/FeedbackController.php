<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Feedback;
use App\Models\Material;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FeedbackController extends Controller
{
    //  Add feedback based on material (but only store seller_id)
    public function store(Request $request)
    {
        if (Auth::user()->role !== 'buyer') {
            return response()->json(['message' => 'Only buyers can submit feedback'], 403);
        }

        $request->validate([
            'material_id' => 'required|exists:materials,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string',
        ]);

        // Get seller from material
        $material = Material::findOrFail($request->material_id);
        $seller_id = $material->seller_id;

        $feedback = Feedback::create([
            'seller_id' => $seller_id,
            'buyer_id' => Auth::id(),
            'rating' => $request->rating,
            'comment' => $request->comment,
        ]);

        return response()->json(['message' => 'Feedback added', 'data' => $feedback], 201);
    }

    public function getSellerFeedback($seller_id)
    {
        $feedbacks = Feedback::with(['buyer', 'seller']) // Add seller relationship
            ->where('seller_id', $seller_id)
            ->orderByDesc('created_at')
            ->get();

        return response()->json($feedbacks);
    }

    public function myFeedbacks()
    {
        if (Auth::user()->role !== 'seller') {
            return response()->json(['message' => 'Only sellers can view their feedback'], 403);
        }

        $feedbacks = Feedback::with(['buyer', 'seller']) // Add seller relationship
            ->where('seller_id', Auth::id())
            ->orderByDesc('created_at')
            ->get();

        return response()->json($feedbacks);
    }

    //  Update feedback (only by the buyer who created it)
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

    //  Delete feedback (only by the buyer who created it)
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
