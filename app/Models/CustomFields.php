<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use DateTimeInterface;

class CustomFields extends Model
{
    use HasFactory;

    protected $table = 'custom_fields';
    protected $fillable = [
        'hash_id',
        'field_to',
        'name',
        'slug',
        'required',
        'type',
        'options',
        'field_order',
        'active',
        'show_on_table',
        'bs_column',
    ];

    use SoftDeletes;

    protected $dates = ['deleted_at'];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

}
