<?php

namespace Database\Seeders;

use App\Models\Studio;
use Illuminate\Database\Seeder;

class StudioSeeder extends Seeder
{
    public function run(): void
    {
        // Explicit demo seed: safe to repeat without replacing edited studios.
        foreach ([
            ['name' => 'Тихая комната', 'description' => 'Демонстрационная студия для сольных занятий и работы над новыми идеями.', 'price_per_hour' => '600.00', 'has_piano' => false],
            ['name' => 'Пиано', 'description' => 'Демонстрационная студия с пианино для занятий, вокала и камерных репетиций.', 'price_per_hour' => '900.00', 'has_piano' => true],
            ['name' => 'Большой звук', 'description' => 'Демонстрационное пространство для совместных репетиций и музыкальных экспериментов.', 'price_per_hour' => '1200.00', 'has_piano' => false],
        ] as $studio) {
            Studio::firstOrCreate(['name' => $studio['name']], $studio + ['is_active' => true]);
        }
    }
}
