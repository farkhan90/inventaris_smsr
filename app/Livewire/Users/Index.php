<?php

namespace App\Livewire\Users;

use App\Livewire\Forms\UserForm;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Mary\Traits\Toast;

#[Title('Kelola User')]
class Index extends Component
{
    use Toast;
    use WithPagination;

    public UserForm $form;
    public bool $showCreateModal = false;

    public function save()
    {
        $this->form->store();
        $this->success('User baru berhasil ditambahkan.');
        $this->showCreateModal = false;
    }

    public function delete(string $id)
    {
        // Keamanan: Mencegah admin menghapus akunnya sendiri
        if ($id === Auth::id()) {
            $this->error('Anda tidak dapat menghapus akun Anda sendiri.');
            return;
        }

        User::findOrFail($id)->delete();
        $this->success('User berhasil dihapus.');
    }

    public function updateRole(string $id, string $role)
    {
        // Keamanan: Mencegah admin mengubah role akunnya sendiri lewat sini
        if ($id === Auth::id()) {
            $this->error('Anda tidak dapat mengubah role akun sendiri dari sini.');
            return;
        }

        $user = User::findOrFail($id);
        $user->role = $role;
        $user->save();

        $this->success("Role untuk {$user->name} berhasil diubah.");
    }

    public function render()
    {
        // Ambil semua user untuk ditampilkan
        $users = User::latest()->paginate(10);
        return view('livewire.users.index', compact('users'));
    }
}
