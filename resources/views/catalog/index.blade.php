@extends('layouts.app')

@section('title', 'Kamar & Aula')

@section('content')
    @php
        $isAdmin = (bool) auth()->user()?->isAdmin();
        $bookable = ! $isAdmin;
        $roomStatuses = [
            'available' => ['Tersedia', 'success'],
            'occupied' => ['Terisi', 'warning'],
            'maintenance' => ['Perawatan', 'destructive'],
        ];
        $hallStatuses = [
            'available' => ['Tersedia', 'success'],
            'in_use' => ['Dipakai', 'warning'],
            'maintenance' => ['Perawatan', 'destructive'],
        ];
        $roomPhotos = [
            asset('images/foto-kamar-1.webp'),
            asset('images/foto-kamar-7.webp'),
            asset('images/foto-kamar-3.webp'),
            asset('images/foto-kamar-6.webp'),
        ];
        $hallPhotos = [
            asset('images/foto-aula-1.webp'),
            asset('images/foto-aula-2.webp'),
            asset('images/foto-aula-3.webp'),
            asset('images/foto-aula-4.webp'),
            asset('images/foto-aula-5.webp'),
        ];
    @endphp

    <div class="w-full space-y-6" x-data="{ tab: 'kamar' }">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight">Kamar & Aula</h1>
            <p class="text-sm text-muted-foreground">
                Pilih kamar atau aula sesuai kebutuhan Anda. Harga kamar sudah termasuk {{ $rooms->first()?->base_capacity ?? 4 }} orang.
            </p>
        </div>

        <div class="inline-flex rounded-lg border bg-muted p-1 text-sm">
            <button type="button" @click="tab = 'kamar'"
                class="rounded-md px-4 py-1.5 transition-colors"
                :class="tab === 'kamar' ? 'bg-background font-medium text-foreground shadow-sm' : 'text-muted-foreground hover:text-foreground'">
                Kamar
            </button>
            <button type="button" @click="tab = 'aula'"
                class="rounded-md px-4 py-1.5 transition-colors"
                :class="tab === 'aula' ? 'bg-background font-medium text-foreground shadow-sm' : 'text-muted-foreground hover:text-foreground'">
                Aula
            </button>
        </div>

        {{-- TAB KAMAR --}}
        <div x-cloak x-show="tab === 'kamar'" class="space-y-4">
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5">
                @foreach ($rooms as $index => $room)
                    @php
                        $photos = $roomPhotos;
                    @endphp
                    <x-card class="overflow-hidden transition-shadow hover:shadow-md">
                        <div x-data="{ active: 0, photos: {{ json_encode($photos) }} }" class="relative aspect-[16/10] w-full overflow-hidden bg-muted">
                            <template x-for="(photo, idx) in photos" :key="idx">
                                <img x-show="active === idx" :src="photo" alt="Foto {{ $room->room_name }}"
                                    class="absolute inset-0 h-full w-full object-cover">
                            </template>
                            <template x-if="photos.length > 1">
                                <div>
                                    <button type="button" @click.stop="active = (active - 1 + photos.length) % photos.length"
                                        class="absolute left-2 top-1/2 -translate-y-1/2 rounded-full bg-background/80 p-1.5 shadow-sm transition hover:bg-background">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-4 w-4">
                                            <path fill-rule="evenodd" d="M12.79 5.23a.75.75 0 01-.02 1.06L8.832 10l3.938 3.71a.75.75 0 11-1.04 1.08l-4.5-4.25a.75.75 0 010-1.08l4.5-4.25a.75.75 0 011.06.02z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                    <button type="button" @click.stop="active = (active + 1) % photos.length"
                                        class="absolute right-2 top-1/2 -translate-y-1/2 rounded-full bg-background/80 p-1.5 shadow-sm transition hover:bg-background">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-4 w-4">
                                            <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                    <div class="absolute bottom-2 left-1/2 flex -translate-x-1/2 gap-1.5">
                                        <template x-for="(photo, idx) in photos" :key="idx">
                                            <button type="button" @click.stop="active = idx"
                                                class="h-1.5 w-1.5 rounded-full transition"
                                                :class="active === idx ? 'bg-background' : 'bg-background/60'"></button>
                                        </template>
                                    </div>
                                </div>
                            </template>
                        </div>
                        <x-card-header class="space-y-1">
                            <div class="flex items-start justify-between gap-2">
                                <div>
                                    <x-card-title class="text-lg">{{ $room->room_name }}</x-card-title>
                                    <x-card-description>No. {{ $room->room_number }}</x-card-description>
                                </div>
                                
                                <div class="flex flex-col items-end gap-1">
                                    <x-badge variant="{{ $roomStatuses[$room->status][1] }}">
                                        {{ $roomStatuses[$room->status][0] }}
                                    </x-badge>
                                    
                                    @if ($room->status === 'occupied' && $room->reservations->isNotEmpty())
                                        @php
                                            $latestCheckout = \Carbon\Carbon::parse($room->reservations->first()->check_out_date);
                                            
                                            foreach ($room->reservations as $res) {
                                                $resCheckIn = \Carbon\Carbon::parse($res->check_in_date);
                                                $resCheckOut = \Carbon\Carbon::parse($res->check_out_date);
                                                
                                                if ($resCheckIn->lte($latestCheckout)) {
                                                    if ($resCheckOut->gt($latestCheckout)) {
                                                        $latestCheckout = $resCheckOut;
                                                    }
                                                }
                                            }
                                        @endphp
                                        
                                        <span class="text-[10px] font-medium text-muted-foreground">
                                            s.d. {{ $latestCheckout->format('d M Y') }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </x-card-header>
                        <x-card-content class="space-y-3">
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-muted-foreground">Kapasitas</span>
                                <span class="font-medium">{{ $room->base_capacity }} orang</span>
                            </div>
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-muted-foreground">Harga / Malam</span>
                                <span class="font-semibold">Rp{{ number_format($room->base_price, 2, ',', '.') }}</span>
                            </div>
                            <p class="text-xs text-muted-foreground">
                                Sudah termasuk {{ $room->base_capacity }} orang • Ekstra Rp{{ number_format($room->extra_person_rate, 0, ',', '.') }}/org
                            </p>
                            @if ($room->amenities->isNotEmpty())
                                <div class="flex flex-wrap gap-1">
                                    @foreach ($room->amenities->take(4) as $amenity)
                                        <x-badge variant="outline">{{ $amenity->amenity_name }}</x-badge>
                                    @endforeach
                                    @if ($room->amenities->count() > 4)
                                        <x-badge variant="outline">+{{ $room->amenities->count() - 4 }} lainnya</x-badge>
                                    @endif
                                </div>
                            @endif
                        </x-card-content>
                        <x-card-footer>
                            @if ($isAdmin)
                                <x-button href="{{ route('rooms.show', $room) }}" variant="outline" class="w-full">Detail Internal</x-button>
                            @else
                                <x-button href="{{ route('booking.room', ['room' => $room->id]) }}" class="w-full">Pesan Sekarang</x-button>
                            @endif
                        </x-card-footer>
                    </x-card>
                @endforeach
            </div>

            @if ($rooms->hasPages())
                <div class="flex justify-center">
                    {{ $rooms->links() }}
                </div>
            @endif
        </div>

        {{-- TAB AULA (SUDAH BISA DIGESER) --}}
        <div x-cloak x-show="tab === 'aula'" class="space-y-4">
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5">
                @foreach ($halls as $hall)
                    @php
                        $photos = $hallPhotos;
                    @endphp
                    <x-card class="overflow-hidden transition-shadow hover:shadow-md">
                        <div x-data="{ active: 0, photos: {{ json_encode($photos) }} }" class="relative aspect-[16/10] w-full overflow-hidden bg-muted">
                            <template x-for="(photo, idx) in photos" :key="idx">
                                <img x-show="active === idx" :src="photo" alt="Foto {{ $hall->hall_name }}"
                                    class="absolute inset-0 h-full w-full object-cover">
                            </template>
                            <template x-if="photos.length > 1">
                                <div>
                                    <button type="button" @click.stop="active = (active - 1 + photos.length) % photos.length"
                                        class="absolute left-2 top-1/2 -translate-y-1/2 rounded-full bg-background/80 p-1.5 shadow-sm transition hover:bg-background">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-4 w-4">
                                            <path fill-rule="evenodd" d="M12.79 5.23a.75.75 0 01-.02 1.06L8.832 10l3.938 3.71a.75.75 0 11-1.04 1.08l-4.5-4.25a.75.75 0 010-1.08l4.5-4.25a.75.75 0 011.06.02z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                    <button type="button" @click.stop="active = (active + 1) % photos.length"
                                        class="absolute right-2 top-1/2 -translate-y-1/2 rounded-full bg-background/80 p-1.5 shadow-sm transition hover:bg-background">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-4 w-4">
                                            <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                    <div class="absolute bottom-2 left-1/2 flex -translate-x-1/2 gap-1.5">
                                        <template x-for="(photo, idx) in photos" :key="idx">
                                            <button type="button" @click.stop="active = idx"
                                                class="h-1.5 w-1.5 rounded-full transition"
                                                :class="active === idx ? 'bg-background' : 'bg-background/60'"></button>
                                        </template>
                                    </div>
                                </div>
                            </template>
                        </div>
                        <x-card-header class="space-y-1">
                            <div class="flex items-start justify-between gap-2">
                                <x-card-title class="text-lg">{{ $hall->hall_name }}</x-card-title>
                                <x-badge variant="{{ $hallStatuses[$hall->status][1] }}">
                                    {{ $hallStatuses[$hall->status][0] }}
                                </x-badge>
                            </div>
                        </x-card-header>
                        <x-card-content class="space-y-3">
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-muted-foreground">Kapasitas</span>
                                <span class="font-medium">{{ $hall->capacity }} orang</span>
                            </div>
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-muted-foreground">Tarif / Hari</span>
                                <span class="font-semibold">Rp{{ number_format($hall->dailyRate(), 2, ',', '.') }}</span>
                            </div>
                            <p class="text-xs text-muted-foreground">{{ $hall->operational_start }}–{{ $hall->operational_end }} WITA · denda Rp{{ number_format($hall->lateChargeRate(), 2, ',', '.') }}/jam</p>
                        </x-card-content>
                        <x-card-footer>
                            @if ($isAdmin)
                                <x-button href="{{ route('halls.show') }}" variant="outline" class="w-full">Detail Internal</x-button>
                            @else
                                <x-button href="{{ route('booking.hall', ['hall' => $hall->id]) }}" class="w-full">Pesan Sekarang</x-button>
                            @endif
                        </x-card-footer>
                    </x-card>
                @endforeach
            </div>
        </div>
    </div>
@endsection