@extends('layouts.app')

@section('title', 'Daftar Pelanggan')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-semibold tracking-tight">Daftar Pelanggan</h1>
        <p class="text-sm text-muted-foreground">Kelola akun pelanggan dan reset kata sandi jika dibutuhkan.</p>
    </div>

    <x-card>
        <x-card-content class="p-0">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-muted/50 border-b">
                        <tr>
                            <th class="px-4 py-3 font-medium text-muted-foreground">Nama Lengkap</th>
                            <th class="px-4 py-3 font-medium text-muted-foreground">Email</th>
                            <th class="px-4 py-3 font-medium text-muted-foreground">No. HP</th>
                            <th class="px-4 py-3 font-medium text-muted-foreground text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @forelse ($users as $customer)
                            <tr class="hover:bg-muted/50 transition-colors" x-data="{ resetModalOpen: false }">
                                <td class="px-4 py-3 font-medium">{{ $customer->name }}</td>
                                <td class="px-4 py-3">{{ $customer->email }}</td>
                                <td class="px-4 py-3">{{ $customer->phone ?? '-' }}</td>
                                <td class="px-4 py-3 text-right">
                                    <x-button size="sm" variant="outline" @click="resetModalOpen = true">
                                        Reset Password
                                    </x-button>

                                    <!-- Modal Reset Password (Alpine.js) -->
                                    <div x-cloak x-show="resetModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 text-left">
                                        <div @click.away="resetModalOpen = false" class="w-full max-w-md bg-background rounded-xl p-6 shadow-lg">
                                            <h3 class="text-lg font-semibold mb-2">Reset Sandi: {{ $customer->name }}</h3>
                                            <p class="text-sm text-muted-foreground mb-4">Masukkan kata sandi baru untuk pelanggan ini.</p>
                                            
                                            <form method="POST" action="{{ route('users.reset-password', $customer) }}">
                                                @csrf
                                                @method('PUT')
                                                
                                                <div class="space-y-4">
                                                    <div class="space-y-1">
                                                        <label class="text-sm font-medium">Kata Sandi Baru</label>
                                                        <input type="password" name="password" class="flex h-9 w-full rounded-md border border-input px-3 py-1 text-sm" required>
                                                    </div>
                                                    <div class="space-y-1">
                                                        <label class="text-sm font-medium">Konfirmasi Sandi Baru</label>
                                                        <input type="password" name="password_confirmation" class="flex h-9 w-full rounded-md border border-input px-3 py-1 text-sm" required>
                                                    </div>
                                                </div>

                                                <div class="mt-6 flex justify-end gap-2">
                                                    <x-button type="button" variant="outline" @click="resetModalOpen = false">Batal</x-button>
                                                    <x-button type="submit">Simpan Password Baru</x-button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-8 text-center text-muted-foreground">Belum ada pelanggan terdaftar.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if ($users->hasPages())
                <div class="p-4 border-t">
                    {{ $users->links() }}
                </div>
            @endif
        </x-card-content>
    </x-card>
</div>
@endsection