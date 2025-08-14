<?php

namespace App\Providers;

use Carbon\Carbon;
use App\Models\Cart;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('*', function ($view) {
            // logic mirip getCart
            if (Auth::check()) {
                $cart = Cart::firstOrCreate(['user_id' => Auth::id()]);
            } else {
                $cartId = Session::get('cart_id');
                if ($cartId) {
                    $cart = Cart::find($cartId);
                }
                if (empty($cart)) {
                    $cart = Cart::create();
                    Session::put('cart_id', $cart->id);
                }
            }

            $cart->load('items');
            $cartCount = $cart->items->sum('quantity');

            $view->with('cartCount', $cartCount);
        });

        Carbon::setLocale('id');
    }
}
