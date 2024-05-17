<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Contracttypes;
use Hash;
use Validator;
use Auth;
use Str;
use DB;

class ContractTypesController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth'); 
    }

    public function getList(Request $request)
    {
        try{
            if(Auth::user()->can('view-contract-types') || Auth::user()->is_administator==1){
                $sort_by = $request->get('sortby');
                $sort_type = $request->get('sorttype');
                $query = $request->get('query');
                $query = str_replace(" ", "%", $query);
                $res=Contracttypes::where('id', 'like', '%'.$query.'%')->orWhere('name', 'like', '%'.$query.'%');
                if($sort_by && $sort_type){
                    $res=$res->orderBy($sort_by, $sort_type);
                }
                $res = $res->paginate();
                return response()->json(['message' => 'Get list.','data' => cleanObject($res)], 200);
            }else{
                return response()->json(['error' => "You don't have permission for this part."]);
            }
        } catch (\Exception $e) {

            return response()->json(['error' => $e->getMessage()]);
        }
    }

    public function getByid($hash_id)
    {
        try{
            if(Auth::user()->can('edit-contract-types') || Auth::user()->is_administator==1){
                $record=Contracttypes::where('hash_id',$hash_id)->get();
                return response()->json(['message' => 'Get record.','data' => cleanObject($record)], 200);
            }else{
                return response()->json(['error' => "You don't have permission for this part."]);
            }
        } catch (\Exception $e) {

            return response()->json(['error' => $e->getMessage()]);
        }
    }

    public function update(Request $request)
    {
        try{
            if(Auth::user()->can('edit-contract-types') || Auth::user()->is_administator==1){

                $hash_id=request('hash_id');
                $validator = Validator::make($request->all(), [
                    'hash_id' => 'required',
                    'name' => 'required|max:100|unique:contract_types,name,'.$hash_id.',hash_id'
                ]);

                if($validator->fails()){
                    return response()->json(['error' => $validator->errors()->all()]);
                }

                $record=Contracttypes::where('hash_id', '=', $hash_id)->first();
                if(!$record){
                    return response()->json(['error' => "Record doesn't exist."]);
                }
                $request=request()->except(['hash_id']);
                Contracttypes::where('id', $record->id)->update($request);
                setActivityLog('Contract Type Updated [ID: ' . $record->id . ', ' . request('name') . ']',json_encode($request),activityEnums('contract-types'),$record->id,Auth::user()->id);
                return response()->json(['message' => 'Record successfully updated '.$record->name.'.'], 200);
            }else{
                return response()->json(['error' => "You don't have permission for this part."]);
            }

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()]);
        }
    }

    public function create(Request $request)
    {
        try{
            if(Auth::user()->can('create-contract-types') || Auth::user()->is_administator==1){
                $validator = Validator::make($request->all(), [
                    'name' => 'required|max:100|unique:contract_types,name'
                ]);

                if($validator->fails()){
                    return response()->json(['error' => $validator->errors()->all()]);
                }

                DB::beginTransaction();
                $request=request()->all();
                $request['hash_id'] = getHashid();
                $record=Contracttypes::create($request);
                if(!$record){
                    DB::rollBack();
                    return response()->json(['error' => 'Something went to wrong while saving record.']);
                }
                DB::commit();
                setActivityLog('New Contract Type Added [ID: ' . $record->id . ', ' . request('name') . ']',json_encode($request),activityEnums('contract-types'),$record->id,Auth::user()->id);
                return response()->json(['message' => 'Record successfully created '.request('name').'.'], 200);
            }else{
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

            if($validator->fails()){
                return response()->json(['error' => $validator->errors()->all()]);
            }
            $hash_id=request('hash_id');
            $record=Contracttypes::where('hash_id', '=', $hash_id)->first();
            if(!$record){
                return response()->json(['error' => "Record doesn't exist."]);
            }
            // if(AdminRoleRelation::where('role_id', $role->id)->exists()){
            //     return response()->json(['error' => "Record related with other row so, you can't delete it."]);
            // }
            if(Auth::user()->can('delete-contract-types') || Auth::user()->is_administator==1){
                $record->delete();
                setActivityLog('Contract Type Deleted [ID: ' . $record->id . ']',json_encode($request->all()),activityEnums('contract-types'),$record->id,Auth::user()->id);
            }else{
                return response()->json(['error' => "You don't have permission for this part."]);
            }
            return response()->json(['message' => 'Record successfully deleted '.$record->name.'.'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()]);
        }
    }

    public function getRecords(Request $request)
    {
        try{
            if(Auth::user()->can('view-contract-types') || Auth::user()->is_administator==1){
                $record=Contracttypes::get();
                return response()->json(['message' => 'Get records.','data' => cleanObject($record)], 200);
            }else{
                return response()->json(['error' => "You don't have permission for this part."]);
            }
        } catch (\Exception $e) {

            return response()->json(['error' => $e->getMessage()]);
        }
    }


}