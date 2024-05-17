<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use DateTimeInterface;

class CustomFieldValue extends Model
{
    use HasFactory;

    protected $table = 'custom_field_value';
    protected $fillable = [
        'hash_id',
        'field_to',
        'value',
        'field_id',
        'rel_column_id',
    ];

    use SoftDeletes;

    protected $dates = ['deleted_at'];

    public function customField()
    {
        return $this->hasOne(CustomFields::class,'id','field_id');
    }

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
    

}
