<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Order extends Model
{
    protected $fillable = [
        'order_number',
        'user_id',
        'total_amount',
        'status',
        'notes',
    ];
    protected function casts(): array
    {
        return [
            'total_amount' => 'decimal:2',
        ];
    }
    const STATUS_PENDING    = 'pending';
    const STATUS_PREPARING  = 'preparing';
    const STATUS_READY      = 'ready';
    const STATUS_COMPLETED  = 'completed';
    const STATUS_CANCELLED  = 'cancelled';
    const VALID_TRANSITIONS = [
        'pending'   => ['preparing', 'cancelled'],
        'preparing' => ['ready',     'cancelled'],
        'ready'     => ['completed', 'cancelled'],
        'completed' => [],
        'cancelled' => [],   
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }
    public function canTransitionTo(string $newStatus): bool
    {
        $allowed = self::VALID_TRANSITIONS[$this->status] ?? [];
        return in_array($newStatus, $allowed);
    }
    public function recalculateTotal(): void
    {
        $this->update([
            'total_amount' => $this->items()->sum('subtotal'),
        ]);
    }
    public static function generateOrderNumber(): string
    {
        $latest = self::max('id') ?? 0;
        return 'ORD-' . str_pad($latest + 1, 5, '0', STR_PAD_LEFT);
    }
}