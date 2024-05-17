<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use DateTimeInterface;

class TaskComments extends Model
{
    use HasFactory;

    protected $table = 'task_comments';
    protected $fillable = [
        'hash_id',
        'content',
        'task_id',
        'staff_id',
        'file_id',
    ];

    use SoftDeletes;

    protected $dates = ['deleted_at'];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function file()
    {
        return $this->hasOne(FileUpload::class,'id','file_id');
    }
}
