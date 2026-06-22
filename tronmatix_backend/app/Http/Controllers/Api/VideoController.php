<?php

// app/Http/Controllers/Api/VideoController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Video;

class VideoController extends Controller
{
    /**
     * GET /api/videos
     * Returns active videos ordered by `order`, separate from banners.
     */
    public function index()
    {
        $videos = Video::active()->get([
            'id',
            'title',
            'description',
            'video_type',
            'video',
            'thumbnail',
            'product_id',
            'order',
        ])->map(function (Video $v) {
            return [
                'id' => $v->id,
                'title' => $v->title,
                'description' => $v->description,
                'video_type' => $v->video_type,
                'video' => $v->video_url,     // accessor resolves upload path or returns embed URL
                'thumbnail' => $v->thumbnail_url,
                'product_id' => $v->product_id,
                'order' => $v->order,
            ];
        });

        return response()->json(['success' => true, 'data' => $videos]);
    }
}