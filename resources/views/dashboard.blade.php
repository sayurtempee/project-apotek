@extends('components.app')
@include('layouts.sidebar')

@section('content')
    {{-- SweetAlert --}}
    @if (session('login_success'))
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'success',
                    title: 'Selamat Datang, {{ session('login_name') }}',
                    html: 'Role Anda: <strong>{{ ucfirst(session('login_role')) }}</strong>',
                    confirmButtonText: 'Lanjutkan',
                    timer: 4000
                });
            });
        </script>
    @endif

    {{-- Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-10">
        @if (Auth::user()->role === 'admin')
            {{-- Admin Card --}}
            <x-dashboard-card title="Admin" :count="$adminCount" icon="fa-user-shield" bg="blue" />
            {{-- Kasir Card --}}
            <x-dashboard-card title="Kasir" :count="$kasirCount" icon="fa-cash-register" bg="green" />
            {{-- Total Penjualan --}}
            <x-dashboard-card title="Total Penjualan" :count="number_format($totalSales, 0, ',', '.')" icon="fa-sack-dollar" bg="purple" />
            {{-- Kategori Obat --}}
            <x-dashboard-card title="Kategori Obat" :count="$kategoriCount" icon="fa-tags" bg="indigo" />
        @endif

        @if (Auth::user()->role === 'kasir')
            {{-- Member --}}
            <x-dashboard-card title="Member" :count="$memberCount" icon="fa-users" bg="yellow" />
        @endif

        {{-- Penjualan Hari Ini --}}
        <x-dashboard-card title="Penjualan Hari Ini" :count="number_format($totalSalesToday, 0, ',', '.')" icon="fa-calendar-day" bg="green" />
        {{-- Obat --}}
        <x-dashboard-card title="Obat" :count="$obatCount" icon="fa-pills" bg="red" />
    </div>

    {{-- Statistik Penjualan --}}
    <div class="bg-white rounded-2xl shadow p-6 mb-8">
        <div class="flex flex-col md:flex-row md:items-center justify-between mb-4 gap-4">
            <div>
                <h2 class="text-xl font-semibold text-gray-800">Statistik Penjualan</h2>
                <p class="text-sm text-gray-500">Ringkasan transaksi berdasarkan filter</p>
            </div>

            <div class="flex flex-wrap gap-3">
                <div class="relative">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Filter</label>
                    <select id="filter-type" class="border rounded-md px-4 py-2">
                        <option value="day">Per Hari</option>
                        <option value="month" selected>Per Bulan</option>
                        <option value="year">Per Tahun</option>
                        <option value="all">Semua Transaksi</option>
                    </select>
                </div>
                <div id="dynamic-dropdown"></div>
                <div class="flex gap-3">
                    <a href="#" id="pdf-month"
                        class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Unduh PDF Bulan Ini</a>
                    <a href="#" id="pdf-year"
                        class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">Unduh PDF Tahun Ini</a>
                    <a href="{{ route('orders.download.pdf') }}" target="_blank"
                        class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">Unduh Seluruh PDF</a>
                </div>
            </div>
        </div>

        {{-- Charts --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <h2 class="font-semibold mb-2">Total Penjualan (Rp)</h2>
                <canvas id="salesChart" height="200"></canvas>
            </div>
            <div>
                <h2 class="font-semibold mb-2">Jumlah Transaksi</h2>
                <canvas id="transaksiChart" height="200"></canvas>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const filterType = document.getElementById('filter-type');
            const dynamicDropdown = document.getElementById('dynamic-dropdown');

            function renderDropdown(type) {
                const currentYear = new Date().getFullYear();
                let html = '';

                if (type === 'day') {
                    html = `<div class="relative">
                        <label class="block text-xs font-medium text-gray-600 mb-1">Pilih Tanggal</label>
                        <input type="date" id="day-select" class="border rounded-md px-4 py-2">
                    </div>`;
                } else if (type === 'month') {
                    html = `<div class="relative">
                        <label class="block text-xs font-medium text-gray-600 mb-1">Pilih Bulan</label>
                        <select id="month-select" class="border rounded-md px-4 py-2">
                            ${[...Array(12).keys()].map(m => `<option value="${m+1}">${new Date(0,m).toLocaleString('id-ID',{month:'long'})}</option>`).join('')}
                        </select>
                    </div>`;
                } else if (type === 'year') {
                    let years = @json($years);
                    html = `<div class="relative">
                        <label class="block text-xs font-medium text-gray-600 mb-1">Pilih Tahun</label>
                        <select id="year-select" class="border rounded-md px-4 py-2">
                            ${years.map(y => `<option value="${y}">${y}</option>`).join('')}
                        </select>
                    </div>`;
                } else if (type === 'all') {
                    html = '';
                }

                dynamicDropdown.innerHTML = html;
            }

            filterType.addEventListener('change', function() {
                renderDropdown(this.value);
            });

            // Event listener untuk update chart saat pilih tahun
            document.addEventListener('change', function(e) {
                if (e.target && e.target.id === 'year-select') {
                    let selectedYear = e.target.value;
                    fetch(`/dashboard/data/${selectedYear}`)
                        .then(res => res.json())
                        .then(data => renderCharts(data.labels, data.totalPenjualan, data.jumlahTransaksi));
                }
            });

            // Inisialisasi dropdown default
            renderDropdown(filterType.value);

            // Chart.js
            let salesChart, transaksiChart;

            function renderCharts(labels, totalPenjualan, jumlahTransaksi) {
                const salesCtx = document.getElementById('salesChart').getContext('2d');
                if (salesChart) salesChart.destroy();
                salesChart = new Chart(salesCtx, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Total Penjualan',
                            data: totalPenjualan,
                            borderColor: '#4F46E5',
                            backgroundColor: 'rgba(99,102,241,0.2)',
                            fill: true,
                            tension: 0.4
                        }]
                    },
                    options: {
                        responsive: true
                    }
                });

                const transaksiCtx = document.getElementById('transaksiChart').getContext('2d');
                if (transaksiChart) transaksiChart.destroy();
                transaksiChart = new Chart(transaksiCtx, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Jumlah Transaksi',
                            data: jumlahTransaksi,
                            backgroundColor: 'rgba(34,197,94,0.5)',
                            borderColor: '#22C55E',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true
                    }
                });
            }

            // Inisialisasi chart awal (bulan)
            const monthLabels = @json($salesPerYear->pluck('month')->map(fn($m) => \Carbon\Carbon::create()->month($m)->locale('id')->monthName));
            const totalPenjualan = @json($salesPerYear->pluck('total'));
            const jumlahTransaksi = @json($transactionsPerYear->pluck('count'));
            renderCharts(monthLabels, totalPenjualan, jumlahTransaksi);
        });
    </script>
@endsection
