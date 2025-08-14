<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;


class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Admin User',
            'email' => 'gemoyy71jkt@gmail.com',
            'role' => 'admin',
            'password' => Hash::make('mii123'),
            'foto' => 'profile/admin.jpg'
        ]);

        $categories = [
            [
                'nama' => 'Obat Bebas',
                'foto' => 'obat-bebas.jpg'
            ],
            [
                'nama' => 'Obat Bebas Terbatas',
                'foto' => 'obat-bebas-terbatas.png'
            ],
            [
                'nama' => 'Obat Keras',
                'foto' => 'obat-keras.png'
            ],
            [
                'nama' => 'Obat Jamu',
                'foto' => 'obat-jamu.png'
            ],
            [
                'nama' => 'Obat Narkoba',
                'foto' => 'obat-narkoba.png'
            ],
        ];

        Storage::disk('public')->makeDirectory('icon_category');

        foreach ($categories as $category) {
            DB::table('categories')->insert([
                'nama' => $category['nama'],
                'slug' => Str::slug($category['nama']),
                'foto' => 'icon_category/' . $category['foto'],
                'created_at' => now(),
                'updated_at' => now()
            ]);

            $sampleImg = database_path('seeders/category_img/' . $category['foto']);
            $destination = 'icon_category/' . $category['foto'];
            if (file_exists($sampleImg)) {
                Storage::disk('public')->put($destination, File::get($sampleImg));
            }
        }
    }
}
