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

    private array $shippingNames = [
        ['name' => 'Sokha Chea'],
        ['name' => 'Dara Ly'],
        ['name' => 'Chanthy Kim'],
        ['name' => 'Visal Peng'],
        ['name' => 'Sreymom Noun'],
        ['name' => 'Bopha Sann'],
        ['name' => 'Rathana Hok'],
        ['name' => 'Kimheng Tep'],
    ];

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
    ];

    public function run(): void
    {
        $users = User::with('locations')->get();
        $products = Product::all();

        if ($products->isEmpty()) {
            $this->command->warn('⚠️  No products found — run ProductSeeder first.');

            return;
        }

        // ── Create 40 orders spread across users ──────────────────────────────
        for ($i = 0; $i < 40; $i++) {

            $orderDate = Carbon::now()
                ->subMonths(rand(0, 11))
                ->subDays(rand(0, 28))
                ->subHours(rand(0, 23));

            $user = $users->random();
            $locations = $user->locations;
            $nameInfo = $this->shippingNames[array_rand($this->shippingNames)];
            $city = $this->cities[array_rand($this->cities)];

            // ── Resolve location_id ───────────────────────────────────────────
            // Use the user's real location record when available so that
            // $order->location relationship works in the dashboard.
            // Fall back to null (shipping JSON only) if user has none.
            $location = $locations->isNotEmpty() ? $locations->random() : null;
            $locationId = $location?->id;

            // Build shipping snapshot (cast 'array' on Order model — no json_encode needed)
            $shipping = [
                'name' => $location?->name ?? $nameInfo['name'],
                'phone' => $location?->phone ?? ('0'.rand(10, 19).' '.rand(100, 999).' '.rand(100, 999)),
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
                $subtotal += $product->price * $qty;
                $lineItems[] = ['product' => $product, 'qty' => $qty];
            }

            $delivery = $subtotal > 100 ? 0 : 5.00;
            $tax = round($subtotal * 0.1, 2);
            $total = round($subtotal + $delivery + $tax, 2);
            $subtotal = round($subtotal, 2);

            // ── Create order ──────────────────────────────────────────────────
            $order = Order::create([
                'order_id' => 'TRX-'.strtoupper(substr(uniqid(), -8)),
                'user_id' => $user->id,
                'location_id' => $locationId,   // ← fixed: was missing entirely
                'payment_method' => $this->payments[array_rand($this->payments)],
                'status' => $this->weightedRandom($this->statuses, $this->statusWeights),
                'subtotal' => $subtotal,
                'tax' => $tax,
                'delivery' => $delivery,
                'total' => $total,
                'shipping' => $shipping,     // ← fixed: pass array, not json_encode()
                'created_at' => $orderDate,
                'updated_at' => $orderDate,
            ]);

            // ── Create order items ────────────────────────────────────────────
            foreach ($lineItems as $line) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $line['product']->id,
                    'name' => $line['product']->name,
                    'price' => $line['product']->price,
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
