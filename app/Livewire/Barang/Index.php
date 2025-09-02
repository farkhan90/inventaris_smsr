<?php

namespace App\Livewire\Barang;

use App\Models\Barang;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection; // Pastikan ini di-import
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithFileUploads;
use Mary\Traits\Toast;
use Illuminate\Support\Str;

#[Title('Beranda')]
class Index extends Component
{
    use WithFileUploads;
    use Toast;

    // Properti form
    public string $nama_barang = '';
    public $foto_barang;

    // Properti state
    #[Url(as: 'q', keep: true)]
    public string $search = '';
    public bool $readyToLoad = false; // Gunakan nama yang lebih deskriptif

    /**
     * Mount hanya dijalankan sekali untuk inisialisasi.
     * Tidak ada pengambilan data di sini.
     */
    public function mount()
    {
        // Kosongkan saja, property sudah didefinisikan di atas
    }

    /**
     * Method ini akan dipanggil oleh `wire:init` di view.
     * Tugasnya hanya satu: memberitahu komponen bahwa ia siap memuat data.
     */
    public function loadBarangs()
    {
        $this->readyToLoad = true;
    }

    public function save()
    {
        if (Auth::user()->role !== User::ROLE_ADMIN) {
            abort(403);
        }

        $validated = $this->validate([
            'nama_barang' => 'required|string|max:255',
            'foto_barang' => 'required|image|max:2048',
        ]);

        $baseFileName = Str::slug($validated['nama_barang']);
        $extension = $this->foto_barang->getClientOriginalExtension();
        $directory = 'barang_foto';
        $path = $directory . '/' . $baseFileName . '.' . $extension;

        $counter = 1;
        while (Storage::disk('local')->exists($path)) {
            $path = $directory . '/' . $baseFileName . '_' . $counter . '.' . $extension;
            $counter++;
        }

        $manager = new ImageManager(new Driver());
        $image = $manager->read($this->foto_barang->getRealPath());
        $image->scale(height: 500);

        Storage::disk('local')->put($path, (string) $image->encode());

        Barang::create([
            'nama_barang' => $validated['nama_barang'],
            'foto_barang' => $path,
        ]);

        $this->success('Barang berhasil ditambahkan!');
        $this->reset('nama_barang', 'foto_barang');
    }

    public function delete(string $id)
    {
        if (Auth::user()->role !== User::ROLE_ADMIN) {
            abort(403);
        }
        $barang = Barang::findOrFail($id);
        Storage::disk('local')->delete($barang->foto_barang);
        $barang->delete();

        $this->success('Barang berhasil dihapus.');
    }

    public function render()
    {
        // Ambil data hanya jika komponen sudah siap.
        // Jika belum, kembalikan collection kosong.
        $barangs = $this->readyToLoad
            ? Barang::query()
            ->when($this->search, function ($query) {
                $query->where('nama_barang', 'like', '%' . $this->search . '%');
            })
            ->latest()
            ->get()
            : collect();

        return view('livewire.barang.index', [
            'barangs' => $barangs
        ]);
    }
}
