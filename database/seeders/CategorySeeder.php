<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $defaults = [
            'Obat Bebas',
            'Obat Bebas Terbatas',
            'Obat Keras',
            'Obat Jamu',
        ];

        foreach ($defaults as $name) {
            Category::firstOrCreate(
                ['nama' => $name],
                ['slug' => \Illuminate\Support\Str::slug($name)]
            );
        }
    }
}
