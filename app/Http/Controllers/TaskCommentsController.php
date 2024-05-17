<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Admin;
use App\Models\TaskComments;
use App\Models\Tasks;
use App\Models\FileUpload;
use Hash,DB;
use Validator;
use Auth;

class TaskCommentsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth'); 
    }

    public function getList(Request $request)
    {
        try{
            if (Auth::user()->can('view-task-comments') || Auth::user()->is_administator == 1) {
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
                $res = TaskComments::where('task_id',$task->id)->with('file')->orderBy('id', 'desc');
                $res = $res->get();
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
            if (Auth::user()->can('edit-task-comments') || Auth::user()->is_administator == 1) {

                $record = TaskComments::with('file')->where('hash_id',$hash_id)->get();
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
            if(Auth::user()->can('edit-task-comments') || Auth::user()->is_administator == 1){

                $hash_id = request('hash_id');
                $validator = Validator::make($request->all(), [
                    'content' => 'max:1500',
                    'task_id' => 'required',
                    'file'    => 'mimes:jpg,jpeg,png,gif|max:10000',
                ]);

                if ($validator->fails()) {
                    return response()->json(['error' => $validator->errors()->all()]);
                }

                $record = TaskComments::where('hash_id', '=', $hash_id)->first();

                if (!$record) {
                    return response()->json(['error' => "Record doesn't exist."]);
                }

                if(request('task_id')){
                    $task = Tasks::where('hash_id',request('task_id'))->first();
                    if(!$task){
                        return response()->json(['error' => 'Task not found.']);
                    }
                }

                $request=request()->except(['hash_id']);
                $request = request()->except(['file']);
                $request['staff_id'] = Auth::user()->id;
                $request['task_id'] = isset($task)?$task->id:NULL;

                TaskComments::where('id', $record->id)->update($request);

                
                if(request('file')){
                    $file = new FileUploadController();
                    $uploadedFile = DB::table('files')->where('id',$record->file_id)->first();
                    $folder_name = 'task_comment';
                    $file->removeFromFolder($uploadedFile,$folder_name);
                    $task_comment_pic=$file->upload(request(), 'file', 'task_comment');
                    $record->file_id = $task_comment_pic;
                    $record->save();
                }

                setActivityLog('Task Comments Updated [ID: ' . $record->id . ', ' . request('email') . ']',json_encode($request),activityEnums('task-comments'),$record->id,Auth::user()->id);
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
                    'content' => 'max:1500',
                    'task_id' => 'required',
                    'file'    => 'mimes:jpg,jpeg,png,gif|max:10000',
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
                $request['staff_id'] = Auth::user()->id;
                $request['task_id'] = isset($task)?$task->id:NULL;
                $record = TaskComments::create($request);
                
                if (!$record) {
                    DB::rollBack();
                    return response()->json(['error' => 'Something went to wrong while saving record.']);
                }

                if(request('file')){
                    $file = new FileUploadController();
                    $task_comment_pic=$file->upload(request(), 'file', 'task_comment');
                    $record->file_id = $task_comment_pic;
                    $record->save();
                }

                DB::commit();
                setActivityLog('New Task Comment Added [ID: ' . $record->id . ']',json_encode($request),activityEnums('task-comments'),$record->id,Auth::user()->id);
                
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
            $record  = TaskComments::where('hash_id', '=', $hash_id)->first();

            if (!$record) {
                return response()->json(['error' => "Record doesn't exist."]);
            }

            if (Auth::user()->can('delete-task-comments') || Auth::user()->is_administator == 1) {
                $file = new FileUploadController();
                $uploadedFile = DB::table('files')->where('id',$record->file_id)->first();
                $folder_name = 'task_comment';
                $file->removeFromFolder($uploadedFile,$folder_name);
                $record->delete();
                setActivityLog('Task Comment Deleted [ID: ' . $record->id . ']',json_encode($request->all()),activityEnums('task-comments'),$record->id,Auth::user()->id);
            } else {
                return response()->json(['error' => "You don't have permission for this part."]);
            }
            return response()->json(['message' => 'Record successfully deleted '], 200);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()]);
        }
    }

}