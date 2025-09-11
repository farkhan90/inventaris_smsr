<!-- resources/views/livewire/barang/index.blade.php -->
<div>
    {{-- Hanya Admin yang bisa melihat form ini --}}
    @if (auth()->user()->role === \App\Models\User::ROLE_ADMIN)
        <x-card title="Tambah Barang Baru" shadow separator>
            <x-form wire:submit="save">
                <div class="grid gap-5 sm:grid-cols-2">
                    <x-input label="Nama Barang" wire:model="nama_barang" />
                    <x-file label="Foto Barang" wire:model="foto_barang" accept="image/png, image/jpeg" />
                </div>

                <x-slot:actions>
                    <x-button label="Batal" @click="$wire.reset()" />
                    <x-button label="Simpan" type="submit" class="btn-primary" spinner="save" />
                </x-slot:actions>
            </x-form>
        </x-card>
    @endif

    {{-- Tabel untuk menampilkan data barang --}}
    <div class="mt-8">
        <div class="items-center justify-between gap-5 md:flex">
            <x-header title="Daftar Barang" size="text-2xl" separator />
            <x-input icon="o-magnifying-glass" placeholder="Cari nama barang..." wire:model.live.debounce.500ms="search"
                class="w-full md:w-72" clearable />
        </div>

        {{-- Gunakan `wire:init` untuk memanggil method `loadBarangs` --}}
        <div wire:init="loadBarangs">
            {{-- Pengecekan sekarang menggunakan `$readyToLoad` --}}
            @if ($readyToLoad)
                @if ($barangs->isNotEmpty())
                    <div
                        class="grid grid-cols-1 gap-6 mt-5 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5">
                        @foreach ($barangs as $barang)
                            <div wire:key="{{ $barang->id }}">
                                <x-card
                                    class="transition duration-300 ease-in-out shadow-md hover:shadow-2xl hover:scale-105">
                                    <img src="{{ route('images.show', ['filename' => basename($barang->foto_barang)]) }}"
                                        class="object-cover w-full h-48 rounded-t-lg" alt="{{ $barang->nama_barang }}">
                                    <div class="p-4">
                                        <h3 class="font-bold min-h-16" title="{{ $barang->nama_barang }}">
                                            {{ $barang->nama_barang }}</h3>
                                        <p class="mt-1 text-xs text-gray-500">
                                            Ditambahkan: {{ $barang->created_at->diffForHumans() }}
                                        </p>
                                        <p class="mt-1 text-xs text-gray-500">
                                            Inventarisasi : @if ($barang->pernah_didownload)
                                                <x-icon name="o-check-circle" class="text-green-500 shrink-0"
                                                    title="Pernah di-download" />
                                            @endif
                                        </p>
                                    </div>
                                    <x-slot:actions>
                                        <div class="flex justify-end w-full gap-2"> {{-- Tambahkan gap-2 untuk spasi --}}

                                            {{-- TOMBOL DOWNLOAD BARU --}}
                                            {{-- Ini adalah link biasa, bukan aksi Livewire, jadi tidak perlu `wire:click` --}}
                                            <x-button icon="o-arrow-down-tray" class="btn-sm btn-circle btn-info"
                                                title="Download Gambar"
                                                wire:click="startDownload('{{ $barang->id }}')"
                                                spinner="startDownload('{{ $barang->id }}')" />

                                            @if (auth()->user()->role === \App\Models\User::ROLE_ADMIN)
                                                <x-button icon="o-trash" class="btn-sm btn-error btn-circle"
                                                    wire:click="delete('{{ $barang->id }}')"
                                                    wire:confirm="Apakah Anda yakin ingin menghapus barang ini?"
                                                    spinner="delete('{{ $barang->id }}')" title="Hapus Barang" />
                                            @endif
                                        </div>
                                    </x-slot:actions>
                                </x-card>
                            </div>
                        @endforeach
                    </div>
                    <div class="mt-8">
                        {{ $barangs->links() }}
                    </div>
                @else
                    <div class="mt-10">
                        @if (empty($search))
                            <x-alert title="Data Kosong!" description="Belum ada data barang yang ditambahkan."
                                icon="o-exclamation-triangle" />
                        @else
                            <x-alert title="Tidak Ditemukan!"
                                description="Tidak ada barang yang cocok dengan pencarian '{{ $search }}'."
                                icon="o-exclamation-triangle" class="alert-warning" />
                        @endif
                    </div>
                @endif
            @else
                {{-- Tampilan Placeholder --}}
                <div class="grid grid-cols-1 gap-6 mt-5 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5">
                    @for ($i = 0; $i < 10; $i++)
                        <div class="overflow-hidden border rounded-lg shadow bg-base-100">
                            <div class="p-4 animate-pulse">
                                <div class="w-full h-48 rounded-lg bg-base-300"></div>
                                <div class="w-3/4 h-5 mt-4 rounded-lg bg-base-300"></div>
                                <div class="w-1/2 h-3 mt-2 rounded-lg bg-base-300"></div>
                            </div>
                        </div>
                    @endfor
                </div>
            @endif
        </div>
    </div>
</div>
