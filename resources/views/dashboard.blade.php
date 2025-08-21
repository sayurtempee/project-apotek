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
            <x-dashboard-card title="Admin" :count="$adminCount" icon="fa-solid fa-user-shield" bg="from-blue-500 to-blue-600" />
            <x-dashboard-card title="Kasir" :count="$kasirCount" icon="fa-solid fa-cash-register"
                bg="from-green-500 to-green-600" />
            <x-dashboard-card title="Total Penjualan" :count="number_format($totalSales, 0, ',', '.')" icon="fa-solid fa-sack-dollar"
                bg="from-purple-500 to-purple-600" />
            <x-dashboard-card title="Obat" :count="$obatCount" icon="fa-solid fa-pills" bg="from-red-500 to-red-600" />
            <x-dashboard-card title="Kategori" :count="$kategoriCount" icon="fa-solid fa-pills" bg="from-red-500 to-red-600" />
        @endif

        @if (Auth::user()->role === 'kasir')
            <x-dashboard-card title="Member" :count="$memberCount" icon="fa-solid fa-users"
                bg="from-yellow-500 to-yellow-600" />
            <x-dashboard-card title="Obat" :count="$obatCount" icon="fa-solid fa-pills" bg="from-red-500 to-red-600" />
        @endif

        <x-dashboard-card title="Penjualan Hari Ini" :count="number_format($totalSalesToday, 0, ',', '.')" icon="fa-solid fa-calendar-day"
            bg="from-green-500 to-green-600" />
    </div>

    {{-- Statistik Penjualan --}}
    <div class="bg-white rounded-2xl shadow-lg p-6 mb-8 border border-gray-100">
        <div class="flex flex-col md:flex-row md:items-center justify-between mb-6 gap-4">
            <div>
                <h2 class="text-xl font-bold text-gray-800">📊 Statistik Penjualan</h2>
                <p class="text-sm text-gray-500">Lihat performa penjualan berdasarkan periode</p>
            </div>

            <div class="flex flex-wrap items-end gap-3">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Filter</label>
                    <select id="filter-type" class="border rounded-lg px-4 py-2 focus:ring focus:ring-blue-200">
                        <option value="day">Per Hari</option>
                        <option value="month" selected>Per Bulan</option>
                        <option value="year">Per Tahun</option>
                        <option value="all">Semua Transaksi</option>
                    </select>
                </div>
                <div id="dynamic-dropdown"></div>

                <div class="flex gap-2">
                    <a href="{{ route('dashboard.export.pdf.month') }}"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg shadow hover:bg-blue-700 transition no-underline hover:no-underline">
                        Bulan Ini
                    </a>
                    <a href="{{ route('dashboard.export.pdf.year') }}"
                        class="px-4 py-2 bg-green-600 text-white rounded-lg shadow hover:bg-green-700 transition no-underline hover:no-underline">
                        Tahun Ini
                    </a>
                    <a href="{{ route('dashboard.export.pdf.all') }}"
                        class="px-4 py-2 bg-red-600 text-white rounded-lg shadow hover:bg-red-700 transition no-underline hover:no-underline">
                        Semua Data
                    </a>
                </div>
            </div>
        </div>

        {{-- Charts --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-gray-50 p-4 rounded-xl">
                <h2 class="font-semibold text-gray-700 mb-3">Total Penjualan (Rp)</h2>
                <canvas id="salesChart" height="200"></canvas>
            </div>
            <div class="bg-gray-50 p-4 rounded-xl">
                <h2 class="font-semibold text-gray-700 mb-3">Jumlah Transaksi</h2>
                <canvas id="transaksiChart" height="200"></canvas>
            </div>
        </div>
    </div>

    {{-- Script Chart --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const filterType = document.getElementById('filter-type');
            const dynamicDropdown = document.getElementById('dynamic-dropdown');
            let salesChart, transaksiChart;

            function renderDropdown(type) {
                let html = '';
                if (type === 'day') {
                    html = `<div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Pilih Tanggal</label>
                        <input type="date" id="day-select" class="border rounded-md px-4 py-2">
                    </div>`;
                } else if (type === 'month') {
                    html = `<div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Pilih Bulan</label>
                        <select id="month-select" class="border rounded-md px-4 py-2">
                            ${[...Array(12).keys()].map(m => `<option value="${m+1}">${new Date(0,m).toLocaleString('id-ID',{month:'long'})}</option>`).join('')}
                        </select>
                    </div>`;
                } else if (type === 'year') {
                    let years = @json($years);
                    html = `<div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Pilih Tahun</label>
                        <select id="year-select" class="border rounded-md px-4 py-2">
                            ${years.map(y => `<option value="${y}">${y}</option>`).join('')}
                        </select>
                    </div>`;
                }
                dynamicDropdown.innerHTML = html;
            }

            function fetchChartData(params = {}) {
                let query = new URLSearchParams(params).toString();
                fetch(`{{ route('dashboard.data') }}?${query}`)
                    .then(res => res.json())
                    .then(data => renderCharts(data.labels, data.totalPenjualan, data.jumlahTransaksi));
            }

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
                        plugins: {
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                grid: {
                                    color: '#f3f4f6'
                                }
                            },
                            x: {
                                grid: {
                                    display: false
                                }
                            }
                        }
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
                        plugins: {
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                grid: {
                                    color: '#f3f4f6'
                                }
                            },
                            x: {
                                grid: {
                                    display: false
                                }
                            }
                        }
                    }
                });
            }

            filterType.addEventListener('change', function() {
                renderDropdown(this.value);
                attachDropdownListener(this.value);
            });

            function attachDropdownListener(type) {
                if (type === 'day') {
                    document.getElementById('day-select').addEventListener('change', function() {
                        fetchChartData({
                            filter: 'day',
                            date: this.value
                        });
                    });
                } else if (type === 'month') {
                    document.getElementById('month-select').addEventListener('change', function() {
                        fetchChartData({
                            filter: 'month',
                            year: new Date().getFullYear(),
                            month: this.value
                        });
                    });
                } else if (type === 'year') {
                    document.getElementById('year-select').addEventListener('change', function() {
                        fetchChartData({
                            filter: 'year',
                            year: this.value
                        });
                    });
                } else {
                    fetchChartData({
                        filter: 'all'
                    });
                }
            }

            // Init
            renderDropdown(filterType.value);
            attachDropdownListener(filterType.value);
            fetchChartData({
                filter: 'month',
                year: new Date().getFullYear(),
                month: new Date().getMonth() + 1
            });
        });
    </script>
@endsection
