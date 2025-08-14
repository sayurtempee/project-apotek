<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Cart;

class ClearExpiredCarts extends Command
{
    protected $signature = 'cart:clear-expired';
    protected $description = 'Hapus semua item keranjang yang sudah expired';

    public function handle()
    {
        $carts = Cart::where('expires_at', '<', now())->get();

        foreach ($carts as $cart) {
            $cart->items()->delete(); // hapus item
            $cart->delete(); // hapus keranjang
        }

        $this->info('Waktu Sudah Habis.');
    }
}
