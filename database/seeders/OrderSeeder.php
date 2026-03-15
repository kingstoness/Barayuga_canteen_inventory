<?php

namespace Database\Seeders;

use App\Models\InventoryLog;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $products  = Product::all();
        $customers = User::where('role', 'customer')->get();
        $cashiers  = User::where('role', 'cashier')->get();
        $targetOrders = 220;
        $daysBack     = 60;
        $ordersSeeded = 0;
        $orderCounter = 1;
        for ($day = $daysBack; $day >= 0; $day--) {
            $date      = Carbon::now()->subDays($day);
            $dayOfWeek = $date->dayOfWeek; // 0 = Sunday, 6 = Saturday

            if ($dayOfWeek === 0 || $dayOfWeek === 6) {
                $ordersThisDay = rand(1, 3);
            } else {
                $ordersThisDay = rand(3, 6);
            }
            for ($o = 0; $o < $ordersThisDay && $ordersSeeded < $targetOrders; $o++) {
                // Random time during canteen hours (7am – 5pm)
                $orderTime = $date->copy()->setTime(rand(7, 16), rand(0, 59), rand(0, 59));
                $user = rand(0, 1) ? $customers->random() : null;
                if ($day > 7) {
                    $status = rand(0, 10) < 9 ? 'completed' : 'cancelled';
                } elseif ($day > 2) {
                    $status = collect(['completed', 'completed', 'cancelled'])->random();
                } else {
                    $status = collect(['pending', 'preparing', 'ready', 'completed'])->random();
                }
                $order = Order::create([
                    'order_number' => 'ORD-' . str_pad($orderCounter, 5, '0', STR_PAD_LEFT),
                    'user_id'      => $user?->id,
                    'total_amount' => 0,    // will be recalculated below
                    'status'       => $status,
                    'created_at'   => $orderTime,
                    'updated_at'   => $orderTime,
                ]);
                $itemCount      = rand(1, 4);
                $pickedProducts = $products->random(min($itemCount, $products->count()));
                $total          = 0;
                foreach ($pickedProducts as $product) {
                    $quantity  = rand(1, 3);
                    $unitPrice = $product->price;
                    $subtotal  = $quantity * $unitPrice;
                    $total    += $subtotal;
                    OrderItem::create([
                        'order_id'   => $order->id,
                        'product_id' => $product->id,
                        'quantity'   => $quantity,
                        'unit_price' => $unitPrice,
                        'subtotal'   => $subtotal,
                        'created_at' => $orderTime,
                        'updated_at' => $orderTime,
                    ]);
                    if ($status === 'completed') {
                        $cashier = $cashiers->random();
                        InventoryLog::create([
                            'product_id'      => $product->id,
                            'user_id'         => $cashier->id,
                            'type'            => 'sale',
                            'quantity_change' => -$quantity,
                            'stock_before'    => $product->current_stock + $quantity,
                            'stock_after'     => $product->current_stock,
                            'reason'          => 'Order #' . $order->order_number,
                            'created_at'      => $orderTime,
                            'updated_at'      => $orderTime,
                        ]);
                    }
                }
                $order->update(['total_amount' => $total]);
                $orderCounter++;
                $ordersSeeded++;
            }
        }
        $this->command->info("Seeded {$ordersSeeded} orders successfully.");
    }
}