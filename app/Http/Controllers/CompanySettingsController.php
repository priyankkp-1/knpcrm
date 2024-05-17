<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CompanySettings;
use App\Models\Taxes;
use App\Models\Status;
use Hash;
use Validator;
use Auth;
use Str;
use DB;

class CompanySettingsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth'); 
    }


    public function edit(Request $request)
    {
        try{
            if(Auth::user()->can('edit-company-settings') || Auth::user()->is_administator==1){

                $request=request()->except(['company_logo','default_tax','lead_converted_status','customer_default_status']);
                if(!$request){
                    return response()->json(['error' => 'Invalid data request!']);
                }
                DB::beginTransaction();
                
                $arr=[];
                foreach ($request as $key => $value) {
                    $arr[]=['is_default' => 1,'meta_key' => $key, 'meta_value' => $value];
                }

                if(request('lead_converted_status')){
                    $lead_converted_status = Status::where('hash_id',request('lead_converted_status'))->where('type','lead')->first();
                    if ($lead_converted_status) {
                        $arr[]=['is_default' => 1,'meta_key' => 'lead_converted_status', 'meta_value' => request('lead_converted_status')];
                    } else {
                        return response()->json(['error' => 'Default lead converted status not valid!']);
                    }
                } else {
                    $arr[]=['is_default' => 1,'meta_key' => 'lead_converted_status', 'meta_value' => ''];
                }

                if (request('customer_default_status')) {
                    $customer_default_status = Status::where('hash_id',request('customer_default_status'))->where('type','customer')->first();
                    if ($customer_default_status) {
                        $arr[]=['is_default' => 1,'meta_key' => 'customer_default_status', 'meta_value' => request('customer_default_status')];
                    } else {
                        return response()->json(['error' => 'Customer default status not valid!']);
                    }
                } else {
                    $arr[]=['is_default' => 1,'meta_key' => 'customer_default_status', 'meta_value' => ''];
                }

                if(request('default_tax')){
                    
                    $tax=Taxes::where('hash_id',request('default_tax'))->first();
                    if($tax){
                        $arr[]=['is_default' => 1,'meta_key' => 'default_tax', 'meta_value' => request('default_tax')];
                    }else{
                        return response()->json(['error' => 'Default tax not valid!']);
                    }
                }else{
                    $arr[]=['is_default' => 1,'meta_key' => 'default_tax', 'meta_value' => ''];
                }

                if(request('company_logo')){
                    $file = new FileUploadController();
                    $logo=$file->upload(request(), 'company_logo', 'company_logo_images');
                    $arr[]=['is_default' => 1,'meta_key' => 'company_logo', 'meta_value' => $logo];
                }elseif(request('company_logo') == null){
                    $arr[]=['is_default' => 1,'meta_key' => 'company_logo', 'meta_value' => ''];
                }

                if($arr){
                    DB::table('company_meta')->upsert($arr,['meta_key', 'is_default'], ['meta_value']);
                }

                DB::commit();
                setActivityLog('Company settings updated',json_encode($request),activityEnums('company_settings'),0,Auth::user()->id);
                return response()->json(['message' => 'Record successfully updated.'], 200);
            }else{
                return response()->json(['error' => "You don't have permission for this part."]);
            }

        } catch (\Exception $e) {
            DB::rollBack();
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