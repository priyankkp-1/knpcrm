<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WebToLeadStaffNotifyRel extends Model
{
    use HasFactory;

    protected $table = 'web_to_lead_staff_notify_rel';
    protected $fillable = [
        'staff_id',
        'web_to_lead_id',
    ];

}
