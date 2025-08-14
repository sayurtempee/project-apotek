@extends('components.app')
@include('layouts.sidebar')

@section('content')
    <div class="mb-4">
        <h1 class="text-2xl font-bold text-black">Daftar Admin</h1>
        <p class="text-gray-500">Total Admin: {{ $admins->count() }}</p>
    </div>

    <div class="bg-white rounded shadow overflow-hidden">
        <table class="w-full text-left border-collapse">
            <thead class="bg-gray-100">
                <tr>
                    <th class="p-3">#</th>
                    <th class="p-3">Foto</th>
                    <th class="p-3">Nama</th>
                    <th class="p-3">Email</th>
                    <th class="p-3">Role</th>
                    <th class="p-3">Status</th>
                    <th class="p-3">Dibuat Pada</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($admins as $index => $admin)
                    <tr class="border-t">
                        <td class="p-3">{{ $index + 1 }}</td>
                        <td class="p-3">
                            @if ($admin->foto)
                                <img src="{{ asset('storage/' . $admin->foto) }}" alt="{{ $admin->name }}"
                                    class="w-10 h-10 rounded-full object-cover">
                            @else
                                <img src="{{ asset('images/default-profile.png') }}" alt="Default"
                                    class="w-10 h-10 rounded-full object-cover">
                            @endif
                        </td>
                        <td class="p-3">{{ $admin->name }}</td>
                        <td class="p-3">{{ $admin->email }}</td>
                        <td class="p-3 capitalize">{{ $admin->role }}</td>
                        <td class="p-3">
                            @if ($admin->status)
                                <span class="px-3 py-1 bg-green-100 text-green-700 text-sm rounded-full">Aktif</span>
                            @else
                                <span class="px-3 py-1 bg-red-100 text-red-700 text-sm rounded-full">Tidak Aktif</span>
                            @endif
                        </td>
                        <td class="p-3">{{ $admin->created_at->locale('id')->translatedFormat('l, d-m-Y') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="p-4 text-center text-gray-500">Tidak ada admin.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
