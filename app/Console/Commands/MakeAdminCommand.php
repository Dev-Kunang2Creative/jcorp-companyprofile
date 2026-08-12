<?php

namespace App\Console\Commands;

use App\Enums\UserRole;
use App\Models\Business;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

use function Laravel\Prompts\password as promptPassword;
use function Laravel\Prompts\select;
use function Laravel\Prompts\text;

/**
 * Membuat akun panel (spec §6 — tidak ada halaman pendaftaran).
 *
 * Password diminta lewat prompt tersembunyi dan sengaja TIDAK disediakan
 * sebagai opsi baris perintah: argumen perintah tersimpan di riwayat shell
 * dan terlihat di daftar proses.
 */
class MakeAdminCommand extends Command
{
    protected $signature = 'jcorp:make-admin
                            {--name= : Nama lengkap pemilik akun}
                            {--email= : Alamat email untuk masuk}
                            {--role= : super_admin atau business_admin}
                            {--business= : Slug anak usaha (wajib untuk business_admin)}';

    protected $description = 'Membuat akun admin panel J Corp';

    public function handle(): int
    {
        $name = $this->option('name') ?: text(
            label: 'Nama lengkap',
            required: true,
        );

        $email = $this->option('email') ?: text(
            label: 'Alamat email',
            required: true,
        );

        // select() bertipe int|string karena kunci array PHP boleh berupa
        // angka; di sini kuncinya selalu string, jadi dinormalkan sekali di
        // sini daripada dibiarkan menyebar sebagai tipe campuran.
        $role = (string) ($this->option('role') ?: select(
            label: 'Peran',
            options: [
                UserRole::BusinessAdmin->value => UserRole::BusinessAdmin->label(),
                UserRole::SuperAdmin->value => UserRole::SuperAdmin->label(),
            ],
            default: UserRole::BusinessAdmin->value,
        ));

        $businessSlug = $this->option('business');

        if ($role === UserRole::BusinessAdmin->value && ! $businessSlug) {
            $available = $this->assignableBusinesses();

            if ($available->isEmpty()) {
                $this->error('Semua anak usaha sudah punya admin. Hapus admin lamanya dulu, atau buat super-admin.');

                return self::FAILURE;
            }

            $businessSlug = (string) select(
                label: 'Anak usaha yang dipegang',
                options: $available->all(),
            );
        }

        $password = promptPassword(
            label: 'Password',
            required: true,
        );

        $validator = Validator::make([
            'name' => $name,
            'email' => $email,
            'role' => $role,
            'business' => $businessSlug,
            'password' => $password,
        ], $this->rules($role));

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $message) {
                $this->error($message);
            }

            return self::FAILURE;
        }

        $user = new User;
        $user->name = $name;
        $user->email = $email;
        $user->password = $password;
        $user->role = UserRole::from($role);
        $user->business_id = $businessSlug
            ? Business::where('slug', $businessSlug)->value('id')
            : null;
        $user->save();

        $this->info("Akun {$user->email} dibuat sebagai {$user->role->label()}.");

        if ($user->business_id) {
            $this->line('Memegang: '.$user->business->name);
        }

        return self::SUCCESS;
    }

    /**
     * Aturan validasi. Dua di antaranya menegakkan syarat spec §4 bahwa satu
     * admin memegang tepat satu anak usaha:
     *
     * - business_admin WAJIB punya anak usaha
     * - super_admin WAJIB tidak punya
     *
     * @return array<string, mixed>
     */
    private function rules(string $role): array
    {
        $isBusinessAdmin = $role === UserRole::BusinessAdmin->value;

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique(User::class, 'email')],
            'role' => ['required', Rule::enum(UserRole::class)],
            'business' => [
                $isBusinessAdmin ? 'required' : 'prohibited',
                'nullable',
                'string',
                Rule::exists(Business::class, 'slug')->whereNull('deleted_at'),
                // Satu anak usaha hanya boleh dipegang satu admin (spec §4).
                function (string $attribute, mixed $value, \Closure $fail) {
                    $businessId = Business::where('slug', $value)->value('id');

                    if ($businessId && User::where('business_id', $businessId)->exists()) {
                        $fail('Anak usaha ini sudah punya admin.');
                    }
                },
            ],
            'password' => ['required', 'string', Password::defaults()],
        ];
    }

    /**
     * Anak usaha yang belum dipegang admin mana pun.
     *
     * @return Collection<string, string>
     */
    private function assignableBusinesses(): Collection
    {
        return Business::query()
            ->whereDoesntHave('admins')
            ->orderBy('sort_order')
            ->pluck('name', 'slug');
    }
}
