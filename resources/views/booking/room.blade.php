@extends('layouts.app')

@section('title', 'Reservasi Kamar')

@section('content')
    @php
        $roomPhotos = [
            asset('images/foto-kamar-1.webp'),
            asset('images/foto-kamar-2.webp'),
            asset('images/foto-kamar-3.webp'),
            asset('images/foto-kamar-4.webp'),
            asset('images/foto-kamar-5.webp'),
            asset('images/foto-kamar-6.webp'),
            asset('images/foto-kamar-7.webp'),
        ];
    @endphp
    <div x-data="roomBooking({{ $payload }})" class="mx-auto max-w-4xl space-y-6">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold tracking-tight">Reservasi Kamar</h1>
                <p class="text-sm text-muted-foreground">
                    Pilih kamar dan tanggal inap. Harga dihitung otomatis.
                </p>
            </div>
            <x-button href="{{ route('booking.create') }}" variant="outline" size="sm">← Pilih Lainnya</x-button>
        </div>

        <x-card class="overflow-hidden">
            <div x-data="{ active: 0, photos: {{ json_encode($roomPhotos) }} }" class="relative aspect-[16/9] w-full overflow-hidden bg-muted">
                <template x-for="(photo, idx) in photos" :key="idx">
                    <img x-show="active === idx" :src="photo" alt="Foto Kamar" class="absolute inset-0 h-full w-full object-cover">
                </template>
                <template x-if="photos.length > 1">
                    <div>
                        <button type="button" @click="active = (active - 1 + photos.length) % photos.length"
                            class="absolute left-2 top-1/2 -translate-y-1/2 rounded-full bg-background/80 p-1.5 shadow-sm transition hover:bg-background">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-4 w-4">
                                <path fill-rule="evenodd" d="M12.79 5.23a.75.75 0 01-.02 1.06L8.832 10l3.938 3.71a.75.75 0 11-1.04 1.08l-4.5-4.25a.75.75 0 010-1.08l4.5-4.25a.75.75 0 011.06.02z" clip-rule="evenodd" />
                            </svg>
                        </button>
                        <button type="button" @click="active = (active + 1) % photos.length"
                            class="absolute right-2 top-1/2 -translate-y-1/2 rounded-full bg-background/80 p-1.5 shadow-sm transition hover:bg-background">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-4 w-4">
                                <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" />
                            </svg>
                        </button>
                        <div class="absolute bottom-2 left-1/2 flex -translate-x-1/2 gap-1.5">
                            <template x-for="(photo, idx) in photos" :key="idx">
                                <button type="button" @click="active = idx"
                                    class="h-1.5 w-1.5 rounded-full transition"
                                    :class="active === idx ? 'bg-background' : 'bg-background/60'"></button>
                            </template>
                        </div>
                    </div>
                </template>
            </div>
            <x-card-header>
                <x-card-title class="text-base">Galeri Kamar</x-card-title>
                <x-card-description>Lihat foto kamar sebelum melakukan pemesanan.</x-card-description>
            </x-card-header>
        </x-card>

        <form method="POST" action="{{ route('booking.store') }}" enctype="multipart/form-data">
            @csrf

            <x-card>
                <x-card-header>
                    <x-card-title class="text-base">Kamar & Tanggal Inap</x-card-title>
                </x-card-header>
                <x-card-content class="space-y-4 pb-6">
                    <div class="space-y-2">
                        <x-label>Pilih Kamar</x-label>
                        <x-select name="room_id" x-model="form.room_id" required>
                            <option value="">— Pilih kamar —</option>
                            @foreach ($rooms as $room)
                                <option value="{{ $room->id }}" @selected(old('room_id', $preselectedRoomId) == $room->id)
                                    :disabled="isRoomUnavailable({{ $room->id }})">
                                    No. {{ $room->room_number }} — {{ $room->room_name }}
                                    (Rp{{ number_format($room->base_price, 2, ',', '.') }}/malam,
                                    maks. {{ $room->base_capacity }} org)
                                </option>
                            @endforeach
                        </x-select>
                        <p x-show="hasUnavailableRooms" x-cloak class="text-xs text-muted-foreground">
                            Kamar yang sudah dipesan pada tanggal tersebut tidak dapat dipilih.
                            Pilih tanggal lain untuk melihat kamar yang tersedia.
                        </p>
                        <x-input-error name="room_id" />
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="space-y-2">
                            <x-label>Check-in</x-label>
                            <x-input name="check_in_date" type="date" x-model="form.check_in_date"
                                x-bind:min="minDate" required />
                            <x-input-error name="check_in_date" />
                        </div>
                        <div class="space-y-2">
                            <x-label>Check-out</x-label>
                            <x-input name="check_out_date" type="date" x-model="form.check_out_date"
                                x-bind:min="form.check_in_date || minDate" required />
                            <x-input-error name="check_out_date" />
                        </div>
                    </div>

                    <div class="space-y-2">
                        <x-label>Jumlah Tamu</x-label>
                        <x-input name="guest_count" type="number" min="1"
                            x-bind:max="room ? room.base_capacity + 1 : 5"
                            x-model="form.guest_count" required class="sm:max-w-40" />
                        <p class="text-xs text-muted-foreground">
                            Harga sudah termasuk
                            <span x-text="room ? room.base_capacity : 4"></span> orang.
                            Maksimal 1 orang ekstra
                            (total <span x-text="room ? room.base_capacity + 1 : 5"></span> orang).
                        </p>
                        <x-input-error name="guest_count" />
                    </div>

                    <div class="space-y-2">
                        <x-label>Skema Pembayaran</x-label>
                        <x-payment-scheme />
                    </div>

                    <div class="space-y-2">
                        <x-label>Metode Pembayaran</x-label>
                        <input type="hidden" name="payment_method" value="bank_transfer">
                        <div class="rounded-md border border-input bg-muted/40 px-3 py-2">
                            <p class="text-sm font-medium">Transfer Bank - BRI 4567898765(testing)</p>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <x-label>Bukti Pembayaran <span class="font-normal text-muted-foreground">(bukti transfer)</span></x-label>
                        <input type="file" name="payment_proof" accept="image/png,image/jpeg,application/pdf" required
                            class="block w-full cursor-pointer rounded-md border border-input text-sm file:mr-3 file:border-0 file:bg-muted file:px-3 file:py-2 file:text-sm file:font-medium file:text-foreground hover:file:bg-accent" />
                        <x-input-error name="payment_proof" />
                        <p class="text-xs text-muted-foreground">
                            Transfer <span x-text="rupiah(dueAmount)"></span>, lalu unggah buktinya
                            (JPG/PNG/PDF, maks. 2 MB).
                        </p>
                    </div>

                    <div class="space-y-2 border-t pt-4 text-sm">
                        <div class="flex justify-between">
                            <span class="text-muted-foreground">Durasi</span>
                            <span><span x-text="nights"></span> malam</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-muted-foreground">Tamu</span>
                            <span>
                                <span x-text="form.guest_count || 0"></span> orang
                                <template x-if="extraGuests > 0">
                                    <span>(<span x-text="extraGuests"></span> ekstra)</span>
                                </template>
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-muted-foreground">Total</span>
                            <span class="font-medium" x-text="rupiah(total)"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-muted-foreground">Dibayar di muka</span>
                            <span class="font-semibold" x-text="rupiah(dueAmount)"></span>
                        </div>
                        <p x-show="form.payment_scheme === 'dp'" class="text-xs text-muted-foreground">
                            Sisa tagihan dibayar saat check-out (termasuk denda bila ada).
                        </p>
                    </div>

                    <x-button type="submit" class="w-full"
                        x-bind:disabled="form.payment_scheme === 'dp' && downPaymentInvalid">Pesan Kamar</x-button>
                </x-card-content>
            </x-card>
        </form>
    </div>
@endsection
