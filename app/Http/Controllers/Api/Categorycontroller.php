<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
class CategoryController extends Controller
{
    public function index()
    {
        return response()->json(
            Category::withCount('products')->orderBy('name')->get()
        );
    }
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:100|unique:categories,name',
            'description' => 'nullable|string|max:255',
            'is_active'   => 'boolean',
        ]);
        $category = Category::create($data);
        return response()->json($category->loadCount('products'), 201);
    }
    public function show(Category $category)
    {
        return response()->json($category->loadCount('products'));
    }
    public function update(Request $request, Category $category)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:100|unique:categories,name,' . $category->id,
            'description' => 'nullable|string|max:255',
            'is_active'   => 'boolean',
        ]);
        $category->update($data);
        return response()->json($category->loadCount('products'));
    }
    public function destroy(Category $category)
    {
        if ($category->products()->exists()) {
            return response()->json([
                'message' => 'Cannot delete — this category still has products assigned to it.',
            ], 422);
        }
        $category->delete();
        return response()->json(['message' => 'Category deleted.']);
    }
}