<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cart extends Model
{
    protected $fillable = [
        'user_id',
        'subtotal',
        'discount',
        'grand_total',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function recalcTotals(): void
    {
        $subtotal = $this->items()->get()->reduce(fn($carry, CartItem $item) => $carry + $item->line_total, 0);
        $discount = $this->calculateDiscount($subtotal);
        $grand = max(0, $subtotal - $discount);

        $this->subtotal = $subtotal;
        $this->discount = $discount;
        $this->grand_total = $grand;
        $this->save();
    }

    protected function calculateDiscount(float $subtotal): float
    {
        // Placeholder: bisa pakai logika member / status kasir / poin dsb.
        return 0;
    }

    // di Cart model
    public static function current()
    {
        if (auth()->check()) {
            return self::firstOrCreate(
                ['user_id' => auth()->id()],
                [
                    'subtotal' => 0,
                    'discount' => 0,
                    'grand_total' => 0,
                    'expires_at' => null,
                ]
            );
        } else {
            $sessionId = session()->getId();
            return self::firstOrCreate(
                ['session_id' => $sessionId],
                [
                    'subtotal' => 0,
                    'discount' => 0,
                    'grand_total' => 0,
                    'expires_at' => null,
                ]
            );
        }
    }

    public function total()
    {
        return $this->items->sum(function ($item) {
            return $item->obat->harga_jual * $item->quantity;
        });
    }

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public function isExpired(): bool
    {
        return $this->expires_at && now()->greaterThanOrEqualTo($this->expires_at);
    }
}
