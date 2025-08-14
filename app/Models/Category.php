<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama',
        'slug',
        'foto',
    ];

    protected static function booted()
    {
        static::saving(function (Category $category) {
            if (empty($category->slug)) {
                $category->slug = Str::slug($category->nama);
            }

            // pastikan slug unik (tambahkan suffix jika perlu)
            $original = $category->slug;
            $i = 1;
            while (
                Category::where('slug', $category->slug)
                ->when($category->id, fn($q) => $q->where('id', '!=', $category->id))
                ->exists()
            ) {
                $category->slug = $original . '-' . $i++;
            }
        });
    }

    public function obats()
    {
        return $this->hasMany(Obat::class);
    }
}
