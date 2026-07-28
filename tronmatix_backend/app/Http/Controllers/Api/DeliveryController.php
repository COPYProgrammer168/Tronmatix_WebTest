<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DeliveryProvider;
use App\Models\Province;
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

    public function deliveryProviders()
    {
        $zoneId = request()->query('zone_id');
        if (!$zoneId) {
            return response()->json(['success' => false, 'message' => 'zone_id is required.'], 422);
        }
        $providers = DeliveryProvider::active()
            ->where('delivery_zone_id', $zoneId)
            ->get(['id', 'name', 'logo', 'fee', 'estimated_time'])
            ->map(function (DeliveryProvider $dp) {
                return ['id' => $dp->id, 'name' => $dp->name, 'logo' => $dp->logo, 'fee' => $dp->fee, 'estimated_time' => $dp->estimated_time];
            });
        return response()->json(['success' => true, 'data' => $providers]);
    }
}
