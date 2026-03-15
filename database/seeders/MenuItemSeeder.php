<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class MenuItemSeeder extends Seeder
{
    public function run(): void
    {
        $cat = fn(string $name) => Category::where('name', $name)->value('id');

        $items = [
            // Meals
            ['code' => 'P001', 'name' => 'Adobo Rice Meal',           'cat' => 'Meals',     'price' => 55,  'stock' => 50],
            ['code' => 'P002', 'name' => 'Sinigang sa Hipon',          'cat' => 'Meals',     'price' => 75,  'stock' => 30],
            ['code' => 'P003', 'name' => 'Beef Kaldereta',             'cat' => 'Meals',     'price' => 80,  'stock' => 25],
            ['code' => 'P004', 'name' => 'Grilled Liempo',             'cat' => 'Meals',     'price' => 70,  'stock' => 5],   // low stock
            ['code' => 'P005', 'name' => 'Pinakbet Rice Meal',         'cat' => 'Meals',     'price' => 55,  'stock' => 40],
            ['code' => 'P006', 'name' => 'Chicken Tinola',             'cat' => 'Meals',     'price' => 65,  'stock' => 35],
            ['code' => 'P007', 'name' => 'Lechon Kawali',              'cat' => 'Meals',     'price' => 85,  'stock' => 20],

            // Snacks
            ['code' => 'P008', 'name' => 'Hotdog on Stick',            'cat' => 'Snacks',    'price' => 20,  'stock' => 80],
            ['code' => 'P009', 'name' => 'Fish Ball (10 pcs)',          'cat' => 'Snacks',    'price' => 15,  'stock' => 60],
            ['code' => 'P010', 'name' => 'Kwek-Kwek (5 pcs)',           'cat' => 'Snacks',    'price' => 20,  'stock' => 50],
            ['code' => 'P011', 'name' => 'Cheese Bread',                'cat' => 'Snacks',    'price' => 12,  'stock' => 45],
            ['code' => 'P012', 'name' => 'Banana Cue',                  'cat' => 'Snacks',    'price' => 10,  'stock' => 3],   // low stock
            ['code' => 'P013', 'name' => 'Turon (2 pcs)',               'cat' => 'Snacks',    'price' => 15,  'stock' => 30],

            // Beverages
            ['code' => 'P014', 'name' => 'Bottled Water',               'cat' => 'Beverages', 'price' => 15,  'stock' => 100],
            ['code' => 'P015', 'name' => 'Softdrinks (Coke)',            'cat' => 'Beverages', 'price' => 25,  'stock' => 60],
            ['code' => 'P016', 'name' => 'Iced Coffee',                  'cat' => 'Beverages', 'price' => 35,  'stock' => 40],
            ['code' => 'P017', 'name' => 'Milo Drink',                   'cat' => 'Beverages', 'price' => 20,  'stock' => 55],
            ['code' => 'P018', 'name' => 'Fruit Juice (Pineapple)',      'cat' => 'Beverages', 'price' => 20,  'stock' => 0],   // out of stock
            ['code' => 'P019', 'name' => 'Hot Chocolate',                'cat' => 'Beverages', 'price' => 25,  'stock' => 30],
            ['code' => 'P020', 'name' => 'Gulaman at Sago',              'cat' => 'Beverages', 'price' => 15,  'stock' => 45],

            // Desserts
            ['code' => 'P021', 'name' => 'Halo-Halo',                   'cat' => 'Desserts',  'price' => 45,  'stock' => 20],
            ['code' => 'P022', 'name' => 'Buko Pandan',                 'cat' => 'Desserts',  'price' => 30,  'stock' => 25],
            ['code' => 'P023', 'name' => 'Maja Blanca (slice)',          'cat' => 'Desserts',  'price' => 20,  'stock' => 15],
            ['code' => 'P024', 'name' => 'Leche Flan',                  'cat' => 'Desserts',  'price' => 35,  'stock' => 18],
            ['code' => 'P025', 'name' => 'Bibingka (slice)',             'cat' => 'Desserts',  'price' => 25,  'stock' => 12],

            // Breakfast
            ['code' => 'P026', 'name' => 'Tapsilog',                    'cat' => 'Breakfast', 'price' => 65,  'stock' => 30],
            ['code' => 'P027', 'name' => 'Longsilog',                   'cat' => 'Breakfast', 'price' => 60,  'stock' => 25],
            ['code' => 'P028', 'name' => 'Tocilog',                     'cat' => 'Breakfast', 'price' => 60,  'stock' => 20],
            ['code' => 'P029', 'name' => 'Hotsilog',                    'cat' => 'Breakfast', 'price' => 55,  'stock' => 15],
            ['code' => 'P030', 'name' => 'Sinangag + Itlog',            'cat' => 'Breakfast', 'price' => 35,  'stock' => 40],

            // Combos
            ['code' => 'P031', 'name' => 'Budget Meal A (Adobo + Rice + Drinks)', 'cat' => 'Combos', 'price' => 75,  'stock' => 20],
            ['code' => 'P032', 'name' => 'Budget Meal B (Pork + Rice + Gulaman)', 'cat' => 'Combos', 'price' => 70,  'stock' => 20],
            ['code' => 'P033', 'name' => 'Snack Pack (3 fishball + drink)',        'cat' => 'Combos', 'price' => 35,  'stock' => 30],
            ['code' => 'P034', 'name' => 'Breakfast Combo (Tapsilog + Coffee)',    'cat' => 'Combos', 'price' => 85,  'stock' => 15],
            ['code' => 'P035', 'name' => 'Merienda Set (Snack + Gulaman)',         'cat' => 'Combos', 'price' => 30,  'stock' => 25],
        ];
        foreach ($items as $item) {
            Product::create([
                'product_code'        => $item['code'],
                'product_name'        => $item['name'],
                'category_id'         => $cat($item['cat']),
                'price'               => $item['price'],
                'current_stock'       => $item['stock'],
                'is_available'        => $item['stock'] > 0,
                'low_stock_threshold' => 10,
            ]);
        }
    }
}