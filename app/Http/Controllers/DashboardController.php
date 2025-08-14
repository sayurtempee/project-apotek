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
use Illuminate\Support\Facades\Request;

class DashboardController extends Controller
{
    public function index()
    {
        // Informasi pengguna
        $adminCount = User::where('role', 'admin')->count();
        $kasirCount = User::where('role', 'kasir')->count();

        // Informasi entitas
        $obatCount = Obat::count();
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
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;

        $sales = Order::selectRaw('DAY(created_at) as label, SUM(total) as total')
            ->whereMonth('created_at', $currentMonth)
            ->whereYear('created_at', $currentYear)
            ->groupBy('label')
            ->orderBy('label')
            ->get();

        $pdf = Pdf::loadView('pdf.template', [
            'title' => 'Laporan Penjualan Bulan Ini',
            'column1' => 'Hari',
            'sales' => $sales
        ])->setPaper('a4', 'landscape');

        return $pdf->download('laporan-bulan-ini.pdf');
    }

    public function exportPdfYear()
    {
        $currentYear = Carbon::now()->year;

        $sales = Order::selectRaw('MONTH(created_at) as label, SUM(total) as total')
            ->whereYear('created_at', $currentYear)
            ->groupBy('label')
            ->orderBy('label')
            ->get();

        // Konversi angka bulan ke nama bulan
        foreach ($sales as $s) {
            $s->label = Carbon::create()->month($s->label)->format('F');
        }

        $pdf = Pdf::loadView('pdf.template', [
            'title' => 'Laporan Penjualan Tahun Ini',
            'column1' => 'Bulan',
            'sales' => $sales
        ])->setPaper('a4', 'landscape');

        return $pdf->download('laporan-tahun-ini.pdf');
    }

    public function exportPdfAll()
    {
        $sales = Order::selectRaw('DATE(created_at) as label, SUM(total) as total')
            ->groupBy('label')
            ->orderBy('label')
            ->get();

        // Format tanggal
        foreach ($sales as $s) {
            $s->label = Carbon::parse($s->label)->format('d M Y');
        }

        $pdf = Pdf::loadView('pdf.template', [
            'title' => 'Seluruh Penjualan',
            'column1' => 'Tanggal',
            'sales' => $sales
        ])->setPaper('a4', 'landscape');

        return $pdf->download('laporan-seluruh-penjualan.pdf');
    }

    public function getChartDataByYear($year)
    {
        $sales = Order::selectRaw('MONTH(created_at) as month, SUM(total) as total')
            ->whereYear('created_at', $year)
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $transactions = Order::selectRaw('MONTH(created_at) as month, COUNT(*) as count')
            ->whereYear('created_at', $year)
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        return response()->json([
            'labels' => $sales->pluck('month')->map(fn($m) => \Carbon\Carbon::create()->month($m)->locale('id')->monthName),
            'totalPenjualan' => $sales->pluck('total'),
            'jumlahTransaksi' => $transactions->pluck('count')
        ]);
    }
}
