@props([
    'title' => 'Komponen Perhitungan',
    'items' => [],
    'total' => null,
    'totalLabel' => 'Total',
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
        <table class="w-full text-sm">
            <tbody>
                @foreach ($items as $label => $value)
                    <tr class="border-b text-muted-foreground last:border-0">
                        <td class="py-2 pr-4">{{ $label }}</td>
                        <td class="py-2 text-right tabular-nums">Rp{{ number_format($value, 2, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
            @if ($total !== null)
                <tfoot>
                    <tr class="border-t font-semibold">
                        <td class="py-2 pr-4">{{ $totalLabel }}</td>
                        <td class="py-2 text-right tabular-nums">Rp{{ number_format($total, 2, ',', '.') }}</td>
                    </tr>
                </tfoot>
            @endif
        </table>
    </x-card-content>
</x-card>
