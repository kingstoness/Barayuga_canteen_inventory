<?php
namespace App\Models;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
class User extends Authenticatable
{
    use Notifiable;
    protected $fillable = ['name', 'email', 'password', 'token', 'role'];
    protected $hidden   = ['password', 'token'];

    protected function casts(): array
    {
        return ['password' => 'hashed'];
    }
    public function isAdmin():    bool { return $this->role === 'admin'; }
    public function isCashier():  bool { return $this->role === 'cashier'; }
    public function isCustomer(): bool { return $this->role === 'customer'; }
    public function orders()        { return $this->hasMany(Order::class); }
    public function inventoryLogs() { return $this->hasMany(InventoryLog::class); }
}