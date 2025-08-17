<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Cart;
use App\Models\Obat;
use App\Models\Order;
use App\Models\Member;
use App\Models\CartItem;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use App\Services\FonnteService;


class CartController extends Controller
{
    protected $fonnteService;

    public function __construct(FonnteService $fonnteService)
    {
        $this->fonnteService = $fonnteService;
    }
    public function index(Request $request)
    {
        $cart = Cart::current()->load('items.obat');

        // Atur waktu kadaluarsa jika ada item
        if ($cart->items->count() > 0) {
            if (!$cart->expires_at || $cart->isExpired()) {
                $cart->expires_at = now()->addMinutes(1);
                $cart->save();
            }
        } else {
            if ($cart->expires_at) {
                $cart->expires_at = null;
                $cart->save();
            }
        }

        $items = $cart->items;
        $subtotal = $items->sum(fn($item) => $item->obat->harga * $item->quantity);
        $totalQty = $items->sum('quantity');

        $phone = $request->input('phone', session('member_phone'));
        $member = null;
        $points = 0;
        $discount = 0;

        if ($phone) {
            session(['member_phone' => $phone]);

            $member = Member::where('phone', $phone)->where('is_active', true)->first();
            if ($member) {
                $points = $member->points;
                $discount = $points * 100; // 1 poin = Rp100
            }
        }

        $grandTotal = max(0, $subtotal - $discount);

        $title = 'Daftar Keranjang';
        $project = 'Apotek Mii';

        return view('cart.index', compact(
            'cart',
            'items',
            'subtotal',
            'discount',
            'grandTotal',
            'totalQty',
            'phone',
            'points',
            'member',
            'title',
            'project'
        ));
    }

    protected function getCart(): Cart
    {
        if (Auth::check()) {
            return Cart::firstOrCreate(['user_id' => Auth::id()]);
        }

        $cartId = Session::get('cart_id');
        if ($cartId) {
            $cart = Cart::find($cartId);
        }
        if (empty($cart)) {
            $cart = Cart::create();
            Session::put('cart_id', $cart->id);
        }
        return $cart;
    }

    public function scan(Request $request)
    {
        $request->validate([
            'barcode' => 'required|string',
        ]);

        $barcode = $request->input('barcode');

        $obat = Obat::where('kode', $barcode)->first(); // asumsi `kode` adalah barcode

        if (! $obat) {
            return response()->json(['message' => 'Obat tidak ditemukan.'], 404);
        }

        // ✅ Cek kadaluarsa
        if ($obat->kadaluarsa && Carbon::parse($obat->kadaluarsa)->lt(Carbon::today())) {
            return response()->json([
                'message' => "Obat {$obat->nama} sudah kadaluarsa dan tidak dapat dimasukkan ke keranjang."
            ], 400);
        }

        // ✅ Cek stok
        if (($obat->stok ?? 0) < 1) {
            return response()->json(['message' => 'Stok habis.'], 400);
        }

        $cart = $this->getCart();
        if (!$cart->expires_at || $cart->isExpired()) {
            $cart->expires_at = now()->addMinutes(1);
            $cart->save();
        }

        $item = $cart->items()->where('obat_id', $obat->id)->first();
        $price = $obat->harga;
        $name = $obat->nama;

        if ($item) {
            // cek kalau setelah ditambah tidak melebihi stok
            if ($item->quantity + 1 > $obat->stok) {
                return response()->json(['message' => "Stok tidak cukup. Maksimum: {$obat->stok}"], 400);
            }
            $item->quantity += 1;
        } else {
            $item = new CartItem([
                'obat_id' => $obat->id,
                'product_name' => $name,
                'price' => $price,
                'quantity' => 1,
                'line_total' => 0,
            ]);
            $cart->items()->save($item);
        }

        $item->line_total = $item->price * $item->quantity;
        $item->save();

        $cart->recalcTotals();

        return response()->json([
            'message' => 'Obat ditambahkan ke keranjang.',
            'product_name' => $name,
            'expires_at' => $cart->expires_at ? $cart->expires_at->toIso8601String() : null,
            'cart' => $cart->load('items'),
        ]);
    }

    public function show()
    {
        $cart = $this->getCart()->load('items');
        return view('cart.show', compact('cart'));
    }

