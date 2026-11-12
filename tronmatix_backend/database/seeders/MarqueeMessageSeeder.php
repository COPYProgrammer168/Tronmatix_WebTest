<?php

// database/seeders/MarqueeMessageSeeder.php

namespace Database\Seeders;

use App\Models\MarqueeMessage;
use Illuminate\Database\Seeder;

class MarqueeMessageSeeder extends Seeder
{
    /**
     * Seed the default Telegram connect marquee messages.
     *
     * Route == null means "all pages" (fallback).
     * Idempotent — skips rows that already exist.
     */
    public function run(): void
    {
        $messages = [
            [
                'route'     => null,
                'text_en'   => 'Connect your Telegram to receive order notifications instantly',
                'text_kh'   => 'ភ្ជាប់ទូរស័ព្ទរបស់អ្នកទៅ Telegram ដើម្បីទទួលបានសារជូនដំណឹងប្រចាំពាក្យបញ្ជាទិញ',
                'is_active' => true,
                'order'     => 0,
            ],
        ];

        foreach ($messages as $msg) {
            // Skip if a global (route=null) active message already exists
            if (MarqueeMessage::whereNull('route')->where('is_active', true)->exists()) {
                continue;
            }

            MarqueeMessage::create($msg);
        }
    }
}
