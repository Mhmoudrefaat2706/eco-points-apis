<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Material;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MaterialController extends Controller
{
    //  Get all materials
    public function index()
    {
        return response()->json(Material::all());
    }

    //  Get materials for logged-in seller
    public function myMaterials()
    {
        $sellerId = Auth::id();
        $materials = Material::where('seller_id', $sellerId)->get();
        return response()->json($materials);
    }

    //  Add new material (only seller)
    public function store(Request $request)
    {
        if (Auth::user()->role !== 'seller') {
            return response()->json(['message' => 'Only sellers can add materials'], 403);
        }

        $request->validate([
            'name' => 'required|string|max:100',
            'category' => 'required|string|max:100',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'price_unit' => 'required|string|max:20',
            'image_url' => 'nullable|url',
        ]);

        $material = Material::create([
            'name' => $request->name,
            'category' => $request->category,
            'description' => $request->description,
            'price' => $request->price,
            'price_unit' => $request->price_unit,
            'image_url' => $request->image_url,
            'seller_id' => Auth::id(),
        ]);

        return response()->json(['message' => 'Material added', 'data' => $material], 201);
    }

    // Update material (only by owner seller)
    public function update(Request $request, $id)
    {
        $material = Material::findOrFail($id);

        if ($material->seller_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'name' => 'sometimes|required|string|max:100',
            'category' => 'sometimes|required|string|max:100',
            'description' => 'nullable|string',
            'price' => 'sometimes|required|numeric|min:0',
            'price_unit' => 'sometimes|required|string|max:20',
            'image_url' => 'nullable|url',
        ]);

        $material->update($request->all());

        return response()->json(['message' => 'Material updated', 'data' => $material]);
    }

    //  Delete material (only by owner seller)
    public function destroy($id)
    {
        $material = Material::findOrFail($id);

        if ($material->seller_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $material->delete();

        return response()->json(['message' => 'Material deleted']);
    }

    //  Get latest 6 materials
    public function latest()
    {
        $latest = Material::latest()->take(6)->get();
        return response()->json($latest);
    }
}
