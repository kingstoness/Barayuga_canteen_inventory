<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    protected $fillable = ['supplier_code', 'supplier_name', 'contact_email', 'contact_number'];

    public function products()
    {
        return $this->belongsToMany(Product::class, 'stock_entries')
                    ->withPivot('quantity', 'delivery_reference')
                    ->withTimestamps();
    }

    public function stockEntries()
    {
        return $this->hasMany(StockEntry::class);
    }
}