<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
class MenuController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with('category');
        if ($request->filled('category')) {
            $query->whereHas('category', function ($q) use ($request) {
                $q->where('slug', $request->category);
            });
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('product_name', 'like', "%{$search}%")
                  ->orWhere('description',  'like', "%{$search}%");
            });
        }
        if ($request->boolean('available')) {
            $query->where('is_available', true);
        }
        $products = $query->orderBy('product_name')->get();
        $products->each(function ($product) {
            $product->is_low_stock = $product->isLowStock();
        });

        return response()->json($products);
    }
    public function show($id)
    {
        $product = Product::with(['category', 'stockEntries.supplier'])
            ->findOrFail($id);

        $product->is_low_stock = $product->isLowStock();

        return response()->json($product);
    }
    public function create()
    {
        $code = 'P' . str_pad(Product::count() + 1, 3, '0', STR_PAD_LEFT);
        return response()->json(['product_code' => $code]);
    }
    public function store(Request $request)
    {
        $request->validate([
            'product_name'        => 'required|string|max:255',
            'category_id'         => 'required|exists:categories,id',
            'description'         => 'nullable|string|max:500',
            'price'               => 'required|numeric|min:0',
            'current_stock'       => 'integer|min:0',
            'low_stock_threshold' => 'integer|min:1',
            'is_available'        => 'boolean',
            'image'               => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $code      = 'P' . str_pad(Product::count() + 1, 3, '0', STR_PAD_LEFT);
        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('menu', 'public');
        }
        $product = Product::create([
            'product_code'        => $code,
            'product_name'        => $request->product_name,
            'category_id'         => $request->category_id,
            'description'         => $request->description,
            'price'               => $request->price,
            'current_stock'       => $request->integer('current_stock', 0),
            'low_stock_threshold' => $request->integer('low_stock_threshold', 10),
            'is_available'        => $request->boolean('is_available', true),
            'image'               => $imagePath,
        ]);

        return response()->json($product->load('category'), 201);
    }
    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);
        $request->validate([
            'product_name'        => 'sometimes|string|max:255',
            'category_id'         => 'sometimes|exists:categories,id',
            'description'         => 'nullable|string|max:500',
            'price'               => 'sometimes|numeric|min:0',
            'current_stock'       => 'integer|min:0',
            'low_stock_threshold' => 'integer|min:1',
            'is_available'        => 'boolean',
            'image'               => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);
        $data = $request->only([
            'product_name', 'category_id', 'description',
            'price', 'current_stock', 'low_stock_threshold', 'is_available',
        ]);
        if ($request->hasFile('image')) {
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            $data['image'] = $request->file('image')->store('menu', 'public');
        }
        $product->update($data);
        return response()->json($product->load('category'));
    }
    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        if ($product->orderItems()->exists()) {
            return response()->json([
                'message' => 'Cannot delete a product that has existing orders. Mark it as unavailable instead.',
            ], 422);
        }
        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }
        $product->delete();
        return response()->json(['message' => 'Product deleted successfully.']);
    }
    public function toggle($id)
    {
        $product = Product::findOrFail($id);
        $product->update(['is_available' => !$product->is_available]);
        return response()->json([
            'message'      => 'Availability updated.',
            'is_available' => $product->is_available,
        ]);
    }
}