<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    protected $fillable = [
        'cart_id',
        'obat_id',
        'product_name',
        'price',
        'quantity',
        'line_total',
        'is_checked'
    ];

    public function cart()
    {
        return $this->belongsTo(Cart::class);
    }

    public function obat()
    {
        return $this->belongsTo(Obat::class, 'obat_id');
    }
}
