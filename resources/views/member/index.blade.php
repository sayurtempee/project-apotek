@extends('components.app')
@include('layouts.sidebar')

@section('content')
    <div class="container mx-auto p-6">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-3xl font-semibold text-gray-800">Daftar Member</h2>
            @if (Auth::user()->role === 'kasir')
            <a href="{{ route('members.create') }}"
                class="bg-blue-600 hover:bg-blue-700 text-white font-medium px-4 py-2 rounded-lg transition">
                + Tambah Member
            </a>
            @endif
        </div>

        <div class="bg-white shadow-md rounded-xl overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-6 py-3 text-left text-sm font-medium text-gray-600">Foto</th>
                        <th class="px-6 py-3 text-left text-sm font-medium text-gray-600">Nama</th>
                        <th class="px-6 py-3 text-left text-sm font-medium text-gray-600">Telepon</th>
                        <th class="px-6 py-3 text-left text-sm font-medium text-gray-600">Poin</th>
                        <th class="px-6 py-3 text-left text-sm font-medium text-gray-600">Status</th>
                        <th class="px-6 py-3 text-left text-sm font-medium text-gray-600">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse ($members as $member)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-gray-800">
                                @if ($member->foto)
                                    <img src="{{ asset('storage/' . $member->foto) }}" alt="Foto {{ $member->name }}"
                                        class="w-12 h-12 rounded-full object-cover" />
                                @else
                                    <div class="flex flex-col items-center">
                                        <span class="text-xs text-gray-500 mt-1">None Foto</span>
                                    </div>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-gray-800">{{ $member->name }}</td>
                            <td class="px-6 py-4 text-gray-800">{{ $member->phone }}</td>
                            <td class="px-6 py-4 text-gray-800">{{ $member->points }}</td>
                            <td class="px-6 py-4">
                                <span
                                    class="px-3 py-1 rounded-full text-sm font-semibold
                                    {{ $member->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                    {{ $member->is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 flex space-x-3">
                                <a href="{{ route('members.edit', $member) }}"
                                    class="text-blue-600 hover:underline">Edit</a>

                                <form action="{{ route('members.destroy', $member) }}" method="POST"
                                    onsubmit="return confirm('Yakin ingin menghapus member ini?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:underline">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-4 text-center text-gray-500">Belum ada member terdaftar.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
