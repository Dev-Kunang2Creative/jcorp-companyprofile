<?php

namespace Tests\Feature\Panel;

use App\Models\Business;
use App\Models\CatalogItem;
use App\Models\PortfolioItem;
use App\Models\User;
use App\Services\ImageService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Unggah gambar (spec §5 dan §11).
 *
 * Yang dijaga: berkas diperiksa dari ISInya bukan namanya, ada batas ukuran,
 * dan nama berkas dibuat ulang sistem.
 */
class ImageUploadTest extends TestCase
{
    use RefreshDatabase;

    private Business $business;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        // Disk palsu: berkas uji tidak menyentuh storage sungguhan.
        Storage::fake('public');

        // withPortfolio() karena sebagian test menguji unggahan foto
        // portfolio — yang sakelarnya mati mengembalikan 404.
        $this->business = Business::factory()
            ->withPortfolio()
            ->create(['slug' => 'sweetness-things']);

        $this->admin = User::factory()->forBusiness($this->business)->create();
    }

    // ------------------------------------------------------------ validasi

    /**
     * Berkas sungguhan di disk, bukan UploadedFile::fake().
     *
     * `UploadedFile::fake()->createWithContent()` MEMALSUKAN MIME type-nya
     * menjadi image/jpeg alih-alih membiarkannya dideteksi dari isi berkas.
     * Akibatnya berkas sampah lolos validasi di test padahal ditolak di
     * kenyataan — persis kebalikan dari yang mau dibuktikan di sini.
     */
    private function realFile(string $name, string $contents): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'uji');
        file_put_contents($path, $contents);

        return new UploadedFile($path, $name, null, null, true);
    }

    public function test_a_non_image_file_named_jpg_is_rejected(): void
    {
        // Inti ujiannya: berkas apa pun bisa diberi nama .jpg. Yang memeriksa
        // harus isinya, bukan akhiran namanya.
        $this->actingAs($this->admin)
            ->post(route('panel.catalog.store'), [
                'name' => 'Item Uji',
                'is_available' => true,
                'sort_order' => 0,
                'image' => $this->realFile('jahat.jpg', 'ini cuma teks biasa, sama sekali bukan gambar'),
            ])
            ->assertSessionHasErrors('image');

        $this->assertDatabaseMissing('catalog_items', ['name' => 'Item Uji']);
    }

    public function test_an_svg_is_rejected(): void
    {
        // SVG bisa memuat skrip. Rule::imageFile() menolaknya secara bawaan.
        $this->actingAs($this->admin)
            ->post(route('panel.catalog.store'), [
                'name' => 'Item SVG',
                'is_available' => true,
                'sort_order' => 0,
                'image' => $this->realFile(
                    'gambar.svg',
                    '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>',
                ),
            ])
            ->assertSessionHasErrors('image');

        $this->assertDatabaseMissing('catalog_items', ['name' => 'Item SVG']);
    }

    public function test_a_php_file_disguised_as_an_image_is_rejected(): void
    {
        $this->actingAs($this->admin)
            ->post(route('panel.catalog.store'), [
                'name' => 'Item PHP',
                'is_available' => true,
                'sort_order' => 0,
                'image' => $this->realFile('shell.png', '<?php echo shell_exec($_GET["c"]); ?>'),
            ])
            ->assertSessionHasErrors('image');

        $this->assertDatabaseMissing('catalog_items', ['name' => 'Item PHP']);
    }

    public function test_a_file_over_the_size_limit_is_rejected(): void
    {
        $tooBig = UploadedFile::fake()
            ->image('besar.jpg')
            ->size(ImageService::MAX_UPLOAD_KB + 1);

        $response = $this->actingAs($this->admin)
            ->post(route('panel.catalog.store'), [
                'name' => 'Item Besar',
                'is_available' => true,
                'sort_order' => 0,
                'image' => $tooBig,
            ]);

        $response->assertSessionHasErrors('image');

        // Pesannya harus menyebut batas yang berlaku, bukan sekadar "tidak
        // valid" (spec §10).
        $errors = session('errors')->get('image');
        $this->assertStringContainsString('4 MB', $errors[0]);
    }

    public function test_the_image_is_optional_for_a_catalog_item(): void
    {
        // Item tanpa foto tetap boleh disimpan — halaman publik menampilkan
        // kotak inisial sebagai gantinya.
        $this->actingAs($this->admin)
            ->post(route('panel.catalog.store'), [
                'name' => 'Tanpa Foto',
                'is_available' => true,
                'sort_order' => 0,
            ])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('catalog_items', [
            'name' => 'Tanpa Foto',
            'image_path' => null,
        ]);
    }

    public function test_a_portfolio_photo_is_mandatory(): void
    {
        // Berbeda dari katalog: portfolio adalah kumpulan foto, jadi tanpa
        // foto barisnya tidak ada gunanya.
        $this->actingAs($this->admin)
            ->post(route('panel.portfolio.store'), [
                'caption' => 'Tanpa foto',
                'sort_order' => 0,
            ])
            ->assertSessionHasErrors('image');
    }

    // ------------------------------------------------------- penyimpanan

    public function test_an_uploaded_image_is_stored_as_webp_with_a_thumbnail(): void
    {
        $this->actingAs($this->admin)
            ->post(route('panel.catalog.store'), [
                'name' => 'Item Berfoto',
                'is_available' => true,
                'sort_order' => 0,
                'image' => UploadedFile::fake()->image('foto.jpg', 1600, 1200),
            ])
            ->assertSessionHasNoErrors();

        $item = CatalogItem::where('name', 'Item Berfoto')->firstOrFail();

        $this->assertNotNull($item->image_path);
        $this->assertStringEndsWith('.webp', $item->image_path);

        $disk = Storage::disk('public');
        $disk->assertExists($item->image_path);
        $disk->assertExists(app(ImageService::class)->thumbnailPath($item->image_path));
    }

    public function test_the_original_filename_is_never_used(): void
    {
        // Nama asli bisa memuat karakter jalur ("../"), karakter bermasalah di
        // sistem berkas, atau akhiran ganda yang menyesatkan.
        $this->actingAs($this->admin)
            ->post(route('panel.catalog.store'), [
                'name' => 'Item Nama Aneh',
                'is_available' => true,
                'sort_order' => 0,
                'image' => UploadedFile::fake()->image('RAHASIA foto asli (1).jpg'),
            ])
            ->assertSessionHasNoErrors();

        $item = CatalogItem::where('name', 'Item Nama Aneh')->firstOrFail();

        $this->assertStringNotContainsString('RAHASIA', $item->image_path);
        $this->assertStringNotContainsString('asli', $item->image_path);
        $this->assertStringNotContainsString(' ', $item->image_path);
    }

    public function test_the_image_is_stored_under_the_business_folder(): void
    {
        $this->actingAs($this->admin)
            ->post(route('panel.catalog.store'), [
                'name' => 'Item Folder',
                'is_available' => true,
                'sort_order' => 0,
                'image' => UploadedFile::fake()->image('foto.jpg'),
            ]);

        $item = CatalogItem::where('name', 'Item Folder')->firstOrFail();

        $this->assertStringStartsWith('sweetness-things/', $item->image_path);
    }

    // --------------------------------------------------- ganti & hapus

    public function test_replacing_an_image_deletes_the_old_files(): void
    {
        // Tanpa penghapusan ini, folder penyimpanan membengkak oleh berkas
        // yatim yang tidak lagi dirujuk baris mana pun.
        $item = CatalogItem::factory()->for($this->business)->create();

        $this->actingAs($this->admin)->put(route('panel.catalog.update', $item), [
            'name' => $item->name,
            'is_available' => true,
            'sort_order' => 0,
            'image' => UploadedFile::fake()->image('pertama.jpg'),
        ]);

        $firstPath = $item->refresh()->image_path;

        $this->actingAs($this->admin)->put(route('panel.catalog.update', $item), [
            'name' => $item->name,
            'is_available' => true,
            'sort_order' => 0,
            'image' => UploadedFile::fake()->image('kedua.jpg'),
        ]);

        $secondPath = $item->refresh()->image_path;

        $this->assertNotSame($firstPath, $secondPath);

        $disk = Storage::disk('public');
        $images = app(ImageService::class);

        $disk->assertMissing($firstPath);
        $disk->assertMissing($images->thumbnailPath($firstPath));
        $disk->assertExists($secondPath);
    }

    public function test_updating_without_a_new_file_keeps_the_existing_image(): void
    {
        $item = CatalogItem::factory()->for($this->business)->create();

        $this->actingAs($this->admin)->put(route('panel.catalog.update', $item), [
            'name' => $item->name,
            'is_available' => true,
            'sort_order' => 0,
            'image' => UploadedFile::fake()->image('foto.jpg'),
        ]);

        $path = $item->refresh()->image_path;

        // Ubah teksnya saja, tanpa mengirim berkas.
        $this->actingAs($this->admin)->put(route('panel.catalog.update', $item), [
            'name' => 'Nama Baru',
            'is_available' => true,
            'sort_order' => 0,
        ])->assertSessionHasNoErrors();

        $item->refresh();

        $this->assertSame('Nama Baru', $item->name);
        $this->assertSame($path, $item->image_path);
        Storage::disk('public')->assertExists($path);
    }

    public function test_soft_deleting_an_item_keeps_its_image_file(): void
    {
        // Spec §4: data yang ditandai terhapus masih bisa dipulihkan, dan foto
        // produk yang berkasnya sudah dimusnahkan tidak bisa dikembalikan.
        $item = CatalogItem::factory()->for($this->business)->create();

        $this->actingAs($this->admin)->put(route('panel.catalog.update', $item), [
            'name' => $item->name,
            'is_available' => true,
            'sort_order' => 0,
            'image' => UploadedFile::fake()->image('foto.jpg'),
        ]);

        $path = $item->refresh()->image_path;

        $this->actingAs($this->admin)->delete(route('panel.catalog.destroy', $item));

        $this->assertSoftDeleted($item);
        Storage::disk('public')->assertExists($path);
    }

    public function test_image_path_cannot_be_set_directly_from_the_request(): void
    {
        // Kalau field ini ikut terisi dari input, jalur gambar bisa disetel
        // sembarangan — termasuk menunjuk berkas milik anak usaha lain.
        $this->actingAs($this->admin)
            ->post(route('panel.catalog.store'), [
                'name' => 'Item Selundupan',
                'is_available' => true,
                'sort_order' => 0,
                'image_path' => 'anak-usaha-lain/rahasia.webp',
            ])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('catalog_items', [
            'name' => 'Item Selundupan',
            'image_path' => null,
        ]);
    }

    public function test_an_update_sent_as_post_with_method_override_still_carries_the_file(): void
    {
        // Inilah jalur yang benar-benar dipakai browser. PHP TIDAK mengurai
        // multipart/form-data pada request PUT, jadi form ubah mengirim POST
        // dengan _method=put. Test lain memakai $this->put() milik Laravel,
        // yang mem-bypass PHP dan menyuntikkan berkas langsung — jadi lulus
        // di sana tidak membuktikan jalur browser bekerja.
        $item = CatalogItem::factory()->for($this->business)->create();

        $this->actingAs($this->admin)
            ->post(route('panel.catalog.update', $item), [
                '_method' => 'put',
                'name' => 'Lewat Method Override',
                'is_available' => true,
                'sort_order' => 0,
                'image' => UploadedFile::fake()->image('foto.jpg'),
            ])
            ->assertSessionHasNoErrors();

        $item->refresh();

        $this->assertSame('Lewat Method Override', $item->name);
        $this->assertNotNull($item->image_path);
        Storage::disk('public')->assertExists($item->image_path);
    }

    public function test_a_portfolio_photo_is_processed_the_same_way(): void
    {
        $this->actingAs($this->admin)
            ->post(route('panel.portfolio.store'), [
                'caption' => 'Hasil kerja',
                'sort_order' => 0,
                'image' => UploadedFile::fake()->image('kerja.png', 2000, 2000),
            ])
            ->assertSessionHasNoErrors();

        $item = PortfolioItem::where('caption', 'Hasil kerja')->firstOrFail();

        $this->assertStringEndsWith('.webp', $item->image_path);
        Storage::disk('public')->assertExists($item->image_path);
    }
    // -------------------------------------------------------- hapus foto

    /**
     * Admin bisa MENGHAPUS foto tanpa menghapus itemnya.
     *
     * Sebelum 23 Agustus 2026 tidak ada caranya sama sekali: satu-satunya
     * jalan adalah menghapus itemnya lalu membuatnya lagi dari nol —
     * beserta harga, keterangan, dan urutannya.
     *
     * Item katalog memang boleh tanpa foto; kartunya menampilkan kotak
     * inisial (spec §10).
     */
    public function test_an_admin_can_remove_a_photo_without_deleting_the_item(): void
    {
        $item = CatalogItem::factory()->for($this->business)->create();

        $this->actingAs($this->admin)->put(route('panel.catalog.update', $item), [
            'name' => $item->name,
            'is_available' => true,
            'sort_order' => 0,
            'image' => UploadedFile::fake()->image('foto.jpg'),
        ]);

        $path = $item->refresh()->image_path;
        $this->assertNotNull($path);

        $this->actingAs($this->admin)
            ->put(route('panel.catalog.update', $item), [
                'name' => $item->name,
                'is_available' => true,
                'sort_order' => 0,
                'remove_image' => '1',
            ])
            ->assertSessionHasNoErrors();

        // Itemnya masih ada, fotonya yang hilang.
        $this->assertNotNull($item->fresh());
        $this->assertNull($item->refresh()->image_path);
    }

    public function test_removing_a_photo_deletes_the_files_from_disk(): void
    {
        // Berkas yatim yang tidak lagi dirujuk baris mana pun hanya
        // memenuhi disk. Berbeda dari destroy(), yang MENYIMPAN berkasnya
        // karena itemnya cuma di-soft-delete dan masih bisa dipulihkan.
        $item = CatalogItem::factory()->for($this->business)->create();

        $this->actingAs($this->admin)->put(route('panel.catalog.update', $item), [
            'name' => $item->name,
            'is_available' => true,
            'sort_order' => 0,
            'image' => UploadedFile::fake()->image('foto.jpg'),
        ]);

        $path = $item->refresh()->image_path;
        $thumb = app(ImageService::class)->thumbnailPath($path);

        Storage::disk('public')->assertExists($path);
        Storage::disk('public')->assertExists($thumb);

        $this->actingAs($this->admin)->put(route('panel.catalog.update', $item), [
            'name' => $item->name,
            'is_available' => true,
            'sort_order' => 0,
            'remove_image' => '1',
        ]);

        Storage::disk('public')->assertMissing($path);
        Storage::disk('public')->assertMissing($thumb);
    }

    /**
     * PENJAGA TERPENTING dari fitur ini.
     *
     * Form tanpa berkas berarti admin cuma mengubah harga atau keterangan
     * — dan itu yang PALING SERING terjadi. Kalau perubahan biasa ikut
     * menghapus foto, admin kehilangan berkasnya tanpa pernah memintanya.
     */
    public function test_an_ordinary_edit_never_touches_the_photo(): void
    {
        $item = CatalogItem::factory()->for($this->business)->create();

        $this->actingAs($this->admin)->put(route('panel.catalog.update', $item), [
            'name' => $item->name,
            'is_available' => true,
            'sort_order' => 0,
            'image' => UploadedFile::fake()->image('foto.jpg'),
        ]);

        $path = $item->refresh()->image_path;

        // Mengubah harga saja — tanpa berkas, tanpa remove_image.
        $this->actingAs($this->admin)->put(route('panel.catalog.update', $item), [
            'name' => 'Nama Baru',
            'price' => 99000,
            'is_available' => true,
            'sort_order' => 0,
        ]);

        $this->assertSame($path, $item->refresh()->image_path);
        Storage::disk('public')->assertExists($path);
        $this->assertSame('Nama Baru', $item->name);
    }

    public function test_uploading_a_new_photo_wins_over_the_remove_flag(): void
    {
        // Kalau keduanya terkirim — misalnya penanda tertinggal dari
        // interaksi sebelumnya — yang dimaksud admin jelas MENGGANTI,
        // bukan mengosongkan.
        $item = CatalogItem::factory()->for($this->business)->create();

        $this->actingAs($this->admin)->put(route('panel.catalog.update', $item), [
            'name' => $item->name,
            'is_available' => true,
            'sort_order' => 0,
            'image' => UploadedFile::fake()->image('baru.jpg'),
            'remove_image' => '1',
        ]);

        $this->assertNotNull($item->refresh()->image_path);
    }

    public function test_removing_a_photo_that_does_not_exist_is_harmless(): void
    {
        $item = CatalogItem::factory()->for($this->business)->create([
            'image_path' => null,
        ]);

        $this->actingAs($this->admin)
            ->put(route('panel.catalog.update', $item), [
                'name' => $item->name,
                'is_available' => true,
                'sort_order' => 0,
                'remove_image' => '1',
            ])
            ->assertSessionHasNoErrors();

        $this->assertNull($item->refresh()->image_path);
    }
}
