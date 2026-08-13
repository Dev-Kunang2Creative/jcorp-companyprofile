<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Pesan validasi harus berupa kalimat, bukan kunci mentah.
 *
 * Latar belakangnya: project disetel APP_LOCALE=id DAN APP_FALLBACK_LOCALE=id,
 * sementara Laravel hanya membawa terjemahan `en`. Tanpa lang/id/validation.php,
 * yang muncul di layar adalah "validation.password.mixed" — bukan penjelasan
 * yang bisa dipahami admin.
 *
 * Ini lolos dari seluruh test lain karena test biasanya memeriksa ADA tidaknya
 * error (assertSessionHasErrors), bukan BUNYI pesannya. Kerusakannya baru
 * ketahuan saat membuat akun admin di server sungguhan.
 *
 * Karena itu test di sini memeriksa isi pesannya, dan yang paling penting:
 * memastikan tidak ada pesan yang masih berupa kunci mentah.
 */
class ValidationMessageTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Penjaga utama: pesan apa pun yang diawali "validation." berarti
     * terjemahannya belum ada.
     */
    private function assertBukanKunciMentah(array $pesan): void
    {
        foreach ($pesan as $p) {
            $this->assertStringNotContainsString(
                'validation.',
                $p,
                "Pesan masih berupa kunci mentah: {$p}",
            );
        }
    }

    public function test_the_app_runs_in_indonesian(): void
    {
        $this->assertSame('id', app()->getLocale());
    }

    public function test_password_rules_speak_indonesian(): void
    {
        // Aturan yang sama dengan produksi (lihat AppServiceProvider).
        $rules = ['password' => [
            'required',
            Password::min(12)->mixedCase()->letters()->numbers()->symbols(),
        ]];

        $errors = Validator::make(['password' => 'abc'], $rules)->errors()->all();

        $this->assertBukanKunciMentah($errors);

        $gabungan = implode(' ', $errors);
        $this->assertStringContainsString('Password', $gabungan);
        $this->assertStringContainsString('huruf besar', $gabungan);
        $this->assertStringContainsString('simbol', $gabungan);
    }

    public function test_common_rules_speak_indonesian(): void
    {
        $errors = Validator::make(
            ['price' => 'bukan angka', 'email' => 'bukan email'],
            [
                'name' => ['required'],
                'price' => ['numeric'],
                'email' => ['email'],
            ],
        )->errors()->all();

        $this->assertBukanKunciMentah($errors);
        $this->assertCount(3, $errors);
    }

    public function test_catalog_form_speaks_indonesian(): void
    {
        $business = Business::factory()->create();
        $admin = User::factory()->forBusiness($business)->create();

        $response = $this->actingAs($admin)
            ->from(route('panel.catalog.index'))
            ->post(route('panel.catalog.store'), []);

        $response->assertSessionHasErrors();

        $errors = session('errors')->all();

        $this->assertBukanKunciMentah($errors);

        // Nama kolom teknis diganti sebutan yang dimengerti admin.
        $this->assertStringContainsString('nama item', implode(' ', $errors));
    }

    public function test_contact_form_speaks_indonesian(): void
    {
        $business = Business::factory()->create();
        $admin = User::factory()->forBusiness($business)->create();

        $response = $this->actingAs($admin)
            ->from(route('panel.profile.edit'))
            ->put(route('panel.profile.update'), [
                'whatsapp' => '0812-3456',
                'catalog_label' => 'Menu Kami',
                'portfolio_label' => 'Hasil Kerja',
            ]);

        $response->assertSessionHasErrors('whatsapp');

        $this->assertBukanKunciMentah(session('errors')->all());
    }

    public function test_image_upload_errors_speak_indonesian(): void
    {
        $business = Business::factory()->create();
        $admin = User::factory()->forBusiness($business)->create();

        $path = tempnam(sys_get_temp_dir(), 'uji');
        file_put_contents($path, 'ini bukan gambar');

        $response = $this->actingAs($admin)
            ->from(route('panel.catalog.index'))
            ->post(route('panel.catalog.store'), [
                'name' => 'Item Uji',
                'is_available' => true,
                'sort_order' => 0,
                'image' => new UploadedFile($path, 'palsu.jpg', null, null, true),
            ]);

        $response->assertSessionHasErrors('image');

        $this->assertBukanKunciMentah(session('errors')->all());
    }

    public function test_login_errors_speak_indonesian(): void
    {
        $errors = Validator::make([], [
            'email' => ['required', 'email'],
            'password' => ['required'],
        ])->errors()->all();

        $this->assertBukanKunciMentah($errors);
    }

    /**
     * @return array<string, array{string}>
     */
    public static function translationKeys(): array
    {
        return [
            // Yang paling sering dilihat orang: muncul setiap kali salah
            // ketik password di halaman login.
            'login gagal' => ['auth.failed'],
            'password salah' => ['auth.password'],
            'terlalu sering mencoba' => ['auth.throttle'],

            // Reset password belum berfungsi (MAIL_MAILER=log), tapi
            // pesannya sudah disiapkan.
            'password diganti' => ['passwords.reset'],
            'tautan dikirim' => ['passwords.sent'],
            'tautan kedaluwarsa' => ['passwords.token'],
            'email tak dikenal' => ['passwords.user'],

            'halaman sebelumnya' => ['pagination.previous'],
            'halaman berikutnya' => ['pagination.next'],
        ];
    }

    #[DataProvider('translationKeys')]
    public function test_framework_messages_are_translated(string $key): void
    {
        // __() mengembalikan kuncinya sendiri kalau terjemahannya tidak ada.
        $this->assertNotSame(
            $key,
            __($key),
            "Terjemahan untuk [{$key}] belum ada — pengguna akan melihat kunci mentahnya.",
        );
    }

    public function test_a_failed_login_shows_a_readable_message(): void
    {
        // Jalur sungguhan yang dilalui admin, bukan __() terisolasi.
        $user = User::factory()->create();

        $response = $this->from(route('login'))
            ->post(route('login.store'), [
                'email' => $user->email,
                'password' => 'password-yang-salah',
            ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();

        $pesan = session('errors')->get('email');

        $this->assertBukanKunciMentah($pesan);
        $this->assertSame('Email atau password salah.', $pesan[0]);
    }
}
