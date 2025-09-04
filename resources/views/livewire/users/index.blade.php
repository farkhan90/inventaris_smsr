<div>
    {{-- HEADER DAN TOMBOL TAMBAH --}}
    <x-header title="Kelola User" separator>
        <x-slot:actions>
            <x-button label="Tambah User" icon="o-plus" class="btn-primary" @click="$wire.showCreateModal = true" />
        </x-slot:actions>
    </x-header>

    {{-- TABEL PENGGUNA --}}
    <x-card>
        <x-table :headers="[
            ['key' => 'name', 'label' => 'Nama'],
            ['key' => 'email', 'label' => 'Email'],
            ['key' => 'role', 'label' => 'Role'],
            ['key' => 'actions', 'label' => 'Aksi', 'class' => 'w-1'],
        ]" :rows="$users" with-pagination>

            @scope('cell_role', $user)
                {{-- Hanya bisa mengubah role user lain --}}
                @if ($user->id !== auth()->id())
                    <x-select :options="[['id' => 'admin', 'name' => 'Admin'], ['id' => 'staff', 'name' => 'Staff']]" wire:model.live="role.{{ $user->id }}"
                        wire:change="updateRole('{{ $user->id }}', $event.target.value)" :value="$user->role"
                        class="select-sm" />
                @else
                    <x-badge :value="$user->role" class="badge-primary" />
                @endif
            @endscope

            @scope('cell_actions', $user)
                {{-- Hanya bisa menghapus user lain --}}
                @if ($user->id !== auth()->id())
                    <x-button icon="o-trash" wire:click="delete('{{ $user->id }}')" wire:confirm="Apakah Anda yakin?"
                        spinner class="btn-sm btn-error" />
                @endif
            @endscope
        </x-table>
    </x-card>

    {{-- MODAL UNTUK MEMBUAT USER BARU --}}
    <x-modal wire:model="showCreateModal" title="Tambah User Baru" separator>
        <x-form wire:submit="save">
            <x-input label="Nama" wire:model="form.name" />
            <x-input label="Email" wire:model="form.email" type="email" />
            <x-select label="Role" :options="[['id' => 'staff', 'name' => 'Staff'], ['id' => 'admin', 'name' => 'Admin']]" wire:model="form.role" />
            <x-input label="Password" wire:model="form.password" type="password" />
            <x-input label="Konfirmasi Password" wire:model="form.password_confirmation" type="password" />

            <x-slot:actions>
                <x-button label="Batal" @click="$wire.showCreateModal = false" />
                <x-button label="Simpan" class="btn-primary" type="submit" spinner="save" />
            </x-slot:actions>
        </x-form>
    </x-modal>
</div>
