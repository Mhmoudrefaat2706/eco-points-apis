<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategoriesTableSeeder extends Seeder
{
    public function run()
    {
        $categories = [
            'Metal',
            'Wood',
            'Fabric',
            'Plastic',
            'Stone',
            'Elastomer',
            'Ceramic',
            'Composite',
            'Construction',
            'Other'
        ];

        foreach ($categories as $category) {
            Category::create(['name' => $category]);
        }
    }
}
