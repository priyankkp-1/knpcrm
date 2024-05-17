<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Admin;
use App\Models\TaskCheckList;
use App\Models\Tasks;
use Hash,DB;
use Validator;
use Auth;

class TaskCheckListController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth'); 
    }

    public function getList(Request $request)
    {
        try{
            if (Auth::user()->can('view-task-checklist') || Auth::user()->is_administator == 1) {
                $validator = Validator::make($request->all(), [
                    'task_id'        => 'required',
                ]);

                if ($validator->fails()) {
                    return response()->json(['error' => $validator->errors()->all()]);
                }

                if(request('task_id')){
                    $task = Tasks::where('hash_id',request('task_id'))->first();
                    if(!$task){
                        return response()->json(['error' => 'Task not found.']);
                    }
                }
                $res = TaskCheckList::where('task_id',$task->id)->orderBy('list_order', 'ASC')->get();
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
            if (Auth::user()->can('edit-task-checklist') || Auth::user()->is_administator == 1) {

                $record = TaskCheckList::where('hash_id',$hash_id)->get();
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
            if(Auth::user()->can('edit-task-checklist') || Auth::user()->is_administator == 1){

                $hash_id = request('hash_id');
                $validator = Validator::make($request->all(), [
                    'description'    => 'max:1500',
                    'task_id'        => 'required',
                    'list_order'     => 'required|integer',
                    'is_finished'    => 'boolean',
                ]);

                if ($validator->fails()) {
                    return response()->json(['error' => $validator->errors()->all()]);
                }

                $record = TaskCheckList::where('hash_id', '=', $hash_id)->where('is_finished', '=', 0)->first();

                if (!$record) {
                    return response()->json(['error' => "Record doesn't exist or finished."]);
                }

                if(request('task_id')){
                    $task = Tasks::where('hash_id',request('task_id'))->first();
                    if(!$task){
                        return response()->json(['error' => 'Task not found.']);
                    }
                }

                $request=request()->except(['hash_id']);
                if(request('is_finished') && request('is_finished')==1){
                    $request['finished_from'] = Auth::user()->id;
                }
                $request['task_id'] = isset($task)?$task->id:NULL;

                TaskCheckList::where('id', $record->id)->update($request);

                setActivityLog('Task Check List Updated [ID: ' . $record->id . ', ' . request('email') . ']',json_encode($request),activityEnums('task-checklist'),$record->id,Auth::user()->id);
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
            if (Auth::user()->can('create-task-checklist') || Auth::user()->is_administator == 1) {
                $validator = Validator::make($request->all(), [
                    'description'    => 'max:1500',
                    'task_id'        => 'required',
                    'list_order'     => 'required|integer',
                    'is_finished'    => 'boolean',
                ]);

                if ($validator->fails()) {
                    return response()->json(['error' => $validator->errors()->all()]);
                }

                if(request('task_id')){
                    $task = Tasks::where('hash_id',request('task_id'))->first();
                    if(!$task){
                        return response()->json(['error' => 'Task not found.']);
                    }
                }
             
              
                DB::beginTransaction();
                $request = request()->all();
                $request['hash_id'] = getHashid();
                $request['added_from'] = Auth::user()->id;
                if(request('is_finished') && request('is_finished')==1){
                    $request['finished_from'] = Auth::user()->id;
                }
                $request['task_id'] = isset($task)?$task->id:NULL;
                $record = TaskCheckList::create($request);
                
                if (!$record) {
                    DB::rollBack();
                    return response()->json(['error' => 'Something went to wrong while saving record.']);
                }

                DB::commit();
                setActivityLog('New Task Checklist Added [ID: ' . $record->id . ']',json_encode($request),activityEnums('task-checklist'),$record->id,Auth::user()->id);
                
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
            $record  = TaskCheckList::where('hash_id', '=', $hash_id)->first();

            if (!$record) {
                return response()->json(['error' => "Record doesn't exist."]);
            }

            if (Auth::user()->can('delete-task-checklist') || Auth::user()->is_administator == 1) {
                $record->delete();
                setActivityLog('Task Check List Deleted [ID: ' . $record->id . ']',json_encode($request->all()),activityEnums('task-checklist'),$record->id,Auth::user()->id);
            } else {
                return response()->json(['error' => "You don't have permission for this part."]);
            }
            return response()->json(['message' => 'Record successfully deleted '.$record->email.'.'], 200);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()]);
        }
    }
}