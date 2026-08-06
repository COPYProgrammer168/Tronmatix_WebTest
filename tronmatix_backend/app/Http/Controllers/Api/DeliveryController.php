<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DeliveryProvider;
use App\Models\DeliveryProviderZone;
use App\Models\Province;
use App\Services\DeliveryFeeCalculator;
use Illuminate\Http\Request;

class DeliveryController extends Controller
{
    public function provinces()
    {
        $provinces = Province::select(['id', 'name_en', 'name_kh', 'delivery_zone_id'])
            ->orderBy('name_en')->get()
            ->map(function (Province $p) {
                return ['id' => $p->id, 'name_en' => $p->name_en, 'name_kh' => $p->name_kh, 'delivery_zone_id' => $p->delivery_zone_id];
            });
        return response()->json(['success' => true, 'data' => $provinces]);
    }

    public function deliveryProviders(Request $request)
    {
        $zoneId = $request->query('zone_id');
        if (!$zoneId) {
            return response()->json(['success' => false, 'message' => 'zone_id is required.'], 422);
        }

        // Map legacy zone_id → zone slug (5 = phnom_penh, 6 = province)
        $zoneSlug = $zoneId == 5 ? 'phnom_penh' : 'province';

        // Return providers that have a matching per-zone row for the requested
        // zone. This is the source of truth — the legacy flat delivery_zone_id
        // is no longer the filter because a provider can serve multiple zones.
        $providerIds = DeliveryProviderZone::where('zone', $zoneSlug)
            ->pluck('delivery_provider_id');

        $providers = DeliveryProvider::active()
            ->whereIn('id', $providerIds)
            ->get(['id', 'name', 'logo', 'fee', 'estimated_time'])
            ->map(function (DeliveryProvider $dp) use ($zoneSlug) {
                // Resolve fee/ETA from per-zone child table first; fall back to flat.
                $zd = DeliveryProviderZone::where('delivery_provider_id', $dp->id)
                    ->where('zone', $zoneSlug)
                    ->first();

                return [
                    'id'             => $dp->id,
                    'name'           => $dp->name,
                    'logo'           => $dp->logo,
                    'fee'            => $zd?->fee ?? $dp->fee,
                    'estimated_time' => $zd?->estimated_time ?? $dp->estimated_time,
                ];
            });

        return response()->json(['success' => true, 'data' => $providers]);
    }

    /**
     * POST /api/delivery/calculate-fee
     * Distance-based delivery fee from the customer's picked { lat, lng }.
     */
    public function calculateFee(Request $request, DeliveryFeeCalculator $calculator)
    {
        $validated = $request->validate([
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
        ]);

        $result = $calculator->calculate(
            (float) $validated['lat'],
            (float) $validated['lng']
        );

        return response()->json(['success' => true, 'data' => $result]);
    }
}
