<?php

// app/Http/Controllers/Dashboard/DiscountController.php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Discount;
use Illuminate\Http\Request;

class DiscountController extends Controller
{
    // ── Validation rules shared by store() and update() ───────────────────────
    private function rules(int $ignoreId = 0): array
    {
        return [
            'code'                => 'required|string|max:50|unique:discounts,code,' . $ignoreId,
            'type'                => 'required|in:percentage,fixed',
            'value'               => 'required|numeric|min:0',
            'kind'                => 'nullable|in:code,badge',
            'product_id'          => 'nullable|exists:products,id',
            'min_order'           => 'nullable|numeric|min:0',
            'max_uses'            => 'nullable|integer|min:1',
            'expires_at'          => 'nullable|date',
            'is_active'           => 'nullable|boolean',
            'categories'          => 'nullable|array',
            'categories.*'        => 'string|max:100',
            'badge_config'        => 'nullable|array',
            'badge_config.text'   => 'nullable|string|max:30',
            'badge_config.icon'   => 'nullable|string|max:10',
            'badge_config.bg'     => 'nullable|string|max:100',
            'badge_config.border' => 'nullable|string|max:100',
            'badge_config.color'  => 'nullable|string|max:30',
        ];
    }

    // ── POST /dashboard/discounts ─────────────────────────────────────────────
    public function store(Request $request)
    {
        $data = $request->validate($this->rules());

        $data['kind']         = $request->input('kind', 'code');
        $data['is_active']    = $request->boolean('is_active', true);
        $data['product_id']   = $request->input('product_id') ?: null;
        
        // Ensure exclusivity: if product_id is provided, ignore categories
        $data['categories']   = $data['product_id'] ? null : ($request->input('categories', []) ?: null);
        $data['badge_config'] = $request->input('badge_config') ?: null;

        $discount = Discount::create($data);

        \App\Services\ActivityLogger::log([
            'action'      => 'discount_created',
            'entity_type' => get_class($discount),
            'entity_id'   => $discount->id,
            'entity_name' => $discount->code,
            'details'     => [
                'code'         => $discount->code,
                'type'         => $discount->type,
                'value'        => $discount->value,
                'kind'         => $discount->kind ?? 'code',
                'badge_config' => $discount->badge_config,
            ],
        ], $request);

        return redirect()
            ->route('dashboard.discounts')
            ->with('success', 'Discount created successfully.');
    }

    // ── PUT /dashboard/discounts/{discount} ───────────────────────────────────
    public function update(Request $request, Discount $discount)
    {
        $data = $request->validate($this->rules($discount->id));

        $data['kind']         = $request->input('kind', $discount->kind ?? 'code');
        $data['is_active']    = $request->boolean('is_active', false);
        $data['product_id']   = $request->input('product_id') ?: null;
        
        // Ensure exclusivity: if product_id is provided, ignore categories
        $data['categories']   = $data['product_id'] ? null : ($request->input('categories', []) ?: null);
        $data['badge_config'] = $request->input('badge_config') ?: null;

        $badgeBefore = $discount->badge_config;
        $discount->update($data);

        \App\Services\ActivityLogger::log([
            'action'      => 'discount_updated',
            'entity_type' => get_class($discount),
            'entity_id'   => $discount->id,
            'entity_name' => $discount->code,
            'details'     => [
                'code'         => $discount->code,
                'type'         => $discount->type,
                'value'        => $discount->value,
                'kind'         => $discount->kind ?? 'code',
                // Flag a badge change specifically so the UI can say "Badge updated"
                'badge_config' => ! empty($badgeBefore) || ! empty($discount->badge_config),
            ],
        ], $request);

        return redirect()
            ->route('dashboard.discounts')
            ->with('success', 'Discount updated successfully.');
    }

    // ── DELETE /dashboard/discounts/{discount} ────────────────────────────────
    public function destroy(Discount $discount, Request $request)
    {
        $snapshot = [
            'code'  => $discount->code,
            'type'  => $discount->type,
            'value' => $discount->value,
        ];

        $discount->delete();

        \App\Services\ActivityLogger::log([
            'action'      => 'discount_deleted',
            'entity_type' => \App\Models\Discount::class,
            'entity_id'   => $discount->id,
            'entity_name' => $snapshot['code'],
            'details'     => $snapshot,
        ], $request);

        return redirect()
            ->route('dashboard.discounts')
            ->with('success', 'Discount deleted.');
    }

    // ── PATCH /dashboard/discounts/{discount}/badge ───────────────────────────
    // Called from the admin badge modal via fetch() with FormData.
    // Passing clear_badge=1 removes the badge and resets kind to 'code'.
    public function saveBadge(Request $request, Discount $discount)
    {
        // clear_badge=1 means the admin clicked "CLEAR" in the badge modal
        if ($request->boolean('clear_badge')) {
            $discount->update([
                'badge_config' => null,
                // Reset kind to 'code' when badge is cleared
                'kind'         => 'code',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Badge cleared.',
                'data'    => $this->formatDiscount($discount->fresh()),
            ]);
        }

        // Validate badge fields (sent as FormData from the blade modal)
        $request->validate([
            'badge_config.text'   => 'required|string|max:30',
            'badge_config.icon'   => 'nullable|string|max:10',
            'badge_config.bg'     => 'nullable|string|max:100',
            'badge_config.border' => 'nullable|string|max:100',
            'badge_config.color'  => 'nullable|string|max:30',
        ]);

        $badgeConfig = [
            'text'   => $request->input('badge_config.text'),
            'icon'   => $request->input('badge_config.icon', '🏷️'),
            'bg'     => $request->input('badge_config.bg', '#F97316'),
            'border' => $request->input('badge_config.border', '#F97316'),
            'color'  => $request->input('badge_config.color', '#fff'),
        ];

        $discount->update([
            'badge_config' => $badgeConfig,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Badge saved.',
            'data'    => $this->formatDiscount($discount->fresh()),
        ]);
    }

    // ── Private helper — consistent response shape ────────────────────────────
    private function formatDiscount(Discount $d): array
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
