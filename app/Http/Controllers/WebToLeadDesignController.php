<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\WebToLead;
use App\Models\Templates;
use Hash;
use Validator;
use Auth;
use Str;
use DB;

class WebToLeadDesignController extends Controller
{

    public  function template_html($form_hash_id)
    {
        $view = request('view');
        $web_lead_form = WebToLead::with(['template' => function($q){
            $q->whereNotNull('body')->where('body','!=','');
        }])->whereHas('template',function($q){
            $q->whereNotNull('body')->where('body','!=','');
        })->where('hash_id',$form_hash_id)->first();
        if(!$web_lead_form && $web_lead_form==null){
            return response()->json(['error' => "Form doesn't exist."]);
        }
        $web_lead_form = $web_lead_form->toArray();
        $json_array = $web_lead_form ? json_decode($web_lead_form['form_data']):[];
        $lead = new LeadController();
        $system_custom_field = $lead->admin_system_custom_field('lead',1);
        $system_field = $system_custom_field['system_field'];
        $custom_field = $system_custom_field['custom_field'];
        $template_id = $web_lead_form ? $web_lead_form['template_id'] :'';
        $button_name = $web_lead_form?$web_lead_form['submit_button'] :'';
        $hash_id = $web_lead_form?$web_lead_form['hash_id'] :'';
        $template = Templates::find($template_id);

        $bodyform = $json_array ? render_template($json_array,$system_field,$custom_field,$hash_id,$button_name):'';
        

        $html = view('templates/'.$web_lead_form['template']['body'])->render();
        $replacearr = [];
        $replacearr['{message_after_success}'] = $web_lead_form ? $web_lead_form['message_after_success'] :'';
        $replacearr['{form_data}'] = $bodyform;
        $replacearr['{header_title}'] = $web_lead_form ? $web_lead_form['header_title'] : $template->header_title;
        $replacearr['{header_front_size}'] = $web_lead_form ? $web_lead_form['header_front_size'] : $template->header_front_size;
        $replacearr['{header_color}'] = $web_lead_form ? $web_lead_form['header_color'] : $template->header_color;
        $replacearr['{header_background_color}'] = $web_lead_form ? $web_lead_form['header_background_color'] : $template->header_background_color;
        $replacearr['{form_name}'] = $web_lead_form ? $web_lead_form['form_name'] :$template->form_title;
        $replacearr['{form_front_size}'] = $web_lead_form ? $web_lead_form['form_front_size'] :$template->form_front_size;
        $replacearr['{form_button_color}'] = $web_lead_form ? $web_lead_form['form_button_color'] : $template->form_button_color;
        $replacearr['{form_button_background_color}'] =  $web_lead_form ? $web_lead_form['form_button_background_color'] : $template->form_button_background_color;
        $replacearr['{footer_title}'] = $web_lead_form ? $web_lead_form['footer_title'] : $template->footer_title;
        $replacearr['{footer_color}'] = $web_lead_form ? $web_lead_form['footer_color'] : $template->footer_color;
        $replacearr['{footer_front_size}'] = $web_lead_form ? $web_lead_form['footer_front_size'] : $template->footer_front_size;
        $replacearr['{footer_background_color}'] =  $web_lead_form ? $web_lead_form['footer_background_color'] : $template->footer_background_color;
        $replacearr['{success_redirect_url}'] =  ($web_lead_form && $web_lead_form['thank_you_page_link']) ? $web_lead_form['thank_you_page_link'] : route('lead_thank_you');
        $html=str_replace(array_keys($replacearr),array_values($replacearr),$html);
        //return $html;
        if($view && $view==1){
            return $html;
        }else{
            return response()->json(['message' => 'Template HTMl','data'=>$html], 200);
        }
    }

}