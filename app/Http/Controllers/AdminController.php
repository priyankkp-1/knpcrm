<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Admin;
use Hash;
use Validator;
use Auth;
use Str;
use DB;
use MailHandler;

class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth', ['except' => ['getCountry']]);
    }

    public function create(Request $request)
    {
        try{
            if(Auth::user()->can('create-staff') || Auth::user()->is_administator==1){
                $validator = Validator::make($request->all(), [
                    'first_name' => 'required|max:100',
                    'last_name' => 'required|max:100',
                    'country_code' => 'required|max:5',
                    'phone' => 'required|max:12',
                    'profile_img' => 'required|mimes:jpg,jpeg,png,gif|max:10000',
                    'last_ip' => 'required',
                    'role_id' => 'required',
                    'password' => 'required',
                    'is_administator' => 'required|boolean',
                    'is_active' => 'required|boolean',
                    'email' => 'required|email|max:200|unique:admins,email',
                    'admin_permissions' => 'json',
                    'send_welcome_email' => 'required|boolean',
                ]);
    
                if($validator->fails()){
                    return response()->json(['error' => $validator->errors()->all()]);
                }
    
                
                DB::beginTransaction();
                $request=request()->all();
                $cleanpassword=$request['password'];
                $request['hash_id'] = getHashid();
                $request['password'] = bcrypt($request['password']);
                $admin=Admin::create($request);
                
                if(!$admin){
                    DB::rollBack();
                    return response()->json(['error' => 'Something went to wrong while saving record.']);
                }

                $admin->saverole()->sync([request('role_id')]);
                if(Auth::user()->can('edit-permissions') || Auth::user()->is_administator==1){
                    $admin->savepermissions()->sync(json_decode(request('admin_permissions'),true));
                }
    
                $file = new FileUploadController();
                $profile_picture=$file->upload(request(), 'profile_img', 'profile_picture');
                $admin->profile_img = $profile_picture;
                $admin->save();
                
                if(request('send_welcome_email')==1){
                    $bodyParams=[];
                    $bodyParams['to']=[$admin->email];
                    $bodyParams['replacedata_array']=array('{staff_firstname}'=>$admin->first_name,'{staff_email}'=>$admin->email,'{password}'=>$cleanpassword,'{admin_url}'=>env('FRONT_URL'));
                    MailHandler::mailsend('new-staff-created',$bodyParams);
                }
                
                DB::commit();
                setActivityLog('New Staff Member Added [ID: ' . $admin->id . ', ' . request('first_name') . ' ' . request('last_name') . ']',json_encode($request),activityEnums('staff'),$admin->id,Auth::user()->id);
                return response()->json(['message' => 'Record successfully created '.request('first_name').'.'], 200);
            }else{
                return response()->json(['error' => "You don't have permission for this part."]);
            }

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()]);
        }
    }

    public function edit(Request $request)
    {
        try{
            if(Auth::user()->can('edit-staff') || Auth::user()->is_administator==1){

                $hash_id=request('staff_hash_id');
                $validator = Validator::make($request->all(), [
                    'staff_hash_id' => 'required',
                    'email' => 'required|email|max:200|unique:admins,email,'.$hash_id.',hash_id',
                    'first_name' => 'required|max:100',
                    'last_name' => 'required|max:100',
                    'country_code' => 'required|max:5',
                    'phone' => 'required|max:12',
                    'profile_img' => 'mimes:jpg,jpeg,png,gif|max:10000',
                    'last_ip' => 'required',
                    'role_id' => 'required',
                    'password' => 'required',
                    'is_administator' => 'required|boolean',
                    'is_active' => 'required|boolean',
                    'admin_permissions' => 'json',
                ]);

                if($validator->fails()){
                    return response()->json(['error' => $validator->errors()->all()]);
                }

                DB::beginTransaction();
                $request=request()->except(['staff_hash_id','role_id','admin_permissions']);
                $request['password'] = bcrypt($request['password']);

                $admin=Admin::where('hash_id', '=', $hash_id)->first();
                
                if(!$admin){
                    return response()->json(['error' => "Record doesn't exist."]);
                }

                Admin::where('id', $admin->id)->update($request);
                $admin->saverole()->sync([request('role_id')]);
                if(Auth::user()->can('edit-permissions') || Auth::user()->is_administator==1){
                    $admin->savepermissions()->sync(json_decode(request('admin_permissions'),true));
                }
    
                if(request('profile_img')){
                    $file = new FileUploadController();
                    $profile_picture=$file->upload(request(), 'profile_img', 'profile_picture');
                    $admin->profile_img = $profile_picture;
                    $admin->save();
                }
                
                DB::commit();
                setActivityLog('Staff Member Updated [ID: ' . $admin->id . ', ' . request('first_name') . ' ' . request('last_name') . ']',json_encode($request),activityEnums('staff'),$admin->id,Auth::user()->id);
                return response()->json(['message' => 'Record successfully updated '.request('first_name').'.'], 200);
            }else{
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
            if(Auth::user()->can('view-staff') || Auth::user()->is_administator==1){
                $sort_by = $request->get('sortby');
                $sort_type = $request->get('sorttype');
                $query = $request->get('query');
                $query = str_replace(" ", "%", $query);
                $res=Admin::where('id', 'like', '%'.$query.'%')->orWhere('first_name', 'like', '%'.$query.'%')->orWhere('last_name', 'like', '%'.$query.'%');
                if($sort_by && $sort_type){
                    $res=$res->orderBy($sort_by, $sort_type);
                }
                $res = $res->paginate();
                $success =  $res;

                return response()->json(['message' => 'Result.','data' => cleanObject($success)], 200);
            }else{
                return response()->json(['error' => "You don't have permission for this part."]);
            }
        } catch (\Exception $e) {

            return response()->json(['error' => $e->getMessage()]);
        }
    }

    public function getCountry()
    {
        try{
            $list = DB::table('countries')->get();
            $success =  $list;

            return response()->json(['message' => 'Result.','data' => cleanObject($list)], 200);
        } catch (\Exception $e) {

            return response()->json(['error' => $e->getMessage()]);
        }
    }


    public function getById($staff_id)
    {
        try{
            if(Auth::user()->can('edit-staff') || Auth::user()->is_administator==1){
                $admin=Admin::where('hash_id',$staff_id)->with('permissions','role')->first();
                return response()->json(['message' => 'Get list.','data' => cleanObject($admin)], 200);
            }else{
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
                'admin_hash_id' => 'required',
            ]);

            if($validator->fails()){
                return response()->json(['error' => $validator->errors()->all()]);
            }
            $admin_id=request('admin_hash_id');
            $admin=Admin::where('hash_id', '=', $admin_id)->first();
            if(Auth::user()->can('delete-staff') || Auth::user()->is_administator==1){
                if(!$admin){
                    return response()->json(['error' => "Record doesn't exist."]);
                }
                if(Auth::user()->id == $admin->id){
                    return response()->json(['error' => "You don't have to delete for this record."]);    
                }
                $admin->delete();
                setActivityLog('Staff Member Deleted [Name: ' . $admin->first_name . ']',json_encode($request->all()),activityEnums('staff'),$admin->id,Auth::user()->id);    
            }else{
                return response()->json(['error' => "You don't have permission for this part."]);
            }
            return response()->json(['message' => 'Record successfully deleted '.$admin->first_name.'.'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()]);
        }
    }


}
