<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use DateTimeInterface;

class Products extends Model
{
    use HasFactory;

    protected $table = 'products';
    protected $fillable = [
        'name',
        'description',
        'rate',
        'unit',
        'tax_id',
        'tax2_id',
        'itemgroup_id',
        'hash_id',
    ];

    use SoftDeletes;

    protected $dates = ['deleted_at'];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }


    public function tax() 
    {
        return $this->hasOne(Taxes::class,'id','tax_id');
    }

    public function tax2() 
    {
        return $this->hasOne(Taxes::class,'id','tax2_id');
    }

    public function itemgroup() 
    {
        return $this->hasOne(ItemGroup::class,'id','itemgroup_id');
    }
}
