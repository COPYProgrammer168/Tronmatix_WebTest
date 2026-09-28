<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\DeliveryProvider;
use App\Models\DeliveryZone;
use App\Services\ImageStorageService;
use Illuminate\Http\Request;

class DeliveryProviderController extends Controller
{
    public function __construct(private readonly ImageStorageService $storage) {}

    public function index()
    {
        $providers = DeliveryProvider::with(['deliveryZone', 'zones'])->orderBy('sort_order')->get();
        return view('dashboard.delivery-providers.index', compact('providers'));
    }

    public function create()
    {
        return view('dashboard.delivery-providers.create');
    }

    public function store(Request $request)
    {
        $f = $request->files->get('logo_file');
        if ($f && $f->getError() === UPLOAD_ERR_INI_SIZE) {
            return back()->withErrors(['logo_file' => 'File too large (exceeds server PHP limit).'])->withInput();
        }
        $validated = $this->validateProvider($request);
        $validated['logo']      = $this->resolveLogo($request, null);
        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['sort_order']= (int) $request->input('sort_order', 0);
        // Legacy flat fields — left null; per-zone fees live in delivery_provider_zones.
        $validated['delivery_zone_id'] = null;
        $validated['fee']              = null;
        $validated['estimated_time']   = null;

        $provider = DeliveryProvider::create($validated);
        $this->syncZoneRows($provider, $request);

        return redirect()->route('dashboard.delivery-providers.index')->with('success', 'Delivery provider created.');
    }

    public function edit(DeliveryProvider $deliveryProvider)
    {
        $deliveryProvider->load(['deliveryZone', 'zones']);
        return view('dashboard.delivery-providers.edit', compact('deliveryProvider'));
    }

    public function update(Request $request, DeliveryProvider $deliveryProvider)
    {
        $f = $request->files->get('logo_file');
        if ($f && $f->getError() === UPLOAD_ERR_INI_SIZE) {
            return back()->withErrors(['logo_file' => 'File too large (exceeds server PHP limit).'])->withInput();
        }
        $validated = $this->validateProvider($request, $deliveryProvider);
        if ($request->boolean('remove_logo')) {
            $this->storage->delete($deliveryProvider->logo);
            $validated['logo'] = null;
        } elseif ($request->hasFile('logo_file') && $request->file('logo_file')->isValid()) {
            $this->storage->delete($deliveryProvider->logo);
            $validated['logo'] = $this->storage->store($request->file('logo_file'), 'delivery-providers');
        } elseif ($request->filled('logo_url')) {
            $this->storage->delete($deliveryProvider->logo);
            $validated['logo'] = $request->input('logo_url');
        } else {
            $validated['logo'] = $deliveryProvider->logo;
        }
        $validated['is_active'] = $request->boolean('is_active', $deliveryProvider->is_active);
        $validated['sort_order']= (int) $request->input('sort_order', $deliveryProvider->sort_order);
        // Leave flat fee/estimated_time untouched (legacy).
        unset($validated['delivery_zone_id'], $validated['fee'], $validated['estimated_time']);
        $deliveryProvider->update($validated);

        $this->syncZoneRows($deliveryProvider, $request);

        return redirect()->route('dashboard.delivery-providers.index')->with('success', 'Delivery provider updated.');
    }

    // ── Helper: sync per-zone fee/time rows from the form ───────────────────
    private function syncZoneRows(DeliveryProvider $provider, Request $request): void
    {
        foreach (['phnom_penh', 'province'] as $zone) {
            $enabled = $request->boolean("zone_{$zone}_enabled");
            $provider->zones()->where('zone', $zone)->delete();

            if ($enabled) {
                $provider->zones()->create([
                    'zone'           => $zone,
                    'fee'            => $request->filled("zone_{$zone}_fee")
                        ? (float) $request->input("zone_{$zone}_fee")
                        : null,
                    'estimated_time' => $request->filled("zone_{$zone}_time")
                        ? $request->input("zone_{$zone}_time")
                        : null,
                ]);
            }
        }
    }

    public function toggleStatus(DeliveryProvider $deliveryProvider)
    {
        $deliveryProvider->update(['is_active' => !$deliveryProvider->is_active]);
        return redirect()->route('dashboard.delivery-providers.index')->with('success', 'Provider ' . ($deliveryProvider->is_active ? 'deactivated' : 'activated') . '.');
    }

    public function destroy(DeliveryProvider $deliveryProvider)
    {
        $this->storage->delete($deliveryProvider->logo);
        $deliveryProvider->delete();
        return redirect()->route('dashboard.delivery-providers.index')->with('success', 'Delivery provider deleted.');
    }

    private function resolveLogo(Request $request, ?string $current): ?string
    {
        if ($request->hasFile('logo_file') && $request->file('logo_file')->isValid()) {
            return $this->storage->store($request->file('logo_file'), 'delivery-providers');
        }
        if ($request->filled('logo_url')) {
            return $request->input('logo_url');
        }
        return $current;
    }

    private function validateProvider(Request $request, ?DeliveryProvider $provider = null): array
    {
        $validated = $request->validate([
            'name'                    => 'required|string|max:255',
            'sort_order'              => 'nullable|integer|min:0',
            'is_active'               => 'nullable',
            'logo_file'               => 'nullable|file|max:51200|mimes:jpg,jpeg,png,webp,gif',
            'logo_url'                => 'nullable|string|max:500',
            'remove_logo'             => 'nullable|boolean',
            // Per-zone fields
            'zone_phnom_penh_enabled' => 'nullable|boolean',
            'zone_phnom_penh_fee'     => 'nullable|numeric|min:0|max:99999999.99',
            'zone_phnom_penh_time'    => 'nullable|string|max:100',
            'zone_province_enabled'   => 'nullable|boolean',
            'zone_province_fee'       => 'nullable|numeric|min:0|max:99999999.99',
            'zone_province_time'      => 'nullable|string|max:100',
        ]);
        unset($validated['logo_file'], $validated['remove_logo'], $validated['logo_url']);
        return $validated;
    }
}
