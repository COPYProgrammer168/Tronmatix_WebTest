<?php

namespace Database\Seeders;

use App\Models\MarqueeMessage;
use Illuminate\Database\Seeder;

class MarqueeSeeder extends Seeder
{
    public function run(): void
    {
        $messages = [
            [
                'route'   => null,
                'text_en' => '👋 New here? Connect your Telegram in Profile and we\'ll keep you posted on every order — instantly.',
                'text_kh' => 'សួស្តី! អ្នកកំពុងចូលមកលើកដំបូងមែនទេ? ភ្ជាប់ Telegram ក្នុង Profile ដើម្បីទទួលដំណឹងបញ្ជាទិញភ្លាមៗ។',
                'order'   => 1,
            ],
        ];

        foreach ($messages as $msg) {
            MarqueeMessage::create($msg);
        }
    }
}
