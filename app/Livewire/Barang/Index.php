<?php

namespace App\Livewire\Barang;

use App\Models\Barang;
use App\Models\User;
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
use Illuminate\Support\Collection;
use Livewire\WithPagination;

#[Title('Beranda')]
class Index extends Component
{
    use WithFileUploads;
    use Toast;
    use WithPagination;

    // Properti form
    public string $nama_barang = '';
    public $foto_barang;

    // Properti state
    #[Url(as: 'q', keep: true)]
    public string $search = '';
    public bool $readyToLoad = false; // Gunakan nama yang lebih deskriptif

    public bool $showEditModal = false;
    public ?Barang $editingBarang = null; // Akan menampung model Barang yang sedang diedit
    public string $editingNamaBarang = '';
    public $newFotoBarang;

    public Collection $namaBarangSuggestions;
    public bool $showSuggestions = false;

    /**
     * Mount hanya dijalankan sekali untuk inisialisasi.
     * Tidak ada pengambilan data di sini.
     */
    public function mount()
    {
        $this->namaBarangSuggestions = collect();
    }

    public function updatedNamaBarang($value)
    {
        // Tampilkan saran hanya jika input tidak kosong
        if (!empty($value)) {
            $this->namaBarangSuggestions = Barang::query()
                ->where('nama_barang', 'like', '%' . $value . '%')
                ->distinct()
                ->pluck('nama_barang')
                ->take(5); // Batasi hanya 5 saran untuk performa

            $this->showSuggestions = $this->namaBarangSuggestions->isNotEmpty();
        } else {
            $this->showSuggestions = false;
        }
    }

    public function useSuggestedName(string $name)
    {
        $this->nama_barang = $name; // Isi input dengan nama yang dipilih
        $this->showSuggestions = false; // Sembunyikan daftar saran
    }

    public function hideSuggestions()
    {
        // Tunda sedikit agar aksi klik 'useSuggestedName' sempat dieksekusi
        sleep(0.1);
        $this->showSuggestions = false;
    }

    /**
     * Method ini akan dipanggil oleh `wire:init` di view.
     * Tugasnya hanya satu: memberitahu komponen bahwa ia siap memuat data.
     */
    public function loadBarangs()
    {
        $this->readyToLoad = true;
    }

    public function edit(string $barangId)
    {
        $this->editingBarang = Barang::findOrFail($barangId);
        $this->editingNamaBarang = $this->editingBarang->nama_barang;

        // Reset properti lain untuk memastikan form bersih setiap kali dibuka
        $this->reset('newFotoBarang');
        $this->resetValidation();

        $this->showEditModal = true;
    }

    public function update()
    {
        // Pastikan ada barang yang sedang diedit
        if (!$this->editingBarang) {
            return;
        }

        // Validasi input
        $validated = $this->validate([
            'editingNamaBarang' => 'required|string|max:255',
            'newFotoBarang' => 'nullable|image|max:2048', // Foto baru bersifat opsional
        ]);

        // Update nama barang
        $this->editingBarang->nama_barang = $validated['editingNamaBarang'];

        // Cek jika ada foto baru yang diunggah
        if ($this->newFotoBarang) {
            // Hapus foto lama dari storage
            Storage::disk('local')->delete($this->editingBarang->foto_barang);

            // Proses dan simpan foto baru (logika sama seperti method save())
            $baseFileName = Str::slug($validated['editingNamaBarang']);
            $extension = $this->newFotoBarang->getClientOriginalExtension();
            $directory = 'barang_foto';
            $path = $directory . '/' . $baseFileName . '.' . $extension;

            $counter = 1;
            while (Storage::disk('local')->exists($path)) {
                $path = $directory . '/' . $baseFileName . '_' . $counter . '.' . $extension;
                $counter++;
            }

            $manager = new ImageManager(new Driver());
            $image = $manager->read($this->newFotoBarang->getRealPath());
            $image->resize(800, 800, fn($constraint) => $constraint->aspectRatio()->upsize());
            Storage::disk('local')->put($path, (string) $image->encode());

            // Update path foto di database
            $this->editingBarang->foto_barang = $path;
        }

        // Simpan semua perubahan ke database
        $this->editingBarang->save();

        $this->success('Barang berhasil diperbarui.');
        $this->closeEditModal();
    }

    public function closeEditModal()
    {
        $this->showEditModal = false;
        $this->reset('editingBarang', 'editingNamaBarang', 'newFotoBarang');
    }

    public function save()
    {
        if (Auth::user()->role !== User::ROLE_ADMIN) {
            abort(403);
        }

        $validated = $this->validate([
            'nama_barang' => 'required|string|max:255',
            'foto_barang' => 'required|image',
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

    public function startDownload(string $barangId)
    {
        $barang = Barang::findOrFail($barangId);

        // 1. Update status di database jika belum pernah di-download
        if (!$barang->pernah_didownload) {
            $barang->pernah_didownload = true;
            $barang->save();
        }

        // 2. Buat URL download yang aman
        $downloadUrl = route('barang.download', $barang->id);

        // 3. Kirim event ke browser untuk memulai download
        //    Livewire akan otomatis me-refresh komponen setelah aksi ini,
        //    sehingga centang akan langsung muncul.
        $this->dispatch('start-download', url: $downloadUrl);
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
            ->paginate(12) // Ganti get() menjadi paginate(). 12 item (3x4 grid) per halaman
            : collect();

        return view('livewire.barang.index', [
            'barangs' => $barangs
        ]);
    }
}
