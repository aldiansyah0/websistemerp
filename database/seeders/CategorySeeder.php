<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['code' => 'BEV', 'name' => 'Beverages', 'description' => 'Minuman siap jual dan impulse beverages.', 'sort_order' => 1],
            ['code' => 'SNK', 'name' => 'Snacks', 'description' => 'Snack cepat saji dan pelengkap transaksi.', 'sort_order' => 2],
            ['code' => 'HOU', 'name' => 'Household', 'description' => 'Produk kebutuhan rumah tangga harian.', 'sort_order' => 3],
            ['code' => 'PER', 'name' => 'Personal Care', 'description' => 'Produk perawatan tubuh dan kebersihan pribadi.', 'sort_order' => 4],
            ['code' => 'FRE', 'name' => 'Fresh Food', 'description' => 'Produk dingin, chilled, dan shelf life pendek.', 'sort_order' => 5],
        ];

        foreach ($categories as $category) {
            Category::query()->updateOrCreate(
                ['code' => $category['code']],
                [
                    'name' => $category['name'],
                    'slug' => Str::slug($category['name']),
                    'description' => $category['description'],
                    'is_active' => true,
                    'sort_order' => $category['sort_order'],
                ]
            );
        }
    }
}
