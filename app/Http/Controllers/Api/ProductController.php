<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index()
    {
        return response()->json(Product::all());
    }

    public function create()
    {
        $code = 'P' . str_pad(Product::count() + 1, 3, '0', STR_PAD_LEFT);
        return response()->json(['productCode' => $code]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_name' => 'required|string|max:255',
            'price'        => 'required|numeric|min:0',
        ]);

        $code = 'P' . str_pad(Product::count() + 1, 3, '0', STR_PAD_LEFT);

        $product = Product::create([
            'product_code'  => $code,
            'product_name'  => $request->product_name,
            'price'         => $request->price,
            'current_stock' => 0,
        ]);

        return response()->json($product, 201);
    }

    public function show($id)
    {
        $product = Product::with(['suppliers' => function ($q) {
            $q->withPivot('quantity', 'delivery_reference');
        }])->findOrFail($id);

        $totalStock = $product->stockEntries()->sum('quantity');

        return response()->json([
            'product'     => $product,
            'total_stock' => $totalStock,
        ]);
    }

    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $request->validate([
            'product_name' => 'sometimes|string|max:255',
            'price'        => 'sometimes|numeric|min:0',
        ]);

        $product->update($request->only('product_name', 'price'));
        return response()->json($product);
    }

    public function destroy($id)
    {
        Product::findOrFail($id)->delete();
        return response()->json(['message' => 'Product deleted.']);
    }
}