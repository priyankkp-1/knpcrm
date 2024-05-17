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

class Mailtemplate extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens, HasPermissionsTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $table = 'email_templates';
    protected $fillable = [
        'hash_id', 'subject', 'message', 'from_name', 'is_plain_text', 'is_active',
    ];

    use SoftDeletes;

    protected $dates = ['deleted_at'];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}