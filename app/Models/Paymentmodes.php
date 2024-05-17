<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use DateTimeInterface;

class Paymentmodes extends Model
{
    use HasFactory;

    protected $table = 'paymentmodes';
    protected $fillable = [
        'name',
        'description',
        'show_on_pdf',
        'use_only',
        'selected_by_default',
        'isactive',
        'hash_id',
    ];

    use SoftDeletes;

    protected $dates = ['deleted_at'];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}
