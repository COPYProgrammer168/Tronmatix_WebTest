<?php

// app/Http/Controllers/Api/BannerController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Banner;

class BannerController extends Controller
{
    /**
     * GET /api/banners
     * Returns active banners ordered by `order` column (global scope).
     * Includes video fields so the frontend can render video banners.
     */
    public function index()
    {
        $banners = Banner::active()->get([
            'id', 'title', 'subtitle', 'badge',
            'bg_color', 'text_color',
            'image',
            'video', 'video_type',   // ← video support
            'order',
        ])->map(function (Banner $b) {
            return [
                'id' => $b->id,
                'title' => $b->title,
                'subtitle' => $b->subtitle,
                'badge' => $b->badge,
                'bg_color' => $b->bg_color,
                'text_color' => $b->text_color,
                'image' => $b->image,
                'is_gif' => $b->is_gif,
                'video' => $b->video_url,   // uses accessor — resolves path
                'video_type' => $b->video_type,
                'has_video' => $b->has_video,
                'order' => $b->order,
            ];
        });

        return response()->json(['success' => true, 'data' => $banners]);
    }
}
