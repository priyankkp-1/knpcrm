<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use DateTimeInterface;

class Templates extends Model
{
    use HasFactory;
    protected $table = 'templates';

    protected $fillable = [
        'header_title', 'header_color', 'header_front_size','header_background_color','form_title','form_front_size','form_button_color','form_button_background_color','footer_title','footer_color','footer_front_size','footer_background_color'
    ];

    use SoftDeletes;

    protected $dates = ['deleted_at'];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

}
