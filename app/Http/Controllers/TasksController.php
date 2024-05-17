<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Admin;
use App\Models\Tasks;
use App\Models\Leads;
use App\Models\Customers;
use Hash,DB;
use Validator;
use Auth;

class TasksController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth'); 
    }

    public function getList(Request $request)
    {
        try{
            $validator = Validator::make($request->all(), [
                'type'     => 'required|in:lead,customer',
                'rel_id'   => 'required'
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()->all()]);
            }
            $type   = $request->get('type');
            $rel_id   = $request->get('rel_id');
            if (Auth::user()->can('view-tasks') || Auth::user()->is_administator == 1) {
                $sort_by   = $request->get('sortby');
                $sort_type = $request->get('sorttype');
                $query     = $request->get('query');
                $query     = str_replace(" ", "%", $query);

                if($rel_id){
                    if ($type == 'lead') {
                        $lc_field = Leads::where('hash_id',request('rel_id'))->first();
                    } else {
                        $lc_field = Customers::where('hash_id',request('rel_id'))->first();
                    }
                    if (!$lc_field) {
                        return response()->json(['error' => 'Relation record not found.']);
                    }
                }

                $res = Tasks::with('assignedTo','comments','checklists')->where('rel_type',$type)->where('rel_id',$lc_field->id)->where(function ($q) use($query) {
                    $q->where('name', 'like', '%'.$query.'%')->orWhere('priority', 'like', '%'.$query.'%')->orWhere('status', 'like', '%'.$query.'%')->orWhere('rel_type', 'like', '%'.$query.'%');
                });
                
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
            if (Auth::user()->can('edit-tasks') || Auth::user()->is_administator == 1) {

                $record = Tasks::where('hash_id',$hash_id)->get();
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
            if(Auth::user()->can('edit-tasks') || Auth::user()->is_administator == 1){

                $hash_id = request('hash_id');
                $validator = Validator::make($request->all(), [
                    'name'            => 'required|max:100',
                    'priority'        => 'required|in:low,medium,heigh,urgent',
                    'status'          => 'required|in:pending,inprogress,testing,waiting_for_feedback,complete',
                    'start_date'      => 'required|date|date_format:Y-m-d',
                    'end_date'        => 'required|date|date_format:Y-m-d',
                    'completed_date'  => 'nullable|date_format:Y-m-d H:i:s',
                    'description'     => 'max:1500',
                    'rel_type'        => 'required|in:lead,customer',
                    'rel_id'          => 'required',
                    'added_to'       => 'required|json'
                ]);

                if ($validator->fails()) {
                    return response()->json(['error' => $validator->errors()->all()]);
                }

                $record = Tasks::where('hash_id', '=', $hash_id)->first();

                if (!$record) {
                    return response()->json(['error' => "Record doesn't exist."]);
                }

                if (request('rel_id')) {
                    if (request('rel_type') == 'lead') {
                        $lc_field = Leads::where('hash_id',request('rel_id'))->first();
                    } else {
                        $lc_field = Customers::where('hash_id',request('rel_id'))->first();
                    }
    
                    if (!$lc_field) {
                        return response()->json(['error' => 'Customer or Lead not found.']);
                    }
                }

                $request=request()->except(['hash_id','added_to']);
                $request['rel_id'] = isset($lc_field)?$lc_field->id:NULL;

                Tasks::where('id', $record->id)->update($request);

                $added_to = json_decode(request('added_to'));
                if ($added_to) {
                    $find = Admin::whereIn('hash_id',$added_to)->pluck('id');
                    if ($find) {
                        $record->taskAssign()->sync($find);
                    } else {
                        return response()->json(['error' => 'Something went to wrong while saving record for task assigned.']);
                    }
                }

                setActivityLog('Tasks Updated [ID: ' . $record->id . ']',json_encode($request),activityEnums('tasks'),$record->id,Auth::user()->id);
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
            if (Auth::user()->can('create-tasks') || Auth::user()->is_administator == 1) {
                $validator = Validator::make($request->all(), [
                    'name'            => 'required|max:100',
                    'priority'        => 'required|in:low,medium,heigh,urgent',
                    'status'          => 'required|in:pending,inprogress,testing,waiting_for_feedback,complete',
                    'start_date'      => 'required|date|date_format:Y-m-d',
                    'end_date'        => 'required|date|date_format:Y-m-d',
                    'completed_date'  => 'nullable|date_format:Y-m-d H:i:s',
                    'description'     => 'max:1500',
                    'rel_type'        => 'required|in:lead,customer',
                    'rel_id'          => 'required',
                    'added_to'       => 'required|json'
                ]);

                if ($validator->fails()) {
                    return response()->json(['error' => $validator->errors()->all()]);
                }

                if (request('rel_id')) {
                    if (request('rel_type') == 'lead') {
                        $lc_field = Leads::where('hash_id',request('rel_id'))->first();
                    } else {
                        $lc_field = Customers::where('hash_id',request('rel_id'))->first();
                    }
    
                    if (!$lc_field) {
                        return response()->json(['error' => 'Customer or Lead not found.']);
                    }
                }

                DB::beginTransaction();
                $request = request()->all();
                $request['hash_id'] = getHashid();
                $request['addedfrom'] = Auth::user()->id;
                $request['rel_id'] = isset($lc_field)?$lc_field->id:NULL;
                $record = Tasks::create($request);
                
                if (!$record) {
                    DB::rollBack();
                    return response()->json(['error' => 'Something went to wrong while saving record.']);
                }

                $added_to = json_decode(request('added_to'));

                if ($added_to) {
                    $find = Admin::whereIn('hash_id',$added_to)->pluck('id');
                    if ($find) {
                        $record->taskAssign()->sync($find);
                    } else {
                        return response()->json(['error' => 'Something went to wrong while saving record for task assigned.']);
                    }
                }

                DB::commit();
                setActivityLog('New Task Added [ID: ' . $record->id . ']',json_encode($request),activityEnums('tasks'),$record->id,Auth::user()->id);
                
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
            $record  = Tasks::where('hash_id', '=', $hash_id)->first();

            if (!$record) {
                return response()->json(['error' => "Record doesn't exist."]);
            }

            if (Auth::user()->can('delete-tasks') || Auth::user()->is_administator == 1) {
                $record->delete();
                setActivityLog('Tasks Deleted [ID: ' . $record->id . ']',json_encode($request->all()),activityEnums('tasks'),$record->id,Auth::user()->id);
            } else {
                return response()->json(['error' => "You don't have permission for this part."]);
            }
            return response()->json(['message' => 'Record successfully deleted '.$record->email.'.'], 200);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()]);
        }
    }
    public function getRecords(Request $request)
    {
        try{
            $validator = Validator::make($request->all(), [
                'type'     => 'required|in:lead,customer',
                'rel_id'   => 'required'
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()->all()]);
            }
            $type   = $request->get('type');
            $rel_id   = $request->get('rel_id');
            if (Auth::user()->can('view-tasks') || Auth::user()->is_administator == 1) {
                if($rel_id){
                    if ($type == 'lead') {
                        $lc_field = Leads::where('hash_id',request('rel_id'))->first();
                    } else {
                        $lc_field = Customers::where('hash_id',request('rel_id'))->first();
                    }
                    if (!$lc_field) {
                        return response()->json(['error' => 'Relation record not found.']);
                    }
                }
                $record = Tasks::with('assignedTo','comments','checklists')->where('rel_type',$type)->where('rel_id',$lc_field->id)->where('rel_type', 'like', '%'.$type.'%')->get();
                return response()->json(['message' => 'Get records.','data' => cleanObject($record)], 200);
            } else {
                return response()->json(['error' => "You don't have permission for this part."]);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()]);
        }
    }
}