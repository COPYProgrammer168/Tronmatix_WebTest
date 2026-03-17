<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        Product::truncate();

        $catalog = [
            'CPU' => [
                'brand' => 'AMD',
                'specs' => ['socket' => 'AM5', 'tdp' => '120W', 'cores' => '8', 'memory' => 'DDR5'],
                'items' => [
                    ['name' => 'AMD Ryzen 7 9800X3D',     'price' => 479,  'is_hot' => true,  'stock' => 25],
                    ['name' => 'AMD Ryzen 9 9950X3D',     'price' => 699,  'is_hot' => true,  'stock' => 15],
                    ['name' => 'Intel Core Ultra 9 285K',  'price' => 589,  'is_hot' => false, 'stock' => 20],
                    ['name' => 'AMD Ryzen 5 9600X',        'price' => 279,  'is_hot' => false, 'stock' => 40],
                ],
            ],
            'RAM' => [
                'brand' => 'Corsair',
                'specs' => ['type' => 'DDR5', 'speed' => '6000MHz', 'voltage' => '1.35V'],
                'items' => [
                    ['name' => 'Corsair Vengeance DDR5 32GB', 'price' => 189, 'is_hot' => false, 'stock' => 35],
                    ['name' => 'G.Skill Trident Z5 64GB',     'price' => 329, 'is_hot' => true,  'stock' => 18],
                    ['name' => 'Kingston Fury Beast 16GB',    'price' => 89,  'is_hot' => false, 'stock' => 50],
                ],
            ],
            'MAINBOARD' => [
                'brand' => 'ASUS',
                'specs' => ['socket' => 'AM5', 'form' => 'ATX', 'chipset' => 'B650', 'pcie' => 'PCIe 5.0'],
                'items' => [
                    ['name' => 'ASUS ROG Strix B650-E Gaming', 'price' => 299, 'is_hot' => true,  'stock' => 22],
                    ['name' => 'MSI MEG Z790 ACE',              'price' => 499, 'is_hot' => false, 'stock' => 12],
                    ['name' => 'Gigabyte B650 AORUS Elite AX',  'price' => 219, 'is_hot' => false, 'stock' => 30],
                ],
            ],
            'COOLING' => [
                'brand' => 'be quiet!',
                'specs' => ['type' => 'Air', 'tdp' => '250W', 'height' => '168mm', 'fans' => '2x140mm'],
                'items' => [
                    ['name' => 'be quiet! Dark Rock Pro 5', 'price' => 99,  'is_hot' => false, 'stock' => 28],
                    ['name' => 'Noctua NH-D15 G2',          'price' => 119, 'is_hot' => true,  'stock' => 20],
                    ['name' => 'NZXT Kraken Elite 360 RGB', 'price' => 229, 'is_hot' => true,  'stock' => 14],
                ],
            ],
            'M2' => [
                'brand' => 'Samsung',
                'specs' => ['interface' => 'PCIe 4.0 NVMe', 'read' => '7450MB/s', 'write' => '6900MB/s'],
                'items' => [
                    ['name' => 'Samsung 990 Pro 2TB',      'price' => 179, 'is_hot' => true,  'stock' => 32],
                    ['name' => 'WD Black SN850X 1TB',      'price' => 99,  'is_hot' => false, 'stock' => 45],
                    ['name' => 'Seagate FireCuda 530 2TB', 'price' => 189, 'is_hot' => false, 'stock' => 26],
                ],
            ],
            'VGA' => [
                'brand' => 'NVIDIA',
                'specs' => ['memory' => '16GB GDDR7', 'bus' => '256-bit', 'pcie' => 'PCIe 5.0'],
                'items' => [
                    ['name' => 'NVIDIA GeForce RTX 5080',  'price' => 999,  'is_hot' => true,  'stock' => 8],
                    ['name' => 'NVIDIA GeForce RTX 4090',  'price' => 1599, 'is_hot' => false, 'stock' => 5],
                    ['name' => 'AMD Radeon RX 7900 XTX',   'price' => 799,  'is_hot' => false, 'stock' => 10],
                    ['name' => 'NVIDIA RTX 4070 Ti Super', 'price' => 599,  'is_hot' => true,  'stock' => 15],
                ],
            ],
            'CASE' => [
                'brand' => 'Lian Li',
                'specs' => ['form' => 'Mid Tower', 'material' => 'Aluminum/Glass', 'fans' => '3x120mm'],
                'items' => [
                    ['name' => 'Lian Li PC-O11 Dynamic EVO', 'price' => 149, 'is_hot' => true,  'stock' => 20],
                    ['name' => 'Fractal Design Torrent RGB',  'price' => 189, 'is_hot' => false, 'stock' => 16],
                    ['name' => 'NZXT H9 Elite',               'price' => 229, 'is_hot' => false, 'stock' => 12],
                ],
            ],
            'POWER SUPPLY' => [
                'brand' => 'EVGA',
                'specs' => ['wattage' => '1000W', 'efficiency' => '80+ Gold', 'modular' => 'Full Modular'],
                'items' => [
                    ['name' => 'EVGA SuperNOVA 1000W G7',    'price' => 179, 'is_hot' => false, 'stock' => 22],
                    ['name' => 'Seasonic Prime TX-1000',      'price' => 229, 'is_hot' => false, 'stock' => 18],
                    ['name' => 'be quiet! Dark Power Pro 13', 'price' => 199, 'is_hot' => true,  'stock' => 14],
                ],
            ],
            'MONITOR' => [
                'brand' => 'ASUS',
                'specs' => ['resolution' => '2560x1440', 'refresh' => '165Hz', 'panel' => 'IPS'],
                'items' => [
                    ['name' => 'ASUS ROG Swift PG27AQN', 'price' => 799, 'is_hot' => true,  'stock' => 12],
                    ['name' => 'LG UltraGear 27GP950-B', 'price' => 649, 'is_hot' => false, 'stock' => 16],
                    ['name' => 'Samsung Odyssey G7 32"', 'price' => 549, 'is_hot' => true,  'stock' => 10],
                ],
            ],
            'KEYBOARD' => [
                'brand' => 'Corsair',
                'specs' => ['type' => 'Mechanical', 'switch' => 'Cherry MX Red', 'backlight' => 'RGB'],
                'items' => [
                    ['name' => 'Corsair K100 RGB',        'price' => 229, 'is_hot' => true,  'stock' => 25],
                    ['name' => 'Razer BlackWidow V4 Pro', 'price' => 199, 'is_hot' => false, 'stock' => 30],
                    ['name' => 'Logitech G915 TKL',       'price' => 169, 'is_hot' => false, 'stock' => 22],
                ],
            ],
            'MOUSE' => [
                'brand' => 'Logitech',
                'specs' => ['dpi' => '25600', 'buttons' => '11', 'wireless' => 'Yes'],
                'items' => [
                    ['name' => 'Logitech G Pro X Superlight 2',  'price' => 159, 'is_hot' => true,  'stock' => 35],
                    ['name' => 'Razer DeathAdder V3 HyperSpeed', 'price' => 99,  'is_hot' => false, 'stock' => 28],
                    ['name' => 'SteelSeries Rival 650',          'price' => 119, 'is_hot' => false, 'stock' => 20],
                ],
            ],
        ];

        $count = 0;

        foreach ($catalog as $category => $data) {
            foreach ($data['items'] as $index => $item) {
                Product::create([
                    'name' => $item['name'],
                    'description' => 'High-performance '.$category
                                   .' engineered for gaming and professional workloads. '
                                   .'Features cutting-edge architecture with exceptional performance.',
                    'price' => $item['price'],
                    'category' => $category,
                    'brand' => $data['brand'],

                    'images' => [
                        'https://picsum.photos/seed/'.md5($item['name']).'/400/400',
                        'https://picsum.photos/seed/'.md5($item['name'].'_b').'/400/400',
                        'https://picsum.photos/seed/'.md5($item['name'].'_c').'/400/400',
                    ],
                    // Pass specs as plain array — 'specs' cast: 'array' handles encoding
                    'specs' => $data['specs'],
                    'stock' => $item['stock'],
                    'rating' => number_format(3.5 + mt_rand(0, 15) / 10, 1),
                    'is_featured' => $index === 0,
                    'is_hot' => $item['is_hot'],
                ]);

                $count++;
            }
        }

        $this->command->info("✅ Products seeded: {$count} products across ".count($catalog).' categories');
    }
}
