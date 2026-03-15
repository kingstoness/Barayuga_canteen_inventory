<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\InventoryLog;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with(['user:id,name,email', 'items.product:id,product_name,price'])
            ->orderByDesc('created_at');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        $orders = $query->paginate(20);
        return response()->json($orders);
    }
    public function show($id)
    {
        $order = Order::with([
            'user:id,name,email',
            'items.product:id,product_name,price,image',
        ])->findOrFail($id);

        return response()->json($order);
    }
    public function store(Request $request)
    {
        $request->validate([
            'items'              => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity'   => 'required|integer|min:1',
            'user_id'            => 'nullable|exists:users,id',
            'notes'              => 'nullable|string|max:500',
        ]);
        $order = DB::transaction(function () use ($request) {
            $order = Order::create([
                'order_number' => Order::generateOrderNumber(),
                'user_id'      => $request->user_id,
                'total_amount' => 0,
                'status'       => Order::STATUS_PENDING,
                'notes'        => $request->notes,
            ]);
            $total = 0;
            foreach ($request->items as $line) {
                $product  = Product::lockForUpdate()->findOrFail($line['product_id']);
                $quantity = $line['quantity'];
                if (!$product->is_available) {
                    throw new \Exception("'{$product->product_name}' is currently unavailable.");
                }
                if ($product->current_stock < $quantity) {
                    throw new \Exception(
                        "Insufficient stock for '{$product->product_name}'. " .
                        "Available: {$product->current_stock}, requested: {$quantity}."
                    );
                }
                $unitPrice = $product->price;
                $subtotal  = $quantity * $unitPrice;
                $total    += $subtotal;

                OrderItem::create([
                    'order_id'   => $order->id,
                    'product_id' => $product->id,
                    'quantity'   => $quantity,
                    'unit_price' => $unitPrice,
                    'subtotal'   => $subtotal,
                ]);
                $stockBefore = $product->current_stock;
                $product->decrement('current_stock', $quantity);
                $product->refresh();
                InventoryLog::create([
                    'product_id'      => $product->id,
                    'user_id'         => $request->user()?->id,
                    'type'            => InventoryLog::TYPE_SALE,
                    'quantity_change' => -$quantity,
                    'stock_before'    => $stockBefore,
                    'stock_after'     => $product->current_stock,
                    'reason'          => 'Order #' . $order->order_number,
                ]);
            }
            $order->update(['total_amount' => $total]);

            return $order;
        });
        return response()->json(
            $order->load(['user:id,name,email', 'items.product:id,product_name,price,image']),
            201
        );
    }
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,preparing,ready,completed,cancelled',
        ]);
        $order     = Order::findOrFail($id);
        $newStatus = $request->status;
        if (!$order->canTransitionTo($newStatus)) {
            return response()->json([
                'message' => "Cannot move order from '{$order->status}' to '{$newStatus}'.",
            ], 422);
        }
        if ($newStatus === Order::STATUS_CANCELLED) {
            DB::transaction(function () use ($order, $request) {
                foreach ($order->items as $item) {
                    $product     = $item->product;
                    $stockBefore = $product->current_stock;

                    $product->increment('current_stock', $item->quantity);
                    $product->refresh();
                    InventoryLog::create([
                        'product_id'      => $product->id,
                        'user_id'         => $request->user()?->id,
                        'type'            => InventoryLog::TYPE_ADJUSTMENT,
                        'quantity_change' => $item->quantity,
                        'stock_before'    => $stockBefore,
                        'stock_after'     => $product->current_stock,
                        'reason'          => 'Order #' . $order->order_number . ' cancelled — stock restored',
                    ]);
                }
            });
        }
        $order->update(['status' => $newStatus]);

        return response()->json([
            'message' => 'Order status updated.',
            'order'   => $order->load('items.product'),
        ]);
    }
    public function myOrders(Request $request)
    {
        $orders = Order::with(['items.product:id,product_name,price,image'])
            ->where('user_id', $request->user()->id)
            ->orderByDesc('created_at')
            ->paginate(10);
        return response()->json($orders);
    }
}