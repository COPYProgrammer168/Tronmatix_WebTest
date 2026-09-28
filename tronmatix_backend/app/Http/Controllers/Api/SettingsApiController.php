<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Feature;
use App\Models\Role;

class SettingsApiController extends Controller
{
    /** GET /api/settings/roles — public-ish; used by PortalGuards.jsx */
    public function roles()
    {
        $roles = Role::orderBy('sort_order')->get([
            'id', 'key', 'label', 'color', 'icon', 'is_staff_portal', 'is_locked',
        ]);

        return response()->json([
            'success' => true,
            'data'    => $roles,
        ]);
    }

    /** GET /api/settings/features — public-ish; used by frontend feature toggles */
    public function features()
    {
        $features = Feature::orderBy('sort_order')->get([
            'id', 'key', 'label', 'icon', 'category',
        ]);

        return response()->json([
            'success' => true,
            'data'    => $features,
        ]);
    }
}
