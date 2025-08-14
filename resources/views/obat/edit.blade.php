@extends('components.app')
@include('layouts.sidebar')

@section('content')
    <h1 class="text-2xl font-bold mb-4">Edit Obat</h1>

    @if ($errors->any())
        <div class="bg-red-100 text-red-700 p-3 rounded mb-4">
            <ul class="list-disc ml-5">
                @foreach ($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('obat.update', $obat) }}" method="POST" enctype="multipart/form-data" class="space-y-4 max-w-lg">
        @csrf
        @method('PUT')
        <div class="mb-4">
            <label for="foto" class="block text-sm font-medium text-gray-700 mb-1">Foto Obat</label>

            <div class="flex items-start gap-6">
                {{-- Preview existing or placeholder --}}
                <div class="flex-shrink-0">
                    <div class="w-32 h-32 bg-gray-100 border rounded overflow-hidden flex items-center justify-center">
                        <img id="preview-img"
                            @if ($obat->foto) src="{{ asset('storage/' . $obat->foto) }}"
                    @else
                        src="https://via.placeholder.com/150?text=No+Image" @endif
                            alt="Preview Foto Obat" class="object-cover w-full h-full">
                    </div>
                </div>

                <div class="flex-1 space-y-2">
                    @if ($obat->foto)
                        <div class="flex items-center gap-2">
                            <input type="checkbox" name="hapus_foto" id="hapus_foto" value="1"
                                class="h-4 w-4 text-red-600 border-gray-300 rounded">
                            <label for="hapus_foto" class="text-sm text-red-600">Hapus foto lama</label>
                        </div>
                        <p class="text-xs text-gray-500">Unggah baru untuk mengganti, atau centang untuk menghapus tanpa
                            mengganti.</p>
                    @else
                        <p class="text-sm text-gray-600">Belum ada foto. Unggah untuk menambah.</p>
                    @endif

                    <div>
                        <input id="foto" type="file" name="foto" accept="image/*"
                            class="block w-full text-sm text-gray-700 file:border file:rounded file:px-3 file:py-2 file:bg-blue-50 file:border-blue-300 cursor-pointer">
                        @error('foto')
                            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <p class="text-xs text-gray-500">Maksimum 2MB. Format: JPEG, JPG, PNG, GIF.</p>
                </div>
            </div>
        </div>
        <div class="mb-3">
            <label class="block font-medium">Kode</label>
            <input type="text" name="kode" value="{{ old('kode', $obat->kode) }}" class="w-full border p-2 rounded"
                required>
        </div>
        <div class="mb-3">
            <label class="block font-medium">Nama</label>
            <input type="text" name="nama" value="{{ old('nama', $obat->nama) }}" class="w-full border p-2 rounded"
                required>
        </div>
        <div class="mb-3">
            <label class="block font-medium">Kategori</label>
            <select name="kategori_select" id="kategori-select" class="w-full border p-2 rounded" required>
                <option value="">Pilih kategori</option>
                @foreach ($categories as $cat)
                    <option value="{{ $cat->nama }}"
                        {{ old('kategori_select', $obat->category?->nama ?? '') === $cat->nama ? 'selected' : '' }}>
                        {{ $cat->nama }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="mb-3">
            <label for="kadaluarsa" class="form-label">Tanggal Kadaluarsa</label>
            <input type="date" name="kadaluarsa" id="kadaluarsa" class="form-control"
                value="{{ old('kadaluarsa', optional($obat->kadaluarsa)->format('Y-m-d')) }}">
            @error('kadaluarsa')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label class="block font-medium">Harga</label>
            <input type="number" name="harga" value="{{ old('harga', $obat->harga) }}" class="w-full border p-2 rounded"
                step="0.01" required>
        </div>
        <div class="mb-3">
            <label class="block font-medium">Stok</label>
            <input type="number" name="stok" value="{{ old('stok', $obat->stok) }}" class="w-full border p-2 rounded"
                required>
        </div>
        <div class="mb-3">
            <label class="block font-medium">Deskripsi</label>
            <textarea name="deskripsi" class="w-full border p-2 rounded" rows="4">{{ old('deskripsi', $obat->deskripsi) }}</textarea>
        </div>

        <div class="flex gap-3">
            <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded">Perbarui</button>
            <a href="{{ route('obat.index') }}" class="bg-gray-300 px-6 py-2 rounded">Batal</a>
        </div>
    </form>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const input = document.getElementById('foto');
            const previewImg = document.getElementById('preview-img');
            const hapusCheckbox = document.getElementById('hapus_foto');

            input?.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (!file) return;

                const reader = new FileReader();
                reader.onload = function(ev) {
                    previewImg.src = ev.target.result;
                };
                reader.readAsDataURL(file);

                // Jika pilih file baru, otomatis uncheck hapus
                if (hapusCheckbox) {
                    hapusCheckbox.checked = false;
                }
            });

            // Jika user centang "hapus", ganti preview ke placeholder
            hapusCheckbox?.addEventListener('change', function() {
                if (this.checked) {
                    previewImg.src = 'https://via.placeholder.com/150?text=No+Image';
                    // kosongkan input file supaya tidak kebalik
                    if (input) input.value = '';
                } else if ("{{ $obat->foto }}") {
                    // restore foto lama jika uncheck
                    previewImg.src = "{{ asset('storage/' . $obat->foto) }}";
                }
            });
        });
    </script>
@endsection
