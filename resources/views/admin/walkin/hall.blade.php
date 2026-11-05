@extends('layouts.app')

@section('title', 'Walk-In Aula')

@section('content')
    <div x-data="hallBooking({{ $payload }})" class="mx-auto max-w-4xl space-y-6">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold tracking-tight">Reservasi Walk-In — Aula</h1>
                <p class="text-sm text-muted-foreground">
                    Isi data pelanggan di lokasi. Pilih DP (minimal Rp{{ number_format(App\Models\Reservation::MIN_DOWN_PAYMENT, 0, ',', '.') }}) atau lunas,
                    lalu terima pembayaran tunai atau transfer (sertakan bukti).
                </p>
            </div>
            <x-button href="{{ route('walkin.chooser') }}" variant="outline" size="sm">← Jenis Lainnya</x-button>
        </div>

        <form method="POST" action="{{ route('walkin.storeHall') }}" enctype="multipart/form-data">
            @csrf

            <x-card>
                <x-card-header>
                    <x-card-title class="text-base">Aula, Tanggal & Data Pelanggan</x-card-title>
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
                        <x-input name="hall_date" type="date" min="{{ now()->toDateString() }}" x-model="form.hall_date" required />
                        <x-input-error name="hall_date" />
                        <p class="text-xs text-muted-foreground">
                            Pemakaian aula {{ $hall?->operational_start ?? '08:00' }}–{{ $hall?->operational_end ?? '16:00' }} WITA.
                        </p>
                        <p x-show="isDateBooked" x-cloak class="text-xs text-destructive">
                            Aula sudah dibooking pada tanggal ini. Silakan pilih tanggal lain.
                        </p>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="space-y-2">
                            <x-label>Nama Pelanggan</x-label>
                            <x-input name="customer_name" value="{{ old('customer_name') }}" required />
                            <x-input-error name="customer_name" />
                        </div>
                        <div class="space-y-2">
                            <x-label>No. HP <span class="font-normal text-muted-foreground">(opsional)</span></x-label>
                            <x-input name="customer_phone" value="{{ old('customer_phone') }}" />
                            <x-input-error name="customer_phone" />
                        </div>
                    </div>

                    <div class="space-y-2">
                        <x-label>Skema Pembayaran</x-label>
                        <x-payment-scheme />
                    </div>

                    <div class="space-y-2">
                        <x-label>Metode Pembayaran</x-label>
                        <x-select name="payment_method" x-model="form.payment_method" required>
                            <option value="cash">Tunai</option>
                            <option value="bank_transfer">Transfer Bank</option>
                        </x-select>
                        <x-input-error name="payment_method" />
                    </div>

                    <div class="space-y-2" x-show="form.payment_method === 'bank_transfer'" x-cloak>
                        <x-label>Bukti Transfer</x-label>
                        <input type="file" name="payment_proof" accept="image/png,image/jpeg,application/pdf"
                            class="block w-full cursor-pointer rounded-md border border-input text-sm file:mr-3 file:border-0 file:bg-muted file:px-3 file:py-2 file:text-sm file:font-medium file:text-foreground hover:file:bg-accent" />
                        <x-input-error name="payment_proof" />
                        <p class="text-xs text-muted-foreground">
                            Wajib diunggah bila pembayaran via transfer (JPG/PNG/PDF, maks. 2 MB).
                        </p>
                    </div>

                    <div class="space-y-2 border-t pt-4 text-sm">
                        <div class="flex justify-between">
                            <span class="text-muted-foreground">Skema</span>
                            <span class="font-semibold"
                                x-text="form.payment_scheme === 'dp' ? 'DP' : 'Lunas'"></span>
                        </div>
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
                            <span class="text-muted-foreground">Dibayar sekarang</span>
                            <span class="font-semibold" x-text="rupiah(dueAmount)"></span>
                        </div>
                        <p x-show="form.payment_scheme === 'dp'" class="text-xs text-muted-foreground">
                            Sisa tagihan dan denda keterlambatan dibayar saat check-out.
                        </p>
                    </div>

                    @if ($hall)
                        <x-button type="submit" class="w-full"
                            x-bind:disabled="!form.hall_date || isDateBooked || (form.payment_scheme === 'dp' && downPaymentInvalid)">Simpan & Terima Pembayaran</x-button>
                    @endif
                </x-card-content>
            </x-card>
        </form>
    </div>
@endsection