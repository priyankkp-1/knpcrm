<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use DateTimeInterface;

class Tasks extends Model
{
    use HasFactory;

    protected $table = 'tasks';
    protected $fillable = [
        'hash_id',
        'name',
        'priority',
        'status',
        'start_date',
        'end_date',
        'completed_date',
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

    public function taskAssign()
    {
        return $this->belongsToMany(TaskAssign::class, 'task_assigned', 'task_id','staff_id');
    }

    public function assignedTo()
    {
        return $this->belongsToMany(admin::class, 'task_assigned','task_id','staff_id');
    }

    public function comments()
    {
        return $this->hasMany(TaskComments::class,'task_id','id')->with('file');
    }

    public function checklists()
    {
        return $this->hasMany(TaskCheckList::class,'task_id','id');
    }

}
