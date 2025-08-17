<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrderItem extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'order_id',
        'obat_id',
        'product_name',
        'price',
        'quantity',
        'line_total'
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function obat()
    {
        return $this->belongsTo(Obat::class)->withTrashed();
    }
}
