<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Panel\Concerns\ResolvesActiveBusiness;
use App\Http\Requests\Panel\BusinessProfileRequest;
use App\Services\ImageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BusinessProfileController extends Controller
{
    use ResolvesActiveBusiness;

    public function __construct(private readonly ImageService $images) {}

    public function edit(Request $request): Response
    {
        $business = $this->activeBusiness($request);

        return Inertia::render('panel/profile', [
            'business' => [
                'slug' => $business->slug,
                'name' => $business->name,
                'cover_image_url' => $this->images->url($business->cover_image_path),
                'whatsapp' => $business->whatsapp,
                'whatsapp_alt' => $business->whatsapp_alt,
                'instagram' => $business->instagram,
                'tiktok' => $business->tiktok,
                'address' => $business->address,
                'business_hours' => $business->business_hours,
                'contact_note' => $business->contact_note,
                'catalog_label' => $business->catalog_label,
                'catalog_note' => $business->catalog_note,
                'portfolio_label' => $business->portfolio_label,
            ],
        ]);
    }

    public function update(BusinessProfileRequest $request): RedirectResponse
    {
        $business = $this->activeBusiness($request);

        $this->authorize('update', $business);

        $attributes = $request->safe()->except([
            'cover_image',
            'remove_cover_image',
        ]);

        if ($request->hasFile('cover_image')) {
            $attributes['cover_image_path'] = $this->images->replace(
                $request->file('cover_image'),
                "{$business->slug}/profile",
                $business->cover_image_path,
            );
        } elseif ($request->boolean('remove_cover_image')) {
            $this->images->delete($business->cover_image_path);
            $attributes['cover_image_path'] = null;
        }

        $business->update($attributes);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Profil dan kontak diperbarui.']);

        return back();
    }
}
