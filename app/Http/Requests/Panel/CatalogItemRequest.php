<?php

namespace App\Http\Requests\Panel;

use App\Services\ImageService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CatalogItemRequest extends FormRequest
{
    /**
     * Kepemilikan diperiksa di controller lewat authorize() terhadap Policy,
     * bukan di sini — pemeriksaannya butuh objek item atau anak usaha yang
     * dituju, yang baru tersedia setelah route model binding.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            // Batas atas menahan angka tak masuk akal yang melebihi
            // decimal(12,2) dan akan ditolak MySQL dengan pesan mentah.
            'price' => ['nullable', 'numeric', 'min:0', 'max:9999999999'],
            'price_note' => ['nullable', 'string', 'max:50'],
            'category' => ['nullable', 'string', 'max:100'],
            'is_available' => ['required', 'boolean'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:9999'],

            // Rule::imageFile() memeriksa ISI berkas, bukan akhiran namanya —
            // berkas apa pun bisa diberi nama .jpg. SVG ditolak secara bawaan
            // oleh aturan ini, dan memang harus: SVG bisa memuat skrip.
            'image' => [
                'nullable',
                Rule::imageFile()
                    ->extensions(['jpeg', 'jpg', 'png', 'webp'])
                    ->max(ImageService::MAX_UPLOAD_KB),
            ],

            // Penanda hapus foto. Terpisah dari `image` karena keduanya
            // menyatakan hal berbeda: `image` kosong berarti "biarkan
            // fotonya", sedangkan ini berarti "buang fotonya".
            //
            // Tanpa pembeda itu, satu-satunya cara menghapus foto adalah
            // menghapus itemnya lalu membuatnya lagi dari nol.
            'remove_image' => ['nullable', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        $mb = round(ImageService::MAX_UPLOAD_KB / 1024);

        return [
            // Pesan menyebut alasan DAN batas yang berlaku (spec §10).
            'image.max' => "Ukuran foto melebihi batas {$mb} MB. Perkecil dulu, atau pilih foto lain.",
            'image.image' => 'Berkas yang diunggah bukan gambar. Pakai JPEG, PNG, atau WebP.',
            'image.mimes' => 'Format gambar tidak didukung. Pakai JPEG, PNG, atau WebP.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'nama item',
            'description' => 'deskripsi',
            'price' => 'harga',
            'price_note' => 'keterangan harga',
            'category' => 'kategori',
            'is_available' => 'status tampil',
            'sort_order' => 'urutan',
        ];
    }
}
