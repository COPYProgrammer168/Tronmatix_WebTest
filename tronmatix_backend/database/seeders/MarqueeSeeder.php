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
            [
                'route'   => 'category/search',
                'text_en' => '🔍 Searching for something specific? Connect Telegram for instant stock updates!',
                'text_kh' => '🔍 កំពុងស្វែងរកផលិតផលជាក់លាក់? ភ្ជាប់ Telegram ដើម្បីទទួលដំណឹងស្តុកភ្លាមៗ!',
                'order'   => 1,
            ],
            [
                'route'   => 'cart',
                'text_en' => '🛒 Ready to checkout? Connect Telegram to track your delivery in real-time.',
                'text_kh' => '🛒 រួចរាល់ដើម្បីទូទាត់? ភ្ជាប់ Telegram ដើម្បីតាមដានការដឹកជញ្ជូនរហ័សៗ។',
                'order'   => 1,
            ],
        ];

        foreach ($messages as $msg) {
            MarqueeMessage::create($msg);
        }
    }
}
