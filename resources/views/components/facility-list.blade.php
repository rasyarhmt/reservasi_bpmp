@props([
    'title' => 'Daftar Fasilitas / Inventaris',
    'items' => [],
    'note' => null,
])

<x-card>
    <x-card-header>
        <x-card-title class="text-base">{{ $title }}</x-card-title>
        @if ($note)
            <x-card-description>{{ $note }}</x-card-description>
        @endif
    </x-card-header>
    <x-card-content>
        <ul class="grid gap-1.5 sm:grid-cols-2">
            @foreach ($items as $name => $quantity)
                <li class="flex items-center justify-between rounded-md border px-3 py-2 text-sm">
                    <span>{{ $name }}</span>
                    <span class="font-medium tabular-nums">{{ $quantity }}</span>
                </li>
            @endforeach
        </ul>
    </x-card-content>
</x-card>
