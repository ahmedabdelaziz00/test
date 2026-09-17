<?php

namespace Database\Seeders;

use App\Models\Website;
use Illuminate\Database\Seeder;

class WebsiteSeeder extends Seeder
{
    public function run(): void
    {
        $websites = [
            ['name' => 'TechCrunch',  'url' => 'https://techcrunch.com'],
            ['name' => 'The Verge',   'url' => 'https://theverge.com'],
            ['name' => 'Hacker News', 'url' => 'https://news.ycombinator.com'],
        ];

        foreach ($websites as $website) {
            Website::firstOrCreate(['url' => $website['url']], $website);
        }
    }
}
