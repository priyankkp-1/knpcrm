<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TaskDocuments;
use App\Models\Tasks;
use Hash,DB;
use Validator;
use Auth;

class TaskDocumentsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth'); 
    }

    public function getList(Request $request)
    {
        try{
            if (Auth::user()->can('view-task-documents') || Auth::user()->is_administator == 1) {
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
                $res = TaskDocuments::where('task_id',$task->id)->with('file')->orderBy('id', 'desc');
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
            if (Auth::user()->can('edit-task-documents') || Auth::user()->is_administator == 1) {
                $record = TaskDocuments::with('file')->where('hash_id',$hash_id)->get();
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
            if(Auth::user()->can('edit-task-documents') || Auth::user()->is_administator == 1){

                $hash_id = request('hash_id');
                $validator = Validator::make($request->all(), [
                    'task_id'  => 'required',
                    'file'     => 'nullable|mimes:jpg,jpeg,png,pdf,xlsx,xls,docx,doc,csv,txt,ppt,pptx,html,xml,rtf,odt,ods,zip|max:500000',
                ]);

                if ($validator->fails()) {
                    return response()->json(['error' => $validator->errors()->all()]);
                }

                $record = TaskDocuments::where('hash_id', '=', $hash_id)->first();

                if (!$record) {
                    return response()->json(['error' => "Record doesn't exist."]);
                }

                if(request('task_id')){
                    $task = Tasks::where('hash_id',request('task_id'))->first();
                    if(!$task){
                        return response()->json(['error' => 'Task not found.']);
                    }
                }

                $request = request()->except(['hash_id']);
                $request = request()->except(['file']);
                $request['task_id'] = isset($task)?$task->id:NULL;

                TaskDocuments::where('id', $record->id)->update($request);

                if(request('file')) {
                    $file = new FileUploadController();
                    $uploadedFile = DB::table('files')->where('id',$record->file_id)->first();
                    $folder_name = 'task_document';
                    $file->removeFromFolder($uploadedFile,$folder_name);
                    $task_document = $file->upload(request(), 'file', 'task_document');
                    $record->file_id = $task_document;
                    $record->save();
                }

                setActivityLog('Task Document Updated [ID: ' . $record->id . ']',json_encode($request),activityEnums('task-documents'),$record->id,Auth::user()->id);
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
            if (Auth::user()->can('create-task-documents') || Auth::user()->is_administator == 1) {
                $validator = Validator::make($request->all(), [
                    'task_id'  => 'required',
                    'file'     => 'nullable|mimes:jpg,jpeg,png,pdf,xlsx,xls,docx,doc,csv,txt,ppt,pptx,html,xml,rtf,odt,ods,zip|max:500000',
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
                $request['task_id'] = isset($task)?$task->id:NULL;
                $record = TaskDocuments::create($request);
                
                if (!$record) {
                    DB::rollBack();
                    return response()->json(['error' => 'Something went to wrong while saving record.']);
                }

                if(request('file')){
                    $file = new FileUploadController();
                    $task_document = $file->upload(request(), 'file', 'task_document');
                    $record->file_id = $task_document;
                    $record->save();
                }

                DB::commit();
                setActivityLog('New Task Document Added [ID: ' . $record->id . ']',json_encode($request),activityEnums('task-documents'),$record->id,Auth::user()->id);
                
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
            $record  = TaskDocuments::where('hash_id', '=', $hash_id)->first();

            if (!$record) {
                return response()->json(['error' => "Record doesn't exist."]);
            }

            if (Auth::user()->can('delete-task-documents') || Auth::user()->is_administator == 1) {
                $file = new FileUploadController();
                $uploadedFile = DB::table('files')->where('id',$record->file_id)->first();
                $folder_name = 'task_document';
                $file->removeFromFolder($uploadedFile,$folder_name);
                $record->delete();
                setActivityLog('Task Document Deleted [ID: ' . $record->id . ']',json_encode($request->all()),activityEnums('task-documents'),$record->id,Auth::user()->id);
            } else {
                return response()->json(['error' => "You don't have permission for this part."]);
            }
            return response()->json(['message' => 'Record successfully deleted '], 200);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()]);
        }
    }

}