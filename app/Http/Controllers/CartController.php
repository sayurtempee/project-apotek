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
        $request->validate([
            'no_telp' => 'required|string',
            'ids'     => 'required|string',
        ]);

        $target   = $request->input('no_telp');
        $idsArray = explode(',', $request->input('ids'));

        $items = \App\Models\OrderItem::with(['obat', 'order.member'])
            ->whereIn('id', $idsArray)
            ->get();

        if ($items->isEmpty()) {
            return response()->json(['message' => 'Transaksi tidak ditemukan'], 404);
        }

        $order = $items->first()->order;

        $subtotal       = $items->sum('line_total');
        $grandTotal     = $order->total ?? $subtotal;
        $paidAmount     = $order->paid_amount ?? 0;
        $change         = $order->change ?? 0;
        $discountAmount = $subtotal - $grandTotal;
        $discountPct    = $subtotal > 0 ? ($discountAmount / $subtotal) * 100 : 0;

        // Header
        $message  = "🏥 *Apotek Mii*\n";
        $message .= "Cakung Timur, Jakarta Timur\n";
        $message .= "Gang Bayam No.17\n";
        $message .= "Telp: (021) 78374839\n";
        $message .= str_repeat("=", 42) . "\n";
        $message .= "*Invoice #{$order->id}*\n";
        $message .= "Tanggal : " . $order->created_at->format('d-m-Y H:i') . "\n";

        // Member info kalau ada
        if ($order->member) {
            $message .= "Member : " . ($order->member->name ?? '-') . "\n";
            $message .= "No. Member : " . ($order->member->phone ?? '-') . "\n";
        }

        $message .= str_repeat("=", 42) . "\n\n";

        // Table Header
        $message .= str_pad("Nama Obat", 20) .
            str_pad("Harga", 10) .
            str_pad("Qty", 5) .
            "Subtotal\n";
        $message .= str_repeat("-", 42) . "\n";

        // Item list
        foreach ($items as $item) {
            $nama     = $item->product_name ?? ($item->obat->nama ?? '-');
            $harga    = number_format($item->price, 0, ',', '.');
            $qty      = $item->quantity;
            $subTotal = number_format($item->line_total, 0, ',', '.');

            $message .= str_pad(substr($nama, 0, 18), 20);
            $message .= str_pad("Rp$harga", 10);
            $message .= str_pad($qty, 5);
            $message .= "Rp$subTotal\n";
        }

        $message .= str_repeat("-", 42) . "\n";

        // Total barang
        $totalQty = $items->sum('quantity');
        $message .= str_pad("Total Barang: {$totalQty}", 25);
        $message .= "Total: Rp" . number_format($grandTotal, 0, ',', '.') . "\n";

        // Diskon jika ada
        if ($discountAmount > 0) {
            $message .= str_pad("Diskon Poin (" . number_format($discountPct, 2) . "%)", 25);
            $message .= "- Rp" . number_format($discountAmount, 0, ',', '.') . "\n";
        }

        // Jumlah bayar
        $message .= str_pad("Jumlah Bayar", 25);
        $message .= "Rp" . number_format($paidAmount, 0, ',', '.') . "\n";

        // Kembalian
        $message .= str_pad("Kembalian", 25);
        $message .= "Rp" . number_format($change, 0, ',', '.') . "\n";

        $message .= str_repeat("=", 42) . "\n";
        $message .= "_Terima kasih atas kunjungan Anda!_\n";
        $message .= "_Barang yang sudah dibeli tidak dapat dikembalikan._\n";

        // Kirim WA via Fonnte
        $response = $this->fonnteService->sendWhatsAppMessage($target, $message);

        if (!$response['status'] || (isset($response['data']['status']) && !$response['data']['status'])) {
            $errorReason = $response['data']['reason'] ?? 'Unknown error occurred';
            return response()->json(['message' => 'Error', 'error' => $errorReason], 500);
        }

        return back()->with('success', 'Invoice berhasil dikirim ke WhatsApp!');
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
