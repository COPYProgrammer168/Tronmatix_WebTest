<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Video;
use App\Models\Product;
use App\Services\ImageStorageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class VideoController extends Controller
{
    public function __construct(
        private readonly ImageStorageService $storage
    ) {
    }

    public function index()
    {
        $videos = Video::orderBy('order')->get();
        $products = Product::all();

        return view('dashboard.videos', compact('videos', 'products'));
    }

    public function store(Request $request)
    {
        foreach (['video_file', 'thumbnail_file'] as $field) {
            $f = $request->files->get($field);
            if ($f && $f->getError() === UPLOAD_ERR_INI_SIZE) {
                return back()
                    ->withErrors([$field => 'File too large (exceeds server PHP limit).'])
                    ->withInput();
            }
        }

        $validated = $this->validateVideo($request);

        $videoType = $this->resolveVideoType($request);
        $validated['video_type'] = $videoType;
        $validated['video'] = $this->resolveVideoValue($request, $videoType, null);
        $validated['thumbnail'] = $this->handleThumbnail($request, null);

        // Auto-fetch a thumbnail from the platform itself if the admin
        // didn't manually upload one — currently supported for TikTok.
        // YouTube doesn't need this: the frontend builds its thumbnail URL
        // directly from the video ID, no API call required.
        if (!$validated['thumbnail'] && $videoType === 'tiktok') {
            $validated['thumbnail'] = $this->fetchTikTokThumbnail($validated['video']);
        }

        $validated['active'] = $request->boolean('active', true);
        $validated['order'] = (int) $request->input('order', 0);

        Video::create($validated);

        return redirect()->route('dashboard.videos')->with('success', 'Video created.');
    }

    public function update(Request $request, Video $video)
    {
        foreach (['video_file', 'thumbnail_file'] as $field) {
            $f = $request->files->get($field);
            if ($f && $f->getError() === UPLOAD_ERR_INI_SIZE) {
                return back()
                    ->withErrors([$field => 'File too large (exceeds server PHP limit).'])
                    ->withInput();
            }
        }

        $validated = $this->validateVideo($request, $video);

        $videoType = $this->resolveVideoType($request);
        $newValue = $this->resolveVideoValue($request, $videoType, $video->video);

        // If switching source type or value changed, clean up old upload file
        if ($video->video_type === 'upload' && ($videoType !== 'upload' || $newValue !== $video->video)) {
            $this->storage->delete($video->video);
        }

        $validated['video_type'] = $videoType;
        $validated['video'] = $newValue;

        if ($request->boolean('remove_thumbnail')) {
            $this->storage->delete($video->thumbnail);
            $validated['thumbnail'] = null;
        } else {
            $newThumb = $this->handleThumbnail($request, $video->thumbnail);
            if ($newThumb !== $video->thumbnail) {
                $this->storage->delete($video->thumbnail);
                $validated['thumbnail'] = $newThumb;
            }
        }

        // Auto-fetch a TikTok thumbnail if there still isn't one — covers
        // both "never had one" and "source URL just changed to TikTok".
        if (!$validated['thumbnail'] && $videoType === 'tiktok') {
            $validated['thumbnail'] = $this->fetchTikTokThumbnail($validated['video']);
        }

        $validated['active'] = $request->boolean('active', $video->active);
        $validated['order'] = (int) $request->input('order', $video->order);

        $video->update($validated);

        return redirect()->route('dashboard.videos')->with('success', 'Video updated.');
    }

    public function toggle(Video $video)
    {
        $video->update(['active' => !$video->active]);

        return redirect()->route('dashboard.videos')
            ->with('success', 'Video ' . ($video->active ? 'deactivated' : 'activated') . '.');
    }

    public function destroy(Video $video)
    {
        if ($video->video_type === 'upload') {
            $this->storage->delete($video->video);
        }
        $this->storage->delete($video->thumbnail);

        $video->delete();

        return redirect()->route('dashboard.videos')->with('success', 'Video deleted.');
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    /**
     * Which source tab the dashboard form submitted.
     * Falls back to detecting from a pasted URL if not explicitly sent.
     */
    private function resolveVideoType(Request $request): string
    {
        $tab = $request->input('video_source_tab');

        if (in_array($tab, ['upload', 'youtube', 'facebook', 'tiktok'], true)) {
            return $tab;
        }

        // Fallback: detect from URL
        $url = (string) $request->input('video_url');
        if (str_contains($url, 'youtube.com') || str_contains($url, 'youtu.be'))
            return 'youtube';
        if (str_contains($url, 'facebook.com') || str_contains($url, 'fb.watch'))
            return 'facebook';
        if (str_contains($url, 'tiktok.com'))
            return 'tiktok';

        return 'upload';
    }

    /**
     * Returns the value to store in the `video` column:
     * - 'upload': stored file path (existing path kept if no new file uploaded)
     * - embeds: the pasted URL, trimmed
     */
    private function resolveVideoValue(Request $request, string $videoType, ?string $current): ?string
    {
        if ($videoType === 'upload') {
            if ($request->hasFile('video_file') && $request->file('video_file')->isValid()) {
                return $this->storage->store($request->file('video_file'), 'videos');
            }
            return $current;
        }

        return trim((string) $request->input('video_url')) ?: $current;
    }

    private function handleThumbnail(Request $request, ?string $current): ?string
    {
        if (!$request->hasFile('thumbnail_file') || !$request->file('thumbnail_file')->isValid()) {
            return $current;
        }

        return $this->storage->store($request->file('thumbnail_file'), 'videos/thumbnails');
    }

    /**
     * Calls TikTok's public oEmbed endpoint (no API key required) to get
     * the post's own cover image. Returns null silently on any failure —
     * a missing thumbnail just falls back to the frontend's placeholder,
     * it should never block saving the video.
     */
    private function fetchTikTokThumbnail(?string $videoUrl): ?string
    {
        if (!$videoUrl) {
            return null;
        }

        try {
            $response = Http::timeout(5)
                ->get('https://www.tiktok.com/oembed', ['url' => $videoUrl]);

            if (!$response->successful()) {
                return null;
            }

            return $response->json('thumbnail_url');
        } catch (\Throwable $e) {
            report($e);
            return null;
        }
    }

    private function validateVideo(Request $request, ?Video $video = null): array
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'product_id' => 'nullable|exists:products,id',
            'order' => 'nullable|integer|min:0',
            'active' => 'nullable',
            'video_source_tab' => 'nullable|in:upload,youtube,facebook,tiktok',
            'video_file' => 'nullable|file|max:102400|mimes:mp4,webm,ogg',
            'video_url' => [
                'required_if:video_source_tab,youtube,facebook,tiktok',
                'nullable',
                'url',
                'max:500',
                function ($attribute, $value, $fail) {
                    if (!$value)
                        return;
                    if (str_contains($value, 'facebook.com') && str_contains($value, '/reel/')) {
                        $fail('The Facebook URL cannot be a Reel link. Please copy the URL of a standard video post from a desktop browser.');
                    }
                },
            ],
            'thumbnail_file' => 'nullable|image|max:5120',
            'remove_thumbnail' => 'nullable|boolean',
        ]);

        unset(
            $validated['video_source_tab'],
            $validated['video_file'],
            $validated['video_url'],
            $validated['thumbnail_file'],
            $validated['remove_thumbnail'],
        );

        return $validated;
    }
}
