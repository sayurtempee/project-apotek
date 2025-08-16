@extends('components.app')
@include('layouts.sidebar')

@section('content')
    <h1 class="text-2xl font-bold mb-4 text-white">Tambah Obat</h1>

    @if ($errors->any())
        <div class="bg-red-100 text-red-700 p-3 rounded mb-4">
            <ul class="list-disc ml-5">
                @foreach ($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('obat.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4 max-w-lg">
        @csrf
        <div class="mb-3">
            <label class="block font-medium text-gray-700 mb-1">Foto Obat (opsional)</label>
            <input type="file" name="foto" accept="image/jpeg,image/jpg,image/png,image/gif"
                class="block w-full border rounded px-2 py-1">
            @error('foto')
                <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
            @enderror
            <p class="text-xs text-gray-500 mt-1">Maks 2MB. Format: jpg, jpeg, png, gif.</p>
        </div>
        <div class="mb-3">
            <label class="block font-medium text-black">Kode</label>
            <input id="kode-input" type="text" name="kode" value="{{ old('kode') }}" i
                class="w-full border p-2 rounded text-black bg-gray-800" required>
        </div>
        <!-- Preview barcode -->
        <div class="mb-3 mt-4">
            <svg id="barcode"></svg>
        </div>
        <div class="mb-3">
            <label class="block font-medium text-black">Nama</label>
            <input type="text" name="nama" value="{{ old('nama') }}" class="w-full border p-2 rounded text-black"
                required>
        </div>
        <div class="mb-3">
            <label class="block font-medium text-black">Kategori</label>
            <select name="kategori_select" id="kategori-select" class="w-full border p-2 rounded" required>
                <option value="">Pilih kategori</option>
                @foreach ($categories as $cat)
                    <option value="{{ $cat->id }}" {{ old('kategori_select') == $cat->id ? 'selected' : '' }}>
                        {{ $cat->nama }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="mb-3">
            <label for="kadaluarsa" class="form-label">Tanggal Kadaluarsa</label>
            <input type="date" name="kadaluarsa" id="kadaluarsa" class="form-control" value="{{ old('kadaluarsa') }}">
            @error('kadaluarsa')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>
        <div class="mb-3">
            <label class="block font-medium text-black">Harga</label>
            <input type="number" name="harga" value="{{ old('harga') }}" class="w-full border p-2 rounded text-black"
                step="0.01" required>
        </div>
        <div class="mb-3">
            <label class="block font-medium text-black">Stok</label>
            <input type="number" name="stok" value="{{ old('stok', 0) }}" class="w-full border p-2 rounded text-black"
                required>
        </div>
        <div class="mb-3">
            <label class="block font-medium text-black">Satuan / Unit</label>
            <select name="unit_id" class="w-full border p-2 rounded text-black">
                <option value="">Pilih Satuan</option>
                @foreach ($units as $unit)
                    <option value="{{ $unit->id }}" {{ old('unit_id') == $unit->id ? 'selected' : '' }}>
                        {{ $unit->name }}
                    </option>
                @endforeach
            </select>
            @error('unit_id')
                <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
            @enderror
        </div>
        <div class="mb-3">
            <label class="block font-medium text-black">Deskripsi</label>
            <textarea name="deskripsi" class="w-full border p-2 rounded text-black" rows="4">{{ old('deskripsi') }}</textarea>
        </div>

        <div class="flex gap-3">
            <button type="submit" class="bg-blue-600 text-black px-6 py-2 rounded">Simpan</button>
            <a href="{{ route('obat.index') }}" class="bg-gray-300 px-6 py-2 rounded">Batal</a>
        </div>
    </form>

    <script>
        const input = document.getElementById('kode-input');
        const barcodeSvg = document.getElementById('barcode');

        function updateBarcode(value) {
            if (value.trim() === '') {
                barcodeSvg.innerHTML = '';
                return;
            }
            JsBarcode('#barcode', value, {
                format: "CODE128",
                displayValue: true,
                fontSize: 14,
                height: 60,
                margin: 10
            });
        }

        // Inisialisasi kalau ada old value
        updateBarcode(input.value);

        input.addEventListener('input', function() {
            updateBarcode(this.value);
        });
    </script>
@endsection
