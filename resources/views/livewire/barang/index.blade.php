<!-- resources/views/livewire/barang/index.blade.php -->
<div>
    {{-- Hanya Admin yang bisa melihat form ini --}}
    @if (auth()->user()->role === \App\Models\User::ROLE_ADMIN)
        <x-card title="Tambah Barang Baru" shadow separator>
            <x-form wire:submit="save">
                <div class="grid gap-5 sm:grid-cols-2">
                    <div class="relative" x-data @click.away="$wire.hideSuggestions()">
                        {{-- Input teks standar yang 100% kompatibel dengan Livewire --}}
                        <x-input label="Nama Barang" wire:model.live.debounce.300ms="nama_barang"
                            placeholder="Ketik nama barang..." autocomplete="off" />

                        {{-- Daftar Saran (Dropdown Autocomplete) --}}
                        @if ($showSuggestions)
                            <div
                                class="absolute z-20 w-full mt-1 bg-base-100 border border-base-300 rounded-lg shadow-lg max-h-60 overflow-auto">
                                <ul class="divide-y divide-base-300">
                                    @forelse($namaBarangSuggestions as $suggestion)
                                        <li wire:key="suggestion-{{ $loop->index }}"
                                            class="flex items-center justify-between p-3 hover:bg-base-200">
                                            <span class="text-base-content">{{ $suggestion }}</span>
                                            <x-button label="Gunakan" class="btn-xs btn-ghost" {{-- PENTING: .prevent agar tidak memicu @click.away --}}
                                                wire:click.prevent="useSuggestedName('{{ $suggestion }}')"
                                                spinner="useSuggestedName('{{ $suggestion }}')" />
                                        </li>
                                    @empty
                                        <li class="p-3 text-center text-gray-500">Tidak ada saran ditemukan.</li>
                                    @endforelse
                                </ul>
                            </div>
                        @endif
                    </div>
                    <x-file label="Foto Barang" wire:model="foto_barang" accept="image/png, image/jpeg" />
                    <div wire:loading wire:target="foto_barang" class="mt-2 text-sm text-gray-500">
                        Sedang mengunggah foto...
                    </div>
                </div>

                <x-slot:actions>
                    <x-button label="Batal" @click="$wire.showCreateModal = false" />
                    <x-button label="Simpan" class="btn-primary" type="submit" spinner="save"
                        wire:loading.attr="disabled" wire:loading.class="btn-disabled"
                        wire:target="foto_barang, save" />
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
                                        <div class="flex justify-end w-full gap-2">

                                            @if (auth()->user()->role === \App\Models\User::ROLE_ADMIN)
                                                <x-button icon="o-pencil" class="btn-sm btn-circle btn-warning"
                                                    title="Edit Barang" wire:click="edit('{{ $barang->id }}')"
                                                    spinner="edit('{{ $barang->id }}')" />
                                            @endif

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
                        {{ $barangs->links('components.paginator') }}
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

    {{-- MODAL UNTUK EDIT BARANG --}}
    <x-modal wire:model="showEditModal" title="Edit Barang" separator>
        {{-- Tampilkan modal hanya jika ada barang yang sedang diedit --}}
        @if ($editingBarang)
            <x-form wire:submit="update">
                <div class="space-y-4">
                    {{-- Preview foto saat ini --}}
                    <div>
                        <label class="block mb-2 text-sm font-bold">Foto Saat Ini</label>
                        <img src="{{ route('images.show', ['filename' => basename($editingBarang->foto_barang)]) }}"
                            class="h-40 rounded-lg" alt="Foto saat ini">
                    </div>

                    {{-- Form input --}}
                    <x-input label="Nama Barang" wire:model="editingNamaBarang" />
                    <x-file label="Ganti Foto (Opsional)" wire:model="newFotoBarang" accept="image/png, image/jpeg" />

                    {{-- Loading state untuk file upload --}}
                    <div wire:loading wire:target="newFotoBarang" class="mt-2 text-sm text-gray-500">
                        Sedang mengunggah foto baru...
                    </div>

                    {{-- Preview foto baru yang akan diunggah --}}
                    @if ($newFotoBarang)
                        <div>
                            <label class="block mb-2 text-sm font-bold">Preview Foto Baru</label>
                            <img src="{{ $newFotoBarang->temporaryUrl() }}" class="h-40 rounded-lg">
                        </div>
                    @endif
                </div>

                <x-slot:actions>
                    <x-button label="Batal" wire:click="closeEditModal" />
                    <x-button label="Update" class="btn-primary" type="submit" spinner="update"
                        wire:loading.attr="disabled" wire:loading.class="btn-disabled"
                        wire:target="newFotoBarang, update" />
                </x-slot:actions>
            </x-form>
        @endif
    </x-modal>
</div>
