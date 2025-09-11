<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BarangController extends Controller
{
    /**
     * Menangani permintaan download gambar barang.
     *
     * @param string $id UUID dari barang
     * @return StreamedResponse|\Illuminate\Http\Response
     */
    public function downloadImage(string $id)
    {
        // 1. Cari data barang, jika tidak ada akan otomatis 404 Not Found
        $barang = Barang::findOrFail($id);

        // 2. Dapatkan path file dari database (contoh: 'barang_foto/nama-file.jpg')
        $path = $barang->foto_barang;

        // 3. Pastikan file benar-benar ada di storage 'local' kita
        if (!Storage::disk('local')->exists($path)) {
            abort(404, 'File gambar tidak ditemukan.');
        }

        if (!$barang->pernah_didownload) {
            $barang->pernah_didownload = true;
            $barang->save();
        }

        // 4. Buat nama file yang lebih ramah pengguna untuk di-download
        $extension = pathinfo($path, PATHINFO_EXTENSION);
        $friendlyFilename = Str::slug($barang->nama_barang) . '.' . $extension;

        // 5. Kembalikan response download
        //    Laravel akan menangani header Content-Type dan Content-Disposition
        return Storage::disk('local')->download($path, $friendlyFilename);
    }
}
