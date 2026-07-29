<?php

namespace App\Services;

use Illuminate\Http\Request;
use App\Models\ActivityLog;

class ActivityLogger
{
    /**
     * Log an activity action.
     *
     * @param  array  $data  {action, entity_type, entity_id, entity_name, details, actor_override?}
     * @param  Request|null  $request
     */
    public static function log(array $data, ?Request $request = null): void
    {
        $request = $request ?? app(Request::class);

        // Resolve actor from any authenticated guard
        $actor = null;
        $actorType = null;
        $actorName = null;

        if ($request->user('admin')) {
            $actor = $request->user('admin');
            $actorType = 'Admin';
            $actorName = $actor->name;
        } elseif ($request->user('staff')) {
            $actor = $request->user('staff');
            $actorType = 'Staff';
            $actorName = $actor->name;
        } elseif ($request->user()) {
            // Fallback for API sanctum user
            $actor = $request->user();
            $actorType = get_class($actor);
            $actorName = $actor->name ?? $actor->email ?? 'Unknown';
        }

        // Allow override
        if (isset($data['actor_override'])) {
            [$actorType, $actorName] = $data['actor_override'];
        }

        ActivityLog::create([
            'actor_id'    => $actor?->id,
            'actor_type'  => $actorType ?? $data['actor_type'] ?? null,
            'actor_name'  => $actorName ?? $data['actor_name'] ?? 'System',
            'action'      => $data['action'],
            'entity_type' => $data['entity_type'] ?? null,
            'entity_id'   => $data['entity_id'] ?? null,
            'entity_name' => $data['entity_name'] ?? null,
            'details'     => $data['details'] ?? null,
            'ip_address'  => $request->ip(),
            'user_agent'  => $request->userAgent(),
        ]);
    }

    /**
     * Quick shortcut for order status changes.
     */
    public static function orderStatusChange($order, string $oldStatus, string $newStatus, $request = null): void
    {
        static::log([
            'action'      => 'order_status_update',
            'entity_type' => 'Order',
            'entity_id'   => $order->id,
            'entity_name' => $order->order_id,
            'details'     => ['old_status' => $oldStatus, 'new_status' => $newStatus],
        ], $request);
    }

    /**
     * Quick shortcut for payment verification.
     */
    public static function paymentVerified($order, $request = null): void
    {
        static::log([
            'action'      => 'payment_verified',
            'entity_type' => 'Order',
            'entity_id'   => $order->id,
            'entity_name' => $order->order_id,
            'details'     => ['payment_status' => 'paid'],
        ], $request);
    }

    /**
     * Quick shortcut for delivery confirmation.
     */
    public static function deliveryConfirmed($order, string $by, $request = null): void
    {
        static::log([
            'action'      => 'delivery_confirmed',
            'entity_type' => 'Order',
            'entity_id'   => $order->id,
            'entity_name' => $order->order_id,
            'details'     => ['confirmed_by' => $by],
        ], $request);
    }

    /**
     * Quick shortcut for product CRUD.
     */
    public static function productCreated($product, $request = null): void
    {
        static::log([
            'action'      => 'product_create',
            'entity_type' => 'Product',
            'entity_id'   => $product->id,
            'entity_name' => $product->name,
            'details'     => ['name' => $product->name, 'price' => $product->price],
        ], $request);
    }

    public static function productUpdated($product, $request = null): void
    {
        static::log([
            'action'      => 'product_update',
            'entity_type' => 'Product',
            'entity_id'   => $product->id,
            'entity_name' => $product->name,
            'details'     => ['name' => $product->name],
        ], $request);
    }

    public static function productDeleted($product, $request = null): void
    {
        static::log([
            'action'      => 'product_delete',
            'entity_type' => 'Product',
            'entity_id'   => $product->id,
            'entity_name' => $product->name,
            'details'     => ['name' => $product->name],
        ], $request);
    }

    /**
     * Quick shortcut for staff management.
     */
    public static function staffInvited(string $name, string $role, $request = null): void
    {
        static::log([
            'action'      => 'staff_invited',
            'entity_type' => 'Staff',
            'details'     => ['name' => $name, 'role' => $role],
        ], $request);
    }

    public static function staffRoleChanged($staff, string $oldRole, string $newRole, $request = null): void
    {
        static::log([
            'action'      => 'staff_role_changed',
            'entity_type' => 'Staff',
            'entity_id'   => $staff->id,
            'entity_name' => $staff->name,
            'details'     => ['old_role' => $oldRole, 'new_role' => $newRole],
        ], $request);
    }

    public static function staffToggled($staff, bool $activated, $request = null): void
    {
        static::log([
            'action'      => $activated ? 'staff_activated' : 'staff_deactivated',
            'entity_type' => 'Staff',
            'entity_id'   => $staff->id,
            'entity_name' => $staff->name,
            'details'     => ['activated' => $activated],
        ], $request);
    }

    public static function staffDeleted($staff, $request = null): void
    {
        static::log([
            'action'      => 'staff_deleted',
            'entity_type' => 'Staff',
            'entity_id'   => $staff->id,
            'entity_name' => $staff->name,
            'details'     => ['name' => $staff->name],
        ], $request);
    }

    /**
     * Quick shortcut for login attempts.
     */
    public static function loginSuccess($user, $request = null): void
    {
        static::log([
            'action'      => 'login_success',
            'entity_type' => get_class($user),
            'entity_id'   => $user->id,
            'entity_name' => $user->name ?? $user->email,
            'details'     => ['guard' => $request->guard ?? 'unknown'],
        ], $request);
    }

    public static function loginFailed(string $email, $request = null, ?string $reason = null): void
    {
        static::log([
            'action'      => 'login_failed',
            'entity_type' => 'Auth',
            'entity_name' => $email,
            'details'     => ['reason' => $reason ?? 'invalid_credentials'],
        ], $request);
    }

    /**
     * Quick shortcut for rate-limit hit on login.
     */
    public static function loginRateLimited(string $identifier, $request = null): void
    {
        static::log([
            'action'      => 'login_rate_limited',
            'entity_type' => 'Auth',
            'entity_name' => $identifier,
            'details'     => ['reason' => 'too_many_attempts'],
        ], $request);
    }
}