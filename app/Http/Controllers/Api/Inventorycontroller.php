<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\InventoryLog;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class InventoryController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with('category')
            ->select('id', 'product_code', 'product_name', 'category_id',
                     'current_stock', 'low_stock_threshold', 'is_available', 'price');

        // ?low_stock=1 — only show items at or below threshold
        if ($request->boolean('low_stock')) {
            $query->whereColumn('current_stock', '<=', 'low_stock_threshold');
        }
        $products = $query->orderBy('product_name')->get();
        $products->each(function ($product) {
            $product->is_low_stock = $product->isLowStock();
        });
        return response()->json($products);
    }
    public function adjust(Request $request, $id)
    {
        $request->validate([
            'quantity_change' => 'required|integer|not_in:0',
            'type'            => 'required|in:restock,adjustment,waste',
            'reason'          => 'nullable|string|max:255',
        ]);

        $product  = Product::findOrFail($id);
        $change   = $request->integer('quantity_change');
        $newStock = $product->current_stock + $change;

        if ($newStock < 0) {
            return response()->json([
                'message' => "Cannot reduce stock below zero. Current stock: {$product->current_stock}.",
            ], 422);
        }

        DB::transaction(function () use ($product, $change, $request) {
            $stockBefore = $product->current_stock;

            if ($change > 0) {
                $product->increment('current_stock', $change);
            } else {
                $product->decrement('current_stock', abs($change));
            }

            $product->refresh();
            InventoryLog::create([
                'product_id'      => $product->id,
                'user_id'         => $request->user()?->id,
                'type'            => $request->type,
                'quantity_change' => $change,
                'stock_before'    => $stockBefore,
                'stock_after'     => $product->current_stock,
                'reason'          => $request->reason ?? 'Manual adjustment',
            ]);
        });
        return response()->json([
            'message'       => 'Stock updated successfully.',
            'product_id'    => $product->id,
            'product_name'  => $product->product_name,
            'current_stock' => $product->fresh()->current_stock,
            'is_low_stock'  => $product->fresh()->isLowStock(),
        ]);
    }
    public function bulkRestock(Request $request)
    {
        $request->validate([
            'items'              => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity'   => 'required|integer|min:1',
            'items.*.reason'     => 'nullable|string|max:255',
        ]);

        $results = DB::transaction(function () use ($request) {
            $updated = [];
            foreach ($request->items as $line) {
                $product     = Product::lockForUpdate()->findOrFail($line['product_id']);
                $stockBefore = $product->current_stock;
                $quantity    = $line['quantity'];
                $product->increment('current_stock', $quantity);
                $product->refresh();
                InventoryLog::create([
                    'product_id'      => $product->id,
                    'user_id'         => $request->user()?->id,
                    'type'            => InventoryLog::TYPE_RESTOCK,
                    'quantity_change' => $quantity,
                    'stock_before'    => $stockBefore,
                    'stock_after'     => $product->current_stock,
                    'reason'          => $line['reason'] ?? 'Bulk restock',
                ]);
                $updated[] = [
                    'product_id'   => $product->id,
                    'product_name' => $product->product_name,
                    'stock_before' => $stockBefore,
                    'stock_after'  => $product->current_stock,
                ];
            }
            return $updated;
        });

        return response()->json([
            'message' => count($results) . ' product(s) restocked successfully.',
            'updated' => $results,
        ]);
    }
    public function logs(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $logs = InventoryLog::with('user:id,name')
            ->where('product_id', $id)
            ->orderByDesc('created_at')
            ->paginate(20);
        return response()->json([
            'product' => [
                'id'            => $product->id,
                'product_name'  => $product->product_name,
                'current_stock' => $product->current_stock,
            ],
            'logs' => $logs,
        ]);
    }
    public function lowStock()
    {
        $products = Product::with('category:id,name')
            ->whereColumn('current_stock', '<=', 'low_stock_threshold')
            ->where('is_available', true)
            ->orderBy('current_stock')
            ->get(['id', 'product_code', 'product_name', 'category_id',
                   'current_stock', 'low_stock_threshold']);
        return response()->json([
            'count'    => $products->count(),
            'products' => $products,
        ]);
    }
}