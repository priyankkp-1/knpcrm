<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TaskAssign extends Model
{
    use HasFactory;

    protected $table = 'task_assigned';
    protected $fillable = [
        'staff_id',
        'task_id',
    ];

}
