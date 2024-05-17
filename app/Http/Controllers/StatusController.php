<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Status;
use App\Models\WebToLead;
use App\Models\CompanySettings;
use Hash;
use Validator;
use Auth;
use Str;
use DB;

class StatusController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth'); 
    }

    public function getList(Request $request)
    {
        try{
            if(Auth::user()->can('view-status') || Auth::user()->is_administator==1){
                $sort_by = $request->get('sortby');
                $sort_type = $request->get('sorttype');
                $query = $request->get('query');
                $query = str_replace(" ", "%", $query);
                $res=Status::where('id', 'like', '%'.$query.'%')->orWhere('name', 'like', '%'.$query.'%');
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
            if(Auth::user()->can('edit-status') || Auth::user()->is_administator==1){
                $record=Status::where('hash_id',$hash_id)->get();
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
            if(Auth::user()->can('edit-status') || Auth::user()->is_administator==1){

                $hash_id=request('hash_id');
                $validator = Validator::make($request->all(), [
                    'hash_id' => 'required',
                    'name' => 'required|max:100',
                    'statusorder' => 'required|integer|max:3',
                    'type' => 'required|in:lead,customer',
                    'color' => 'required|max:10',
                    'isdefault' => 'required|boolean'
                ]);

                if($validator->fails()){
                    return response()->json(['error' => $validator->errors()->all()]);
                }

                $find=Status::where('name',request('name'))->where('type',request('type'))->where('hash_id', '!=', $hash_id)->first();

                if($find){
                    return response()->json(['error' => 'Name already exist with same type so, please use another name and type combination.']);
                }
                $record=Status::where('hash_id', '=', $hash_id)->first();
                if(!$record){
                    return response()->json(['error' => "Record doesn't exist."]);
                }
                $request=request()->except(['hash_id']);
                Status::where('id', $record->id)->update($request);
                setActivityLog('Status Updated [ID: ' . $record->id . ', ' . request('name') . ']',json_encode($request),activityEnums('status'),$record->id,Auth::user()->id);
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
            if(Auth::user()->can('create-status') || Auth::user()->is_administator==1){
                $validator = Validator::make($request->all(), [
                    'name' => 'required|max:100',
                    'statusorder' => 'required|integer|max:3',
                    'type' => 'required|in:lead,customer',
                    'color' => 'required|max:10',
                    'isdefault' => 'required|boolean'
                ]);

                if($validator->fails()){
                    return response()->json(['error' => $validator->errors()->all()]);
                }

                $find=Status::where('name',request('name'))->where('type',request('type'))->first();

                if($find){
                    return response()->json(['error' => 'Name already exist with same type so, please use another name and type combination.']);
                }

                DB::beginTransaction();
                $request=request()->all();
                $request['hash_id'] = getHashid();
                $record=Status::create($request);
                if(!$record){
                    DB::rollBack();
                    return response()->json(['error' => 'Something went to wrong while saving record.']);
                }
                DB::commit();
                setActivityLog('New Status Added [ID: ' . $record->id . ', ' . request('name') . ']',json_encode($request),activityEnums('status'),$record->id,Auth::user()->id);
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
            $record=Status::where('hash_id', '=', $hash_id)->first();
            if(!$record){
                return response()->json(['error' => "Record doesn't exist."]);
            }

            if(WebToLead::where('status_id', $record->id)->exists()){
                return response()->json(['error' => "Record related with web to lead so, you can't delete it."]);
            }

            $defaultstatus = CompanySettings::select('meta_value')->where('meta_value',$hash_id)->whereIn('meta_key',['lead_converted_status','customer_default_status'])->get();

            if($defaultstatus){
                return response()->json(['error' => "Record related with default status so, you can't delete it."]);
            }
	    
	    if(WebToLead::where('status_id', $record->id)->exists()){
                return response()->json(['error' => "Record related with web to lead so, you can't delete it."]);
            }

            if(Auth::user()->can('delete-status') || Auth::user()->is_administator==1){
                $record->delete();
                setActivityLog('Status Deleted [ID: ' . $record->id . ']',json_encode($request->all()),activityEnums('status'),$record->id,Auth::user()->id);
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
            if(Auth::user()->can('view-status') || Auth::user()->is_administator==1){
                $validator = Validator::make($request->all(), [
                    'type' => 'required|in:lead,customer',
                ]);
    
                if($validator->fails()){
                    return response()->json(['error' => $validator->errors()->all()]);
                }
                $record=Status::where('type',request('type'))->get();
                return response()->json(['message' => 'Get records.','data' => cleanObject($record)], 200);
            }else{
                return response()->json(['error' => "You don't have permission for this part."]);
            }
        } catch (\Exception $e) {

            return response()->json(['error' => $e->getMessage()]);
        }
    }


}