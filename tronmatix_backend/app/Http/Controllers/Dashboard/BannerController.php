<?php

// app/Http/Controllers/Dashboard/BannerController.php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BannerController extends Controller
{
    public function index()
    {
        $banners = Banner::all();

        return view('dashboard.banners', compact('banners'));
    }

    public function store(Request $request)
    {
        // ── Detect PHP-level upload failure (file silently dropped by PHP) ────
        // When upload_max_filesize is exceeded, PHP sets the file's error code to
        // UPLOAD_ERR_INI_SIZE (1). Laravel's Request::hasFile() returns false,
        // which normally passes 'nullable' — but the user gets no feedback.
        // We catch this here and return a clear validation error.
        if ($request->hasAny(['image_file', 'video_file'])) {
            foreach (['image_file', 'video_file'] as $field) {
                $uploadedFile = $request->files->get($field);
                if ($uploadedFile && $uploadedFile->getError() === UPLOAD_ERR_INI_SIZE) {
                    return back()->withErrors([$field => 'File too large — increase upload_max_filesize in php.ini (currently '.ini_get('upload_max_filesize').').'])->withInput();
                }
            }
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'badge' => 'nullable|string|max:100',
            'bg_color' => 'nullable|string|max:50',
            'text_color' => 'nullable|string|max:50',
            // Use 'file' + extension check instead of 'mimes' —
            // 'mimes' relies on finfo MIME detection which can fail for large GIFs
            // and some browsers send images as application/octet-stream.
            'image_file' => ['nullable', 'file', 'max:51200',
                function ($attr, $val, $fail) {
                    $ext = strtolower($val->getClientOriginalExtension());
                    if (! in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'])) {
                        $fail('Image must be jpg, jpeg, png, webp or gif.');
                    }
                }],
            'order' => 'nullable|integer|min:0',
            'active' => 'nullable|in:0,1,true,false',
            'video_type' => 'nullable|in:upload,youtube,vimeo,facebook',
            'video_file' => ['nullable', 'file', 'max:102400',
                function ($attr, $val, $fail) {
                    $ext = strtolower($val->getClientOriginalExtension());
                    if (! in_array($ext, ['mp4', 'webm', 'ogg', 'mov'])) {
                        $fail('Video must be mp4, webm, ogg or mov.');
                    }
                }],
            'video_url' => 'nullable|max:500',
        ]);

        $validated['image'] = $this->handleImage($request, null);
        $validated['video'] = $this->handleVideo($request, null);
        $validated['video_type'] = $this->resolveVideoType($request);
        $validated['active'] = $request->boolean('active', true);
        $validated['order'] = (int) $request->input('order', 0);

        unset($validated['image_file'], $validated['video_file'], $validated['video_url']);

        Banner::create($validated);

        return redirect()->route('dashboard.banners')->with('success', 'Banner created.');
    }

    public function update(Request $request, Banner $banner)
    {
        // ── Detect PHP-level upload failure ───────────────────────────────────
        foreach (['image_file', 'video_file'] as $field) {
            $uploadedFile = $request->files->get($field);
            if ($uploadedFile && $uploadedFile->getError() === UPLOAD_ERR_INI_SIZE) {
                return back()->withErrors([$field => 'File too large — increase upload_max_filesize in php.ini (currently '.ini_get('upload_max_filesize').').'])->withInput();
            }
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'badge' => 'nullable|string|max:100',
            'bg_color' => 'nullable|string|max:50',
            'text_color' => 'nullable|string|max:50',
            'image_file' => ['nullable', 'file', 'max:51200',
                function ($attr, $val, $fail) {
                    $ext = strtolower($val->getClientOriginalExtension());
                    if (! in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'])) {
                        $fail('Image must be jpg, jpeg, png, webp or gif.');
                    }
                }],
            'remove_image' => 'nullable|boolean',
            'order' => 'nullable|integer|min:0',
            'active' => 'nullable|in:0,1,true,false',
            'video_type' => 'nullable|in:upload,youtube,vimeo,facebook',
            'video_file' => ['nullable', 'file', 'max:102400',
                function ($attr, $val, $fail) {
                    $ext = strtolower($val->getClientOriginalExtension());
                    if (! in_array($ext, ['mp4', 'webm', 'ogg', 'mov'])) {
                        $fail('Video must be mp4, webm, ogg or mov.');
                    }
                }],
            'video_url' => 'nullable|max:500',
            'remove_video' => 'nullable|boolean',
        ]);

        // ── Image handling ────────────────────────────────────────────────────
        if ($request->boolean('remove_image')) {
            $this->deleteFile($banner->image);
            $validated['image'] = null;
        } else {
            $newImage = $this->handleImage($request, $banner->image);
            if ($newImage !== $banner->image) {
                $validated['image'] = $newImage;
            }
        }

        // ── Video handling ────────────────────────────────────────────────────
        if ($request->boolean('remove_video')) {
            $this->deleteVideoFile($banner->video, $banner->video_type);
            $validated['video'] = null;
            $validated['video_type'] = null;
        } else {
            $newVideo = $this->handleVideo($request, $banner->video);
            if ($newVideo !== $banner->video) {
                // New video uploaded — delete old uploaded file if it was self-hosted
                if ($banner->video_type === 'upload') {
                    $this->deleteVideoFile($banner->video, 'upload');
                }
                $validated['video'] = $newVideo;
                $validated['video_type'] = $this->resolveVideoType($request);
            }
        }

        $validated['active'] = $request->boolean('active', $banner->active);
        $validated['order'] = (int) $request->input('order', $banner->order);

        unset($validated['image_file'], $validated['video_file'], $validated['video_url'],
            $validated['remove_image'], $validated['remove_video']);

        $banner->update($validated);

        return redirect()->route('dashboard.banners')->with('success', 'Banner updated.');
    }

    public function toggle(Banner $banner)
    {
        $newState = ! $banner->active;
        $banner->update(['active' => $newState]);

        return redirect()->route('dashboard.banners')
            ->with('success', 'Banner '.($newState ? 'activated' : 'deactivated').'.');
    }

    public function destroy(Banner $banner)
    {
        $this->deleteFile($banner->image);
        $this->deleteVideoFile($banner->video, $banner->video_type);
        $banner->delete();

        return redirect()->route('dashboard.banners')->with('success', 'Banner deleted.');
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    private function handleImage(Request $request, ?string $current): ?string
    {
        if (! $request->hasFile('image_file') || ! $request->file('image_file')->isValid()) {
            return $current;
        }

        $this->deleteFile($current);

        $file = $request->file('image_file');
        // MUST preserve extension — GIF without .gif loses animation,
        // and browsers rely on the extension to pick the correct MIME type.
        $filename = \Illuminate\Support\Str::uuid().'.'.$file->getClientOriginalExtension();
        $path = $file->storeAs('banners', $filename, 'public');

        return '/storage/'.$path;
    }

    /**
     * Handle video: either a file upload or a YouTube / Vimeo / Facebook URL.
     * Returns the stored path or URL, or the existing value if nothing changed.
     */
    private function handleVideo(Request $request, ?string $current): ?string
    {
        if ($request->hasFile('video_file') && $request->file('video_file')->isValid()) {
            $file = $request->file('video_file');
            // Preserve extension — browsers need .mp4 / .webm to determine MIME type
            $filename = \Illuminate\Support\Str::uuid().'.'.$file->getClientOriginalExtension();
            $path = $file->storeAs('banners/videos', $filename, 'public');

            return '/storage/'.$path;
        }

        $url = trim($request->input('video_url', ''));
        if ($url) {
            return $url;   // YouTube / Vimeo / Facebook embed URL stored as-is
        }

        return $current;   // Nothing changed
    }

    /**
     * Determine video_type from the current request.
     */
    private function resolveVideoType(Request $request): ?string
    {
        if ($request->hasFile('video_file') && $request->file('video_file')->isValid()) {
            return 'upload';
        }

        $url = trim($request->input('video_url', ''));
        if ($url) {
            if (str_contains($url, 'youtube.com') || str_contains($url, 'youtu.be')) {
                return 'youtube';
            }
            if (str_contains($url, 'vimeo.com')) {
                return 'vimeo';
            }
            if (str_contains($url, 'facebook.com') || str_contains($url, 'fb.watch')) {
                return 'facebook';
            }
        }

        return $request->input('video_type') ?: null;
    }

    private function deleteFile(?string $path): void
    {
        if ($path && str_starts_with($path, '/storage/')) {
            Storage::disk('public')->delete(str_replace('/storage/', '', $path));
        }
    }

    private function deleteVideoFile(?string $video, ?string $type): void
    {
        // Only delete physical files — not YouTube/Vimeo URLs
        if ($type === 'upload' && $video) {
            $this->deleteFile($video);
        }
    }
}
