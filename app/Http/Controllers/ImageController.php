<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ImageController extends Controller
{
    /**
     * Mengambil dan menampilkan gambar dari storage privat.
     */
    public function show(string $filename): StreamedResponse|\Illuminate\Http\Response
    {
        $path = 'barang_foto/' . $filename;

        // Pastikan file ada di storage 'local' (storage/app)
        if (!Storage::disk('local')->exists($path)) {
            abort(404);
        }

        // Kembalikan file sebagai response
        // Laravel akan secara otomatis mengatur Content-Type yang benar
        return Storage::disk('local')->response($path);
    }
}
