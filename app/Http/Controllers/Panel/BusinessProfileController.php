<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Panel\Concerns\ResolvesActiveBusiness;
use App\Http\Requests\Panel\BusinessProfileRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BusinessProfileController extends Controller
{
    use ResolvesActiveBusiness;

    public function edit(Request $request): Response
    {
        $business = $this->activeBusiness($request);

        return Inertia::render('panel/profile', [
            'business' => [
                'slug' => $business->slug,
                'name' => $business->name,
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

        $business->update($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Info kontak diperbarui.']);

        return back();
    }
}
