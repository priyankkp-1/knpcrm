<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdminRoleRelation extends Model
{
    use HasFactory;

    protected $table = 'admins_roles';


    public function role() {

        return $this->belongsTo(Role::class);
    }
}
