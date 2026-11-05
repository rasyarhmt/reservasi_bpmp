@extends('layouts.app')

@section('title', 'Reservasi Aula')

@section('content')
    @php
        $hallPhotos = [
            asset('images/foto-aula-1.webp'),
            asset('images/foto-aula-2.webp'),
            asset('images/foto-aula-3.webp'),
            asset('images/foto-aula-4.webp'),
            asset('images/foto-aula-5.webp'),
        ];
    @endphp
    <div x-data="hallBooking({{ $payload }})" class="mx-auto max-w-4xl space-y-6">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold tracking-tight">Reservasi Aula</h1>
                <p class="text-sm text-muted-foreground">
                    Pilih tanggal pemakaian aula. Operasional 08:00–16:00, tarif per hari dihitung otomatis.
                </p>
            </div>
            <x-button href="{{ route('booking.create') }}" variant="outline" size="sm">← Pilih Lainnya</x-button>
        </div>

        <x-card class="overflow-hidden">
            <div x-data="{ active: 0, photos: {{ json_encode($hallPhotos) }} }" class="relative aspect-[16/9] w-full overflow-hidden bg-muted">
                <template x-for="(photo, idx) in photos" :key="idx">
                    <img x-show="active === idx" :src="photo" alt="Foto Aula" class="absolute inset-0 h-full w-full object-cover">
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
                <x-card-title class="text-base">Galeri Aula</x-card-title>
                <x-card-description>Lihat foto aula sebelum melakukan pemesanan.</x-card-description>
            </x-card-header>
        </x-card>

        <form method="POST" action="{{ route('booking.storeHall') }}" enctype="multipart/form-data">
            @csrf

            <x-card>
                <x-card-header>
                    <x-card-title class="text-base">Aula & Tanggal Pemakaian</x-card-title>
                </x-card-header>
                <x-card-content class="space-y-4 pb-6">
                    @if ($hall)
                        <input type="hidden" name="hall_id" value="{{ $hall->id }}">
                        <div class="space-y-2">
                            <x-label>Aula</x-label>
                            <div class="rounded-md border border-input bg-muted/40 px-3 py-2">
                                <p class="text-sm font-medium">{{ $hall->hall_name }}</p>
                                <p class="text-xs text-muted-foreground">
                                    Rp{{ number_format($hall->dailyRate(), 2, ',', '.') }}/hari ·
                                    {{ $hall->operational_start }}–{{ $hall->operational_end }} ·
                                    kapasitas {{ $hall->capacity }} orang
                                </p>
                            </div>
                            <x-input-error name="hall_id" />
                        </div>
                    @else
                        <x-alert type="warning">Aula sedang tidak tersedia untuk pemesanan.</x-alert>
                    @endif

                    <div class="space-y-2">
                        <x-label>Tanggal Pemakaian</x-label>
                        <x-input name="hall_date" type="date" min="{{ now()->toDateString() }}" x-model="form.hall_date" @change="if (isDateBooked) { /* keep selection */ }" required />
                        <x-input-error name="hall_date" />
                        <p class="text-xs text-muted-foreground">
                            Pemakaian aula {{ $hall?->operational_start ?? '08:00' }}–{{ $hall?->operational_end ?? '16:00' }} WITA.
                        </p>
                        <p x-show="isDateBooked" x-cloak class="text-xs text-destructive">
                            Aula sudah dibooking pada tanggal ini. Silakan pilih tanggal lain.
                        </p>
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
                            <span class="text-muted-foreground">Tanggal</span>
                            <span x-text="dateLabel"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-muted-foreground">Jam operasional</span>
                            <span>{{ $hall?->operational_start ?? '08:00' }}–{{ $hall?->operational_end ?? '16:00' }}</span>
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

                    @if ($hall)
                        <x-button type="submit" class="w-full"
                            x-bind:disabled="!form.hall_date || isDateBooked || (form.payment_scheme === 'dp' && downPaymentInvalid)">Pesan Aula</x-button>
                    @endif
                </x-card-content>
            </x-card>
        </form>
    </div>
@endsection
