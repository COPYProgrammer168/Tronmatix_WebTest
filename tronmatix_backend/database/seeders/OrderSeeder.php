<?php

// database/seeders/OrderSeeder.php

namespace Database\Seeders;

use App\Models\Discount;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Models\UserLocation;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

/**
 * Seeds 150 orders — all inside the CURRENT month, so the dashboard's 1m /
 * "this month" ranges are full. Order items are created through
 * OrderItemSeeder::seedForOrder() so totals always match the item lines.
 *
 * Re-seeding truncates orders + order_items first, so the count stays exactly
 * 150 every run.
 */
class OrderSeeder extends Seeder
{
    private const COUNT = 150;

    private array $statuses      = ['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled'];
    private array $statusWeights = [6, 24, 16, 20, 28, 6];
    private array $paymentMethods = ['bakong', 'cash'];

    public function run(): void
    {
        $users     = User::with('locations')->get();
        $products  = Product::all();
        $discounts = Discount::where('is_active', true)->get();

        if ($users->isEmpty() || $products->isEmpty()) {
            $this->command->warn('⚠️  OrderSeeder: need users + products first (run UserSeeder + StockSeeder).');
            return;
        }

        $provinceIds = Schema::hasTable('provinces')
            ? \App\Models\Province::pluck('id')->all()
            : [];

        // Fresh slate every run — keep the count pinned at 150.
        OrderItem::query()->delete();
        Order::query()->delete();

        for ($i = 0; $i < self::COUNT; $i++) {
            $user       = $users->random();
            $location   = $user->locations->first();        // default / saved address
            $isDelivery = rand(0, 3) > 0;                   // 75% delivery, 25% pickup
            $zone       = $isDelivery
                ? ($location?->city === 'Phnom Penh' ? 'phnom_penh' : 'province')
                : null;

            // ── Cart: 1–3 products ──────────────────────────────────────────
            $productLines = $this->productLines($products);
            $subtotal = 0;
            foreach ($productLines as $line) {
                $subtotal += $this->cleanPrice($line['product']->price) * $line['qty'];
            }
            $subtotal = round($subtotal, 2);

            // ── Discount (stable codes from DiscountSeeder) ─────────────────
            $discount = ($discounts->isNotEmpty() && rand(0, 2) === 0) ? $discounts->random() : null;
            $discountAmount = 0;
            if ($discount && $subtotal >= (float) $discount->min_order) {
                $discountAmount = round($discount->calcAmount($subtotal), 2);
            } else {
                $discount = null;
            }

            // ── Delivery fee by zone (from delivery_provider_zones) ──────────
            $delivery = 0;
            if ($isDelivery && $subtotal <= 100) {
                // Pick a random active provider and read its zone-specific fee.
                $provider = \App\Models\DeliveryProvider::active()->inRandomOrder()->first();
                if ($provider) {
                    $zd = $provider->zoneDetails($zone ?? 'phnom_penh');
                    $delivery = $zd?->fee ?? 0;
                }
            }
            $tax   = round(($subtotal - $discountAmount) * 0.10, 2);
            $total = round($subtotal - $discountAmount + $delivery + $tax, 2);

            $orderDate = Carbon::now()
                ->subDays(rand(0, Carbon::now()->day - 1))
                ->setTime(rand(8, 21), rand(0, 59));

            // ── Province: derive from location if available ──────────────────
            $provinceId = null;
            if ($zone === 'province' && $location?->province_id) {
                $provinceId = $location->province_id;
            } elseif ($zone === 'province' && $provinceIds !== []) {
                $provinceId = $provinceIds[array_rand($provinceIds)];
            }

            $orderData = [
                'order_id'             => 'TRX-' . strtoupper(substr(uniqid(), -8)),
                'user_id'              => $user->id,
                'location_id'          => $location?->id,
                'province_id'          => $provinceId,
                'fulfillment_type'     => $isDelivery ? 'delivery' : 'pickup',
                'delivery_zone'        => $zone,
                'payment_method'       => $this->paymentMethods[array_rand($this->paymentMethods)],
                'payment_status'       => rand(0, 10) > 1 ? 'paid' : 'pending',
                'status'               => $this->weightedRandom($this->statuses, $this->statusWeights),
                'subtotal'             => $subtotal,
                'discount_id'          => $discount?->id,
                'discount_code'        => $discount?->code,
                'discount_amount'      => $discountAmount,
                'delivery'             => $delivery,
                'tax'                  => $tax,
                'total'                => $total,
                'shipping'             => $this->shippingSnapshot($user, $location, $isDelivery),
                'created_at'           => $orderDate,
                'updated_at'           => $orderDate,
            ];

            if ($isDelivery && $location) {
                $orderData['delivery_lat']         = $location->lat;
                $orderData['delivery_lng']         = $location->lng;
                $orderData['delivery_map_address'] = $location->address;
            }

            /** @var Order $order */
            $order = Order::create($orderData);

            // ── Items (same cart math → totals stay consistent) ────────────
            (new OrderItemSeeder())->seedForOrder($order, $productLines, $orderDate);

            // ── Stock ledger: move sold units out of inventory ──────────────
            foreach ($productLines as $line) {
                $product = $line['product'];
                if ($product->current_stock !== null && $product->current_stock > 0) {
                    $product->decrementStock($line['qty']);
                }
            }
        }

        $deliveryCount = Order::whereNotNull('delivery_lat')->count();
        $pickupCount   = Order::where('fulfillment_type', 'pickup')->count();
        $itemCount     = OrderItem::count();

        $this->command->info('✅ OrderSeeder:     ' . Order::count() . ' orders (current month).');
        $this->command->info("✅ OrderItemSeeder: {$itemCount} line items.");
        $this->command->info("📍 Delivery orders with coords: {$deliveryCount}  |  📦 Pickup: {$pickupCount}");
    }

