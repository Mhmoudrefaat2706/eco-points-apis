<?php


namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SellerProfileController extends Controller
{
  public function profile()
{
    $user = auth()->user();
    return response()->json($user);
}
public function update(Request $request)
{
    $seller = auth()->user();

    $validator = Validator::make($request->all(), [
        'first_name' => 'required|string|max:50',
        'last_name' => 'required|string|max:50',
        'address' => 'nullable|string|max:255',
        'city' => 'nullable|string|max:50',
        'state' => 'nullable|string|max:50',
        'postal_code' => 'nullable|string|max:20',
        'country' => 'nullable|string|max:50',
        'paypal_client_id' => 'nullable|string|max:255',
        'paypal_client_secret' => 'nullable|string|max:255',
            'profile_image' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
    ]);
if ($request->hasFile('profile_image')) {
    $filename = uniqid() . '.' . $request->file('profile_image')->getClientOriginalExtension();
    $request->file('profile_image')->move(public_path('profiles'), $filename);
    $seller->profile_image = $filename;
}

$seller->update($request->only([
    'first_name', 'last_name', 'address', 'city', 'state',
    'postal_code', 'country', 'paypal_client_id', 'paypal_client_secret'
]));

$seller->save();

return response()->json([
    'status' => true,
    'message' => 'Profile updated successfully.',
    'user' => $seller
]);
}


}
