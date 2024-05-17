<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Announcement;
use Hash;
use Validator;
use Auth;
use Str;
use DB;

class AnnouncementController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth'); 
    }

    public function getList(Request $request)
    {
        try{
            if(Auth::user()->can('view-announcement') || Auth::user()->is_administator==1){
                $sort_by = $request->get('sortby');
                $sort_type = $request->get('sorttype');
                $query = $request->get('query');
                $query = str_replace(" ", "%", $query);
                $res=Announcement::where('id', 'like', '%'.$query.'%')->orWhere('name', 'like', '%'.$query.'%');
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
            if(Auth::user()->can('edit-announcement') || Auth::user()->is_administator==1){
                $record=Announcement::where('hash_id',$hash_id)->get();
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
            if(Auth::user()->can('edit-announcement') || Auth::user()->is_administator==1){

                $hash_id=request('hash_id');
                $validator = Validator::make($request->all(), [
                    'hash_id' => 'required',
                    'name' => 'required|max:100',
                    'message' => 'required',
                    'showtousers' => 'required|boolean',
                    'showtostaff' => 'required|boolean',
                    'showname' => 'required|boolean'
                ]);

                if($validator->fails()){
                    return response()->json(['error' => $validator->errors()->all()]);
                }

                $record=Announcement::where('hash_id', '=', $hash_id)->first();
                if(!$record){
                    return response()->json(['error' => "Record doesn't exist."]);
                }
                $request=request()->except(['hash_id']);
                $request['username'] = Auth::user()->first_name.' '.Auth::user()->last_name;
                Announcement::where('id', $record->id)->update($request);
                setActivityLog('Announcement Updated [ID: ' . $record->id . ', ' . request('name') . ']',json_encode($request),activityEnums('announcement'),$record->id,Auth::user()->id);
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
            if(Auth::user()->can('create-announcement') || Auth::user()->is_administator==1){
                $validator = Validator::make($request->all(), [
                    'name' => 'required|max:100',
                    'message' => 'required',
                    'showtousers' => 'required|boolean',
                    'showtostaff' => 'required|boolean',
                    'showname' => 'required|boolean'
                ]);

                if($validator->fails()){
                    return response()->json(['error' => $validator->errors()->all()]);
                }

                DB::beginTransaction();
                $request=request()->all();
                $request['hash_id'] = getHashid();
                $request['username'] = Auth::user()->first_name.' '.Auth::user()->last_name;
                $record=Announcement::create($request);
                if(!$record){
                    DB::rollBack();
                    return response()->json(['error' => 'Something went to wrong while saving record.']);
                }
                DB::commit();
                setActivityLog('New Announcement Added [ID: ' . $record->id . ', ' . request('name') . ']',json_encode($request),activityEnums('announcement'),$record->id,Auth::user()->id);
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
            $record=Announcement::where('hash_id', '=', $hash_id)->first();
            if(!$record){
                return response()->json(['error' => "Record doesn't exist."]);
            }

            // if(AdminRoleRelation::where('role_id', $role->id)->exists()){
            //     return response()->json(['error' => "Record related with other row so, you can't delete it."]);
            // }
            if(Auth::user()->can('delete-announcement') || Auth::user()->is_administator==1){
                $record->delete();
                setActivityLog('Announcement Deleted [ID: ' . $record->id . ']',json_encode($request->all()),activityEnums('announcement'),$record->id,Auth::user()->id);
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

            $record=\DB::select("select `t1`.* from `announcements` as `t1` left join `dismissed_announcement` as `t2` on `t2`.`announcement_id` = `t1`.`id` and `t2`.`reftype` = 'staff' AND `t2`.`ref_id` = ".Auth::user()->id." where `t2`.`announcement_id` is null and `t1`.`showtostaff` = 1");
            return response()->json(['message' => 'Get records.','data' => cleanObject($record)], 200);
            
        } catch (\Exception $e) {

            return response()->json(['error' => $e->getMessage()]);
        }
    }

    
    public function staffdissmissed($hash_id)
    {
        try{
            $record=Announcement::where('hash_id',$hash_id)->first();
            if($record){
                dissmissed_announcement($record->id,Auth::user()->id);
            }
            return response()->json(['message' => 'Successfully Dismissed.','data' => []], 200);
        } catch (\Exception $e) {

            return response()->json(['error' => $e->getMessage()]);
        }
    }


}