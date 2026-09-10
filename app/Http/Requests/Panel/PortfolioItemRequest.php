<?php

namespace App\Http\Requests\Panel;

use App\Services\ImageService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PortfolioItemRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if (! $this->has('is_featured_on_home')) {
            $this->merge(['is_featured_on_home' => false]);
        }
    }

    /**
     * Kepemilikan diperiksa di controller lewat Policy — lihat
     * CatalogItemRequest untuk alasannya.
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
        // Berbeda dari katalog: portfolio adalah kumpulan foto, jadi fotonya
        // wajib saat menambah baru. Saat mengubah, foto boleh dikosongkan —
        // artinya foto lama dipertahankan dan hanya keterangannya yang diubah.
        $imageRequired = $this->isMethod('POST') ? 'required' : 'nullable';

        return [
            'image' => [
                $imageRequired,
                Rule::imageFile()
                    ->extensions(['jpeg', 'jpg', 'png', 'webp'])
                    ->max(ImageService::MAX_UPLOAD_KB),
            ],
            'caption' => ['nullable', 'string', 'max:255'],
            'is_featured_on_home' => ['required', 'boolean'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:9999'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        $mb = round(ImageService::MAX_UPLOAD_KB / 1024);

        return [
            'image.required' => 'Pilih foto yang mau diunggah.',
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
            'image' => 'foto',
            'caption' => 'keterangan',
            'is_featured_on_home' => 'pilihan tampil di halaman induk',
            'sort_order' => 'urutan',
        ];
    }
}
