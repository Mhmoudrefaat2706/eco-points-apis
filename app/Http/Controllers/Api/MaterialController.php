<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Material;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MaterialController extends Controller
{
    /**
     * Display a listing of the materials.
     */
    public function index()
    {
        $materials = Material::with(['seller' => function($query) {
            $query->select('id', 'first_name', 'last_name', 'email');
        }])->get();
        
        return response()->json($materials);
    }

    /**
     * Store a newly created material.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'category' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'price_unit' => 'required|in:piece,kg,m²,m³',
            'image_url' => 'nullable|url|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $material = Material::create(array_merge(
            $request->all(),
            ['seller_id' => auth()->id()]
        ));

        return response()->json(
            $this->formatMaterialResponse($material),
            201
        );
    }

    /**
     * Display the specified material.
     */
    public function show(Material $material)
    {
        // Only allow viewing if owner or admin
        if ($material->seller_id !== auth()->id() && !auth()->user()->is_admin) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json(
            $this->formatMaterialResponse($material)
        );
    }

    /**
     * Update the specified material.
     */
    public function update(Request $request, Material $material)
    {
        // Prevent changing seller_id unless admin
        if ($request->has('seller_id') && !auth()->user()->is_admin) {
            return response()->json([
                'message' => 'You cannot change the seller of a material'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:100',
            'category' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'price' => 'sometimes|numeric|min:0',
            'price_unit' => 'sometimes|in:piece,kg,m²,m³',
            'image_url' => 'nullable|url|max:255',
            'seller_id' => 'sometimes|exists:users,id'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $material->update($request->all());

        return response()->json(
            $this->formatMaterialResponse($material)
        );
    }

    /**
     * Remove the specified material.
     */
    public function destroy(Material $material)
    {
        // Only allow seller or admin to delete
        if ($material->seller_id !== auth()->id() && !auth()->user()->is_admin) {
            return response()->json([
                'message' => 'You can only delete your own materials'
            ], 403);
        }

        $material->delete();
        return response()->json(null, 204);
    }

    /**
     * Format material response with limited seller info
     */
    protected function formatMaterialResponse(Material $material)
    {
        $material->load(['seller' => function($query) {
            $query->select('id', 'first_name', 'last_name', 'email');
        }]);
        
        return $material;
    }
}