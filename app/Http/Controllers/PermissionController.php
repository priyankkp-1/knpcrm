<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Permission;
use App\Models\AdminRoleRelation;
use App\Models\Role;
use App\Models\Module;
use Hash;
use Validator;
use Auth;
use Str;
use DB;

class PermissionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth'); 
    }

    public function getlist()
    {
        try{
            if(Auth::user()->can('view-permissions') || Auth::user()->is_administator==1){
                $res = Module::with('permission')->get();
                return response()->json(['message' => 'Get list.','data' => cleanObject($res)], 200);
            }else{
                return response()->json(['error' => "You don't have permission for this part."]);
            }
        } catch (\Exception $e) {

            return response()->json(['error' => $e->getMessage()]);
        }
    }

    public function getRolelist()
    {
        try{
            if(Auth::user()->can('view-roles') || Auth::user()->is_administator==1){
                $role=Role::with('permissions')->get();
                return response()->json(['message' => 'Get list.','data' => cleanObject($role)], 200);
            }else{
                return response()->json(['error' => "You don't have permission for this part."]);
            }
        } catch (\Exception $e) {

            return response()->json(['error' => $e->getMessage()]);
        }
    }

    public function getByRoleid($role_id)
    {
        try{
            if(Auth::user()->can('edit-roles') || Auth::user()->is_administator==1){

                $role=Role::where('hash_id',$role_id)->with('permissions')->get();
                return response()->json(['message' => 'Get list.','data' => cleanObject($role)], 200);
            }else{
                return response()->json(['error' => "You don't have permission for this part."]);
            }
        } catch (\Exception $e) {

            return response()->json(['error' => $e->getMessage()]);
        }
    }

    public function syncPermissionRole(Request $request)
    {
        try{
            if(Auth::user()->can('edit-roles') || Auth::user()->is_administator==1){

                $role_id=request('role_hash_id');
                $validator = Validator::make($request->all(), [
                    'role_hash_id' => 'required',
                    'name' => 'required|unique:roles,name,'.$role_id.',hash_id',
                    'permission' => 'required|json',
                ]);

                if($validator->fails()){
                    return response()->json(['error' => $validator->errors()->all()]);
                }
                $prmission=json_decode(request('permission'));
                if(empty($prmission)){
                    return response()->json(['error' => 'The permission field is required.']);
                }
                $role=Role::where('hash_id', '=', $role_id)->first();
                if(!$role){
                    return response()->json(['error' => "Record doesn't exist."]);
                }
                $res = $role->permissions()->sync($prmission);
                setActivityLog('Role Updated [ID: ' . $role->id . ', ' . request('name') . ']',json_encode($request->all()),activityEnums('role'),$role->id,Auth::user()->id);
                return response()->json(['message' => 'Record successfully updated '.$role->name.'.'], 200);
            }else{
                return response()->json(['error' => "You don't have permission for this part."]);
            }

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()]);
        }
    }

    public function createRoleWithPermission(Request $request)
    {
        try{
            if(Auth::user()->can('create-roles') || Auth::user()->is_administator==1){
                $validator = Validator::make($request->all(), [
                    'name' => 'required|unique:roles,name',
                    'permission' => 'required|json',
                ]);

                if($validator->fails()){
                    return response()->json(['error' => $validator->errors()->all()]);
                }

                $prmission=json_decode(request('permission'));
                if(empty($prmission)){
                    return response()->json(['error' => 'The permission field is required.']);
                }
                DB::beginTransaction();
                $request=request()->all();
                $request['slug']=Str::slug(request('name'));
                $request['hash_id'] = getHashid();
                $role=Role::create($request);
                if(!$role){
                    DB::rollBack();
                    return response()->json(['error' => 'Something went to wrong while saving record.']);
                }
                $res = $role->permissions()->sync($prmission);
                DB::commit();
                setActivityLog('New Role Added [ID: ' . $role->id . ', ' . request('name') . ']',json_encode($request),activityEnums('role'),$role->id,Auth::user()->id);
                return response()->json(['message' => 'Record successfully created '.request('name').'.'], 200);
            }else{
                return response()->json(['error' => "You don't have permission for this part."]);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()]);
        }
    }

    public function deleteRole(Request $request)
    {
        try{
            $validator = Validator::make($request->all(), [
                'role_hash_id' => 'required',
            ]);

            if($validator->fails()){
                return response()->json(['error' => $validator->errors()->all()]);
            }
            $role_id=request('role_hash_id');
            $role=Role::where('hash_id', '=', $role_id)->first();
            if(!$role){
                return response()->json(['error' => "Record doesn't exist."]);
            }
            if(AdminRoleRelation::where('role_id', $role->id)->exists()){
                return response()->json(['error' => "Record related with other row so, you can't delete it."]);
            }
            if(Auth::user()->can('delete-roles') || Auth::user()->is_administator==1){
                $role->delete();
                setActivityLog('Role Deleted [ID: ' . $role->id . ']',json_encode($request->all()),activityEnums('role'),$role->id,Auth::user()->id);
            }else{
                return response()->json(['error' => "You don't have permission for this part."]);
            }
            return response()->json(['message' => 'Record successfully deleted '.$role->name.'.'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()]);
        }
    }



}