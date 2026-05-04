<?php

// app/Http/Controllers/Api/DiscountController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Discount;
use Illuminate\Http\Request;

class DiscountController extends Controller
{
    public function index()
    {
        $discounts = Discount::latest()->get()->map(fn (Discount $d) => $this->format($d));

        return response()->json(['success' => true, 'data' => $discounts]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'code'                => 'required|string|max:50|unique:discounts,code',
            'type'                => 'required|in:percentage,fixed',
            'value'               => 'required|numeric|min:0',
            'kind'                => 'nullable|in:code,badge',
            'min_order'           => 'nullable|numeric|min:0',
            'max_uses'            => 'nullable|integer|min:1',
            'expires_at'          => 'nullable|date',
            'is_active'           => 'boolean',
            'categories'          => 'nullable|array',
            'categories.*'        => 'string|max:100',
            'badge_config'        => 'nullable|array',
            'badge_config.text'   => 'nullable|string|max:30',
            'badge_config.icon'   => 'nullable|string|max:10',
            'badge_config.bg'     => 'nullable|string|max:100',
            'badge_config.border' => 'nullable|string|max:100',
            'badge_config.color'  => 'nullable|string|max:30',
        ]);
        $data['kind']         = $request->input('kind', 'code');
        $data['categories']   = $request->input('categories', []) ?: null;
        $data['badge_config'] = $request->input('badge_config') ?: null;
        $discount = Discount::create($data);

        return response()->json(['success' => true, 'data' => $this->format($discount)], 201);
    }

    public function update(Request $request, Discount $discount)
    {
        $data = $request->validate([
            'code'                => 'required|string|max:50|unique:discounts,code,'.$discount->id,
            'type'                => 'required|in:percentage,fixed',
            'value'               => 'required|numeric|min:0',
            'kind'                => 'nullable|in:code,badge',
            'min_order'           => 'nullable|numeric|min:0',
            'max_uses'            => 'nullable|integer|min:1',
            'expires_at'          => 'nullable|date',
            'is_active'           => 'boolean',
            'categories'          => 'nullable|array',
            'categories.*'        => 'string|max:100',
            'badge_config'        => 'nullable|array',
            'badge_config.text'   => 'nullable|string|max:30',
            'badge_config.icon'   => 'nullable|string|max:10',
            'badge_config.bg'     => 'nullable|string|max:100',
            'badge_config.border' => 'nullable|string|max:100',
            'badge_config.color'  => 'nullable|string|max:30',
        ]);
        $data['kind']         = $request->input('kind', $discount->kind ?? 'code');
        $data['categories']   = $request->input('categories', []) ?: null;
        $data['badge_config'] = $request->input('badge_config') ?: null;
        $discount->update($data);

        return response()->json(['success' => true, 'data' => $this->format($discount)]);
    }

    public function destroy(Discount $discount)
    {
        $discount->delete();

        return response()->json(['success' => true, 'message' => 'Discount deleted.']);
    }

    // ── Save / clear badge config for a discount ──────────────────────────────
    // Called from the admin badge modal via PATCH /api/discounts/{id}/badge.
    // Passing badge_config: null clears the badge.
    public function saveBadge(Request $request, Discount $discount)
    {
        $request->validate([
            'badge_config'        => 'nullable|array',
            'badge_config.text'   => 'required_with:badge_config|string|max:30',
            'badge_config.icon'   => 'nullable|string|max:10',
            'badge_config.bg'     => 'nullable|string|max:100',
            'badge_config.border' => 'nullable|string|max:100',
            'badge_config.color'  => 'nullable|string|max:30',
        ]);

        $discount->update([
            'badge_config' => $request->input('badge_config') ?: null,
        ]);

        return response()->json(['success' => true, 'data' => $this->format($discount)]);
    }

    // ── Public active discounts (storefront badge display) ────────────────────
    // Called by DiscountContext on mount — returns active badge-kind discounts
    // so ProductCard/ProductDetail can show sale badges automatically.
    // Code-kind discounts are NOT returned here; they require manual entry.
    public function storefront()
    {
        $discounts = Discount::where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->get()
            ->filter(fn (Discount $d) => $d->isActiveForBadge())
            ->map(fn (Discount $d) => [
                'id'           => $d->id,
                'kind'         => $d->kind ?? 'code',   // frontend uses this to distinguish
                'type'         => $d->type,
                'value'        => $d->value,
                'categories'   => $d->categories ?? [],
                'badge_config' => $d->badge_config,
                // No code, no used_count — display-only
            ])
            ->values();

        return response()->json(['success' => true, 'data' => $discounts]);
    }

    // ── Apply discount code ───────────────────────────────────────────────────
    public function apply(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
            'subtotal' => 'required|numeric|min:0',
        ]);

        $discount = Discount::where('code', strtoupper($request->code))
            ->where('is_active', true)
            ->first();

        if (! $discount) {
            return response()->json(['success' => false, 'message' => 'Invalid discount code.'], 404);
        }

        // Badge-kind discounts are automatic — reject manual code entry
        if ($discount->isBadgeKind()) {
            return response()->json([
                'success' => false,
                'message' => 'This discount is applied automatically — no code needed.',
            ], 422);
        }

        // FIX [1]: delegate to model's isUsable($subtotal) — single source of truth
        if (! $discount->isUsable((float) $request->subtotal)) {
            // Give specific error message based on what failed
            if ($discount->expires_at && $discount->expires_at->isPast()) {
                $message = 'Discount has expired.';
            } elseif ($discount->max_uses && $discount->used_count >= $discount->max_uses) {
                $message = 'Usage limit reached for this code.';
            } elseif ((float) $request->subtotal < $discount->min_order) {
                $message = "Minimum order of \${$discount->min_order} required.";
            } else {
                $message = 'Discount is not currently valid.';
            }

            return response()->json(['success' => false, 'message' => $message], 422);
        }

        // Use model's calcAmount() — single source of truth
        $amount = $discount->calcAmount((float) $request->subtotal);

        // FIX [2]: increment used_count so coupon tracks usage correctly
        $discount->incrementUsage();

        return response()->json([
            'success'         => true,
            'message'         => 'Discount applied!',
            'discount_id'     => $discount->id,
            'kind'            => $discount->kind ?? 'code',
            'code'            => $discount->code,
            'type'            => $discount->type,
            'value'           => $discount->value,
            'min_order'       => $discount->min_order,
            'categories'      => $discount->categories ?? [],
            'discount_amount' => $amount,
            'final_total'     => max(0, (float) $request->subtotal - $amount),
        ]);
    }

    private function format(Discount $d): array
    {
        return [
            'id'           => $d->id,
            'kind'         => $d->kind ?? 'code',
            'code'         => $d->code,
            'type'         => $d->type,
            'value'        => $d->value,
            'min_order'    => $d->min_order,
            'max_uses'     => $d->max_uses,
            'used_count'   => $d->used_count,
            'expires_at'   => $d->expires_at?->toDateTimeString(),
            'is_active'    => $d->is_active,
            'categories'   => $d->categories ?? [],
            'badge_config' => $d->badge_config,
            'status'       => $d->status,
        ];
    }
}
