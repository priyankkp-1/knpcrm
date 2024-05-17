<?php
  
namespace App\Models;
  
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;
use DateTimeInterface;

  
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
  
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = 'users';
    protected $fillable = [
        'email',
        'password',
        'customers_id',
        'last_name',
        'country_code',
        'phone_number',
        'last_ip',
        'job_title',
        'is_active',
        'invoice_emails',
        'estimate_emails',
        'credit_note_emails',
        'task_emails',
        'invoice_permissions',
        'estimate_permissions',
        'proposals_permissions',
        'support_permissions',
        'is_primary',
        'profile_img',
        'first_name',
        'project_emails',
        'contract_emails',
        'proposals_emails',
        'support_emails',
        'project_permissions',
        'contract_permissions',
        'last_login',
    ];
  
    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];
  
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    
    use SoftDeletes;

    protected $dates = ['deleted_at'];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}