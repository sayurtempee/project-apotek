<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Obat extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama',
        'kode',
        'deskripsi',
        'harga',
        'stok',
        'category_id',
        'foto',
        'kadaluarsa',
    ];

    protected $casts = [
        'kadaluarsa' => 'date',
    ];

    // accessor: $obat->is_expired
    public function getIsExpiredAttribute()
    {
        if (!$this->kadaluarsa) {
            return false;
        }

        // kadaluarsa < hari ini => expired
        return $this->kadaluarsa->lt(Carbon::today());
    }


    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
