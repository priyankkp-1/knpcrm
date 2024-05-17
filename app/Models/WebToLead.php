<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use DateTimeInterface;

class WebToLead extends Model
{
    use HasFactory;

    protected $table = 'web_to_lead';
    protected $fillable = [
        'hash_id',
        'form_name',
        'form_data',
        'status_id',
        'source_id',
        'submit_button',
        'responsible',
        'message_after_success',
        'thank_you_page_link',
        'allow_duplicate_lead_for_entry',
        'mark_as_public',
        'notify_when_lead_import',
        'template_id',
        'footer_background_color',
        'footer_front_size',
        'footer_color',
        'footer_title',
        'form_button_background_color',
        'form_button_color',
        'form_front_size',
        'header_background_color',
        'header_front_size',
        'header_title',
        'header_color',
    ];

    use SoftDeletes;

    protected $dates = ['deleted_at'];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function webleadnotify()
    {
        return $this->belongsToMany(WebToLeadStaffNotifyRel::class, 'staff_id');
    }

    public function webToLeadStaffNotifyRels()
    {
        return $this->belongsToMany(WebToLeadStaffNotifyRel::class, 'web_to_lead_staff_notify_rel','web_to_lead_id','staff_id');
    }

    public function template()
    {
        return $this->hasOne(Templates::class, 'id','template_id');
    }
}
