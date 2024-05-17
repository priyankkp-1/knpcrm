<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Admin;
use App\Models\Leads;
use App\Models\Source;
use App\Models\Status;
use App\Models\CustomFieldValue;
use App\Models\CustomFields;
use App\Models\CompanySettings;
use App\Models\Customers;
use App\Models\User;
use App\Models\AssignAdminsCustomers;
use App\Models\Notes;
use App\Http\Controllers\CustomFieldValueController;
use Hash,DB;
use Validator;
use Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\ImportLead;

class LeadController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth'); 
    }

    public function userGetlist()
    {
        try{
            $res = Admin::paginate();
            $success =  $res;

            return response()->json(['message' => 'Get list.','data' => cleanObject($success)], 200);
        } catch (\Exception $e) {

            return response()->json(['error' => $e->getMessage()]);
        }
    }

    public function systemInBuildField()
    {
        try{
            $success = systemInBuildField();
            return response()->json(['message' => 'Get list.','data' => cleanObject($success)], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()]);
        }
    }

    public function admin_system_custom_field($field_to,$is_array=0)
    {
        try{
            $record = [];
            $systemFieldData = systemInBuildField();
            $record['lead']['system_field'] = $systemFieldData['lead']['system_field'];
            $rescustomlead= CustomFields::select('hash_id','name','slug','field_to','type','options','bs_column',DB::raw('if(required > 0 , "required" ,"")  as validation'))->where('active','1')->where('field_to','lead')->get();
            $record['lead']['custom_field']= $rescustomlead?arrayByToKeys($rescustomlead->toArray(),'hash_id'):[];

            $recordcustomcustomer = CustomFields::select('hash_id','name','slug','field_to','type','options','bs_column',DB::raw('if(required > 0 , "required" ,"")  as validation'))->where('active','1')->where('field_to','customer')->get();
            $record['customer']['system_field'] = $systemFieldData['customer']['system_field'];
            $record['customer']['custom_field'] = $recordcustomcustomer?arrayByToKeys($recordcustomcustomer->toArray(),'hash_id'):[];
            if($field_to && ($field_to == 'lead' || $field_to == 'customer')){
                $res[$field_to] = $record[$field_to];
            }else{
                $res = $record;
            }
            if($is_array==1){
                return $res[$field_to];
            }
            return response()->json(['message' => 'Get list.','data' => cleanObject($res)], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()]);
        }
    }

    public function getList(Request $request)
    {
        try{
            if (Auth::user()->can('view-leads') || Auth::user()->is_administator == 1) {
                $sort_by   = $request->get('sortby');
                $sort_type = $request->get('sorttype');
                $query     = $request->get('query');
                $query     = str_replace(" ", "%", $query);
                $export    = $request->get('is_export');
                
                $res = Leads::join('custom_field_value', function($join) {
                        $join->on('custom_field_value.rel_column_id', '=', 'leads.id')
                        ->where('custom_field_value.field_to','lead')->whereNull('custom_field_value.deleted_at');
                    })->select('leads.*')->groupBy('id')
                    ->where(function($q) use($query) {
                        $q->where('leads.first_name', 'like', '%'.$query.'%')->orWhere('leads.last_name', 'like', '%'.$query.'%')->orWhere('leads.email', 'like', '%'.$query.'%')->orWhere('leads.company', 'like', '%'.$query.'%')->orWhere('leads.job_title', 'like', '%'.$query.'%')->orWhere('custom_field_value.value', 'like', '%'.$query.'%');
                    });
                    
                if(Auth::user()->is_administator == 0){
                    $res = $res->where(function($query) {
                        $query->where('leads.is_public',1)->orWhere('leads.assigned', Auth::user()->id);
                    });
                }
                

                if ($sort_by && $sort_type) {
                    $res=$res->orderBy($sort_by, $sort_type);
                }

                if ( $export == 0 ) {
                    $res = $res->paginate();
                } else {
                    $res = $res->get();
                }

                if($res){
                    $leadIds = $res->pluck('id')->toArray();
                    $customFields = arrayByCustomFieldValue($res,$leadIds,'lead');
                    foreach ($res as $lead) {
                        $leadId = $lead->id;
                        $lead->custom_field = isset($customFields[$leadId]) ? $customFields[$leadId] : [];
                    }
                }
                
                return response()->json(['message' => 'Get list.','data' => cleanObject($res)], 200);
            } else {
                return response()->json(['error' => "You don't have permission for this part."]);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()]);
        }
    }

    public function getByid($hash_id)
    {
        try{
            if (Auth::user()->can('edit-leads') || Auth::user()->is_administator == 1) {

                $record = Leads::where('hash_id',$hash_id)->get();

                if($record){
                    $leadIds = $record->pluck('id')->toArray();
                    $customFields = arrayByCustomFieldValue($record,$leadIds,'lead');
                    foreach ($record as $lead) {
                        $leadId = $lead->id;
                        $lead->custom_field = isset($customFields[$leadId]) ? $customFields[$leadId] : [];
                    }
                }

                return response()->json(['message' => 'Get record.','data' => cleanObject($record)], 200);

            } else {
                return response()->json(['error' => "You don't have permission for this part."]);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()]);
        }
    }

    public function update(Request $request)
    {
        try{
            if(Auth::user()->can('edit-leads') || Auth::user()->is_administator == 1){

                $hash_id = request('hash_id');
                $validator = Validator::make($request->all(), [
                    'first_name'    => 'required|max:100',
                    'last_name'     => 'required|max:100',
                    'company'       => 'required|max:100',
                    'job_title'     => 'required|max:255',
                    'assigned'      => 'required',
                    'status_id'     => 'required',
                    'city'          => 'required|max:100',
                    'state'         => 'required|max:100',
                    'zip'           => 'required|integer|regex:/^\d{2,8}$/',
                    'address'       => 'required|max:1500',
                    'address_1'     => 'max:1500',
                    'address_2'     => 'max:1500',
                    'email'         => 'required|email|max:200|unique:leads,email,'.$hash_id.',hash_id',
                    'country_code'  => 'required|integer|regex:/^\d{1,3}$/',
                    'phone'         => 'required|integer|regex:/^\d{10,12}$/',
                    'description'   => 'max:1500',
                    'is_action'     => 'required|boolean',
                    'country_id'    => 'required|integer',
                    'source_id'     => 'required',
                    'custom_value' => 'required',
                    'lastContact'   =>'required|date_format:Y-m-d H:i:s',
                ]);

                if ($validator->fails()) {
                    return response()->json(['error' => $validator->errors()->all()]);
                }

                $record = Leads::where('hash_id', '=', $hash_id)->first();

                if (!$record) {
                    return response()->json(['error' => "Record doesn't exist."]);
                }

         
                if(request('assigned')){
                    $assign = Admin::where('hash_id',request('assigned'))->first();
                    if(!$assign){
                        return response()->json(['error' => 'Assign Admin not found.']);
                    }
                }

                if(request('source_id')){
                    $source_find = Source::where('hash_id',request('source_id'))->first();
                    if(!$source_find){
                        return response()->json(['error' => 'Source not found.']);
                    }
                }

                if(request('country_id')){
                    $country_find = DB::table('countries')->where('country_id',request('country_id'))->first();
                    if(!$country_find){
                        return response()->json(['error' => 'Country not found.']);
                    }
                }

                if(request('status_id')){
                    $status_find = Status::where('hash_id',request('status_id'))->where('type','lead')->first();
                    if (!$status_find) {
                        return response()->json(['error' => 'Status not found.']);
                    }
                }

                $request=request()->except(['hash_id']);
                $request=request()->except(['custom_value']);
                $request['assigned'] = isset($assign)?$assign->id:NULL;
                $request['source_id'] = isset($source_find)?$source_find->id:NULL;
                $request['country_id'] = isset($country_find)?$country_find->country_id:NULL;
                $request['status_id'] = isset($status_find)?$status_find->id:NULL;

                
                if (request('is_action')) {
                    $request['action_date_time'] = now();
                }

                Leads::where('id', $record->id)->update($request);

                $type = 'lead';
                CustomFieldValueController::customFieldLeadCustomerRel($type,$record->id,request('custom_value'));

                setActivityLog('Leads Updated [ID: ' . $record->id . ', ' . request('email') . ']',json_encode($request),activityEnums('lead'),$record->id,Auth::user()->id);
                return response()->json(['message' => 'Record successfully updated '.request('name').'.'], 200);

            } else {
                return response()->json(['error' => "You don't have permission for this part."]);
            }

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()]);
        }
    }

    public function create(Request $request)
    {
        try{
            if (Auth::user()->can('create-leads') || Auth::user()->is_administator == 1) {
                $validator = Validator::make($request->all(), [
                    'first_name'    => 'required|max:200',
                    'last_name'     => 'required|max:200',
                    'company'       => 'required|max:200',
                    'job_title'     => 'required|max:200',
                    'assigned'      => 'required',
                    'status_id'     => 'required',
                    'city'          => 'required|max:100',
                    'state'         => 'required|max:100',
                    'zip'           => 'required|integer|regex:/^\d{2,8}$/',
                    'address'       => 'required|max:1500',
                    'address_1'     => 'max:1500',
                    'address_2'     => 'max:1500',
                    'email'         => 'required|email|max:200|unique:leads,email',
                    'country_code'  => 'required|integer|regex:/^\d{1,3}$/',
                    'phone'         => 'required|integer|regex:/^\d{10,12}$/',
                    'description'   => 'max:1500',
                    'is_action'     => 'required|boolean',
                    'country_id'    => 'required|integer',
                    'source_id'     => 'required',
                    'custom_value' => 'required',
                    'lastContact'   =>'required|date_format:Y-m-d H:i:s',
                ]);

                if ($validator->fails()) {
                    return response()->json(['error' => $validator->errors()->all()]);
                }

             
                if(request('assigned')){
                    $assign = Admin::where('hash_id',request('assigned'))->first();
                    if(!$assign){
                        return response()->json(['error' => 'Assign Admin not found.']);
                    }
                }

                if(request('source_id')){
                    $source_find = Source::where('hash_id',request('source_id'))->first();
                    if(!$source_find){
                        return response()->json(['error' => 'Source not found.']);
                    }
                }

                if(request('country_id')){
                    $country_find = DB::table('countries')->where('country_id',request('country_id'))->first();
                    if(!$country_find){
                        return response()->json(['error' => 'Country not found.']);
                    }
                }

                if(request('status_id')){
                    $status_find = Status::where('hash_id',request('status_id'))->where('type','lead')->first();
                    if (!$status_find) {
                        return response()->json(['error' => 'Status not found.']);
                    }
                }

                DB::beginTransaction();
                $request = request()->all();
                $request['hash_id'] = getHashid();
                $request['assigned'] = isset($assign)?$assign->id:NULL;
                $request['source_id'] = isset($source_find)?$source_find->id:NULL;
                $request['country_id'] = isset($country_find)?$country_find->country_id:NULL;
                $request['status_id'] = isset($status_find)?$status_find->id:NULL;

                if (request('is_action')) {
                    $request['action_date_time'] = now();
                }

                $record = Leads::create($request);
                
                if (!$record) {
                    DB::rollBack();
                    return response()->json(['error' => 'Something went to wrong while saving record.']);
                }
                $type = 'lead';
                CustomFieldValueController::customFieldLeadCustomerRel($type,$record->id,request('custom_value'));

                DB::commit();
                setActivityLog('New Lead Added [ID: ' . $record->id . ']',json_encode($request),activityEnums('lead'),$record->id,Auth::user()->id);
                
                return response()->json(['message' => 'Record successfully created '], 200);

            } else {
                return response()->json(['error' => "You don't have permission for this part."]);
            }

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()]);
        }
    }

    public function delete(Request $request)
    {
        try{
            $validator = Validator::make($request->all(), [
                'hash_id' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()->all()]);
            }

            $hash_id = request('hash_id');
            $record  = Leads::where('hash_id', '=', $hash_id)->first();

            if (!$record) {
                return response()->json(['error' => "Record doesn't exist."]);
            }

            if (Auth::user()->can('delete-leads') || Auth::user()->is_administator == 1) {
                $record->delete();
                setActivityLog('Leads Deleted [ID: ' . $record->id . ']',json_encode($request->all()),activityEnums('lead'),$record->id,Auth::user()->id);
            } else {
                return response()->json(['error' => "You don't have permission for this part."]);
            }
            return response()->json(['message' => 'Record successfully deleted '.$record->email.'.'], 200);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()]);
        }
    }

    public function lead_convert_to_customer(Request $request)
    {
        try{
            if (Auth::user()->can('convert-to-customers') || Auth::user()->is_administator == 1) {
                
                $validator = Validator::make($request->all(), [
                    'job_title'           => 'nullable|max:200',
                    'first_name'          => 'required|max:200',
                    'last_name'           => 'required|max:200',
                    'email'               => 'required|email|max:200|unique:customers,email',
                    'password'            => 'required|',
                    'phone_number'        => 'required|integer|digits:10',
                    'country_code'        => 'required|integer|digits:2',
                    'is_active'           => 'required|boolean',
                    'last_ip'             => 'nullable',
                    'profile_image'        => 'mimes:jpg,jpeg,png,gif|max:10000',//10kb
                    'invoice_email'       => 'required|boolean',
                    'estimate_email'       => 'required|boolean',
                    'project_email'         => 'required|boolean',
                    'contact_email'         => 'required|boolean',
                    'credit_note_email'     => 'required|boolean',
                    'ticket_email'          => 'required|boolean',
                    'task_email'            => 'required|boolean',
                    'invoice_permissions'   => 'required|boolean',
                    'estimates_permissions'  => 'required|boolean',
                    'proposal_permissions'   => 'required|boolean',
                    'contracts_permissions'  => 'required|boolean',
                    'projects_permissions'   => 'required|boolean',
                    'support_permissions'    => 'required|boolean',
                    'lead_id'                => 'required|max:255',
                ]);

                if ($validator->fails()) {
                    return response()->json(['error' => $validator->errors()->all()]);
                }

                if(request('lead_id')){
                    $lead_find = Leads::where('hash_id',request('lead_id'))->first();
                    if(!$lead_find){
                        return response()->json(['error' => 'Source not found.']);
                    }
                }

                $defaultstatus = CompanySettings::join('status', function($join) {
                    $join->on('status.hash_id', '=', 'company_meta.meta_value');
                })->select('status.id')->where('company_meta.meta_key','=','lead_converted_status')->first();

                DB::beginTransaction();
                $request = request()->all();
                $request['hash_id'] = getHashid();
                $request['password'] = bcrypt($request['password']);
                $request['lead_id'] = isset($lead_find)?$lead_find->id:NULL;
                $request['assigned'] = isset($lead_find)?$lead_find->assigned:NULL;
                $request['source_id'] = isset($lead_find)?$lead_find->source_id:NULL;
                $request['status_id'] = isset($defaultstatus)?$defaultstatus->id:NULL;
                $customer = Customers::create($request);
                
                if ($customer) {

                    $notes = Notes::where('rel_type','lead')->where('rel_id',$lead_find->id)->get();
                    if($notes){
                        $noteArr= [];
                        foreach($notes as $kn=>$vn){
                            $noteArr[$kn]['hash_id'] = getHashid();
                            $noteArr[$kn]['description'] = $vn->description;
                            $noteArr[$kn]['rel_type'] = 'customer';
                            $noteArr[$kn]['rel_id'] = $customer->id;
                            $noteArr[$kn]['addedfrom'] = $vn->addedfrom;
                        }
                        Notes::insert($noteArr);
                    }

                    Leads::where('hash_id',request('lead_id'))->update(['date_customer_converted'=>now()]);
                    $assign_admins_to_customers = [
                        'customer_id' =>$customer->id,
                        'admin_id' =>Auth::user()->id
                    ];
                    AssignAdminsCustomers::create($assign_admins_to_customers);
                    $request['customers_id'] = $customer->id;
                    $request['email_verified_at'] = now();
                  
                    $record = User::create($request);

                    if(!$record) {
                        DB::rollBack();
                        return response()->json(['error' => 'Something went to wrong while saving user record.']);
                    }

                    if (request('profile_image')) {
                        $file = new FileUploadController();
                        $profile_picture = $file->upload(request(), 'profile_image','customer_profile');
                        $record->profile_img = $profile_picture;
                        $record->save();
                    }
                } else {
                    DB::rollBack();
                    return response()->json(['error' => 'Something went to wrong while saving record.']);
                }

                DB::commit();
                setActivityLog('New Customer Added [ID: ' . $record->id . ']',json_encode($request),activityEnums('customer'),$record->id);
                setActivityLog('Lead Convert To Customer Added [ID: ' . $record->id . ']',json_encode($request),activityEnums('lead'),$record->id);
                return response()->json(['message' => 'Record successfully created '], 200);
            } else {
                return response()->json(['error' => "You don't have permission for this part."]);
            }

        } catch (\Exception $e) {
            DB::rollBack();
        }
    }
    
    public function lead_assignee_update(Request $request)
    {
        try{
            $validator = Validator::make($request->all(), [
                'hash_id' => 'required',
                'assignee_id' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()->all()]);
            }

            $hash_id = request('hash_id');
            $record  = Leads::where('hash_id', '=', $hash_id)->first();
            
            if (!$record) {
                return response()->json(['error' => "Record doesn't exist."]);
            }
            if(request('assignee_id')){
                $admin = Admin::where('hash_id',request('assignee_id'))->first();
                if(!$admin){
                    return response()->json(['error' => 'Assign Admin not found.']);
                }
            }


            if (Auth::user()->can('edit-leads') || Auth::user()->is_administator == 1) {
                Leads::where('id', $record->id)->update(['assigned'=>$admin->id]);
                setActivityLog('Leads Assigned To '.$admin->first_name.' '.$admin->last_name.' [ID: ' . $record->id . ']',json_encode($request->all()),activityEnums('lead'),$record->id,Auth::user()->id);
            } else {
                return response()->json(['error' => "You don't have permission for this part."]);
            }
            return response()->json(['message' => 'Lead successfully assigned to '.$admin->first_name.' '.$admin->last_name.'.'], 200);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()]);
        }
    }


    
    public function lead_status_update(Request $request)
    {
        try{
            $validator = Validator::make($request->all(), [
                'hash_id' => 'required',
                'status_id' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()->all()]);
            }

            $hash_id = request('hash_id');
            $record  = Leads::where('hash_id', '=', $hash_id)->first();
            
            if (!$record) {
                return response()->json(['error' => "Record doesn't exist."]);
            }
            if(request('status_id')){
                $status_find = Status::where('hash_id',request('status_id'))->where('type','lead')->first();
                if (!$status_find) {
                    return response()->json(['error' => 'Status not found.']);
                }
            }


            if (Auth::user()->can('edit-leads') || Auth::user()->is_administator == 1) {
                Leads::where('id', $record->id)->update(['status_id'=>$status_find->id]);
                setActivityLog('Leads Status Updated To '.$record->email.' [ID: ' . $record->id . ']',json_encode($request->all()),activityEnums('lead'),$record->id,Auth::user()->id);
            } else {
                return response()->json(['error' => "You don't have permission for this part."]);
            }
            return response()->json(['message' => 'Status successfully updated to '.$record->email.'.'], 200);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()]);
        }
    }

    public function import(Request $request){
        try{
            $validator = Validator::make( [
                'file'      => $request->file,
                'extension' => $request->file?strtolower($request->file->getClientOriginalExtension()):'',
            ], [
                'file'      => 'required',
                'extension' => 'in:csv,xlsx,xls'
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()->all()]);
            }
            Excel::import(new ImportLead, $request->file('file'));
            return response()->json(['message' => 'Leads imported successfully.'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()]);
        }
    }

}