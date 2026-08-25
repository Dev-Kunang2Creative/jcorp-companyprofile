<?php

namespace Tests\Feature\Panel;

use App\Models\Business;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Form info kontak di panel (spec §6).
 *
 * Cakupan yang bisa diubah admin sengaja terbatas: kontak dan label section.
 * Cerita perusahaan, visi, misi, layanan, dan keunggulan TIDAK ada di form —
 * teks panjang yang jarang berubah, diisi lewat seeder saat materi client
 * masuk. Test di sini menjaga batas itu tetap pada tempatnya.
 */
class BusinessProfileTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function payload(array $overrides = []): array
    {
        return [
            'whatsapp' => '628123456789',
            'catalog_label' => 'Menu Kami',
            'portfolio_label' => 'Portfolio',
            ...$overrides,
        ];
    }

    public function test_an_admin_can_save_a_second_phone_number(): void
    {
        $business = Business::factory()->create(['whatsapp_alt' => null]);
        $admin = User::factory()->forBusiness($business)->create();

        $this->actingAs($admin)
            ->put(route('panel.profile.update'), $this->payload([
                'whatsapp_alt' => '628987654321',
            ]))
            ->assertSessionHasNoErrors();

        $this->assertSame('628987654321', $business->refresh()->whatsapp_alt);
    }

    public function test_the_second_phone_number_is_validated_as_strictly_as_the_first(): void
    {
        // Sama-sama dipakai membentuk link wa.me. Nomor berformat lokal
        // menghasilkan tautan mati, dan tidak ada yang menyadarinya sampai
        // ada pengunjung yang menekannya.
        $business = Business::factory()->create();
        $admin = User::factory()->forBusiness($business)->create();

        $this->actingAs($admin)
            ->put(route('panel.profile.update'), $this->payload([
                'whatsapp_alt' => '0819-3802-0815',
            ]))
            ->assertSessionHasErrors('whatsapp_alt');
    }

    public function test_the_second_phone_number_may_be_left_empty(): void
    {
        $business = Business::factory()->create(['whatsapp_alt' => '628987654321']);
        $admin = User::factory()->forBusiness($business)->create();

        $this->actingAs($admin)
            ->put(route('panel.profile.update'), $this->payload([
                'whatsapp_alt' => '',
            ]))
            ->assertSessionHasNoErrors();

        $this->assertNull($business->refresh()->whatsapp_alt);
    }

    public function test_an_admin_can_save_a_catalog_note(): void
    {
        $business = Business::factory()->create(['catalog_note' => null]);
        $admin = User::factory()->forBusiness($business)->create();

        $this->actingAs($admin)
            ->put(route('panel.profile.update'), $this->payload([
                'catalog_note' => 'Harga sudah termasuk pemasangan.',
            ]))
            ->assertSessionHasNoErrors();

        $this->assertSame(
            'Harga sudah termasuk pemasangan.',
            $business->refresh()->catalog_note,
        );
    }

    public function test_an_admin_can_save_a_contact_note(): void
    {
        $business = Business::factory()->create(['contact_note' => null]);
        $admin = User::factory()->forBusiness($business)->create();

        $this->actingAs($admin)
            ->put(route('panel.profile.update'), $this->payload([
                'contact_note' => 'Pemesanan hanya lewat chat WhatsApp.',
            ]))
            ->assertSessionHasNoErrors();

        $this->assertSame(
            'Pemesanan hanya lewat chat WhatsApp.',
            $business->refresh()->contact_note,
        );
    }

    /**
     * Batas yang paling perlu dijaga.
     *
     * Kolom ini menampung materi asli client. Kalau ikut terisi dari form —
     * misalnya karena kelak ditambahkan ke `#[Fillable]` tanpa memikirkan
     * ulang — seorang admin bisa mengubah visi perusahaan dengan menambah
     * satu field di request, tanpa pernah ada layar yang menampilkannya.
     */
    public function test_profile_prose_cannot_be_changed_through_the_contact_form(): void
    {
        $business = Business::factory()->create([
            'description' => 'Cerita asli',
            'vision' => 'Visi asli',
            'mission' => ['Misi asli'],
            'services' => [['title' => 'Layanan asli', 'description' => 'Keterangan']],
            'highlights' => [['title' => 'Keunggulan asli', 'description' => 'Keterangan']],
        ]);
        $admin = User::factory()->forBusiness($business)->create();

        $this->actingAs($admin)
            ->put(route('panel.profile.update'), $this->payload([
                'description' => 'Disusupkan',
                'vision' => 'Disusupkan',
                'mission' => ['Disusupkan'],
                'services' => [['title' => 'Disusupkan', 'description' => 'Disusupkan']],
                'highlights' => [['title' => 'Disusupkan', 'description' => 'Disusupkan']],
            ]));

        $business->refresh();

        $this->assertSame('Cerita asli', $business->description);
        $this->assertSame('Visi asli', $business->vision);
        $this->assertSame(['Misi asli'], $business->mission);
        $this->assertSame('Layanan asli', $business->services[0]['title']);
        $this->assertSame('Keunggulan asli', $business->highlights[0]['title']);
    }

    public function test_the_form_carries_the_new_fields_to_the_screen(): void
    {
        $business = Business::factory()->create([
            'whatsapp_alt' => '628987654321',
            'contact_note' => 'Pemesanan lewat DM.',
        ]);
        $admin = User::factory()->forBusiness($business)->create();

        $this->actingAs($admin)
            ->get(route('panel.profile.edit'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('business.whatsapp_alt', '628987654321')
                ->where('business.contact_note', 'Pemesanan lewat DM.')
            );
    }
}
