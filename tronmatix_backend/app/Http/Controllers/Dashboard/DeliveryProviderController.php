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
        $providers = DeliveryProvider::orderBy('sort_order')->get();
        $zones = DeliveryZone::all();
        return view('dashboard.delivery-providers.index', compact('providers', 'zones'));
    }

    public function create()
    {
        $zones = DeliveryZone::all();
        return view('dashboard.delivery-providers.create', compact('zones'));
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
        DeliveryProvider::create($validated);
        return redirect()->route('dashboard.delivery-providers.index')->with('success', 'Delivery provider created.');
    }

    public function edit(DeliveryProvider $deliveryProvider)
    {
        $zones = DeliveryZone::all();
        return view('dashboard.delivery-providers.edit', compact('deliveryProvider', 'zones'));
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
        $deliveryProvider->update($validated);
        return redirect()->route('dashboard.delivery-providers.index')->with('success', 'Delivery provider updated.');
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
            'delivery_zone_id' => 'required|exists:delivery_zones,id',
            'name'             => 'required|string|max:255',
            'fee'              => 'nullable|numeric|min:0|max:99999999.99',
            'estimated_time'   => 'nullable|string|max:100',
            'sort_order'       => 'nullable|integer|min:0',
            'is_active'        => 'nullable',
            'logo_file'        => 'nullable|file|max:51200|mimes:jpg,jpeg,png,webp,gif',
            'logo_url'         => 'nullable|string|max:500',
            'remove_logo'      => 'nullable|boolean',
        ]);
        unset($validated['logo_file'], $validated['remove_logo'], $validated['logo_url']);
        return $validated;
    }
}
