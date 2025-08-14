<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'user_id',
        'subtotal',
        'discount',
        'status',
        'total',
        'paid_amount',
        'change',
        'phone',
    ];

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function member()
    {
        return $this->belongsTo(Member::class, 'phone', 'phone');
    }
}
