<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class ReportController extends Controller
{
    private function dateRange(string $period): array
    {
        return match ($period) {
            'today'      => [now()->startOfDay(),              now()->endOfDay()],
            'this_week'  => [now()->startOfWeek(),             now()->endOfWeek()],
            'last_month' => [now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth()],
            default      => [now()->startOfMonth(),            now()->endOfMonth()], // this_month
        };
    }
    public function summary(Request $request)
    {
        [$from, $to] = $this->dateRange($request->period ?? 'this_month');

        $base = Order::whereBetween('created_at', [$from, $to])
                     ->where('status', '!=', 'cancelled');

        return response()->json([
            'total_revenue'   => (float) ($base->sum('total_amount') ?? 0),
            'total_orders'    => (int)   $base->count(),
            'avg_order_value' => (float) ($base->avg('total_amount') ?? 0),
            'highest_order'   => (float) ($base->max('total_amount') ?? 0),
        ]);
    }
    public function sales(Request $request)
    {
        $period = $request->period ?? 'daily';
        [$groupFmt, $labelFmt] = match ($period) {
            'weekly'  => ['%Y-%u', 'Week %u, %Y'],
            'monthly' => ['%Y-%m', '%b %Y'],
            default   => ['%Y-%m-%d', '%m/%d'],
        };
        $rows = Order::select(
                    DB::raw("DATE_FORMAT(created_at, '{$groupFmt}') as period_key"),
                    DB::raw("DATE_FORMAT(created_at, '{$labelFmt}') as label"),
                    DB::raw('SUM(total_amount) as revenue'),
                    DB::raw('COUNT(*) as order_count')
                )
                ->where('status', '!=', 'cancelled')
                ->groupBy('period_key', 'label')
                ->orderBy('period_key')
                ->limit(30)
                ->get();

        return response()->json(['data' => $rows]);
    }
    public function topItems(Request $request)
    {
        [$from, $to] = $this->dateRange($request->period ?? 'this_month');
        $rows = OrderItem::select(
                    'product_id',
                    DB::raw('SUM(quantity) as total_quantity'),
                    DB::raw('SUM(subtotal)  as total_revenue')
                )
                ->whereHas('order', fn($q) =>
                    $q->whereBetween('created_at', [$from, $to])
                      ->where('status', '!=', 'cancelled')
                )
                ->with('product:id,product_name,category_id')
                ->groupBy('product_id')
                ->orderByDesc('total_quantity')
                ->limit((int) ($request->limit ?? 8))
                ->get()
                ->map(fn($item) => [
                    'product_name'   => $item->product?->product_name ?? '—',
                    'total_quantity' => (int)   $item->total_quantity,
                    'total_revenue'  => (float) $item->total_revenue,
                ]);
        return response()->json(['data' => $rows]);
    }
    public function categoryBreakdown(Request $request)
    {
        [$from, $to] = $this->dateRange($request->period ?? 'this_month');

        $rows = OrderItem::select(
                    DB::raw('SUM(order_items.subtotal)  as total_revenue'),
                    DB::raw('SUM(order_items.quantity)  as total_quantity'),
                    'categories.name as category_name'
                )
                ->join('products',   'order_items.product_id', '=', 'products.id')
                ->join('categories', 'products.category_id',   '=', 'categories.id')
                ->whereHas('order', fn($q) =>
                    $q->whereBetween('created_at', [$from, $to])
                      ->where('status', '!=', 'cancelled')
                )
                ->groupBy('categories.id', 'categories.name')
                ->orderByDesc('total_revenue')
                ->get();
        return response()->json(['data' => $rows]);
    }
    public function orderTrends(Request $request)
    {
        $days = min((int) ($request->days ?? 30), 90);
        $rows = Order::select(
                    DB::raw('DATE(created_at) as date'),
                    DB::raw('COUNT(*)         as order_count'),
                    DB::raw('SUM(total_amount) as revenue')
                )
                ->where('created_at', '>=', now()->subDays($days)->startOfDay())
                ->where('status', '!=', 'cancelled')
                ->groupBy('date')
                ->orderBy('date')
                ->get();
        return response()->json(['data' => $rows]);
    }
}