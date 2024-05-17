<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use App\Http\Permissions\HasPermissionsTrait;
use Illuminate\Database\Eloquent\SoftDeletes;
use DateTimeInterface;

class Admin extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens, HasPermissionsTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'first_name', 'last_name', 'facebook', 'linkedin', 'skype', 'country_code', 'phone', 'last_ip', 'last_login', 'email_signature', 'hash_id', 'is_administator', 'is_active', 'email', 'password',
    ];

    use SoftDeletes;

    protected $dates = ['deleted_at'];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function role() {
        return $this->relation()->with('role');
    }

    public function relation() {
        return $this->hasOne(AdminRoleRelation::class, 'admin_id');
    }

    public function profile_img() 
    {
        return $this->hasOne(FileUpload::class,'id','profile_img');
    }

    public function savepermissions()
    {
        return $this->belongsToMany(Permission::class, 'admins_permissions');
    }

    public function saverole()
    {
        return $this->belongsToMany(Role::class, 'admins_roles');
    }

    public function webToLeadStaffNotifyRels()
    {
        return $this->belongsToMany(WebToLeadStaffNotifyRel::class, 'web_to_lead_staff_notify_rel', 'staff_id', 'web_to_lead_id');
    }

    public function taskAssign()
    {
        return $this->belongsToMany(TaskAssign::class, 'task_assigned', 'staff_id', 'task_id');
    }
}