<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\StockEntry;
use Illuminate\Http\Request;
class StockEntryController extends Controller
{
    public function index()
    {
        return response()->json(
            StockEntry::with(['product', 'supplier'])->orderByDesc('created_at')->get()
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
        $entry = StockEntry::create($request->only([
            'product_id', 'supplier_id', 'quantity', 'delivery_reference'
        ]));

        $entry->product->increment('current_stock', $entry->quantity);

        return response()->json($entry->load(['product', 'supplier']), 201);
    }
    public function show($id)
    {
        return response()->json(StockEntry::with(['product', 'supplier'])->findOrFail($id));
    }
    public function update(Request $r, $id)
    {
        $entry = StockEntry::findOrFail($id);
        $entry->update($r->only(['quantity', 'delivery_reference']));
        return response()->json($entry);
    }
    public function destroy($id)
    {
        StockEntry::findOrFail($id)->delete();
        return response()->json(['message' => 'Stock entry deleted.']);
    }
}