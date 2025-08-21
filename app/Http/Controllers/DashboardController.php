<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Obat;
use App\Models\User;
use App\Models\Order;
use App\Models\Member;
use App\Models\Category;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Dompdf\Dompdf;
use Dompdf\Options;

class DashboardController extends Controller
{
    public function index()
    {
        // Informasi pengguna
        $adminCount = User::where('role', 'admin')->count();
        $kasirCount = User::where('role', 'kasir')->count();

        // Informasi entitas
        $user = auth()->user();

        if ($user->role === 'kasir') {
            // Kasir: hanya hitung obat yang belum kadaluarsa
            $obatCount = Obat::where('kadaluarsa', '>=', now())->count();
        } else {
            // Admin: semua obat dihitung
            $obatCount = Obat::count();
        }

        $kategoriCount = Category::count();
        $memberCount = Member::count();

        // Informasi penjualan
        $totalSales = (float) Order::sum('total');
        $totalSalesToday = (float) Order::whereDate('created_at', today())->sum('total');

        $currentYear = now()->year;

        // Penjualan per bulan di tahun ini
        $salesPerYear = Order::selectRaw('MONTH(created_at) as month, SUM(total) as total')
            ->whereYear('created_at', $currentYear)
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // Jumlah transaksi per bulan di tahun ini
        $transactionsPerYear = Order::selectRaw('MONTH(created_at) as month, COUNT(*) as count')
            ->whereYear('created_at', $currentYear)
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // Ambil daftar tahun yang ada di database
        $years = Order::selectRaw('YEAR(created_at) as year')
            ->distinct()
            ->orderByDesc('year')
            ->pluck('year');

        $title = 'Dashboard';
        $project = 'Apotek Mii';

        return view('dashboard', compact(
            'adminCount',
            'kasirCount',
            'obatCount',
            'kategoriCount',
            'totalSales',
            'totalSalesToday',
            'salesPerYear',
            'transactionsPerYear',
            'title',
            'project',
            'memberCount',
            'years'
        ));
    }

    public function exportPdfMonth()
    {
        $month = Carbon::now()->month;
        $year = Carbon::now()->year;

        $items = DB::table('order_items')
            ->select(
                'product_name',
                DB::raw('SUM(quantity) as total_qty'),
                DB::raw('SUM(line_total) as total_sales')
            )
            ->whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
            ->groupBy('product_name')
            ->orderBy('product_name', 'asc')
            ->get();

        return $this->generatePdf($items, "Rekap Penjualan Bulan " . Carbon::now()->translatedFormat('F Y'));
    }

    public function exportPdfYear()
    {
        $year = Carbon::now()->year;

        $items = DB::table('order_items')
            ->select(
                'product_name',
                DB::raw('SUM(quantity) as total_qty'),
                DB::raw('SUM(line_total) as total_sales')
            )
            ->whereYear('created_at', $year)
            ->groupBy('product_name')
            ->orderBy('product_name', 'asc')
            ->get();

        return $this->generatePdf($items, "Rekap Penjualan Tahun " . $year);
    }

    public function exportPdfAll()
    {
        $items = DB::table('order_items')
            ->select(
                'product_name',
                DB::raw('SUM(quantity) as total_qty'),
                DB::raw('SUM(line_total) as total_sales')
            )
            ->groupBy('product_name')
            ->orderBy('product_name', 'asc')
            ->get();

        return $this->generatePdf($items, "Rekap Penjualan - Semua Data");
    }

    private function generatePdf($items, $title)
    {
        $total = $items->sum('total_sales');

        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);

        $dompdf = new Dompdf($options);

        $html = view('pdf.rekap-obat', compact('items', 'title', 'total'))->render();

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return $dompdf->stream(str_replace(' ', '_', $title) . '.pdf');
    }

    public function getChartData(Request $request)
    {
        $filter = $request->query('filter', 'month');
        $year = $request->query('year', now()->year);
        $month = $request->query('month', now()->month);
        $date = $request->query('date', now()->toDateString());

        if ($filter === 'day') {
            $sales = Order::selectRaw('HOUR(created_at) as label, SUM(total) as total')
                ->whereDate('created_at', $date)
                ->groupBy('label')
                ->orderBy('label')
                ->get();

            $transactions = Order::selectRaw('HOUR(created_at) as label, COUNT(*) as count')
                ->whereDate('created_at', $date)
                ->groupBy('label')
                ->orderBy('label')
                ->get();

            $labels = $sales->pluck('label')->map(fn($h) => $h . ':00');
        } elseif ($filter === 'month') {
            $sales = Order::selectRaw('DAY(created_at) as label, SUM(total) as total')
                ->whereYear('created_at', $year)
                ->whereMonth('created_at', $month)
                ->groupBy('label')
                ->orderBy('label')
                ->get();

            $transactions = Order::selectRaw('DAY(created_at) as label, COUNT(*) as count')
                ->whereYear('created_at', $year)
                ->whereMonth('created_at', $month)
                ->groupBy('label')
                ->orderBy('label')
                ->get();

            $labels = $sales->pluck('label');
        } elseif ($filter === 'year') {
            $sales = Order::selectRaw('MONTH(created_at) as label, SUM(total) as total')
                ->whereYear('created_at', $year)
                ->groupBy('label')
                ->orderBy('label')
                ->get();

            $transactions = Order::selectRaw('MONTH(created_at) as label, COUNT(*) as count')
                ->whereYear('created_at', $year)
                ->groupBy('label')
                ->orderBy('label')
                ->get();

            $labels = $sales->pluck('label')->map(fn($m) => Carbon::create()->month($m)->locale('id')->monthName);
        } else { // all
            $sales = Order::selectRaw('DATE(created_at) as label, SUM(total) as total')
                ->groupBy('label')
                ->orderBy('label')
                ->get();

            $transactions = Order::selectRaw('DATE(created_at) as label, COUNT(*) as count')
                ->groupBy('label')
                ->orderBy('label')
                ->get();

            $labels = $sales->pluck('label')->map(fn($d) => Carbon::parse($d)->format('d M Y'));
        }

        return response()->json([
            'labels' => $labels,
            'totalPenjualan' => $sales->pluck('total'),
            'jumlahTransaksi' => $transactions->pluck('count')
        ]);
    }
}
