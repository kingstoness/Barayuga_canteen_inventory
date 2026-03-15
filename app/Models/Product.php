<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Product extends Model
{
    protected $fillable = [
        'product_code',
        'product_name',
        'category_id',
        'description',
        'price',
        'current_stock',
        'is_available',
        'low_stock_threshold',
        'image',
    ];
    protected function casts(): array
    {
        return [
            'price'               => 'decimal:2',
            'is_available'        => 'boolean',
            'current_stock'       => 'integer',
            'low_stock_threshold' => 'integer',
        ];
    }
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
    public function suppliers()
    {
        return $this->belongsToMany(Supplier::class, 'stock_entries')
                    ->withPivot('quantity', 'delivery_reference')
                    ->withTimestamps();
    }
    public function stockEntries()
    {
        return $this->hasMany(StockEntry::class);
    }
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }
    public function inventoryLogs()
    {
        return $this->hasMany(InventoryLog::class);
    }
    public function isLowStock(): bool
    {
        return $this->current_stock <= $this->low_stock_threshold;
    }
    public function deductStock(int $quantity): bool
    {
        if ($this->current_stock < $quantity) {
            return false;
        }

        $this->decrement('current_stock', $quantity);
        return true;
    }
    public function addStock(int $quantity): void
    {
        $this->increment('current_stock', $quantity);
    }
}