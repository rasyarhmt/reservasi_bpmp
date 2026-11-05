@extends('layouts.app')

@section('title', 'Simple.Calculia — Reservasi Kamar & Aula')

@section('content')
    @php
        $reservationHref = auth()->check()
            ? (auth()->user()->isAdmin() ? route('katalog') : route('booking.create'))
            : route('register');

        $roomPrice = $featuredRoom?->base_price ?? 0;
        $roomCapacity = $featuredRoom?->base_capacity ?? 4;
        $hallCapacity = $featuredHall?->capacity ?? 0;

        // Label dan Meta Seragam
        $kamarLabel = 'Kamar Standar';
        $kamarMeta = 'Rp'.number_format($roomPrice, 0, ',', '.').' / malam · Kapasitas '.$roomCapacity.' orang · AC, TV, lemari';

        $aulaLabel = $featuredHall?->hall_name ?? 'Aula Utama';
        $aulaMeta = 'Kapasitas '.$hallCapacity.' tamu';

        // 7 Foto Kamar (Menggunakan 1 Label & Meta yang sama)
        $heroKamar = [
            ['src' => asset('images/foto-kamar-1.webp'), 'label' => $kamarLabel, 'meta' => $kamarMeta],
            ['src' => asset('images/foto-kamar-2.webp'), 'label' => $kamarLabel, 'meta' => $kamarMeta],
            ['src' => asset('images/foto-kamar-3.webp'), 'label' => $kamarLabel, 'meta' => $kamarMeta],
            ['src' => asset('images/foto-kamar-4.webp'), 'label' => $kamarLabel, 'meta' => $kamarMeta],
            ['src' => asset('images/foto-kamar-5.webp'), 'label' => $kamarLabel, 'meta' => $kamarMeta],
            ['src' => asset('images/foto-kamar-6.webp'), 'label' => $kamarLabel, 'meta' => $kamarMeta],
            ['src' => asset('images/foto-kamar-7.webp'), 'label' => $kamarLabel, 'meta' => $kamarMeta],
        ];

        // 5 Foto Aula (Menggunakan 1 Label & Meta yang sama)
        $heroAula = [
            ['src' => asset('images/foto-aula-1.webp'), 'label' => $aulaLabel, 'meta' => $aulaMeta],
            ['src' => asset('images/foto-aula-2.webp'), 'label' => $aulaLabel, 'meta' => $aulaMeta],
            ['src' => asset('images/foto-aula-3.webp'), 'label' => $aulaLabel, 'meta' => $aulaMeta],
            ['src' => asset('images/foto-aula-4.webp'), 'label' => $aulaLabel, 'meta' => $aulaMeta],
            ['src' => asset('images/foto-aula-5.webp'), 'label' => $aulaLabel, 'meta' => $aulaMeta],
        ];

        // Data untuk Galeri 5 Foto (Bento Grid)
        // Data untuk Galeri 5 Foto (Bento Grid)
        $gallery = [
            [
                'src' => asset('images/foto-kamar-1.webp'),
                'label' => 'Kamar standar',
                'meta' => 'Kapasitas '.$roomCapacity.' orang · AC, TV, lemari',
                'span' => 'md:col-span-7',
                // Gunakan tinggi statis agar sejajar sempurna
                'ratio' => 'h-64 md:h-[400px]', 
            ],
            [
                'src' => asset('images/foto-aula-1.webp'),
                'label' => $featuredHall?->hall_name ?? 'Aula',
                'meta' => 'Kapasitas '.$hallCapacity.' tamu',
                'span' => 'md:col-span-5',
                // Tinggi disamakan dengan kolom sebelahnya
                'ratio' => 'h-64 md:h-[400px]', 
            ],
            [
                'src' => asset('images/foto-kamar-6.webp'),
                'label' => 'Interior kamar',
                'meta' => 'Fasilitas lengkap',
                'span' => 'md:col-span-4',
                // Baris kedua diberi tinggi yang lebih kecil sedikit
                'ratio' => 'h-56 md:h-[300px]', 
            ],
            [
                'src' => asset('images/foto-kamar-7.webp'),
                'label' => 'Sudut ruang',
                'meta' => 'Sirkulasi udara baik',
                'span' => 'md:col-span-4',
                'ratio' => 'h-56 md:h-[300px]',
            ],
            [
                'src' => asset('images/foto-aula-5.webp'), 
                'label' => 'Fasilitas Aula',
                'meta' => 'Luas & fleksibel',
                'span' => 'md:col-span-4',
                'ratio' => 'h-56 md:h-[300px]',
            ],
        ];
    @endphp

    <div class="relative isolate overflow-x-clip">
        <div class="pointer-events-none absolute inset-0 -z-10 ambient-veil animate-ambient"></div>

        {{-- Hero --}}
        <section class="relative">
            <div class="mx-auto grid w-full max-w-7xl gap-10 px-4 pt-10 sm:pt-12 lg:grid-cols-[1.1fr_0.9fr] lg:items-center lg:gap-12 lg:px-8 lg:pt-16">
                <div class="max-w-2xl">
                    <x-landing.eyebrow class="animate-rise">Simple.Calculia · Banua To Dea</x-landing.eyebrow>

                    <h1 class="mt-5 animate-rise [animation-delay:80ms] font-display text-4xl leading-[1.06] tracking-tight text-balance sm:text-5xl lg:text-[3.4rem]">
                        Tempat tinggal yang hangat,
                        <span class="brass-text italic">di tengah kota.</span>
                    </h1>

                    <p class="mt-4 max-w-xl animate-rise [animation-delay:160ms] text-base leading-relaxed text-pretty text-muted-foreground sm:text-lg">
                        Kamar yang tenang, aula yang lapang, dan hitungan biaya yang jelas sebelum Anda membayar.
                        Pesan dalam hitungan detik, bukti transfer langsung lewat WhatsApp.
                    </p>

                    <div class="mt-7 flex animate-rise [animation-delay:240ms] flex-col gap-3 sm:flex-row sm:items-center">
                        <x-button :href="$reservationHref" size="xl" class="group">
                            Pesan Sekarang
                            <svg class="size-4 transition-transform duration-300 group-hover:translate-x-0.5"
                                viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd"
                                    d="M3 10a.75.75 0 01.75-.75h9.19L9.72 6.03a.75.75 0 111.06-1.06l4.5 4.5a.75.75 0 010 1.06l-4.5 4.5a.75.75 0 11-1.06-1.06l3.22-3.22H3.75A.75.75 0 013 10z"
                                    clip-rule="evenodd" />
                            </svg>
                        </x-button>
                    </div>

                    <ul class="mt-6 flex animate-rise [animation-delay:320ms] flex-wrap items-center gap-x-6 gap-y-3 text-xs text-muted-foreground">
                        <li class="flex items-center gap-2">
                            <svg class="size-3.5 text-brass" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M16.7 5.3a1 1 0 010 1.4l-7.5 7.5a1 1 0 01-1.4 0L3.3 9.7a1 1 0 111.4-1.4l3.8 3.8 6.8-6.8a1 1 0 011.4 0z" clip-rule="evenodd" />
                            </svg>
                            Gratis mendaftar
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="size-3.5 text-brass" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M16.7 5.3a1 1 0 010 1.4l-7.5 7.5a1 1 0 01-1.4 0L3.3 9.7a1 1 0 111.4-1.4l3.8 3.8 6.8-6.8a1 1 0 011.4 0z" clip-rule="evenodd" />
                            </svg>
                            DP fleksibel atau bayar penuh
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="size-3.5 text-brass" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M16.7 5.3a1 1 0 010 1.4l-7.5 7.5a1 1 0 01-1.4 0L3.3 9.7a1 1 0 111.4-1.4l3.8 3.8 6.8-6.8a1 1 0 011.4 0z" clip-rule="evenodd" />
                            </svg>
                            Konfirmasi via WhatsApp
                        </li>
                    </ul>
                </div>

                {{-- Image Slider Interaktif --}}
                <div class="relative group" 
                    x-data="{
                        tab: 'kamar',
                        currentIndex: 0,
                        interval: null,
                        kamar: {{ Illuminate\Support\Js::from($heroKamar) }},
                        aula: {{ Illuminate\Support\Js::from($heroAula) }},
                        get activeImages() { return this[this.tab]; },
                        next() { this.currentIndex = (this.currentIndex + 1) % this.activeImages.length; },
                        prev() { this.currentIndex = (this.currentIndex - 1 + this.activeImages.length) % this.activeImages.length; },
                        setTab(t) { this.tab = t; this.currentIndex = 0; },
                        startTimer() { this.interval = setInterval(() => this.next(), 4000); },
                        stopTimer() { clearInterval(this.interval); }
                    }" 
                    x-init="startTimer()"
                    @mouseenter="stopTimer()"
                    @mouseleave="startTimer()">
                    
                    <div class="pointer-events-none absolute -inset-8 -z-10 rounded-[3rem] bg-gradient-to-br from-brass/25 via-primary/15 to-transparent blur-3xl"></div>

                    <figure class="relative aspect-4/3 sm:aspect-16/11 w-full overflow-hidden rounded-[2rem] border border-border/60 bg-card shadow-xl shadow-primary/5 animate-rise [animation-delay:200ms]">
                        
                        {{-- Toggle Kamar / Aula --}}
                        <div class="absolute top-4 right-4 z-30 flex gap-1 rounded-lg bg-black/40 p-1 backdrop-blur-md">
                            <button @click="setTab('kamar')" :class="tab === 'kamar' ? 'bg-white text-black shadow-sm' : 'text-white/80 hover:bg-white/20'" class="rounded-md px-3 py-1 text-xs font-medium transition-colors">
                                Kamar
                            </button>
                            <button @click="setTab('aula')" :class="tab === 'aula' ? 'bg-white text-black shadow-sm' : 'text-white/80 hover:bg-white/20'" class="rounded-md px-3 py-1 text-xs font-medium transition-colors">
                                Aula
                            </button>
                        </div>

                        {{-- Tombol Navigasi Kiri --}}
                        <button @click.stop="prev(); stopTimer(); startTimer()" class="absolute left-3 top-1/2 z-30 -translate-y-1/2 rounded-full bg-black/40 p-2 text-white/80 opacity-0 backdrop-blur-sm transition-all duration-300 hover:bg-black/60 hover:text-white group-hover:opacity-100 sm:left-4">
                            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                            </svg>
                        </button>

                        {{-- Tombol Navigasi Kanan --}}
                        <button @click.stop="next(); stopTimer(); startTimer()" class="absolute right-3 top-1/2 z-30 -translate-y-1/2 rounded-full bg-black/40 p-2 text-white/80 opacity-0 backdrop-blur-sm transition-all duration-300 hover:bg-black/60 hover:text-white group-hover:opacity-100 sm:right-4">
                            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                            </svg>
                        </button>

                        {{-- Auto-sliding Images --}}
                        <template x-for="(image, index) in activeImages" :key="tab + index">
                            <img x-show="currentIndex === index"
                                x-transition:enter="transition-opacity ease-out duration-700"
                                x-transition:enter-start="opacity-0"
                                x-transition:enter-end="opacity-100"
                                x-transition:leave="transition-opacity ease-in duration-700 absolute"
                                x-transition:leave-start="opacity-100"
                                x-transition:leave-end="opacity-0"
                                :src="image.src"
                                :alt="image.label"
                                class="absolute inset-0 h-full w-full object-cover">
                        </template>

                        <div class="pointer-events-none absolute inset-0 bg-gradient-to-t from-foreground/80 via-foreground/10 to-transparent z-10"></div>

                        {{-- Caption dinamis --}}
                        <figcaption class="absolute inset-x-0 bottom-0 p-5 sm:p-6 z-20">
                            <p class="font-display text-lg text-white sm:text-xl" x-text="activeImages[currentIndex].label"></p>
                            <p class="mt-1 text-xs text-white/80 sm:text-sm" x-text="activeImages[currentIndex].meta"></p>
                            
                            {{-- Slide Indicators (Titik) --}}
                            <div class="mt-4 flex flex-wrap gap-1.5 max-w-full">
                                <template x-for="(img, idx) in activeImages" :key="'dot'+idx">
                                    <button @click="currentIndex = idx; stopTimer(); startTimer()" 
                                        class="h-1.5 rounded-full transition-all duration-300"
                                        :class="currentIndex === idx ? 'w-5 bg-white' : 'w-1.5 bg-white/40 hover:bg-white/70'"></button>
                                </template>
                            </div>
                        </figcaption>
                    </figure>
                </div>
            </div>
        </section>

        {{-- Ruang & Fasilitas --}}
        <section id="ruang" class="relative mx-auto w-full max-w-7xl scroll-mt-24 px-4 py-12 lg:px-8 lg:py-16">
            <x-landing.section-heading eyebrow="Ruang & Fasilitas" title="Ruang yang"
                highlight="nyaman untuk tinggal."
                description="Gambaran singkat kamar dan aula kami — tenang pada hari biasa, lapang saat acara keluarga dilangsungkan." />

            <div class="mt-8 grid gap-4 md:grid-cols-12">
                @foreach ($gallery as $item)
                    <figure class="group relative overflow-hidden rounded-2xl border border-border/60 bg-muted shadow-sm transition duration-500 hover:shadow-xl hover:shadow-primary/10 {{ $item['span'] }}">
                        <img src="{{ $item['src'] }}" alt="{{ $item['label'] }}"
                            class="w-full object-cover transition-transform duration-[1.2s] ease-out group-hover:scale-[1.05] {{ $item['ratio'] }}">

                        <div class="pointer-events-none absolute inset-0 bg-gradient-to-t from-foreground/70 via-foreground/10 to-transparent opacity-90 transition-opacity duration-500 group-hover:opacity-100"></div>

                        <figcaption class="absolute inset-x-0 bottom-0 p-5">
                            <p class="font-display text-base text-white">{{ $item['label'] }}</p>
                            <p class="mt-0.5 text-xs text-white/75">{{ $item['meta'] }}</p>
                        </figcaption>
                    </figure>
                @endforeach
            </div>
        </section>

        {{-- Footer --}}
        <footer class="relative border-t border-border/60 bg-card/50">
            <div class="mx-auto flex w-full max-w-7xl flex-col gap-8 px-4 py-8 sm:flex-row sm:items-start sm:justify-between lg:px-8 lg:py-10">
                <div class="max-w-sm">
                    <div class="flex items-center gap-2 font-semibold">
                        <img src="{{ asset('images/simple.calculia.webp') }}" alt="Simple.Calculia"
                            class="size-9 shrink-0 rounded-lg object-contain">
                        Simple.Calculia
                    </div>
                    <p class="mt-3 text-xs leading-relaxed text-muted-foreground">
                        Simple.Calculia atau Sistem Informasi Manajemen Pelayanan yang ditenagai oleh mesin Kalkulasi HPP Berbasis Activity-Based Costing dioperasikan untuk mengoptimalisasi Barang Milik Negara (BMN) di BPMP Sulawesi Tengah
                    </p>
                </div>

                <div class="grid grid-cols-2 gap-x-12 gap-y-3 text-sm sm:grid-cols-2">
                    @if (auth()->check())
                        <a href="{{ route('dashboard') }}" class="text-muted-foreground transition-colors hover:text-foreground">Dasbor</a>
                        <a href="{{ route('reservations.index') }}" class="text-muted-foreground transition-colors hover:text-foreground">Reservasi Saya</a>
                    @else
                        <a href="{{ route('login') }}" class="text-muted-foreground transition-colors hover:text-foreground">Masuk</a>
                        <a href="{{ route('register') }}" class="text-muted-foreground transition-colors hover:text-foreground">Daftar</a>
                    @endif
                </div>
            </div>

            <div class="border-t border-border/60">
                <p class="mx-auto w-full max-w-7xl px-4 py-4 text-xs text-muted-foreground lg:px-8">
                    © {{ now()->year }} Simple.Calculia. Seluruh harga dalam Rupiah dan sudah termasuk biaya
                    operasional penginapan.
                </p>
            </div>
        </footer>
    </div>
@endsection