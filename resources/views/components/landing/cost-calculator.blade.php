@props([
    'calculator',
    'rooms',
    'halls',
    'preview' => [],
    'bookingHref' => '#',
])

<div x-data="landingCalculator({{ $calculator }})" class="grid gap-6 lg:grid-cols-[1.05fr_1fr] lg:gap-8">
    {{-- Panel kontrol --}}
    <div class="rounded-2xl border border-border/70 bg-card p-6 shadow-sm sm:p-8">
        <div class="inline-flex relative isolate rounded-xl border border-border/70 bg-muted/50 p-1" role="group"
            aria-label="Pilih jenis pemesanan">
            <span class="absolute inset-y-1 left-1 z-0 w-20 rounded-lg bg-primary shadow-sm transition-transform duration-300 ease-out"
                :class="mode === 'room' ? 'translate-x-0' : 'translate-x-20'"></span>

            <button type="button" @click="mode = 'room'" :aria-pressed="mode === 'room'"
                class="relative z-10 w-20 rounded-lg px-3 py-2 text-sm font-medium transition-colors"
                :class="mode === 'room' ? 'text-primary-foreground' : 'text-muted-foreground hover:text-foreground'">
                Kamar
            </button>
            <button type="button" @click="mode = 'hall'" :aria-pressed="mode === 'hall'"
                class="relative z-10 w-20 rounded-lg px-3 py-2 text-sm font-medium transition-colors"
                :class="mode === 'hall' ? 'text-primary-foreground' : 'text-muted-foreground hover:text-foreground'">
                Aula
            </button>
        </div>

        {{-- Kontrol kamar --}}
        <div x-show="mode === 'room'" x-cloak class="mt-8 space-y-7">
            <div class="space-y-2">
                <label for="kalkulator-kamar" class="text-sm font-medium">Pilih kamar</label>
                <select id="kalkulator-kamar" x-model.number="roomId" @change="syncGuests()"
                    class="w-full rounded-xl border border-input bg-background px-3 py-2.5 text-sm shadow-sm transition-colors focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none">
                    @foreach ($rooms as $room)
                        <option value="{{ $room->id }}">
                            {{ $room->room_name }} · Rp{{ number_format($room->base_price, 0, ',', '.') }}/malam
                        </option>
                    @endforeach
                </select>
                <p class="text-xs text-muted-foreground">
                    Kapasitas dasar <span x-text="baseCapacity">{{ $preview['guests'] ?? 0 }}</span> orang per kamar.
                </p>
            </div>

            <div class="flex items-center justify-between gap-4 rounded-xl border border-border/70 bg-background/60 px-4 py-3">
                <span class="min-w-0">
                    <span class="block text-sm font-medium">Jumlah malam</span>
                    <span class="block text-xs text-muted-foreground">Check-in sampai check-out</span>
                </span>

                <span class="flex shrink-0 items-center gap-1">
                    <button type="button" @click="stepNights(-1)" aria-label="Kurangi jumlah malam"
                        class="flex size-8 items-center justify-center rounded-lg border border-border/70 transition-colors hover:bg-accent hover:text-accent-foreground">
                        <svg class="size-3.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path d="M4.5 9h11a.75.75 0 010 1.5h-11a.75.75 0 010-1.5z" />
                        </svg>
                    </button>
                    <output class="w-9 text-center text-sm font-semibold tabular-nums" x-text="nights">{{ $preview['nights'] ?? 1 }}</output>
                    <button type="button" @click="stepNights(1)" aria-label="Tambah jumlah malam"
                        class="flex size-8 items-center justify-center rounded-lg border border-border/70 transition-colors hover:bg-accent hover:text-accent-foreground">
                        <svg class="size-3.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path d="M10 4.5a.75.75 0 01.75.75v4h4a.75.75 0 010 1.5h-4v4a.75.75 0 01-1.5 0v-4h-4a.75.75 0 010-1.5h4v-4A.75.75 0 0110 4.5z" />
                        </svg>
                    </button>
                </span>
            </div>

            <div class="flex items-center justify-between gap-4 rounded-xl border border-border/70 bg-background/60 px-4 py-3">
                <span class="min-w-0">
                    <span class="block text-sm font-medium">Jumlah tamu</span>
                    <span class="block text-xs text-muted-foreground">
                        Termasuk <span x-text="baseCapacity">{{ $preview['guests'] ?? 0 }}</span> orang, tambah <span x-text="maxGuests - baseCapacity">1</span> orang
                    </span>
                </span>

                <span class="flex shrink-0 items-center gap-1">
                    <button type="button" @click="stepGuests(-1)" aria-label="Kurangi jumlah tamu"
                        class="flex size-8 items-center justify-center rounded-lg border border-border/70 transition-colors hover:bg-accent hover:text-accent-foreground">
                        <svg class="size-3.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path d="M4.5 9h11a.75.75 0 010 1.5h-11a.75.75 0 010-1.5z" />
                        </svg>
                    </button>
                    <output class="w-9 text-center text-sm font-semibold tabular-nums" x-text="guests">{{ $preview['guests'] ?? 0 }}</output>
                    <button type="button" @click="stepGuests(1)" aria-label="Tambah jumlah tamu"
                        class="flex size-8 items-center justify-center rounded-lg border border-border/70 transition-colors hover:bg-accent hover:text-accent-foreground">
                        <svg class="size-3.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path d="M10 4.5a.75.75 0 01.75.75v4h4a.75.75 0 010 1.5h-4v4a.75.75 0 01-1.5 0v-4h-4a.75.75 0 010-1.5h4v-4A.75.75 0 0110 4.5z" />
                        </svg>
                    </button>
                </span>
            </div>
        </div>

        {{-- Kontrol aula --}}
        <div x-show="mode === 'hall'" x-cloak class="mt-8 space-y-7">
            <div class="space-y-2">
                <label for="kalkulator-aula" class="text-sm font-medium">Pilih aula</label>
                <select id="kalkulator-aula" x-model.number="hallId"
                    class="w-full rounded-xl border border-input bg-background px-3 py-2.5 text-sm shadow-sm transition-colors focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none">
                    @foreach ($halls as $hall)
                        <option value="{{ $hall->id }}">
                            {{ $hall->hall_name }} · {{ $hall->capacity }} tamu
                        </option>
                    @endforeach
                </select>
                <p class="text-xs text-muted-foreground">
                    Tarif <span x-text="rupiah(dailyRate)">{{ $preview['hallTotal'] ?? 'Rp0' }}</span>/hari ·
                    {{ $halls->first()?->operational_start ?? '08:00' }}–{{ $halls->first()?->operational_end ?? '16:00' }} WITA
                </p>
            </div>

            <div class="flex items-center justify-between gap-4 rounded-xl border border-border/70 bg-background/60 px-4 py-3">
                <span class="min-w-0">
                    <span class="block text-sm font-medium">Tanggal pemakaian</span>
                    <span class="block text-xs text-muted-foreground">Operasional {{ $halls->first()?->operational_start ?? '08:00' }}–{{ $halls->first()?->operational_end ?? '16:00' }} WITA</span>
                </span>

                <span class="shrink-0 text-sm text-muted-foreground">1 hari</span>
            </div>
        </div>
    </div>

    {{-- Ringkasan biaya --}}
    <div class="relative overflow-hidden rounded-2xl border border-border/70 bg-card shadow-sm">
        <div class="pointer-events-none absolute inset-0 ambient-panel opacity-70"></div>

        <div class="relative p-6 sm:p-8">
            <p class="text-[0.7rem] font-medium uppercase tracking-[0.18em] text-muted-foreground">
                Estimasi biaya
            </p>

            <div x-show="mode === 'room'" x-cloak class="mt-6 space-y-4">
                <div class="flex items-baseline justify-between gap-4 text-sm">
                    <span class="text-muted-foreground">
                        Harga dasar × <span x-text="nights">{{ $preview['nights'] ?? 1 }}</span> malam
                    </span>
                    <span class="font-medium tabular-nums" x-text="rupiah(roomBaseTotal)">{{ $preview['baseTotal'] ?? 'Rp0' }}</span>
                </div>

                <div x-show="extraGuests > 0" x-cloak class="flex items-baseline justify-between gap-4 text-sm">
                    <span class="text-muted-foreground">
                        <span x-text="extraGuests">0</span> tamu tambahan × <span x-text="nights">{{ $preview['nights'] ?? 1 }}</span> malam
                    </span>
                    <span class="font-medium tabular-nums" x-text="rupiah(roomExtraTotal)">{{ $preview['extraTotal'] ?? 'Rp0' }}</span>
                </div>

                <p x-show="extraGuests > 0" x-cloak class="text-xs leading-relaxed text-muted-foreground">
                    Di atas kapasitas dasar, tarif tambahan
                    <span class="font-medium text-foreground" x-text="rupiah(extraPersonRate)">{{ $preview['extraPersonRate'] ?? 'Rp0' }}</span>/orang/malam.
                </p>

                <p x-show="extraGuests === 0" x-cloak class="text-xs leading-relaxed text-muted-foreground">
                    Jumlah tamu masih di dalam kapasitas dasar, jadi belum ada tarif tambahan.
                </p>
            </div>

            <div x-show="mode === 'hall'" x-cloak class="mt-6 space-y-4">
                <div class="flex items-baseline justify-between gap-4 text-sm">
                    <span class="text-muted-foreground">
                        Tarif per hari
                    </span>
                    <span class="font-medium tabular-nums" x-text="rupiah(dailyRate)">{{ $preview['hallTotal'] ?? 'Rp0' }}</span>
                </div>

                <p class="text-xs leading-relaxed text-muted-foreground">
                    Sewa per tanggal ({{ $halls->first()?->operational_start ?? '08:00' }}–{{ $halls->first()?->operational_end ?? '16:00' }}). Kapasitas aula
                    <span x-text="hall?.capacity ?? 0">{{ $preview['capacity'] ?? 0 }}</span> orang.
                </p>
            </div>

            <div class="mt-7 border-t border-border/70 pt-6">
                <div class="flex flex-wrap items-end justify-between gap-x-4 gap-y-2">
                    <span class="text-sm font-medium">Total perkiraan</span>
                    <span class="font-display text-3xl leading-none tabular-nums text-primary" x-text="rupiah(total)">{{ $preview['roomTotal'] ?? 'Rp0' }}</span>
                </div>

                <p class="mt-3 text-xs leading-relaxed text-muted-foreground">
                    Angka ini pratinjau. Tanggal, jam, dan pembayaran final ditentukan pada halaman pemesanan.
                </p>
            </div>

            <x-button :href="$bookingHref" size="lg" class="mt-6 w-full">
                Lanjut ke Pemesanan
                <svg class="size-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd"
                        d="M3 10a.75.75 0 01.75-.75h9.19L9.72 6.03a.75.75 0 111.06-1.06l4.5 4.5a.75.75 0 010 1.06l-4.5 4.5a.75.75 0 11-1.06-1.06l3.22-3.22H3.75A.75.75 0 013 10z"
                        clip-rule="evenodd" />
                </svg>
            </x-button>

            <p class="mt-4 text-center text-xs text-muted-foreground">
                Tanpa akun? Daftar sekali, lalu pemesanan berikutnya cukup satu klik.
            </p>
        </div>
    </div>
</div>
