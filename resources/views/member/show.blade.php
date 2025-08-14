@extends('components.app')

@section('content')
    <div class="container mx-auto p-4">
        <h2 class="text-2xl font-bold mb-4">Detail Member</h2>

        <div class="space-y-2">
            <p><strong>Nama:</strong> {{ $member->name }}</p>
            <p><strong>Telepon:</strong> {{ $member->phone }}</p>
            <p><strong>Poin:</strong> {{ $member->points }}</p>
            <p><strong>Status:</strong>
                <span class="{{ $member->is_active ? 'text-green-500' : 'text-red-500' }}">
                    {{ $member->is_active ? 'Aktif' : 'Nonaktif' }}
                </span>
            </p>
            <p><strong>Transaksi Terakhir:</strong>
                {{ $member->last_order_at ? $member->last_order_at->format('d M Y, H:i') : 'Belum pernah' }}
            </p>
        </div>

        <a href="{{ route('members.index') }}" class="mt-4 inline-block text-blue-600">← Kembali ke daftar</a>
    </div>
@endsection
