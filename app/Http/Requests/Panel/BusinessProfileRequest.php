<?php

namespace App\Http\Requests\Panel;

use App\Services\ImageService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BusinessProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Cakupan yang bisa diubah admin (spec §6): info kontak dan label section.
     *
     * `name`, `slug`, `is_published`, dan cerita perusahaan sengaja tidak ada
     * di sini — field yang tidak divalidasi tidak akan ikut tersimpan.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'cover_image' => [
                'nullable',
                Rule::imageFile()
                    ->extensions(['jpeg', 'jpg', 'png', 'webp'])
                    ->max(ImageService::MAX_UPLOAD_KB),
            ],
            'remove_cover_image' => ['nullable', 'boolean'],
            // Format wa.me: 62 diikuti 8-13 digit, tanpa spasi/tanda plus.
            // Divalidasi ketat karena dipakai membentuk link, bukan sekadar
            // ditampilkan — nomor berformat salah menghasilkan link mati.
            'whatsapp' => ['nullable', 'string', 'regex:/^62[0-9]{8,13}$/'],
            // Divalidasi seketat nomor utama — sama-sama dipakai membentuk
            // link wa.me, bukan sekadar ditampilkan.
            'whatsapp_alt' => ['nullable', 'string', 'regex:/^62[0-9]{8,13}$/'],
            'instagram' => ['nullable', 'string', 'max:100', 'regex:/^[A-Za-z0-9._]+$/'],
            'tiktok' => ['nullable', 'string', 'max:100', 'regex:/^[A-Za-z0-9._]+$/'],
            'address' => ['nullable', 'string', 'max:255'],
            'business_hours' => ['nullable', 'string', 'max:255'],
            'contact_note' => ['nullable', 'string', 'max:255'],
            'catalog_label' => ['required', 'string', 'max:50'],
            'catalog_note' => ['nullable', 'string', 'max:500'],
            'portfolio_label' => ['required', 'string', 'max:50'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'cover_image.max' => 'Ukuran foto pembuka melebihi batas 4 MB.',
            'cover_image.image' => 'Berkas foto pembuka harus berupa gambar.',
            'cover_image.mimes' => 'Format foto pembuka harus JPEG, PNG, atau WebP.',
            'whatsapp.regex' => 'Nomor WhatsApp harus diawali 62 tanpa spasi atau tanda plus, contoh: 6281234567890.',
            'whatsapp_alt.regex' => 'Nomor WhatsApp kedua harus diawali 62 tanpa spasi atau tanda plus, contoh: 6281234567890.',
            'instagram.regex' => 'Isi username Instagram saja, tanpa @ atau alamat lengkap.',
            'tiktok.regex' => 'Isi username TikTok saja, tanpa @ atau alamat lengkap.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'cover_image' => 'foto pembuka',
            'whatsapp' => 'nomor WhatsApp',
            'whatsapp_alt' => 'nomor WhatsApp kedua',
            'address' => 'alamat',
            'business_hours' => 'jam buka',
            'contact_note' => 'catatan pemesanan',
            'catalog_label' => 'label katalog',
            'catalog_note' => 'catatan harga',
            'portfolio_label' => 'label portfolio',
        ];
    }
}
