<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use DateTimeInterface;

class Expencescategories extends Model
{
    use HasFactory;

    protected $table = 'expences_categories';
    protected $fillable = [
        'name',
        'description',
        'hash_id',
    ];

    use SoftDeletes;

    protected $dates = ['deleted_at'];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}