    /** Pick 1–3 distinct products with random quantities. */
    private function productLines($products): array
    {
        $picked = $products->random(min(rand(1, 3), $products->count()));
        $lines  = [];

        foreach ($picked as $product) {
            $lines[] = ['product' => $product, 'qty' => rand(1, 3)];
        }

        return $lines;
    }

    private function shippingSnapshot(User $user, ?UserLocation $location, bool $isDelivery): array
    {
        if ($isDelivery && $location) {
            return $location->toShippingArray();
        }

        $city = $location?->city ?? 'Phnom Penh';

        return [
            'name'    => $user->name,
            'phone'   => $this->phone(),
            'address' => 'Tronmatix Store, ' . $city,
            'city'    => $city,
            'country' => 'Cambodia',
            'note'    => 'Store pickup',
        ];
    }

    private function cleanPrice($raw): float
    {
        $clean = preg_replace('/[^0-9.]/', '', (string) $raw);

        return $clean === '' ? 0.0 : (float) $clean;
    }

    private function phone(): string
    {
        $prefixes = ['010', '011', '012', '015', '016', '017', '018', '061', '066', '067',
                     '068', '069', '070', '076', '077', '078', '081', '084', '085', '086',
                     '087', '088', '089', '090', '092', '093', '095', '096', '097', '098', '099'];
        $num = str_pad((string) rand(0, 9999999), 7, '0', STR_PAD_LEFT);

        return $prefixes[array_rand($prefixes)] . ' ' . substr($num, 0, 3) . ' ' . substr($num, 3);
    }

    private function weightedRandom(array $items, array $weights): string
    {
        $rand  = rand(1, array_sum($weights));
        $cumulative = 0;

        foreach ($items as $index => $item) {
            $cumulative += $weights[$index];
            if ($rand <= $cumulative) {
                return $item;
            }
        }

        return $items[0];
    }
}
