<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Traits\StorageHelper;
use Illuminate\Http\Request;
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
            $f = $request->files->get($field);
            if ($f && $f->getError() === UPLOAD_ERR_INI_SIZE)
                return back()->withErrors([$field => 'File too large.'])->withInput();
        }

        $validated = $request->validate([
            'title'      => 'required|string|max:255',
            'subtitle'   => 'nullable|string|max:255',
            'badge'      => 'nullable|string|max:100',
            'bg_color'   => 'nullable|string|max:50',
            'text_color' => 'nullable|string|max:50',
            'image_file' => ['nullable', 'file', 'max:51200', fn($a,$v,$f) => in_array(strtolower($v->getClientOriginalExtension()),['jpg','jpeg','png','webp','gif']) ?: $f('Invalid image type.')],
            'order'      => 'nullable|integer|min:0',
            'active'     => 'nullable|in:0,1,true,false',
            'video_type' => 'nullable|in:upload,youtube,vimeo,facebook',
            'video_file' => ['nullable', 'file', 'max:102400', fn($a,$v,$f) => in_array(strtolower($v->getClientOriginalExtension()),['mp4','webm','ogg','mov']) ?: $f('Invalid video type.')],
            'video_url'  => 'nullable|max:500',
        ]);

        $validated['image']      = $this->handleImage($request, null);
        $validated['video']      = $this->handleVideo($request, null);
        $validated['video_type'] = $this->resolveVideoType($request);
        $validated['active']     = $request->boolean('active', true);
        $validated['order']      = (int) $request->input('order', 0);
        unset($validated['image_file'], $validated['video_file'], $validated['video_url']);

        Banner::create($validated);
        return redirect()->route('dashboard.banners')->with('success', 'Banner created.');
    }

    public function update(Request $request, Banner $banner)
    {
        foreach (['image_file', 'video_file'] as $field) {
            $f = $request->files->get($field);
            if ($f && $f->getError() === UPLOAD_ERR_INI_SIZE)
                return back()->withErrors([$field => 'File too large.'])->withInput();
        }

        $validated = $request->validate([
            'title'        => 'required|string|max:255',
            'subtitle'     => 'nullable|string|max:255',
            'badge'        => 'nullable|string|max:100',
            'bg_color'     => 'nullable|string|max:50',
            'text_color'   => 'nullable|string|max:50',
            'image_file'   => ['nullable', 'file', 'max:51200', fn($a,$v,$f) => in_array(strtolower($v->getClientOriginalExtension()),['jpg','jpeg','png','webp','gif']) ?: $f('Invalid image type.')],
            'remove_image' => 'nullable|boolean',
            'order'        => 'nullable|integer|min:0',
            'active'       => 'nullable|in:0,1,true,false',
            'video_type'   => 'nullable|in:upload,youtube,vimeo,facebook',
            'video_file'   => ['nullable', 'file', 'max:102400', fn($a,$v,$f) => in_array(strtolower($v->getClientOriginalExtension()),['mp4','webm','ogg','mov']) ?: $f('Invalid video type.')],
            'video_url'    => 'nullable|max:500',
            'remove_video' => 'nullable|boolean',
        ]);

        if ($request->boolean('remove_image')) {
            $this->deleteStorageFile($banner->image);
            $validated['image'] = null;
        } else {
            $new = $this->handleImage($request, $banner->image);
            if ($new !== $banner->image) $validated['image'] = $new;
        }

        if ($request->boolean('remove_video')) {
            if ($banner->video_type === 'upload') $this->deleteStorageFile($banner->video);
            $validated['video'] = null;
            $validated['video_type'] = null;
        } else {
            $new = $this->handleVideo($request, $banner->video);
            if ($new !== $banner->video) {
                if ($banner->video_type === 'upload') $this->deleteStorageFile($banner->video);
                $validated['video'] = $new;
                $validated['video_type'] = $this->resolveVideoType($request);
            }
        }

        $validated['active'] = $request->boolean('active', $banner->active);
        $validated['order']  = (int) $request->input('order', $banner->order);
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

    private function handleImage(Request $request, ?string $current): ?string
    {
        if (!$request->hasFile('image_file') || !$request->file('image_file')->isValid()) return $current;
        $this->deleteStorageFile($current);
        $file = $request->file('image_file');
        return $this->storeFileAs($file, 'banners', Str::uuid().'.'.$file->getClientOriginalExtension());
    }

    private function handleVideo(Request $request, ?string $current): ?string
    {
        if ($request->hasFile('video_file') && $request->file('video_file')->isValid()) {
            $file = $request->file('video_file');
            return $this->storeFileAs($file, 'banners/videos', Str::uuid().'.'.$file->getClientOriginalExtension());
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
}
