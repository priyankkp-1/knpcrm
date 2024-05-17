<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use DateTimeInterface;

class TaskCheckList extends Model
{
    use HasFactory;

    protected $table = 'task_checklist';
    protected $fillable = [
        'hash_id',
        'description',
        'task_id',
        'added_from',
        'finished_from',
        'list_order',
        'is_finished',
    ];

    use SoftDeletes;

    protected $dates = ['deleted_at'];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

}
