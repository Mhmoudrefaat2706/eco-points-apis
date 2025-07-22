<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Material;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class MaterialController extends Controller
{

    // Get all materials with relations
    // In the index method, add status filtering:
    public function index(Request $request)
    {
        $query = Material::with(['category', 'seller'])
            ->where('status', 'active');

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('category')) {
            if (is_numeric($request->category)) {
                $query->where('category_id', $request->category);
            } else {
                $query->whereHas('category', function ($q) use ($request) {
                    $q->where('name', $request->category);
                });
            }
        }

        if ($request->has('min_price') && $request->min_price !== null) {
            $query->where('price', '>=', $request->min_price);
        }

        if ($request->has('max_price') && $request->max_price !== null) {
            $query->where('price', '<=', $request->max_price);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $materials = $query->orderBy('created_at', 'desc')->paginate(8);
        return response()->json($materials);
    }

    // Get materials for logged-in seller
    public function myMaterials(Request $request)
    {
        $sellerId = Auth::id();

        // Check if user is seller
        if (Auth::user()->role !== 'seller') {
            return response()->json(['message' => 'Only sellers can view their materials'], 403);
        }

        $query = Material::with(['category'])->where('seller_id', $sellerId);

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('category')) {
            if (is_numeric($request->category)) {
                $query->where('category_id', $request->category);
            } else {
                $query->whereHas('category', function ($q) use ($request) {
                    $q->where('name', $request->category);
                });
            }
        }

        $materials = $query->orderBy('created_at', 'desc')->paginate(8);
        return response()->json($materials);
    }

    public function store(Request $request)
    {
        if (Auth::user()->role !== 'seller') {
            return response()->json(['message' => 'Only sellers can add materials'], 403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'description' => 'nullable|string',
            'price' => 'required|numeric',
            'price_unit' => 'required|string|max:50',
            'quantity' => 'numeric',
            'image_url' => 'nullable|string',
        ]);

        // Use the image_url from the request if provided
        $filename = $request->image_url;

        // Alternatively, if you still want to support direct file uploads:
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('materials'), $filename);
        }

        $material = Material::create([
            'name' => $request->name,
            'category_id' => $request->category_id,
            'description' => $request->description,
            'price' => $request->price,
            'price_unit' => $request->price_unit,
            'quantity' => $request->quantity ?? 100,
            'image_url' => $filename, // This will use either the uploaded filename or the provided image_url
            'seller_id' => Auth::id(),
        ]);

        return response()->json($material, 201);
    }



    // Update material (only by owner seller)
    public function update(Request $request, $id)
    {
        $material = Material::findOrFail($id);

        // Authorization check
        if ($material->seller_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'name' => 'sometimes|required|string|max:100',
            'category_id' => 'sometimes|required|exists:categories,id',
            'description' => 'nullable|string',
            'price' => 'sometimes|required|numeric|min:0',
            'price_unit' => 'sometimes|required|string|max:20',
            'image_url' => 'nullable|string',
            'quantity' => 'sometimes|required|integer|min:0'
        ]);

        $material->update($request->all());
        $material->load('category');

        return response()->json(['message' => 'Material updated', 'data' => $material]);
    }

    // Delete material (only by owner seller)
    public function destroy($id)
    {
        $material = Material::findOrFail($id);

        // Authorization check
        if ($material->seller_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $material->delete();
        return response()->json(['message' => 'Material deleted']);
    }

    // Get material details by ID
    public function show($id)
    {
        $material = Material::with(['category', 'seller'])->find($id);

        if (!$material) {
            return response()->json(['message' => 'Material not found.'], 404);
        }

        return response()->json(['material' => $material], 200);
    }

    // Get latest 6 materials
    public function latest()
    {
        $latest = Material::with(['category', 'seller'])
            ->latest('created_at')
            ->take(6)
            ->get();

        return response()->json($latest);
    }

    // Image upload with proper error handling
    public function uploadImage(Request $request)
    {
        // Authorization - only sellers can upload
        if (Auth::user()->role !== 'seller') {
            return response()->json(['message' => 'Only sellers can upload images'], 403);
        }

        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048'
        ]);

        try {
            // Create materials directory if not exists
            $path = public_path('materials');
            if (!file_exists($path)) {
                mkdir($path, 0777, true);
            }

            $file = $request->file('image');
            $filename = uniqid() . '.' . $file->getClientOriginalExtension();

            // Move file to public/materials
            $file->move($path, $filename);

            return response()->json([
                'filename' => $filename,
                'url' => asset('materials/' . $filename)
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getCategories()
    {
        return response()->json(Category::all());
    }
}
