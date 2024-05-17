<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Mailtemplate;
use Hash;
use Validator;
use Auth;
use Str;
use DB;

class MailtemplatesController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth'); 
    }

    public function edit(Request $request)
    {
        try{
            if(Auth::user()->can('edit-email-template') || Auth::user()->is_administator==1){

                $hash_id=request('hash_id');
                $validator = Validator::make($request->all(), [
                    'hash_id' => 'required',
                    'from_name' => 'required|max:100',
                    'subject' => 'required|max:500',
                    'message' => 'required',
                    'is_plain_text' => 'required|boolean',
                    'is_active' => 'required|boolean',
                ]);

                if($validator->fails()){
                    return response()->json(['error' => $validator->errors()->all()]);
                }
                $request=request()->except(['hash_id']);
                $mailtemplate=Mailtemplate::where('hash_id', '=', $hash_id)->first();

                if(!$mailtemplate){
                    return response()->json(['error' => "Record doesn't exist."]);
                }
                Mailtemplate::where('id', $mailtemplate->id)->update($request);
                setActivityLog('Email Template Updated [Name: ' . $mailtemplate->name . ']',json_encode($request),activityEnums('email_template'),$mailtemplate->id,Auth::user()->id);    
                return response()->json(['message' => 'Record successfully updated '.$mailtemplate->name.'.'], 200);
            }else{
                return response()->json(['error' => "You don't have permission for this part."]);
            }

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()]);
        }
    }

    public function enabledisable(Request $request)
    {
        try{
            if(Auth::user()->can('enable-disable-email-template') || Auth::user()->is_administator==1){

                $hash_id=request('hash_id');
                $validator = Validator::make($request->all(), [
                    'is_active' => 'required|boolean',
                ]);

                if($validator->fails()){
                    return response()->json(['error' => $validator->errors()->all()]);
                }
                $isactive=request('is_active');
                $reqval=1;
                if($isactive==1){
                    $reqval=0;
                }

                if($hash_id){
                    $mailtemplate=Mailtemplate::where('hash_id',$hash_id)->first();
                    if($mailtemplate){
                        Mailtemplate::where('is_active', '=', $reqval)->where('id', $mailtemplate->id)->update(['is_active'=>$isactive]);
                    }
                }else{
                    Mailtemplate::where('is_active', '=', $reqval)->update(['is_active'=>$isactive]);
                }
                
                return response()->json(['message' => 'Status successfully updated .'], 200);
            }else{
                return response()->json(['error' => "You don't have permission for this part."]);
            }

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()]);
        }
    }

    public function getList()
    {
        try{            
            if(Auth::user()->can('view-email-template') || Auth::user()->is_administator==1){
                $mailtemplates = Mailtemplate::select('hash_id','slug','type','name','subject','message','from_name','is_plain_text','is_active')->get();
                $new_array=[];
                foreach ($mailtemplates as $key => $value) {
                    $new_array[$value->type][] = $value;
                }
                $success =  $new_array;

                return response()->json(['message' => 'Result.','data' => cleanObject($success)], 200);
            }else{
                return response()->json(['error' => "You don't have permission for this part."]);
            }
        } catch (\Exception $e) {

            return response()->json(['error' => $e->getMessage()]);
        }
    }


    public function getById($id)
    {
        try{
            if(Auth::user()->can('edit-email-template') || Auth::user()->is_administator==1){
                $mailtemplate=Mailtemplate::select('hash_id','slug','name','subject','message','from_name','type','is_plain_text','is_active')->where('hash_id',$id)->first();

                $mailtemplate=cleanObject($mailtemplate);

                $mailtemplate['variables'][$mailtemplate['type']]=getMailsVariable($mailtemplate['type']);
                $mailtemplate['variables']['other']=getMailsVariable('other');
                return response()->json(['message' => 'Get record.','data' => $mailtemplate], 200);
            }else{
                return response()->json(['error' => "You don't have permission for this part."]);
            }
        } catch (\Exception $e) {

            return response()->json(['error' => $e->getMessage()]);
        }
    }



}