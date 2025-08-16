<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Unit;

class UnitSeeder extends Seeder
{
    public function run(): void
    {
        $units = ['Tablet', 'Kapsul', 'Botol', 'Strip', 'Box', 'Tube', 'Ampul'];

        foreach ($units as $u) {
            Unit::firstOrCreate(['name' => $u]);
        }
    }
}