    public function removeItem(Request $request, CartItem $item)
    {
        $cart = $this->getCart();

        // Pastikan item ini memang milik cart user yang sedang aktif
        if ($item->cart_id !== $cart->id) {
            return back()->with('error', 'Item tidak ditemukan di keranjang Anda.');
        }

        // Lock sebelum timer habis
        if (!$cart->isExpired()) {
            return back()->with('error', 'Item tidak bisa dihapus sebelum timer selesai.');
        }

        $item->delete();
        $cart->recalcTotals();

        if ($cart->items()->count() === 0) {
            $cart->expires_at = null;
            $cart->save();
        }

        return back()->with('success', 'Item dihapus.');
    }

    // Update quantity AJAX
    public function updateQuantity(Request $request, CartItem $item)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $cart = $this->getCart();

        // Pastikan item milik cart ini
        if ($item->cart_id !== $cart->id) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak.'
            ], 403);
        }

        $newQty = $request->input('quantity');
        $obat = Obat::find($item->obat_id);
        if (!$obat) {
            return response()->json([
                'success' => false,
                'message' => 'Obat tidak ditemukan.'
            ]);
        }

        if ($newQty > $obat->stok) {
            return response()->json([
                'success' => false,
                'message' => "Jumlah melebihi stok tersedia ({$obat->stok})."
            ]);
        }

        // Update quantity & line total
        $item->quantity = $newQty;
        $item->line_total = $item->price * $item->quantity;
        $item->save();

        // Hitung ulang total cart (subtotal, discount, grand_total)
        $cart->recalcTotals();

        // Kembalikan JSON
        return response()->json([
            'success' => true,
            'line_total' => $item->line_total,
            'cart_total' => $cart->grand_total
        ]);
    }

    public function checkout(Request $request)
    {
        $cart = $this->getCart()->load('items.obat');

        // Ambil hanya item yang dicentang
        $checkedItems = $cart->items->where('is_checked', true);

        if ($checkedItems->isEmpty()) {
            return back()->with('error', 'Pilih minimal satu barang untuk checkout.');
        }

        // --- CEK KADALUARSA OBAT ---
        foreach ($checkedItems as $item) {
            $obat = $item->obat;
            if ($obat->kadaluarsa && Carbon::parse($obat->kadaluarsa)->lt(Carbon::today())) {
                return back()->with('error', "Obat {$obat->nama} sudah kadaluarsa, transaksi dibatalkan.");
            }
        }

        $phone = $request->input('phone', session('member_phone'));

        $request->validate([
            'paid_amount' => 'required|numeric|min:0',
            'phone' => 'nullable|string',
            'pakai_diskon' => 'nullable|boolean',
        ]);

        if ($phone) {
            session(['member_phone' => $phone]);
        }

        $subtotal = $checkedItems->sum(fn($item) => $item->obat->harga * $item->quantity);

        $member = null;
        $earnedPoints = 0;
        $total = $subtotal;

        if ($phone) {
            $member = Member::where('phone', $phone)->first();
        }

        $discount = 0;
        $pointsUsed = 0;

        if ($member && $request->boolean('pakai_diskon')) {
            $points = $member->points;

            // Nilai per poin = Rp100
            $discount = $points * 100;

            // Pastikan diskon tidak melebihi subtotal
            if ($discount > $subtotal) {
                $pointsUsed = floor($subtotal / 100);
                $discount = $pointsUsed * 100;
            } else {
                $pointsUsed = $points;
            }

            $total -= $discount;
        }

        $paid = $request->input('paid_amount');
        $change = $paid - $total;

        DB::beginTransaction();
        try {
            $order = Order::create([
                'user_id' => auth()->id(),
                'total' => $total,
                'paid_amount' => $paid,
                'change' => $change,
                'phone' => $phone,
                'subtotal' => $subtotal,
            ]);

            foreach ($checkedItems as $item) {
                $obat = $item->obat;

                if ($obat->stok < $item->quantity) {
                    throw new \Exception("Stok untuk {$obat->nama} tidak mencukupi.");
                }

                $obat->stok -= $item->quantity;
                $obat->save();

                $order->items()->create([
                    'obat_id' => $obat->id,
                    'product_name' => $obat->nama,
                    'quantity' => $item->quantity,
                    'price' => $obat->harga,
                    'line_total' => $item->line_total,
                ]);
            }

            // Hapus hanya item yang dicentang dari keranjang
            $cart->items()->where('is_checked', true)->delete();

            // --- Proses poin member setelah checkout berhasil ---
            if ($member) {
                // Kurangi poin jika diskon dipakai
                if ($pointsUsed > 0) {
                    $member->points -= $pointsUsed;
                }

                // Tambah poin baru
                $earnedPoints = floor($subtotal / 10000);
                $member->points += $earnedPoints;
                $member->last_order_at = now();

                if (!$member->is_active && $subtotal >= 50000) {
                    $member->is_active = true;
                }

                $member->save();
            }

            DB::commit();

            // Hapus session member setelah transaksi selesai
            session()->forget('member_phone');

            return redirect()->route('order.invoice', $order->id)
                ->with('success', "Checkout berhasil! Anda mendapat $earnedPoints poin.");
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Terjadi kesalahan saat proses checkout: ' . $e->getMessage());
        }
    }

    public function deleteItem($id)
    {
        $cart = $this->getCart(); // sesuaikan dengan cara kamu mendapatkan keranjang
        $item = $cart->items()->find($id);

        if (!$item) {
            return back()->with('error', 'Item tidak ditemukan.');
        }

        $item->delete();

        return redirect()->route('obat.index')->with('success', 'Item berhasil dihapus dari keranjang.');
    }

    public function updateItem(Request $request, CartItem $item)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $cart = $this->getCart();

        // Pastikan item milik cart ini
        if ($item->cart_id !== $cart->id) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak.'
            ], 403);
        }

        $newQty = $request->input('quantity');
        $obat = Obat::find($item->obat_id);
        if (!$obat) {
            return response()->json([
                'success' => false,
                'message' => 'Obat tidak ditemukan.'
            ]);
        }

        // Cek stok
        if ($newQty > $obat->stok) {
            return response()->json([
                'success' => false,
                'message' => "Jumlah melebihi stok tersedia ({$obat->stok})."
            ]);
        }

        // Update quantity & line_total
        $item->quantity = $newQty;
        $item->line_total = $item->price * $item->quantity;
        $item->save();

        // Hitung ulang total cart
        $cart->recalcTotals();

        // Kembalikan JSON untuk AJAX
        return response()->json([
            'success' => true,
            'line_total' => $item->line_total,
            'cart_total' => $cart->grand_total
        ]);
    }

    public function invoiceShow(Order $order)
    {
        $order->load('items', 'member');
        return view('cart.invoice', compact('order'));
    }

    public function invoiceDownload(Order $order)
    {
        $order->load('items', 'member');
        $pdf = Pdf::loadView('cart.invoice', compact('order'));
        return $pdf->download('Invoice-' . $order->id . '.pdf');
    }

    public function downloadAllTransactions()
    {
        // Ambil semua order beserta relasinya (items, member)
        $orders = Order::with('items', 'member')
            ->orderBy('created_at', 'desc')
            ->get();

        // Gunakan view khusus semua transaksi
        $pdf = Pdf::loadView('cart.all-transactions-pdf', compact('orders'));

        return $pdf->download('Semua-Transaksi-' . now()->format('Y-m-d') . '.pdf');
    }

    public function transaction()
    {
        $transactions = Order::with([
            'items.obat.category', // items -> obat -> category
            'member'               // relasi member
        ])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('transaction.history', compact('transactions'), [
            'title' => 'History Transaksi',
            'project' => 'Apotek Mii',
        ]);
    }

    public function toggleCheck(Request $request, $id)
    {
        $item = CartItem::findOrFail($id);

        // Pastikan item milik cart user saat ini (opsional)
        $cart = auth()->user()->cart ?? null; // atau getCart()
        if ($cart && $item->cart_id !== $cart->id) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak'], 403);
        }

        // Update status checked
        $item->is_checked = $request->input('is_checked') ? 1 : 0;
        $item->save();

        // Hitung ulang total cart jika perlu
        if ($cart) $cart->recalcTotals();

        return response()->json(['success' => true]);
    }

    public function clearExpired(Cart $cart)
    {
        // Pastikan cart ini milik user login
        if ($cart->user_id !== auth()->id()) {
            abort(403);
        }

        // Hapus semua item
        $cart->items()->delete();
        return response()->json(['success' => true]);
    }

    public function sendWhatsappMessage(Request $request)
    {
        // Validasi input
        $request->validate([
            'no_telp' => 'required|string',
            'ids'     => 'required|string',
        ]);

        $target = $request->input('no_telp');
        $idsArray = explode(',', $request->input('ids'));

        // Ambil item beserta relasi order, obat, member
        $items = \App\Models\OrderItem::with(['obat', 'order.member'])
            ->whereIn('id', $idsArray)
            ->get();

        if ($items->isEmpty()) {
            return response()->json(['message' => 'Transaksi tidak ditemukan'], 404);
        }

        $order = $items->first()->order;

        $grandTotal  = $items->sum('line_total');
        $paidAmount  = $order->paid_amount ?? 0;
        $change      = $order->change ?? 0;
        $diskonPoin  = $order->diskon_poin ?? 0;
        $diskonPersen = $order->diskon_persen ?? 0;
        $potongan    = ($grandTotal * $diskonPersen) / 100;

        // Bangun pesan
        $message  = "🏥 Apotek Mii\n";
        $message .= "Jl. Ky Tinggi Rt 009 Rw 03, No.17\n";
        $message .= "Telp: 0812-3456-7890\n";
        $message .= str_repeat('-', 40) . "\n";
        $message .= "Tanggal       : " . now()->format('d-m-Y H:i') . "\n";
        $message .= "No. Transaksi : #" . $order->id . "\n";
        $message .= str_repeat('-', 40) . "\n";

        foreach ($items as $item) {
            $namaProduk = $item->product_name ?? ($item->obat->nama ?? '-');
            $qty        = $item->quantity;
            $harga      = number_format($item->price ?? $item->obat->harga ?? 0, 0, ',', '.');
            $subtotal   = number_format($item->line_total, 0, ',', '.');
            $message   .= "{$namaProduk}\n";
            $message   .= "{$qty} x Rp{$harga} = Rp{$subtotal}\n";
        }

        $message .= str_repeat('-', 40) . "\n";

        // Info member
        $memberName  = optional($order->member)->name ?? '-';
        $memberPhone = optional($order->member)->phone ?? '-';
        $message .= "Member : {$memberName}\n";
        $message .= "Nomor  : {$memberPhone}\n";

        // Diskon poin / persentase
        if ($diskonPoin > 0 || $diskonPersen > 0) {
            $message .= "Diskon:\n";
            if ($diskonPoin > 0)  $message .= "- Poin Digunakan : {$diskonPoin} poin\n";
            if ($diskonPersen > 0) $message .= "- Diskon         : {$diskonPersen}%\n";
            if ($potongan > 0)     $message .= "- Potongan Harga : Rp" . number_format($potongan, 0, ',', '.') . "\n";
        }

        $message .= "Uang Bayar : Rp" . number_format($paidAmount, 0, ',', '.') . "\n";
        $message .= "SubTOTAL   : Rp" . number_format($grandTotal, 0, ',', '.') . "\n";
        $message .= "Kembalian  : Rp" . number_format($change, 0, ',', '.') . "\n";
        $message .= str_repeat('-', 40) . "\n";
        $message .= "Terima kasih atas kunjungan Anda!\n";
        $message .= "Barang yang sudah dibeli tidak dapat dikembalikan.\n";

        // Kirim pesan via FonnteService
        $response = $this->fonnteService->sendWhatsAppMessage($target, $message);

        if (!$response['status'] || (isset($response['data']['status']) && !$response['data']['status'])) {
            $errorReason = $response['data']['reason'] ?? 'Unknown error occurred';
            return response()->json(['message' => 'Error', 'error' => $errorReason], 500);
        }

        return back()->with('success', 'Data berhasil dikirim ke WhatsApp!');
    }

    public function transactionDetail($id)
    {
        $transaction = Order::with('items.obat.category', 'member')->findOrFail($id);

        return view('transaction.detail', [
            'transaction' => $transaction,
            'title' => 'Detail Transaksi',
            'project' => 'Apotek Mii',
        ]);
    }
}
