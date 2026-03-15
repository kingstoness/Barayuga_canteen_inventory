<?php
namespace Database\Seeders;
use App\Models\Category;
use Illuminate\Database\Seeder;
class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name'        => 'Meals',
                'slug'        => 'meals',
                'description' => 'Full rice meals and viands',
            ],
            [
                'name'        => 'Snacks',
                'slug'        => 'snacks',
                'description' => 'Light bites and finger foods',
            ],
            [
                'name'        => 'Beverages',
                'slug'        => 'beverages',
                'description' => 'Hot and cold drinks',
            ],
            [
                'name'        => 'Desserts',
                'slug'        => 'desserts',
                'description' => 'Sweet treats and pastries',
            ],
            [
                'name'        => 'Combos',
                'slug'        => 'combos',
                'description' => 'Meal + drink bundle deals',
            ],
            [
                'name'        => 'Breakfast',
                'slug'        => 'breakfast',
                'description' => 'Morning specials served until 10am',
            ],
        ];
        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}