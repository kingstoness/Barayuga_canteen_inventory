<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class InventoryLog extends Model
{
    protected $fillable = [
        'product_id',
        'user_id',
        'type',
        'quantity_change',
        'stock_before',
        'stock_after',
        'reason',
    ];
    protected function casts(): array
    {
        return [
            'quantity_change' => 'integer',
            'stock_before'    => 'integer',
            'stock_after'     => 'integer',
        ];
    }
    const TYPE_RESTOCK    = 'restock';
    const TYPE_SALE       = 'sale';
    const TYPE_ADJUSTMENT = 'adjustment';
    const TYPE_WASTE      = 'waste';
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public static function record(
        Product $product,
        string  $type,
        int     $change,
        ?string $reason = null,
        ?int    $userId = null
    ): self {
        $stockBefore = $product->current_stock;

        return self::create([
            'product_id'      => $product->id,
            'user_id'         => $userId,
            'type'            => $type,
            'quantity_change' => $change,
            'stock_before'    => $stockBefore,
            'stock_after'     => $stockBefore + $change,
            'reason'          => $reason,
        ]);
    }
}