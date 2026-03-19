<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Traits\StorageHelper;
use Illuminate\Http\Request;
<<<<<<< HEAD
=======
use Illuminate\Support\Facades\Storage;
>>>>>>> 82a51346582e7e958aee906bd907014d342a8a3b
use Illuminate\Support\Str;

class BannerController extends Controller
{
    use StorageHelper;

    public function index()
    {
        $banners = Banner::all();
        return view('dashboard.banners', compact('banners'));
    }

    public function store(Request $request)
    {
        foreach (['image_file', 'video_file'] as $field) {
<<<<<<< HEAD
            $f = $request->files->get($field);
            if ($f && $f->getError() === UPLOAD_ERR_INI_SIZE)
                return back()->withErrors([$field => 'File too large.'])->withInput();
=======
            $uploadedFile = $request->files->get($field);
            if ($uploadedFile && $uploadedFile->getError() === UPLOAD_ERR_INI_SIZE) {
                return back()->withErrors([$field => 'File too large.'])->withInput();
            }
>>>>>>> 82a51346582e7e958aee906bd907014d342a8a3b
        }

        $validated = $request->validate([
            'title'      => 'required|string|max:255',
            'subtitle'   => 'nullable|string|max:255',
            'badge'      => 'nullable|string|max:100',
            'bg_color'   => 'nullable|string|max:50',
            'text_color' => 'nullable|string|max:50',
<<<<<<< HEAD
            'image_file' => ['nullable', 'file', 'max:51200', fn($a,$v,$f) => in_array(strtolower($v->getClientOriginalExtension()),['jpg','jpeg','png','webp','gif']) ?: $f('Invalid image type.')],
            'order'      => 'nullable|integer|min:0',
            'active'     => 'nullable|in:0,1,true,false',
            'video_type' => 'nullable|in:upload,youtube,vimeo,facebook',
            'video_file' => ['nullable', 'file', 'max:102400', fn($a,$v,$f) => in_array(strtolower($v->getClientOriginalExtension()),['mp4','webm','ogg','mov']) ?: $f('Invalid video type.')],
=======
            'image_file' => ['nullable', 'file', 'max:51200', function ($attr, $val, $fail) {
                if (!in_array(strtolower($val->getClientOriginalExtension()), ['jpg','jpeg','png','webp','gif'])) {
                    $fail('Image must be jpg, jpeg, png, webp or gif.');
                }
            }],
            'order'      => 'nullable|integer|min:0',
            'active'     => 'nullable|in:0,1,true,false',
            'video_type' => 'nullable|in:upload,youtube,vimeo,facebook',
            'video_file' => ['nullable', 'file', 'max:102400', function ($attr, $val, $fail) {
                if (!in_array(strtolower($val->getClientOriginalExtension()), ['mp4','webm','ogg','mov'])) {
                    $fail('Video must be mp4, webm, ogg or mov.');
                }
            }],
>>>>>>> 82a51346582e7e958aee906bd907014d342a8a3b
            'video_url'  => 'nullable|max:500',
        ]);

        $validated['image']      = $this->handleImage($request, null);
        $validated['video']      = $this->handleVideo($request, null);
        $validated['video_type'] = $this->resolveVideoType($request);
        $validated['active']     = $request->boolean('active', true);
        $validated['order']      = (int) $request->input('order', 0);
<<<<<<< HEAD
=======

>>>>>>> 82a51346582e7e958aee906bd907014d342a8a3b
        unset($validated['image_file'], $validated['video_file'], $validated['video_url']);

        Banner::create($validated);
        return redirect()->route('dashboard.banners')->with('success', 'Banner created.');
    }

