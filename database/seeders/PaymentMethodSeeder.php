<?php

namespace Database\Seeders;

use App\Models\PaymentMethod;
use Illuminate\Database\Seeder;

class PaymentMethodSeeder extends Seeder
{
    public function run(): void
    {
        $methods = [
            [
                'code' => PaymentMethod::CASH,
                'name' => 'Cash',
                'channel' => 'cash',
                'sort_order' => 10,
                'metadata_json' => null,
            ],
            [
                'code' => PaymentMethod::QRIS_MANUAL,
                'name' => 'QRIS Manual',
                'channel' => 'qris',
                'sort_order' => 20,
                'metadata_json' => [
                    'mode' => 'manual-static-qr',
                ],
            ],
        ];

        foreach ($methods as $method) {
            PaymentMethod::query()->updateOrCreate(
                ['code' => $method['code']],
                [
                    'name' => $method['name'],
                    'channel' => $method['channel'],
                    'is_active' => true,
                    'sort_order' => $method['sort_order'],
                    'metadata_json' => $method['metadata_json'],
                ],
            );
        }
    }
}
