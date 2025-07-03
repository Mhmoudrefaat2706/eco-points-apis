<?php


namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SellerController extends Controller
{
    public function profile($id)
    {
        $user = User::findOrFail($id);
        return response()->json($user);
    }

public function update(Request $request, $id)
{
    $seller = User::findOrFail($id);

    $validator = Validator::make($request->all(), [
        'first_name' => 'required|string|max:50',
        'last_name' => 'required|string|max:50',
        'address' => 'nullable|string|max:255',
        'city' => 'nullable|string|max:50',
        'state' => 'nullable|string|max:50',
        'postal_code' => 'nullable|string|max:20',
        'country' => 'nullable|string|max:50',
    ], [
        'first_name.required' => 'First name is required.',
        'last_name.required' => 'Last name is required.'
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => false,
            'message' => 'Validation error',
            'errors' => $validator->errors()
        ], 422);
    }

    $seller->update($request->only([
        'first_name', 'last_name', 'address', 'city', 'state', 'postal_code', 'country'
    ]));

    return response()->json([
        'status' => true,
        'message' => 'Profile updated successfully.',
        'user' => $seller
    ], 200);
}

}
