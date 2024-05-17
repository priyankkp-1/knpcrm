<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Source;
use App\Models\WebToLead;
use Hash;
use Validator;
use Auth;
use Str;
use DB;

class SourceController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth'); 
    }

    public function getList(Request $request)
    {
        try{
            if(Auth::user()->can('view-source') || Auth::user()->is_administator==1){
                $sort_by = $request->get('sortby');
                $sort_type = $request->get('sorttype');
                $query = $request->get('query');
                $query = str_replace(" ", "%", $query);
                $res=Source::where('id', 'like', '%'.$query.'%')->orWhere('name', 'like', '%'.$query.'%');
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
            if(Auth::user()->can('edit-source') || Auth::user()->is_administator==1){
                $source=Source::where('hash_id',$hash_id)->get();
                return response()->json(['message' => 'Get record.','data' => cleanObject($source)], 200);
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
            if(Auth::user()->can('edit-source') || Auth::user()->is_administator==1){

                $hash_id=request('hash_id');
                $validator = Validator::make($request->all(), [
                    'hash_id' => 'required',
                    'name' => 'required|max:100|unique:sources,name,'.$hash_id.',hash_id',
                ]);

                if($validator->fails()){
                    return response()->json(['error' => $validator->errors()->all()]);
                }
                $source=Source::where('hash_id', '=', $hash_id)->first();
                if(!$source){
                    return response()->json(['error' => "Record doesn't exist."]);
                }
                $request=request()->except(['hash_id']);
                Source::where('id', $source->id)->update($request);
                setActivityLog('Source Updated [ID: ' . $source->id . ', ' . request('name') . ']',json_encode($request),activityEnums('source'),$source->id,Auth::user()->id);
                return response()->json(['message' => 'Record successfully updated '.$source->name.'.'], 200);
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
            if(Auth::user()->can('create-source') || Auth::user()->is_administator==1){
                $validator = Validator::make($request->all(), [
                    'name' => 'required|max:100|unique:sources,name',
                ]);

                if($validator->fails()){
                    return response()->json(['error' => $validator->errors()->all()]);
                }

                DB::beginTransaction();
                $request=request()->all();
                $request['hash_id'] = getHashid();
                $source=Source::create($request);
                if(!$source){
                    DB::rollBack();
                    return response()->json(['error' => 'Something went to wrong while saving record.']);
                }
                DB::commit();
                setActivityLog('New Source Added [ID: ' . $source->id . ', ' . request('name') . ']',json_encode($request),activityEnums('source'),$source->id,Auth::user()->id);
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
            $source=Source::where('hash_id', '=', $hash_id)->first();
            if(!$source){
                return response()->json(['error' => "Record doesn't exist."]);
            }
            if(WebToLead::where('source_id', $record->id)->exists()){
                return response()->json(['error' => "Record related with web to lead so, you can't delete it."]);
            }
            // if(AdminRoleRelation::where('role_id', $role->id)->exists()){
            //     return response()->json(['error' => "Record related with other row so, you can't delete it."]);
            // }
            if(Auth::user()->can('delete-source') || Auth::user()->is_administator==1){
                $source->delete();
                setActivityLog('Source Deleted [ID: ' . $source->id . ']',json_encode($request->all()),activityEnums('source'),$source->id,Auth::user()->id);
            }else{
                return response()->json(['error' => "You don't have permission for this part."]);
            }
            return response()->json(['message' => 'Record successfully deleted '.$source->name.'.'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()]);
        }
    }



}