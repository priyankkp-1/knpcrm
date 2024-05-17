<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use DateTimeInterface;

class Currencies extends Model
{
    use HasFactory;

    protected $table = 'currencies';
    protected $fillable = [
        'name',
        'symbol',
        'decimal_separator',
        'thousand_separator',
        'placement',
        'isdefault',
        'hash_id',
    ];

    use SoftDeletes;

    protected $dates = ['deleted_at'];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}
