<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use DateTimeInterface;

class Announcement extends Model
{
    use HasFactory;

    protected $table = 'announcements';
    protected $fillable = [
        'name',
        'statusorder',
        'message',
        'showtousers',
        'showtostaff',
        'showname',
        'username',
        'hash_id',
    ];

    use SoftDeletes;

    protected $dates = ['deleted_at'];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}
