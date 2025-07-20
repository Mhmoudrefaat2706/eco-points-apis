<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Material;
use Illuminate\Http\Request;
use App\Models\Feedback;
use Illuminate\Support\Facades\Log;

class AdminController extends Controller
{
    // Users
    public function getUsers()
    {
        try {
            $users = User::select([
                'id',
                'first_name',
                'last_name',
                'email',
                'role',
                'status',
                'created_at'
            ])->get();

            return response()->json($users);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error fetching users',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function blockUser($id)
    {
        $user = User::findOrFail($id);
        $user->status = 'blocked';
        $user->save();
        return response()->json(['message' => 'User blocked successfully']);
    }

    public function unblockUser($id)
    {
        $user = User::findOrFail($id);
        $user->status = 'active';
        $user->save();
        return response()->json(['message' => 'User unblocked successfully']);
    }

    public function deleteUser($id)
    {
        $user = User::findOrFail($id);
        $user->delete();
        return response()->json(['message' => 'User deleted successfully']);
    }

    public function createUser(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8',
            'role' => 'required|in:seller,buyer,admin'
        ]);

        $user = User::create($validated);
        return response()->json($user, 201);
    }



    // Materials
    public function getAllMaterials()
    {
        $materials = Material::with(['category', 'seller' => function ($query) {
            $query->select('id', 'first_name', 'last_name');
        }])->get();

        return response()->json($materials);
    }

    public function deleteMaterial($id)
    {
        $material = Material::findOrFail($id);
        $material->forceDelete();
        return response()->json(['message' => 'Material deleted permanently']);
    }

    public function blockMaterial($id)
    {
        $material = Material::findOrFail($id);
        $material->status = 'blocked';
        $material->save();
        return response()->json(['message' => 'Material blocked successfully']);
    }

    public function unblockMaterial($id)
    {
        $material = Material::findOrFail($id);
        $material->status = 'active';
        $material->save();
        return response()->json(['message' => 'Material unblocked successfully']);
    }

    public function updateMaterialStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:active,blocked,pending'
        ]);

        $material = Material::findOrFail($id);
        $material->status = $request->status;
        $material->save();

        return response()->json(['message' => 'Material status updated', 'data' => $material]);
    }

    public function getDashboardStats()
    {
        try {
            // Use eager loading and optimize queries
            $stats = [
                'users_count' => User::count(),
                'active_materials' => Material::where('status', 'active')->count(),
                'blocked_materials' => Material::where('status', 'blocked')->count(),
                'pending_materials' => Material::where('status', 'pending')->count(),
                'recent_users' => User::select(['id', 'first_name', 'last_name', 'email', 'created_at'])
                    ->latest()
                    ->take(5)
                    ->get()
                    ->map(function ($user) {
                        return [
                            'id' => $user->id,
                            'name' => $user->first_name . ' ' . $user->last_name,
                            'email' => $user->email,
                            'created_at' => $user->created_at
                        ];
                    }),
                'recent_materials' => Material::with(['category:id,name'])
                    ->select(['id', 'name', 'price', 'status', 'created_at', 'category_id'])
                    ->latest()
                    ->take(5)
                    ->get()
                    ->map(function ($material) {
                        return [
                            'id' => $material->id,
                            'name' => $material->name,
                            'price' => $material->price,
                            'status' => $material->status,
                            'category' => $material->category->name ?? 'N/A',
                            'created_at' => $material->created_at
                        ];
                    }),
                'materials_by_category' => Material::with(['category:id,name'])
                    ->select('category_id')
                    ->selectRaw('count(*) as count')
                    ->groupBy('category_id')
                    ->get()
                    ->map(function ($item) {
                        return [
                            'category' => $item->category->name ?? 'Uncategorized',
                            'count' => $item->count
                        ];
                    })->toArray() // Ensure we return an array
            ];

            return response()->json($stats);
        } catch (\Exception $e) {
            \Log::error('Dashboard stats error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error fetching dashboard statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Add these methods to AdminController

    public function getAllFeedbacks()
    {
        try {
            $feedbacks = Feedback::with(['seller', 'buyer'])
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json($feedbacks);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error fetching feedbacks',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function deleteFeedback($id)
    {
        try {
            $feedback = Feedback::findOrFail($id);
            $feedback->delete();

            return response()->json(['message' => 'Feedback deleted successfully']);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error deleting feedback',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
