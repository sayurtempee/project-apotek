<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @property Carbon|null $kadaluarsa
 */
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
        'unit_id'
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

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }
}
