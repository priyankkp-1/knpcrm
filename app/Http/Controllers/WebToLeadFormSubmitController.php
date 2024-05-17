<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use App\Models\WebToLead;
use App\Models\CustomFields;
use App\Models\WebToLeadStaffNotifyRel;
use App\Models\Admin;
use App\Models\Source;
use App\Models\Status;
use App\Models\Leads;
use Hash;
use Illuminate\Support\Facades\Validator;
use Str;
use DB;
use MailHandler;

class WebToLeadFormSubmitController extends Controller
{
    public function create(Request $request)
    {
        try{
            // dd($request->all());
            $validator = Validator::make($request->all(), [
                'form_data'        => 'required',
                'hash_id'          => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()->all()]);
            }

            $rec=WebToLead::where('hash_id',request('hash_id'))->first();
            if (!$rec) {
                return response()->json(['error' => 'Something went to wrong while saving form.']);
            }

            DB::beginTransaction();
            $request = request()->all();
            $record = [
                'hash_id' => getHashid(),
                'status_id' => $rec->status_id,
                'source_id' => $rec->source_id,
                'assigned' =>  ($rec->responsible)?$rec->responsible:NULL,
            ];
            $form_data = request('form_data');
            $json_array = $form_data?(is_array($form_data)?$form_data:json_decode($form_data,1)):[];
            
            $systemField = ($json_array && $json_array['system_field'])?$json_array['system_field']:[];
            $customField = ($json_array && $json_array['custom_field'])?$json_array['custom_field']:[];
            $systemFieldKeys = ($json_array && $json_array['system_field'])?array_keys($json_array['system_field']):[];
            $systemFieldValues = ($json_array && $json_array['system_field']) ?array_values($json_array['system_field']):[];
            $customFieldKeys = ($json_array && $json_array['custom_field'])?array_keys($json_array['custom_field']):[];
            $customFieldValues = ($json_array && $json_array['custom_field']) ?array_values($json_array['custom_field']):[];

            $validatorRules = [];
            $input = [];
            $tableName = 'leads';
            $customFieldKeyValue = [];
            $columnListing = Schema::getColumnListing($tableName);
            $defaultSystemField = systemInBuildField();
            $systemFieldRec = $defaultSystemField["lead"]['system_field'];

            foreach ($systemFieldKeys as $key=>$val) {
               $systemValues = $systemFieldValues[$key];
                if (isset($systemFieldRec) && $systemFieldRec[$val]) {
                    if ($systemFieldRec &&  $systemFieldRec[$val] && $systemFieldRec[$val]['validation']) {
                        if($val=='email' && $rec->allow_duplicate_lead_for_entry==1){
                            $validatorRules[$val] = 'required|email|max:200';
                        }else{
                            $validatorRules[$val] = $systemFieldRec[$val]['validation'];
                        }
                    } 
                }
            }


            $lead=new LeadController();
            $system_custom_field=$lead->admin_system_custom_field('lead',1);

            if ($customFieldKeys && $system_custom_field) {
                foreach ($customFieldKeys as $key=>$val) {
                    $currentfield = $system_custom_field['custom_field']?$system_custom_field['custom_field'][$val]:'';
                    $slug = $currentfield ? $currentfield['slug'] : '';
                    $customValues = $customFieldValues[$key];

                    if ($currentfield && $currentfield['validation']) {
                        $validatorRules[$slug] = $currentfield['validation'];
                    }

                    if (isset($slug)) {
                        // $customFieldKeyValue[$slug] = $customValues;
                        $customFieldKeyValue[$slug] = is_array($customValues)?implode(',',$customValues):$customValues;
                    }
                }
            }

            $finalArray = array_merge((array)$systemField,$customFieldKeyValue);

            if ($systemField) {
                foreach ($systemField as $key=>$val) {

                    if (in_array($key, $columnListing)) {
                        $record[$key] = $val;
                    }
                }
            }
            $validator = Validator::make($finalArray, $validatorRules);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()->all()]);
            }
            if ($record) {
                if($rec->mark_as_public==1){
                    $record['is_public'] = 1;
                }

                $record['web_to_lead_id'] = $rec->id;
                $lead = Leads::create($record);
            }
            
            if (!$lead) {
                DB::rollBack();
                return response()->json(['error' => 'Something went to wrong while saving record.']);
            }
            
            if($rec->notify_when_lead_import==1){
                $staff_id=WebToLeadStaffNotifyRel::where('web_to_lead_id',$rec->id)->pluck('staff_id');
                if($staff_id){
                    $admins=Admin::whereIn('id',$staff_id)->where('email','!=','')->whereNotNull('email')->pluck('email');
                    if($admins){

                        foreach ($admins as $key => $value) {
                            $bodyParams=[];
                            $bodyParams['to']=[$value];
                            $bodyParams['replacedata_array']=array('{lead_name}'=>$lead->first_name.' '.$lead->last_name);
                            MailHandler::mailsend('new-web-to-lead-form-submitted',$bodyParams);
                        }
                    }
                }

                $responsible = Admin::find($rec->responsible);
                if($responsible && $responsible->email!=''){
                    $bodyParams=[];
                    $bodyParams['to']=[$responsible->email];
                    $bodyParams['replacedata_array']=array('{lead_name}'=>$lead->first_name.' '.$lead->last_name,'{lead_email}'=>$lead->email,'{lead_assigned}'=>$responsible->first_name.' '.$responsible->last_name);
                    MailHandler::mailsend('new-lead-assigned',$bodyParams);
                }
            }
            
            $type = 'lead';
            if ($customField) {
                $customFieldKeyArray = [];
                foreach ($customField as $key=>$val) {
                    $newarr= [];
                    $newarr['id'] = $key;
                    $newarr['val'] = is_array($val)?implode(',',$val):$val;
                    $customFieldKeyArray[]=$newarr;
                }

                if($customFieldKeyArray){
                    CustomFieldValueController::customFieldLeadCustomerRel($type,$lead->id,$customFieldKeyArray?json_encode($customFieldKeyArray):'');
                }
            }

            DB::commit();
            setActivityLog('New Web To Lead Form Submit Added [ID: ' . $lead->id . ']',json_encode($request),activityEnums('web_to_lead'),$lead->id);
            setActivityLog('New Web To Lead Added [ID: ' . $lead->id . ']',json_encode($request),activityEnums('lead'),$lead->id);
            
            return response()->json(['message' => 'Record successfully created '], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()]);
        }
    }

    public function thank_you(Request $request) {
        return view('thank_you');
    }

}