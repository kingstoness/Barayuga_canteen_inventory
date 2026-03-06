<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = ['product_code', 'product_name', 'price', 'current_stock'];

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
}