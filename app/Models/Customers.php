<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use DateTimeInterface;

class Customers extends Model
{
    use HasFactory;

    protected $table = 'customers';
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
        'country_id',
        'zip',
        'address',
        'address_1',
        'address_2',
        'email',
        'country_code',
        'phone_number',
        'source_id',
        'lead_id'
    ];

    use SoftDeletes;

    protected $dates = ['deleted_at'];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

}
