<?php

// database/seeders/OrderSeeder.php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    // ── Config ────────────────────────────────────────────────────────────────

    // Statuses match OrderController validation + dashboard blade <select>
    private array $statuses = ['pending', 'paid', 'shipped', 'delivered', 'cancelled'];

    private array $statusWeights = [10, 30, 20, 30, 10]; // weighted toward paid/delivered

    // Payment methods match OrderController validation: in:cash,bakong
    private array $payments = ['cash', 'bakong'];

    // Fulfillment types match Order::FULFILLMENT_* constants
    private array $fulfillmentTypes = ['delivery', 'pickup'];

    private array $streets = [
        'St. 310, Boeng Keng Kang I',
        'Norodom Blvd, Chamkarmon',
        'Monivong Blvd, 7 Makara',
        'Russian Federation Blvd, Toul Kork',
        'St. 2004, Chroy Changvar',
        'National Road 4, Por Sen Chey',
    ];

    private array $cities = [
        'Phnom Penh', 'Siem Reap', 'Battambang', 'Kampong Cham', 'Kampot',
        'Sihanoukville', 'Kratie', 'Pursat', 'Takeo', 'Prey Veng',
        'Svay Rieng', 'Stung Treng', 'Pailin', 'Kep', 'Koh Kong',
        'Kampong Speu', 'Kampong Chhnang', 'Kampong Thom', 'Oddar Meanchey', 'Banteay Meanchey'
    ];

    public function run(): void
    {
        $users = User::with('locations')->get();
        $products = Product::all();
        $discounts = \App\Models\Discount::where('is_active', true)->get();

        if ($discounts->isEmpty()) {
            $this->command->warn('⚠️  No active discounts found — check DiscountSeeder.');
        } else {
            $this->command->info('✅ ' . $discounts->count() . ' active discounts loaded.');
        }

        if ($products->isEmpty()) {
            $this->command->warn('⚠️  No products found — run ProductSeeder first.');

            return;
        }

        // ── Create 100 orders spread across the last 12 months ────────────────
        for ($i = 0; $i < 100; $i++) {

            $orderDate = Carbon::now()
                ->subMonths(rand(0, 11))
                ->subDays(rand(0, 28))
                ->subHours(rand(0, 23))
                ->subMinutes(rand(0, 59));

            $user = $users->random();
            $locations = $user->locations;
            $city = $this->cities[array_rand($this->cities)];

            // ── Resolve location_id ───────────────────────────────────────────
            $location = $locations->isNotEmpty() ? $locations->random() : null;
            $locationId = $location?->id;

            // Build shipping snapshot (cast 'array' on Order model — no json_encode needed)
            $shipping = [
                'name' => $location?->name ?? $user->name, // Use user's name if location name not available
                'phone' => $location?->phone ?? ('0'.rand(10, 99).' '.rand(100, 999).' '.rand(100, 999)),
                'address' => $location?->address ?? $this->streets[array_rand($this->streets)],
                'city' => $location?->city ?? $city,
                'country' => $location?->country ?? 'Cambodia',
                'note' => $location?->note ?? null,
            ];

            // ── Pick 1–3 products ─────────────────────────────────────────────
            $pickedProducts = $products->random(min(rand(1, 3), $products->count()));
            $subtotal = 0;
            $lineItems = [];

            foreach ($pickedProducts as $product) {
                $qty = rand(1, 3);
                $subtotal += (float) $product->price * $qty;
                $lineItems[] = ['product' => $product, 'qty' => $qty];
            }

            // ── Apply random discount ─────────────────────────────────────────
            $discount = ($discounts->isNotEmpty() && rand(0, 1)) ? $discounts->random() : null;
            $discountAmount = 0;
            if ($discount && $subtotal >= $discount->min_order) {
                if ($discount->type === 'percentage') {
                    $discountAmount = round($subtotal * ($discount->value / 100), 2);
                } else {
                    $discountAmount = min($discount->value, $subtotal);
                }
            } else {
                $discount = null;
            }

            $delivery = $subtotal > 100 ? 0 : 5.00;
            $tax = round(($subtotal - $discountAmount) * 0.1, 2);
            $total = round($subtotal - $discountAmount + $delivery + $tax, 2);
            $subtotal = round($subtotal, 2);

            // ── Create order ──────────────────────────────────────────────────
            $order = Order::create([
                'order_id' => 'TRX-'.strtoupper(substr(uniqid(), -8)),
                'user_id' => $user->id,
                'location_id' => $locationId,
                'fulfillment_type' => $this->fulfillmentTypes[array_rand($this->fulfillmentTypes)],
                'payment_method' => $this->payments[array_rand($this->payments)],
                'status' => $this->weightedRandom($this->statuses, $this->statusWeights),
                'subtotal' => $subtotal,
                'discount_id' => $discount?->id,
                'discount_code' => $discount?->code,
                'discount_amount' => $discountAmount,
                'tax' => $tax,
                'delivery' => $delivery,
                'total' => $total,
                'shipping' => $shipping,
                'created_at' => $orderDate,
                'updated_at' => $orderDate,
            ]);

            // ── Create order items ────────────────────────────────────────────
            foreach ($lineItems as $line) {
                // Remove non-numeric characters except for the decimal point
                $cleanPrice = preg_replace('/[^0-9.]/', '', (string) $line['product']->price);

                // If the resulting price is empty or non-numeric, default to 0
                $price = ($cleanPrice === '') ? 0 : (float) $cleanPrice;

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $line['product']->id,
                    'name' => $line['product']->name,
                    'price' => $price,
                    'qty' => $line['qty'],
                    'image' => $line['product']->image ?? null,
                    'created_at' => $orderDate,
                    'updated_at' => $orderDate,
                ]);
            }
        }

        $this->command->info('✅ OrderSeeder:     '.Order::count().' orders');
        $this->command->info('✅ OrderItemSeeder: '.OrderItem::count().' items');
    }

    // ── Weighted random picker ────────────────────────────────────────────────
    private function weightedRandom(array $items, array $weights): string
    {
        $rand = rand(1, array_sum($weights));
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
