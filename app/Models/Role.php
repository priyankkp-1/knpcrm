<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use DateTimeInterface;

class Role extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'hash_id',
        'slug',
    ];

    use SoftDeletes;

    protected $dates = ['deleted_at'];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function permissions()
    {

        return $this->belongsToMany(Permission::class, 'roles_permissions');

    }

    public function users()
    {

        return $this->belongsToMany(User::class, 'admins_roles');

    }
}
