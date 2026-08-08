<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MarqueeMessage;
use Illuminate\Http\Request;

class MarqueeController extends Controller
{
    /**
     * GET /api/marquees
     * Returns the active marquee message for the current route.
     * Falls back to general (route = null) messages when no route-specific one exists.
     */
    public function index(Request $request)
    {
        $route = $request->query('route') ?: $request->path();

        $message = MarqueeMessage::active()
            ->where(function ($q) use ($route) {
                $q->where('route', $route)
                  ->orWhere('route', null);
            })
            ->orderByDesc('order')
            ->first();

        if (! $message) {
            return response()->json(['success' => true, 'data' => null]);
        }

        return response()->json([
            'success' => true,
            'data'    => [
                'id'      => $message->id,
                'route'   => $message->route,
                'text_en' => $message->text_en,
                'text_kh' => $message->text_kh,
            ],
        ]);
    }
}
