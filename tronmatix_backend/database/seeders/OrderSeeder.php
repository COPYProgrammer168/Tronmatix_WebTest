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

    private array $statuses = ['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled'];

    // Weight toward confirmed / shipped / delivered
    private array $statusWeights = [5, 20, 15, 20, 30, 10];

    private array $payments = ['cash', 'bakong'];

    private array $fulfillmentTypes = ['delivery', 'pickup'];

    // ── Real Cambodian location coordinates ────────────────────────────────────
    private array $mapLocations = [
        // Phnom Penh
        ['address' => 'St. 310, Boeng Keng Kang I, Phnom Penh',       'lat' => 11.547, 'lng' => 104.919],
        ['address' => 'Norodom Blvd, Chamkarmon, Phnom Penh',         'lat' => 11.556, 'lng' => 104.928],
        ['address' => 'Monivong Blvd, 7 Makara, Phnom Penh',          'lat' => 11.565, 'lng' => 104.913],
        ['address' => 'Russian Federation Blvd, Toul Kork, Phnom Penh','lat' => 11.574, 'lng' => 104.896],
        ['address' => 'St. 2004, Chroy Changvar, Phnom Penh',         'lat' => 11.601, 'lng' => 104.930],
        ['address' => 'St. 271, Tuol Kork, Phnom Penh',               'lat' => 11.562, 'lng' => 104.878],
        ['address' => 'Kampuchea Krom Blvd, Meanchey, Phnom Penh',    'lat' => 11.539, 'lng' => 104.945],
        ['address' => 'St. 1003, Dangkao, Phnom Penh',                'lat' => 11.509, 'lng' => 104.889],
        ['address' => 'St. 163, Toul Svay Prey, Phnom Penh',          'lat' => 11.546, 'lng' => 104.908],
        ['address' => 'Confederation de la Russie Blvd, Phnom Penh',  'lat' => 11.570, 'lng' => 104.903],
        // Siem Reap
        ['address' => 'Pub Street, Siem Reap',                        'lat' => 13.362, 'lng' => 103.859],
        ['address' => 'Sivatha Blvd, Siem Reap',                      'lat' => 13.358, 'lng' => 103.853],
        ['address' => 'Airport Road, Siem Reap',                      'lat' => 13.369, 'lng' => 103.843],
        // Battambang
        ['address' => 'St. 1, Battambang',                            'lat' => 13.102, 'lng' => 103.198],
        ['address' => 'Riverside Road, Battambang',                   'lat' => 13.095, 'lng' => 103.196],
        ['address' => 'Battambang Market',                            'lat' => 13.105, 'lng' => 103.200],
        // Sihanoukville
        ['address' => 'Ekareach St, Sihanoukville',                   'lat' => 10.632, 'lng' => 103.523],
        ['address' => 'Victory Beach Road, Sihanoukville',            'lat' => 10.649, 'lng' => 103.493],
        ['address' => 'Otres Beach Road, Sihanoukville',              'lat' => 10.605, 'lng' => 103.527],
        // Kampong Cham
        ['address' => 'St. 5, Kampong Cham',                          'lat' => 12.001, 'lng' => 105.463],
        ['address' => 'Kampong Cham Riverside',                       'lat' => 11.998, 'lng' => 105.465],
        // Kampot
        ['address' => 'Old Market Area, Kampot',                      'lat' => 10.611, 'lng' => 104.179],
        ['address' => 'Kampot Riverside',                             'lat' => 10.609, 'lng' => 104.182],
        // Kratie
        ['address' => 'Kratie Riverfront, Kratie',                    'lat' => 12.481, 'lng' => 106.019],
        // Takeo
        ['address' => 'Central Market, Takeo',                        'lat' => 10.983, 'lng' => 104.783],
        // Kampong Speu
        ['address' => 'Kampong Speu Town Center',                     'lat' => 11.453, 'lng' => 104.521],
        // Pursat
        ['address' => 'Pursat Market Road, Pursat',                   'lat' => 12.538, 'lng' => 103.919],
        // Prey Veng
        ['address' => 'Prey Veng Town Center',                       'lat' => 11.484, 'lng' => 105.324],
        // Svay Rieng
        ['address' => 'Svay Rieng Market',                           'lat' => 11.082, 'lng' => 105.799],
        // Banteay Meanchey
        ['address' => 'Poipet Town Center, Banteay Meanchey',         'lat' => 13.656, 'lng' => 102.562],
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

        // ── Create 120+ orders spread across the last 12 months ──────────────
        $orderCount = 120;

        for ($i = 0; $i < $orderCount; $i++) {
            $orderDate = Carbon::now()
                ->subMonths(rand(0, 11))
                ->subDays(rand(0, 28))
                ->subHours(rand(0, 23))
                ->subMinutes(rand(0, 59));

            $user = $users->random();
            $locations = $user->locations;
            $isDelivery = rand(0, 3) > 0; // 75% delivery, 25% pickup

            // ── Pick a map location (from saved location or random) ──────────
            $mapLoc = null;
            $city = 'Phnom Penh';
            $shippingAddress = null;

            if ($isDelivery && $locations->isNotEmpty()) {
                // Use user's saved location if available
                $savedLoc = $locations->random();
                $mapLoc = [
                    'lat' => $savedLoc->lat ?? $this->mapLocations[array_rand($this->mapLocations)]['lat'],
                    'lng' => $savedLoc->lng ?? $this->mapLocations[array_rand($this->mapLocations)]['lng'],
                    'address' => $savedLoc->address,
                ];
                $city = $savedLoc->city ?? 'Phnom Penh';
                $shippingAddress = [
                    'name'    => $savedLoc->name ?? $user->name,
                    'phone'   => $savedLoc->phone ?? ('0' . rand(10, 99) . ' ' . rand(100, 999) . ' ' . rand(100, 999)),
                    'address' => $savedLoc->address,
                    'city'    => $city,
                    'country' => 'Cambodia',
                    'note'    => $savedLoc->note,
                ];
            } elseif ($isDelivery) {
                // Fall back to random map location
                $randLoc = $this->mapLocations[array_rand($this->mapLocations)];
                $mapLoc = [
                    'lat'     => $randLoc['lat'] + (rand(-30, 30) / 1000),
                    'lng'     => $randLoc['lng'] + (rand(-30, 30) / 1000),
                    'address' => $randLoc['address'],
                ];
                // Extract city from address
                $parts = explode(', ', $randLoc['address']);
                $city = end($parts);
                $shippingAddress = [
                    'name'    => $user->name,
                    'phone'   => '0' . rand(10, 99) . ' ' . rand(100, 999) . ' ' . rand(100, 999),
                    'address' => $randLoc['address'],
                    'city'    => $city,
                    'country' => 'Cambodia',
                    'note'    => rand(0, 1) ? 'Call before delivery' : null,
                ];
            } else {
                // Pickup — no location needed
                $city = $this->cities()[array_rand($this->cities())];
                $shippingAddress = [
                    'name'    => $user->name,
                    'phone'   => '0' . rand(10, 99) . ' ' . rand(100, 999) . ' ' . rand(100, 999),
                    'address' => 'Tronmatix Store, ' . $city,
                    'city'    => $city,
                    'country' => 'Cambodia',
                    'note'    => 'Store pickup',
                ];
            }

            // ── Pick 1–3 products ───────────────────────────────────────────
            $pickedProducts = $products->random(min(rand(1, 3), $products->count()));
            $subtotal = 0;
            $lineItems = [];

            foreach ($pickedProducts as $product) {
                $qty = rand(1, 3);
                $subtotal += (float) $product->price * $qty;
                $lineItems[] = ['product' => $product, 'qty' => $qty];
            }

            // ── Apply random discount ────────────────────────────────────────
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

            $delivery = ($isDelivery && $subtotal <= 100) ? 5.00 : 0;
            $tax = round(($subtotal - $discountAmount) * 0.1, 2);
            $total = round($subtotal - $discountAmount + $delivery + $tax, 2);
            $subtotal = round($subtotal, 2);

            // ── Build order data ────────────────────────────────────────────
            $orderData = [
                'order_id'           => 'TRX-' . strtoupper(substr(uniqid(), -8)),
                'user_id'            => $user->id,
                'location_id'        => $locations->isNotEmpty() ? $locations->random()->id : null,
                'fulfillment_type'   => $isDelivery ? 'delivery' : 'pickup',
                'payment_method'     => $this->payments[array_rand($this->payments)],
                'payment_status'     => rand(0, 10) > 1 ? 'paid' : 'pending',
                'status'             => $this->weightedRandom($this->statuses, $this->statusWeights),
                'subtotal'           => $subtotal,
                'discount_id'        => $discount?->id,
                'discount_code'      => $discount?->code,
                'discount_amount'    => $discountAmount,
                'delivery'           => $delivery,
                'tax'                => $tax,
                'total'              => $total,
                'shipping'           => $shippingAddress,
                'created_at'         => $orderDate,
                'updated_at'         => $orderDate,
            ];

            // ── Add delivery coordinates (for map display) ──────────────────
            if ($isDelivery && $mapLoc) {
                $orderData['delivery_lat']         = $mapLoc['lat'];
                $orderData['delivery_lng']         = $mapLoc['lng'];
                $orderData['delivery_map_address'] = $mapLoc['address'];
            }

            // ── Create order ────────────────────────────────────────────────
            $order = Order::create($orderData);

            // ── Create order items ──────────────────────────────────────────
            foreach ($lineItems as $line) {
                $cleanPrice = preg_replace('/[^0-9.]/', '', (string) $line['product']->price);
                $price = ($cleanPrice === '') ? 0 : (float) $cleanPrice;

                OrderItem::create([
                    'order_id'    => $order->id,
                    'product_id'  => $line['product']->id,
                    'name'        => $line['product']->name,
                    'price'       => $price,
                    'qty'         => $line['qty'],
                    'image'       => $line['product']->image ?? null,
                    'created_at'  => $orderDate,
                    'updated_at'  => $orderDate,
                ]);
            }
        }

        // ── Report ──────────────────────────────────────────────────────────
        $deliveryOrders = Order::whereNotNull('delivery_lat')->count();
        $pickupOrders   = Order::where('fulfillment_type', 'pickup')->count();

        $this->command->info('✅ OrderSeeder:     ' . Order::count() . ' orders');
        $this->command->info('✅ OrderItemSeeder: ' . OrderItem::count() . ' items');
        $this->command->info("📍 Delivery orders with map coordinates: {$deliveryOrders}");
        $this->command->info("📦 Pickup orders: {$pickupOrders}");
    }

    private function cities(): array
    {
        return [
            'Phnom Penh', 'Siem Reap', 'Battambang', 'Kampong Cham', 'Kampot',
            'Sihanoukville', 'Kratie', 'Pursat', 'Takeo', 'Prey Veng',
            'Svay Rieng', 'Stung Treng', 'Pailin', 'Kep', 'Koh Kong',
            'Kampong Speu', 'Kampong Chhnang', 'Kampong Thom', 'Oddar Meanchey', 'Banteay Meanchey',
        ];
    }

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
