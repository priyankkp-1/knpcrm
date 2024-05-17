<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use DateTimeInterface;

class Reminders extends Model
{
    use HasFactory;

    protected $table = 'reminders';
    protected $fillable = [
        'hash_id',
        'reminder_date_time',
        'description',
        'rel_type',
        'rel_id',
        'addedfrom',
        'notify_by_email',
        'repeat_frequently',
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
