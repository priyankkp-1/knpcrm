<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use DateTimeInterface;

class Leads extends Model
{
    use HasFactory;

    protected $table = 'leads';
    protected $fillable = [
        'hash_id',
        'first_name',
        'last_name',
        'company',
        'job_title',
        'assigned',
        'status_id',
        'city',
        'state',
        'zip',
        'address',
        'address_1',
        'address_2',
        'email',
        'country_code',
        'phone',
        'description',
        'is_action',
        'country_id',
        'source_id',
        'lastContact',
        'action_date_time',
        'date_customer_converted',
        'is_public',
        'web_to_lead_id'
    ];

    use SoftDeletes;

    protected $dates = ['deleted_at'];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function customFieldValues()
    {
        return $this->hasMany(CustomFieldValue::class, 'rel_column_id');
    }

}
