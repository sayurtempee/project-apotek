@props([
    'title' => '',
    'count' => '',
    'icon' => 'fa-solid fa-circle',
    'bg' => 'from-blue-500 to-blue-600',
])

<div
    class="bg-gradient-to-r {{ $bg }} rounded-2xl shadow-md hover:shadow-xl p-6 flex items-center space-x-4 text-white transition-transform transform hover:-translate-y-1">

    {{-- Icon bulat --}}
    <div class="p-4 bg-white/20 rounded-full">
        <i class="{{ $icon }} text-2xl"></i>
    </div>

    {{-- Text --}}
    <div>
        <div class="text-sm font-medium opacity-90">{{ $title }}</div>
        <div class="text-2xl font-bold">{{ $count }}</div>
    </div>
</div>
