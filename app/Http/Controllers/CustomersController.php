<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use App\Models\Source;
use App\Models\Admin;
use App\Models\Status;
use App\Models\Customers;
use App\Models\User;
use App\Models\AssignAdminsCustomers;
use Hash,Auth,DB,Str;
use Illuminate\Support\Facades\Validator;

class CustomersController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth'); 
    }

    
    public function create(Request $request)
    {
        try{
            if (Auth::user()->can('create-customers') || Auth::user()->is_administator == 1) {
                $validator = Validator::make($request->all(), [
                    'first_name'    => 'required|max:200',
                    'last_name'     => 'required|max:200',
                    'company'       => 'required|max:200',
                    'job_title'     => 'required|max:200',
                    'assigned'      => 'required',
                    'status_id'     => 'required',
                    'city'          => 'required|max:100',
                    'state'         => 'required|max:100',
                    'zip'           => 'required|integer|digits:5',
                    'address'       => 'required|max:1500',
                    'address_1'     => 'max:1500',
                    'address_2'     => 'max:1500',
                    'email'         => 'required|email|max:200|unique:customers,email',
                    'country_code'  => 'required|integer|digits:2',
                    'phone_number'  => 'required|integer|digits:10',
                    'country_id'    => 'required|integer',
                    'source_id'     => 'required',
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

                $record = Customers::create($request);

                $assign_admins_to_customers = [
                    'customer_id' =>$record->id,
                    'admin_id' =>Auth::user()->id
                ];
                AssignAdminsCustomers::create($assign_admins_to_customers);
                
                if (!$record) {
                    DB::rollBack();
                    return response()->json(['error' => 'Something went to wrong while saving record.']);
                }

                DB::commit();
                setActivityLog('New User Added [ID: ' . $record->id . ']',json_encode($request),activityEnums('customer'),$record->id,Auth::user()->id);
                
                return response()->json(['message' => 'Record successfully created '], 200);

            } else {
                return response()->json(['error' => "You don't have permission for this part."]);
            }

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()]);
        }
    }

    public function getList(Request $request)
    {
        try{
            if (Auth::user()->can('view-customers') || Auth::user()->is_administator == 1) {
                $sort_by   = $request->get('sortby');
                $sort_type = $request->get('sorttype');
                $query     = $request->get('query');
                $query     = str_replace(" ", "%", $query);

                $res = Customers::where('id', 'like', '%'.$query.'%')->orWhere('first_name', 'like', '%'.$query.'%')->orWhere('last_name', 'like', '%'.$query.'%')->orWhere('email', 'like', '%'.$query.'%')->orWhere('company', 'like', '%'.$query.'%')->orWhere('job_title', 'like', '%'.$query.'%');

                if ($sort_by && $sort_type) {
                    $res=$res->orderBy($sort_by, $sort_type);
                }

                $res = $res->paginate();
                
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
            if (Auth::user()->can('edit-customers') || Auth::user()->is_administator == 1) {

                $record = Customers::where('hash_id',$hash_id)->get();
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
            if(Auth::user()->can('edit-customers') || Auth::user()->is_administator == 1){

                $hash_id = request('hash_id');
                $validator = Validator::make($request->all(), [
                    'first_name'    => 'required|max:200',
                    'last_name'     => 'required|max:200',
                    'company'       => 'required|max:200',
                    'job_title'     => 'required|max:200',
                    'assigned'      => 'required',
                    'status_id'     => 'required',
                    'city'          => 'required|max:100',
                    'state'         => 'required|max:100',
                    'zip'           => 'required|integer|digits:5',
                    'address'       => 'required|max:1500',
                    'address_1'     => 'max:1500',
                    'address_2'     => 'max:1500',
                    'country_code'  => 'required|integer|digits:2',
                    'phone_number'  => 'required|integer|digits:10',
                    'country_id'    => 'required|integer',
                    'source_id'     => 'required',
                    'email'         => 'required|email|max:200|unique:customers,email,'.$hash_id.',hash_id',
                ]);

                if ($validator->fails()) {
                    return response()->json(['error' => $validator->errors()->all()]);
                }

                $record = Customers::where('hash_id', '=', $hash_id)->first();

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
                $request['assigned'] = isset($assign)?$assign->id:NULL;
                $request['source_id'] = isset($source_find)?$source_find->id:NULL;
                $request['country_id'] = isset($country_find)?$country_find->country_id:NULL;
                $request['status_id'] = isset($status_find)?$status_find->id:NULL;

                
                Customers::where('id', $record->id)->update($request);

                setActivityLog('Customers Updated [ID: ' . $record->id . ', ' . request('email') . ']',json_encode($request),activityEnums('customer'),$record->id,Auth::user()->id);
                return response()->json(['message' => 'Record successfully updated '.request('email').'.'], 200);

            } else {
                return response()->json(['error' => "You don't have permission for this part."]);
            }

        } catch (\Exception $e) {
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
            $record  = Customers::where('hash_id', '=', $hash_id)->first();

            if (!$record) {
                return response()->json(['error' => "Record doesn't exist."]);
            }

            if (Auth::user()->can('delete-customers') || Auth::user()->is_administator == 1) {
                $record->delete();
                setActivityLog('Customers Deleted [ID: ' . $record->id . ']',json_encode($request->all()),activityEnums('customer'),$record->id,Auth::user()->id);
            } else {
                return response()->json(['error' => "You don't have permission for this part."]);
            }
            return response()->json(['message' => 'Record successfully deleted '.$record->email.'.'], 200);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()]);
        }
    }

}