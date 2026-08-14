<?php

namespace App\Http\Requests\Panel;

use App\Enums\UserRole;
use App\Models\Business;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class InviteUserRequest extends FormRequest
{
    /**
     * Kewenangan diperiksa di controller lewat Policy — hanya super-admin
     * yang boleh mengundang.
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
        $isBusinessAdmin = $this->input('role') === UserRole::BusinessAdmin->value;

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique(User::class, 'email')],
            'role' => ['required', Rule::enum(UserRole::class)],

            // Aturan spec §4: business_admin WAJIB punya anak usaha,
            // super_admin WAJIB tidak. Ditegakkan di server, bukan cuma
            // disembunyikan di tampilan.
            'business' => [
                $isBusinessAdmin ? 'required' : 'prohibited',
                'nullable',
                'string',
                Rule::exists(Business::class, 'slug')->whereNull('deleted_at'),

                // Satu anak usaha satu admin — tapi hanya yang AKTIF yang
                // dihitung. Anak usaha yang adminnya dinonaktifkan boleh
                // diundangkan admin baru tanpa harus menghapus yang lama.
                function (string $attribute, mixed $value, \Closure $fail) {
                    $businessId = Business::where('slug', $value)->value('id');

                    if (! $businessId) {
                        return;
                    }

                    $sudahAda = User::query()
                        ->active()
                        ->where('business_id', $businessId)
                        ->exists();

                    if ($sudahAda) {
                        $fail('Anak usaha ini sudah punya admin aktif. Nonaktifkan yang lama dulu.');
                    }
                },
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'business.required' => 'Pilih anak usaha yang akan dikelola.',
            'business.prohibited' => 'Super admin mengelola seluruh anak usaha, jadi tidak perlu dipilih.',
            'email.unique' => 'Email ini sudah dipakai akun lain.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'nama',
            'email' => 'email',
            'role' => 'peran',
            'business' => 'anak usaha',
        ];
    }
}
