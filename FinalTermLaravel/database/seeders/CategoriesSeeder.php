<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategoriesSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'raw ingredients' => 'The raw ingredients used in the production of our products',
            'dry goods' => 'Dry goods used in the production of our products',
            'frozen goods' => 'Frozen goods used in the production of our products',
            'disposables' => 'Disposables used in the production of our products',
            'beverages' => 'Beverages used in the production of our products',
            'condiments' => 'Condiments used in the production of our products',
        ];

        foreach ($categories as $name => $description) {
            Category::firstOrCreate(['name' => $name], ['description' => $description]);
        }
    }
}