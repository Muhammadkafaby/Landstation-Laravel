<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Database\Seeder;

class ProductCatalogSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'code' => 'coffee',
                'name' => 'Coffee',
                'sort_order' => 10,
            ],
            [
                'code' => 'non-coffee',
                'name' => 'Non Coffee',
                'sort_order' => 20,
            ],
            [
                'code' => 'snacks',
                'name' => 'Snacks',
                'sort_order' => 30,
            ],
        ];

        foreach ($categories as $category) {
            ProductCategory::query()->updateOrCreate(
                ['code' => $category['code']],
                [
                    'name' => $category['name'],
                    'is_active' => true,
                    'sort_order' => $category['sort_order'],
                ],
            );
        }

        $categoryIds = ProductCategory::query()->pluck('id', 'code');

        $products = [
            [
                'category_code' => 'coffee',
                'sku' => 'cafe-americano',
                'name' => 'Americano',
                'price_rupiah' => 18000,
                'cost_rupiah' => 9000,
            ],
            [
                'category_code' => 'coffee',
                'sku' => 'cafe-latte',
                'name' => 'Latte',
                'price_rupiah' => 22000,
                'cost_rupiah' => 11000,
            ],
            [
                'category_code' => 'snacks',
                'sku' => 'cafe-fries',
                'name' => 'French Fries',
                'price_rupiah' => 20000,
                'cost_rupiah' => 10000,
            ],
        ];

        foreach ($products as $product) {
            Product::query()->updateOrCreate(
                ['sku' => $product['sku']],
                [
                    'product_category_id' => $categoryIds[$product['category_code']],
                    'name' => $product['name'],
                    'product_type' => Product::TYPE_CAFE,
                    'price_rupiah' => $product['price_rupiah'],
                    'cost_rupiah' => $product['cost_rupiah'],
                    'is_active' => true,
                ],
            );
        }
    }
}
