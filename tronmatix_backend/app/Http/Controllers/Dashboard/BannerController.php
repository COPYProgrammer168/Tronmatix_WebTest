<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Services\ImageStorageService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BannerController extends Controller
{
    public function __construct(
        private readonly ImageStorageService $storage
    ) {}

    public function index()
    {
        $banners = Banner::orderBy('order')->get();

        return view('dashboard.banners', compact('banners'));
    }

    public function store(Request $request)
    {
        // Catch PHP upload size error before Laravel validation runs
        foreach (['image_file', 'video_file'] as $field) {
            $f = $request->files->get($field);
            if ($f && $f->getError() === UPLOAD_ERR_INI_SIZE) {
                return back()
                    ->withErrors([$field => 'File too large (exceeds server PHP limit).'])
                    ->withInput();
            }
        }

        $validated = $this->validateBanner($request);

        $validated['image']      = $this->handleImage($request, null);
        $validated['video']      = $this->handleVideo($request, null);
        $validated['video_type'] = $this->resolveVideoType($request);
        $validated['active']     = $request->boolean('active', true);
        $validated['order']      = (int) $request->input('order', 0);

        Banner::create($validated);

        return redirect()->route('dashboard.banners')->with('success', 'Banner created.');
    }

    public function update(Request $request, Banner $banner)
    {
        foreach (['image_file', 'video_file'] as $field) {
            $f = $request->files->get($field);
            if ($f && $f->getError() === UPLOAD_ERR_INI_SIZE) {
                return back()
                    ->withErrors([$field => 'File too large (exceeds server PHP limit).'])
                    ->withInput();
            }
        }

        $validated = $this->validateBanner($request, $banner);

        // ── Image ─────────────────────────────────────────────────────────────
        if ($request->boolean('remove_image')) {
            $this->storage->delete($banner->image);
            $validated['image'] = null;
        } else {
            $newImage = $this->handleImage($request, $banner->image);
            if ($newImage !== $banner->image) {
                // New file uploaded — delete the old one
                $this->storage->delete($banner->image);
                $validated['image'] = $newImage;
            }
        }

        // ── Video ─────────────────────────────────────────────────────────────
        if ($request->boolean('remove_video')) {
            // Only delete from storage if it was an uploaded file (not a YouTube/Vimeo URL)
            if ($banner->video_type === 'upload') {
                $this->storage->delete($banner->video);
            }
            $validated['video']      = null;
            $validated['video_type'] = null;
        } else {
            $newVideo = $this->handleVideo($request, $banner->video);
            if ($newVideo !== $banner->video) {
                if ($banner->video_type === 'upload') {
                    $this->storage->delete($banner->video);
                }
                $validated['video']      = $newVideo;
                $validated['video_type'] = $this->resolveVideoType($request);
            }
        }

        $validated['active'] = $request->boolean('active', $banner->active);
        $validated['order']  = (int) $request->input('order', $banner->order);

        $banner->update($validated);

        return redirect()->route('dashboard.banners')->with('success', 'Banner updated.');
    }

    public function toggle(Banner $banner)
    {
        $banner->update(['active' => !$banner->active]);

        return redirect()->route('dashboard.banners')
            ->with('success', 'Banner ' . ($banner->active ? 'deactivated' : 'activated') . '.');
    }

    public function destroy(Banner $banner)
    {
        $this->storage->delete($banner->image);

        if ($banner->video_type === 'upload') {
            $this->storage->delete($banner->video);
        }

        $banner->delete();

        return redirect()->route('dashboard.banners')->with('success', 'Banner deleted.');
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    /**
     * Upload a new banner image, or return the existing path unchanged.
     * GIFs keep their extension so animation is preserved.
     */
    private function handleImage(Request $request, ?string $current): ?string
    {
        if (!$request->hasFile('image_file') || !$request->file('image_file')->isValid()) {
            return $current;
        }

        return $this->storage->store($request->file('image_file'), 'banners');
    }

    /**
     * Upload a new banner video, or fall back to a URL input, or keep existing.
     * File upload always takes priority over URL.
     */
    private function handleVideo(Request $request, ?string $current): ?string
    {
        if ($request->hasFile('video_file') && $request->file('video_file')->isValid()) {
            return $this->storage->store($request->file('video_file'), 'banners/videos');
        }

        $url = trim($request->input('video_url', ''));

        return $url ?: $current;
    }

    /**
     * Infer video_type from what was submitted (uploaded file > URL pattern > explicit input).
     */
    private function resolveVideoType(Request $request): ?string
    {
        if ($request->hasFile('video_file') && $request->file('video_file')->isValid()) {
            return 'upload';
        }

        $url = trim($request->input('video_url', ''));

        if ($url) {
            if (Str::contains($url, ['youtube.com', 'youtu.be'])) return 'youtube';
            if (Str::contains($url, 'vimeo.com'))                  return 'vimeo';
            if (Str::contains($url, ['facebook.com', 'fb.watch'])) return 'facebook';
        }

        return $request->input('video_type') ?: null;
    }

    private function validateBanner(Request $request, ?Banner $banner = null): array
    {
        $validated = $request->validate([
            'title'        => 'required|string|max:255',
            'subtitle'     => 'nullable|string|max:255',
            'badge'        => 'nullable|string|max:100',
            'bg_color'     => 'nullable|string|max:50',
            'text_color'   => 'nullable|string|max:50',
            'order'        => 'nullable|integer|min:0',
            'active'       => 'nullable',
            'image_file'   => 'nullable|file|max:51200|mimes:jpg,jpeg,png,webp,gif',
            'remove_image' => 'nullable|boolean',
            'video_type'   => 'nullable|in:upload,youtube,vimeo,facebook',
            'video_file'   => 'nullable|file|max:102400|mimes:mp4,webm,ogg,mov',
            'video_url'    => 'nullable|url|max:500',
            'remove_video' => 'nullable|boolean',
        ]);

        // Strip file/meta fields — handled separately, not written directly to DB
        unset(
            $validated['image_file'],
            $validated['video_file'],
            $validated['video_url'],
            $validated['remove_image'],
            $validated['remove_video'],
            $validated['video_type'], // resolved and set by caller
        );

        return $validated;
    }
}
