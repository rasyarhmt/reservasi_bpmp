@extends('layouts.app')

@section('title', 'Ubah Aula '.$hall->hall_name)

@section('content')
    <div class="mx-auto max-w-4xl space-y-6">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight">Ubah Aula {{ $hall->hall_name }}</h1>
            <p class="text-sm text-muted-foreground">Sesuaikan kapasitas, status, dan komponen biaya aula.</p>
        </div>

        <x-card class="max-w-2xl">
            <x-card-content class="pt-6">
                @php
                    $components = collect(old('cost_components', $hall->costComponents()))
                        ->map(fn ($amount, $label) => is_array($amount) ? $amount : ['label' => $label, 'amount' => $amount])
                        ->values();
                @endphp

                <form method="POST" action="{{ route('halls.update') }}" class="space-y-4"
                    x-data="{
                        components: {{ Js::from($components) }},
                        get total() {
                            return this.components.reduce((sum, component) => sum + (Number(component.amount) || 0), 0);
                        },
                        get hourly() {
                            return this.total / {{ App\Models\Hall::OPERATIONAL_HOURS }};
                        },
                        addComponent() {
                            this.components.push({ label: '', amount: 0 });
                        },
                        removeComponent(index) {
                            if (this.components.length > 1) this.components.splice(index, 1);
                        },
                    }">
                    @csrf
                    @method('PUT')

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="space-y-2">
                            <x-label for="hall_name">Nama Aula</x-label>
                            <x-input id="hall_name" name="hall_name"
                                value="{{ old('hall_name', $hall->hall_name) }}" required />
                            <x-input-error name="hall_name" />
                        </div>

                        <div class="space-y-2">
                            <x-label for="capacity">Kapasitas (orang)</x-label>
                            <x-input id="capacity" name="capacity" type="number" min="1"
                                value="{{ old('capacity', $hall->capacity) }}" required />
                            <x-input-error name="capacity" />
                        </div>
                    </div>

                    <div class="space-y-2">
                        <x-label for="status">Status</x-label>
                        <x-select name="status" id="status">
                            @foreach (['available' => 'Tersedia', 'in_use' => 'Digunakan', 'maintenance' => 'Perawatan'] as $value => $label)
                                <option value="{{ $value }}"
                                    @selected(old('status', $hall->status) === $value)>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </x-select>
                        <x-input-error name="status" />
                    </div>

                    <div class="space-y-3 rounded-lg border p-4">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <x-label for="cost_components">Komponen Biaya per Hari</x-label>
                                <p class="text-sm text-muted-foreground">
                                    Harga sewa dan tarif denda per jam dihitung otomatis dari total komponen.
                                </p>
                            </div>
                            <x-button type="button" variant="outline" size="sm" x-on:click="addComponent()">+ Komponen</x-button>
                        </div>

                        <template x-for="(component, index) in components" :key="index">
                            <div class="flex items-start gap-2">
                                <div class="flex-1 space-y-1">
                                    <x-input name="" type="text" placeholder="Nama komponen"
                                        x-model="component.label"
                                        x-bind:name="`cost_components[${index}][label]`" required />
                                    <x-input-error name="cost_components" />
                                </div>
                                <div class="w-40 space-y-1">
                                    <x-input name="" type="number" min="0" step="0.01" placeholder="0"
                                        x-model="component.amount"
                                        x-bind:name="`cost_components[${index}][amount]`" required />
                                </div>
                                <x-button type="button" variant="ghost" size="sm"
                                    x-on:click="removeComponent(index)"
                                    x-bind:disabled="components.length === 1">Hapus</x-button>
                            </div>
                        </template>

                        <div class="space-y-1 border-t pt-3 text-sm">
                            <div class="flex items-center justify-between">
                                <span class="font-medium">Harga sewa / hari</span>
                                <span class="font-semibold"
                                    x-text="'Rp' + new Intl.NumberFormat('id-ID').format(Math.round(total))"></span>
                            </div>
                            <div class="flex items-center justify-between text-muted-foreground">
                                <span>Denda keterlambatan / jam</span>
                                <span x-text="'Rp' + new Intl.NumberFormat('id-ID').format(Math.round(hourly))"></span>
                            </div>
                        </div>
                    </div>

                    <div class="flex gap-3 pt-2">
                        <x-button type="submit">Simpan Perubahan</x-button>
                        <x-button href="{{ route('halls.show') }}" variant="outline" type="button">Batal</x-button>
                    </div>
                </form>
            </x-card-content>
        </x-card>
    </div>
@endsection
