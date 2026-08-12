<?php

namespace App\Http\Requests\Panel;

use Illuminate\Foundation\Http\FormRequest;

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
            // Format wa.me: 62 diikuti 8-13 digit, tanpa spasi/tanda plus.
            // Divalidasi ketat karena dipakai membentuk link, bukan sekadar
            // ditampilkan — nomor berformat salah menghasilkan link mati.
            'whatsapp' => ['nullable', 'string', 'regex:/^62[0-9]{8,13}$/'],
            'instagram' => ['nullable', 'string', 'max:100', 'regex:/^[A-Za-z0-9._]+$/'],
            'tiktok' => ['nullable', 'string', 'max:100', 'regex:/^[A-Za-z0-9._]+$/'],
            'address' => ['nullable', 'string', 'max:255'],
            'business_hours' => ['nullable', 'string', 'max:255'],
            'catalog_label' => ['required', 'string', 'max:50'],
            'portfolio_label' => ['required', 'string', 'max:50'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'whatsapp.regex' => 'Nomor WhatsApp harus diawali 62 tanpa spasi atau tanda plus, contoh: 6281234567890.',
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
            'whatsapp' => 'nomor WhatsApp',
            'address' => 'alamat',
            'business_hours' => 'jam buka',
            'catalog_label' => 'label katalog',
            'portfolio_label' => 'label portfolio',
        ];
    }
}