    public function update(Request $request, Banner $banner)
    {
        foreach (['image_file', 'video_file'] as $field) {
<<<<<<< HEAD
            $f = $request->files->get($field);
            if ($f && $f->getError() === UPLOAD_ERR_INI_SIZE)
                return back()->withErrors([$field => 'File too large.'])->withInput();
=======
            $uploadedFile = $request->files->get($field);
            if ($uploadedFile && $uploadedFile->getError() === UPLOAD_ERR_INI_SIZE) {
                return back()->withErrors([$field => 'File too large.'])->withInput();
            }
>>>>>>> 82a51346582e7e958aee906bd907014d342a8a3b
        }

        $validated = $request->validate([
            'title'        => 'required|string|max:255',
            'subtitle'     => 'nullable|string|max:255',
            'badge'        => 'nullable|string|max:100',
            'bg_color'     => 'nullable|string|max:50',
            'text_color'   => 'nullable|string|max:50',
<<<<<<< HEAD
            'image_file'   => ['nullable', 'file', 'max:51200', fn($a,$v,$f) => in_array(strtolower($v->getClientOriginalExtension()),['jpg','jpeg','png','webp','gif']) ?: $f('Invalid image type.')],
=======
            'image_file'   => ['nullable', 'file', 'max:51200', function ($attr, $val, $fail) {
                if (!in_array(strtolower($val->getClientOriginalExtension()), ['jpg','jpeg','png','webp','gif'])) {
                    $fail('Image must be jpg, jpeg, png, webp or gif.');
                }
            }],
>>>>>>> 82a51346582e7e958aee906bd907014d342a8a3b
            'remove_image' => 'nullable|boolean',
            'order'        => 'nullable|integer|min:0',
            'active'       => 'nullable|in:0,1,true,false',
            'video_type'   => 'nullable|in:upload,youtube,vimeo,facebook',
<<<<<<< HEAD
            'video_file'   => ['nullable', 'file', 'max:102400', fn($a,$v,$f) => in_array(strtolower($v->getClientOriginalExtension()),['mp4','webm','ogg','mov']) ?: $f('Invalid video type.')],
=======
            'video_file'   => ['nullable', 'file', 'max:102400', function ($attr, $val, $fail) {
                if (!in_array(strtolower($val->getClientOriginalExtension()), ['mp4','webm','ogg','mov'])) {
                    $fail('Video must be mp4, webm, ogg or mov.');
                }
            }],
>>>>>>> 82a51346582e7e958aee906bd907014d342a8a3b
            'video_url'    => 'nullable|max:500',
            'remove_video' => 'nullable|boolean',
        ]);

        if ($request->boolean('remove_image')) {
            $this->deleteStorageFile($banner->image);
            $validated['image'] = null;
        } else {
<<<<<<< HEAD
            $new = $this->handleImage($request, $banner->image);
            if ($new !== $banner->image) $validated['image'] = $new;
=======
            $newImage = $this->handleImage($request, $banner->image);
            if ($newImage !== $banner->image) $validated['image'] = $newImage;
>>>>>>> 82a51346582e7e958aee906bd907014d342a8a3b
        }

        if ($request->boolean('remove_video')) {
            if ($banner->video_type === 'upload') $this->deleteStorageFile($banner->video);
            $validated['video'] = null;
            $validated['video_type'] = null;
        } else {
<<<<<<< HEAD
            $new = $this->handleVideo($request, $banner->video);
            if ($new !== $banner->video) {
                if ($banner->video_type === 'upload') $this->deleteStorageFile($banner->video);
                $validated['video'] = $new;
=======
            $newVideo = $this->handleVideo($request, $banner->video);
            if ($newVideo !== $banner->video) {
                if ($banner->video_type === 'upload') $this->deleteVideoFile($banner->video, 'upload');
                $validated['video'] = $newVideo;
>>>>>>> 82a51346582e7e958aee906bd907014d342a8a3b
                $validated['video_type'] = $this->resolveVideoType($request);
            }
        }

        $validated['active'] = $request->boolean('active', $banner->active);
        $validated['order']  = (int) $request->input('order', $banner->order);
<<<<<<< HEAD
=======

>>>>>>> 82a51346582e7e958aee906bd907014d342a8a3b
        unset($validated['image_file'], $validated['video_file'], $validated['video_url'],
              $validated['remove_image'], $validated['remove_video']);

        $banner->update($validated);
        return redirect()->route('dashboard.banners')->with('success', 'Banner updated.');
    }

    public function toggle(Banner $banner)
    {
        $banner->update(['active' => !$banner->active]);
        return redirect()->route('dashboard.banners')
            ->with('success', 'Banner '.($banner->active ? 'activated' : 'deactivated').'.');
    }

    public function destroy(Banner $banner)
    {
        $this->deleteStorageFile($banner->image);
        if ($banner->video_type === 'upload') $this->deleteStorageFile($banner->video);
        $banner->delete();
        return redirect()->route('dashboard.banners')->with('success', 'Banner deleted.');
    }

<<<<<<< HEAD
    private function handleImage(Request $request, ?string $current): ?string
    {
        if (!$request->hasFile('image_file') || !$request->file('image_file')->isValid()) return $current;
        $this->deleteStorageFile($current);
        $file = $request->file('image_file');
        return $this->storeFileAs($file, 'banners', Str::uuid().'.'.$file->getClientOriginalExtension());
=======
    // ── Private helpers ───────────────────────────────────────────────────────

    /**
     * FIX: Upload to S3/R2 instead of local 'public' disk.
     * Returns full S3 URL so it persists across Render deploys.
     */
    private function handleImage(Request $request, ?string $current): ?string
    {
        if (!$request->hasFile('image_file') || !$request->file('image_file')->isValid()) {
            return $current;
        }

        // Delete old file from S3/R2
        $this->deleteFile($current);

        $file     = $request->file('image_file');
        $filename = Str::uuid().'.'.$file->getClientOriginalExtension();
        $disk     = $this->storageDisk();

        // Store to S3/R2 and return full public URL
        $path = $file->storeAs('banners', $filename, $disk);

        return Storage::disk($disk)->url($path);
>>>>>>> 82a51346582e7e958aee906bd907014d342a8a3b
    }

    private function handleVideo(Request $request, ?string $current): ?string
    {
        if ($request->hasFile('video_file') && $request->file('video_file')->isValid()) {
<<<<<<< HEAD
            $file = $request->file('video_file');
            return $this->storeFileAs($file, 'banners/videos', Str::uuid().'.'.$file->getClientOriginalExtension());
=======
            $file     = $request->file('video_file');
            $filename = Str::uuid().'.'.$file->getClientOriginalExtension();
            $disk     = $this->storageDisk();
            $path     = $file->storeAs('banners/videos', $filename, $disk);
            return Storage::disk($disk)->url($path);
>>>>>>> 82a51346582e7e958aee906bd907014d342a8a3b
        }
        $url = trim($request->input('video_url', ''));
        return $url ?: $current;
    }

    private function resolveVideoType(Request $request): ?string
    {
        if ($request->hasFile('video_file') && $request->file('video_file')->isValid()) return 'upload';
        $url = trim($request->input('video_url', ''));
        if ($url) {
            if (str_contains($url, 'youtube.com') || str_contains($url, 'youtu.be')) return 'youtube';
            if (str_contains($url, 'vimeo.com')) return 'vimeo';
            if (str_contains($url, 'facebook.com') || str_contains($url, 'fb.watch')) return 'facebook';
        }
        return $request->input('video_type') ?: null;
    }
<<<<<<< HEAD
=======

    /**
     * Delete file from whatever disk it's on.
     * Handles: S3/R2 full URLs, local /storage/ paths, null.
     */
    private function deleteFile(?string $path): void
    {
        if (!$path) return;

        $disk = $this->storageDisk();

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            // Extract S3 key from full URL
            $bucket = config("filesystems.disks.{$disk}.bucket");
            $key    = preg_replace('#^https?://[^/]+/(?:' . preg_quote($bucket, '#') . '/)?#', '', $path);
            if ($key) Storage::disk($disk)->delete($key);
        } elseif (str_starts_with($path, '/storage/')) {
            Storage::disk('public')->delete(str_replace('/storage/', '', $path));
        }
    }

    private function deleteVideoFile(?string $video, ?string $type): void
    {
        if ($type === 'upload' && $video) $this->deleteFile($video);
    }

    /** Returns 's3' if configured, otherwise 'public' (local dev) */
    private function storageDisk(): string
    {
        return config('filesystems.default') === 's3' ? 's3' : 'public';
    }
>>>>>>> 82a51346582e7e958aee906bd907014d342a8a3b
}
