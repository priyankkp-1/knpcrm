<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Templates;
use Hash;
use Validator;
use Auth;
use Str;
use DB;

class TemplateController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth'); 
    }


    public function update(Request $request)
    {
        try{
            if(Auth::user()->is_administator == 1){

                $validator = Validator::make($request->all(), [
                    'header_title'             => 'required|max:255',
                    'header_color'             => 'required|max:255',
                    'header_front_size'        => 'required|max:255',
                    'header_background_color'  => 'required|max:255',
                    'form_title'               => 'required|max:255',
                    'form_front_size'          => 'required|max:255',
                    'form_button_color'        => 'required|max:255',
                    'form_button_background_color'   => 'required|max:255',
                    'footer_title'             => 'required|max:255',
                    'footer_color'             => 'required|max:255',
                    'footer_front_size'        => 'required|max:255',
                    'footer_background_color'  => 'required|max:255',
                ]);

                if ($validator->fails()) {
                    return response()->json(['error' => $validator->errors()->all()]);
                }

                $record = Templates::where('hash_id', '=', $hash_id)->first();

                if (!$record) {
                    return response()->json(['error' => "Record doesn't exist."]);
                }
         
                $request=request()->except(['hash_id']);
                
                Leads::where('id', $record->id)->update($request);

                setActivityLog('Template Updated [ID: ' . $record->id . ']',json_encode($request),$record->id,Auth::user()->id);
                return response()->json(['message' => 'Record successfully updated '.request('name').'.'], 200);

            } else {
                return response()->json(['error' => "You don't have permission for this part."]);
            }

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()]);
        }
    }

    public function getList()
    {
        try{         
            if(Auth::user()->can('view-company-settings') || Auth::user()->is_administator==1){
                $data = [];
                $data = CompanySettings::where('meta_key','!=','company_logo')->pluck('meta_value','meta_key');

                $file_path = CompanySettings::select('files.file_path')->where('meta_key','company_logo')->join("files","files.id","=","company_meta.meta_value")->first();
                if($file_path){
                    $data['company_logo']=$file_path->file_path;
                }
                
                $success =  $data;

                return response()->json(['message' => 'Result.','data' => cleanObject($success)], 200);
            }else{
                return response()->json(['error' => "You don't have permission for this part."]);
            }
        } catch (\Exception $e) {

            return response()->json(['error' => $e->getMessage()]);
        }
    }



}