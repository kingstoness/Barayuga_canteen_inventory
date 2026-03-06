<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\StockEntry;
use App\Models\Product;
use Illuminate\Http\Request;

class StockEntryController extends Controller
{
    public function index()
    {
        return response()->json(
            StockEntry::with(['product', 'supplier'])->get()
        );
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_id'         => 'required|exists:products,id',
            'supplier_id'        => 'required|exists:suppliers,id',
            'quantity'           => 'required|integer|min:1',
            'delivery_reference' => 'required|string|unique:stock_entries,delivery_reference',
        ]);

        $entry = StockEntry::create($request->only(
            'product_id', 'supplier_id', 'quantity', 'delivery_reference'
        ));

        // Business Rule: automatically increase product stock
        Product::findOrFail($request->product_id)
               ->increment('current_stock', $request->quantity);

        return response()->json($entry->load(['product', 'supplier']), 201);
    }

    public function show($id)
    {
        return response()->json(
            StockEntry::with(['product', 'supplier'])->findOrFail($id)
        );
    }

    public function destroy($id)
    {
        StockEntry::findOrFail($id)->delete();
        return response()->json(['message' => 'Stock entry deleted.']);
    }
}