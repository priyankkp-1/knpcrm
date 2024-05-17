<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanySettings extends Model
{
    use HasFactory;

    protected $fillable = [
        'meta_key', 'meta_value', 'is_default'
    ];
    protected $table = 'company_meta';

}
