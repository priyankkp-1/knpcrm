<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use DateTimeInterface;

class Notes extends Model
{
    use HasFactory;

    protected $table = 'notes';
    protected $fillable = [
        'hash_id',
        'description',
        'rel_type',
        'rel_id',
        'addedfrom',
    ];

    use SoftDeletes;

    protected $dates = ['deleted_at'];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
    
    public function added_from()
    {
        return $this->hasOne(Admin::class,'id','addedfrom');
    }

}
