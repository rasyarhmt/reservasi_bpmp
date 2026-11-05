@extends('layouts.app')

@section('title', 'Ubah Kamar '.$room->room_number)

@section('content')
    <div class="mx-auto max-w-4xl space-y-6">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight">Ubah Kamar {{ $room->room_number }}</h1>
            <p class="text-sm text-muted-foreground">Sesuaikan fasilitas, status, dan tarif kamar.</p>
        </div>

        <x-card class="max-w-2xl">
            <x-card-content class="pt-6">
                @php
                    $components = collect(old('cost_components', $room->costComponents()))
                        ->map(fn ($amount, $label) => is_array($amount) ? $amount : ['label' => $label, 'amount' => $amount])
                        ->values();
                @endphp

                <form method="POST" action="{{ route('rooms.update', $room) }}" class="space-y-4"
                    x-data="{
                        components: {{ Js::from($components) }},
                        baseCapacity: {{ (int) old('base_capacity', $room->base_capacity) }},
                        get total() {
                            return this.components.reduce((sum, component) => sum + (Number(component.amount) || 0), 0);
                        },
                        get extraPersonRate() {
                            const capacity = Math.max(1, Number(this.baseCapacity) || 1);
                            return Math.round((this.total / capacity) * 100) / 100;
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
                            <x-label for="room_number">Nomor Kamar</x-label>
                            <x-input id="room_number" name="room_number"
                                value="{{ old('room_number', $room->room_number) }}" required />
                            <x-input-error name="room_number" />
                        </div>

                        <div class="space-y-2">
                            <x-label for="room_name">Nama / Tipe Kamar</x-label>
                            <x-input id="room_name" name="room_name"
                                value="{{ old('room_name', $room->room_name) }}" required />
                            <x-input-error name="room_name" />
                        </div>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="space-y-2">
                            <x-label for="base_capacity">Kapasitas (orang)</x-label>
                            <x-input id="base_capacity" name="base_capacity" type="number" min="1"
                                value="{{ old('base_capacity', $room->base_capacity) }}" x-model.number="baseCapacity" required />
                            <x-input-error name="base_capacity" />
                        </div>

                        <div class="space-y-2">
                            <x-label for="extra_person_rate">Tarif Ekstra / org / malam</x-label>
                            <x-input id="extra_person_rate" type="text" readonly
                                x-bind:value="'Rp' + new Intl.NumberFormat('id-ID', { maximumFractionDigits: 2 }).format(extraPersonRate)" />
                            <p class="text-xs text-muted-foreground">Dihitung otomatis dari harga dasar per malam dibagi kapasitas kamar.</p>
                        </div>
                    </div>

                    <div class="space-y-3 rounded-lg border p-4">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <x-label for="cost_components">Komponen Biaya per Malam</x-label>
                                <p class="text-sm text-muted-foreground">
                                    Harga dasar dihitung otomatis dari total komponen.
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

                        <div class="flex items-center justify-between border-t pt-3 text-sm">
                            <span class="font-medium">Harga dasar / malam</span>
                            <span class="font-semibold"
                                x-text="'Rp' + new Intl.NumberFormat('id-ID').format(Math.round(total))"></span>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <x-label for="status">Status</x-label>
                        <x-select name="status" id="status">
                            @foreach (['available' => 'Tersedia', 'occupied' => 'Terisi', 'maintenance' => 'Perawatan'] as $value => $label)
                                <option value="{{ $value }}"
                                    @selected(old('status', $room->status) === $value)>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </x-select>
                        <x-input-error name="status" />
                    </div>

                    <div class="space-y-2">
                        <x-label for="amenities">Fasilitas <span class="font-normal text-muted-foreground">(satu per baris)</span></x-label>
                        <textarea id="amenities" name="amenities" rows="6"
                            class="flex w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs placeholder:text-muted-foreground focus-visible:border-ring focus-visible:outline-none"
                            placeholder="AC&#10;WiFi&#10;Televisi">{{ old('amenities', $room->amenities->pluck('amenity_name')->implode("\n")) }}</textarea>
                        <x-input-error name="amenities" />
                    </div>

                    <div class="flex gap-3 pt-2">
                        <x-button type="submit">Simpan Perubahan</x-button>
                        <x-button href="{{ route('rooms.show', $room) }}" variant="outline" type="button">Batal</x-button>
                    </div>
                </form>
            </x-card-content>
        </x-card>
    </div>
@endsection
