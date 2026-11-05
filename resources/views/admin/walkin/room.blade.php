@extends('layouts.app')

@section('title', 'Walk-In Kamar')

@section('content')
    <div x-data="roomBooking({{ $payload }})" class="mx-auto max-w-4xl space-y-6">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold tracking-tight">Reservasi Walk-In — Kamar</h1>
                <p class="text-sm text-muted-foreground">
                    Isi data pelanggan di lokasi. Pilih DP (minimal Rp{{ number_format(App\Models\Reservation::MIN_DOWN_PAYMENT, 0, ',', '.') }}) atau lunas,
                    lalu terima pembayaran tunai atau transfer (sertakan bukti).
                </p>
            </div>
            <x-button href="{{ route('walkin.chooser') }}" variant="outline" size="sm">← Jenis Lainnya</x-button>
        </div>

        <form method="POST" action="{{ route('walkin.store') }}" enctype="multipart/form-data">
            @csrf

            <x-card>
                <x-card-header>
                    <x-card-title class="text-base">Kamar, Jadwal & Data Pelanggan</x-card-title>
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
                            Harga sudah termasuk <span x-text="room ? room.base_capacity : 4"></span> orang.
                            Maksimal 1 orang ekstra
                            (total <span x-text="room ? room.base_capacity + 1 : 5"></span> orang).
                        </p>
                        <x-input-error name="guest_count" />
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
                            <span class="text-muted-foreground">Dibayar sekarang</span>
                            <span class="font-semibold" x-text="rupiah(dueAmount)"></span>
                        </div>
                        <p x-show="form.payment_scheme === 'dp'" class="text-xs text-muted-foreground">
                            Sisa tagihan dan denda keterlambatan dibayar saat check-out.
                        </p>
                    </div>

                    <x-button type="submit" class="w-full"
                        x-bind:disabled="form.payment_scheme === 'dp' && downPaymentInvalid">Simpan & Terima Pembayaran</x-button>
                </x-card-content>
            </x-card>
        </form>
    </div>
@endsection