<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Panel\Concerns\ResolvesActiveBusiness;
use App\Http\Requests\Panel\CatalogItemRequest;
use App\Models\CatalogItem;
use App\Services\ImageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CatalogItemController extends Controller
{
    use ResolvesActiveBusiness;

    public function __construct(private readonly ImageService $images) {}

    public function index(Request $request): Response
    {
        $business = $this->activeBusiness($request);

        return Inertia::render('panel/catalog/index', [
            'business' => [
                'slug' => $business->slug,
                'name' => $business->name,
                'catalog_label' => $business->catalog_label,
            ],
            // Disaring lewat relasi, jadi item milik anak usaha lain tidak
            // pernah ikut terambil — bukan diambil semua lalu difilter di React.
            'items' => $business->catalogItems()
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get()
                ->map(fn (CatalogItem $item) => [
                    'id' => $item->id,
                    'name' => $item->name,
                    'description' => $item->description,
                    'price' => $item->price,
                    'price_note' => $item->price_note,
                    'formatted_price' => $item->formattedPrice(),
                    'category' => $item->category,
                    'is_available' => $item->is_available,
                    'sort_order' => $item->sort_order,
                    'image_url' => $this->images->url($item->image_path),
                    'thumb_url' => $item->image_path
                        ? $this->images->url($this->images->thumbnailPath($item->image_path))
                        : null,
                ]),
        ]);
    }

    public function store(CatalogItemRequest $request): RedirectResponse
    {
        $business = $this->activeBusiness($request);

        $this->authorize('create', [CatalogItem::class, $business]);

        $item = $business->catalogItems()->create($request->safe()->except('image'));

        if ($request->hasFile('image')) {
            $item->update([
                'image_path' => $this->images->store($request->file('image'), $business->slug),
            ]);
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Item katalog ditambahkan.']);

        return back();
    }

    public function update(CatalogItemRequest $request, CatalogItem $catalogItem): RedirectResponse
    {
        // Inti spec §6: item diambil langsung dari ID di alamat, lalu
        // kepemilikannya diperiksa di sini. Mengganti angka di URL dengan ID
        // milik anak usaha lain berakhir 403, bukan berhasil.
        $this->authorize('update', $catalogItem);

        $attributes = $request->safe()->except(['image', 'remove_image']);

        // Tiga kemungkinan, dan ketiganya harus dibedakan:
        //
        //   ada berkas          -> ganti foto, yang lama dihapus
        //   remove_image = 1    -> kosongkan foto
        //   tidak keduanya      -> pertahankan foto yang ada
        //
        // Yang ketiga itu yang paling sering: admin cuma mengubah harga
        // atau keterangan, dan fotonya tidak boleh ikut hilang.
        if ($request->hasFile('image')) {
            $attributes['image_path'] = $this->images->replace(
                $request->file('image'),
                $catalogItem->business->slug,
                $catalogItem->image_path,
            );
        } elseif ($request->boolean('remove_image')) {
            // Berkasnya benar-benar dihapus dari disk, bukan sekadar
            // dilepas dari database. Item katalog memang bisa hidup tanpa
            // foto — kartunya menampilkan kotak inisial (spec §10) — jadi
            // menyimpan berkas yatim hanya memenuhi disk.
            //
            // Berbeda dari destroy(), yang MENYIMPAN berkasnya karena
            // itemnya cuma di-soft-delete dan masih bisa dipulihkan.
            $this->images->delete($catalogItem->image_path);

            $attributes['image_path'] = null;
        }

        $catalogItem->update($attributes);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Item katalog diperbarui.']);

        return back();
    }

    public function destroy(CatalogItem $catalogItem): RedirectResponse
    {
        $this->authorize('delete', $catalogItem);

        // Soft delete: berkas gambarnya sengaja TIDAK dihapus. Datanya masih
        // bisa dipulihkan lewat database, dan foto produk yang berkasnya sudah
        // dimusnahkan tidak bisa dikembalikan (spec §4).
        $catalogItem->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Item katalog dihapus.']);

        return back();
    }
}
