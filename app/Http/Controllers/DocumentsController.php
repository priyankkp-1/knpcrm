<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Admin;
use App\Models\Leads;
use App\Models\Documents;
use App\Models\FileUpload;
use Hash,DB;
use Validator;
use Auth;

class DocumentsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth'); 
    }

    public function getList(Request $request)
    {
        try{
            if (Auth::user()->can('view-lead-documents') || Auth::user()->is_administator == 1) {
                $validator = Validator::make($request->all(), [
                    'lead_id'        => 'required',
                ]);

                if ($validator->fails()) {
                    return response()->json(['error' => $validator->errors()->all()]);
                }

                if(request('lead_id')){
                    $lead = Leads::where('hash_id',request('lead_id'))->first();
                    if(!$lead){
                        return response()->json(['error' => 'Lead not found.']);
                    }
                }
                $res = Documents::where('lead_id',$lead->id)->with('file')->orderBy('id', 'desc');
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
            if (Auth::user()->can('edit-lead-documents') || Auth::user()->is_administator == 1) {
                $record = Documents::with('file')->where('hash_id',$hash_id)->get();
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
            if(Auth::user()->can('edit-lead-documents') || Auth::user()->is_administator == 1){

                $hash_id = request('hash_id');
                $validator = Validator::make($request->all(), [
                    'lead_id'  => 'required',
                    'file'     => 'nullable|mimes:jpg,jpeg,png,pdf,xlsx,xls,docx,doc,csv,txt,ppt,pptx,html,xml,rtf,odt,ods,zip|max:500000',
                ]);

                if ($validator->fails()) {
                    return response()->json(['error' => $validator->errors()->all()]);
                }

                $record = Documents::where('hash_id', '=', $hash_id)->first();

                if (!$record) {
                    return response()->json(['error' => "Record doesn't exist."]);
                }

                if(request('lead_id')){
                    $lead = Leads::where('hash_id',request('lead_id'))->first();
                    if(!$lead){
                        return response()->json(['error' => 'Lead not found.']);
                    }
                }

                $request = request()->except(['hash_id']);
                $request = request()->except(['file']);
                $request['lead_id'] = isset($lead)?$lead->id:NULL;

                Documents::where('id', $record->id)->update($request);

                if(request('file')) {
                    $file = new FileUploadController();
                    $uploadedFile = DB::table('files')->where('id',$record->file_id)->first();
                    $folder_name = 'lead_document';
                    $file->removeFromFolder($uploadedFile,$folder_name);
                    $lead_document = $file->upload(request(), 'file', $folder_name);
                    $record->file_id = $lead_document;
                    $record->save();
                }

                setActivityLog('Lead Document Updated [ID: ' . $record->id . ']',json_encode($request),activityEnums('lead-documents'),$record->id,Auth::user()->id);
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
            if (Auth::user()->can('create-lead-documents') || Auth::user()->is_administator == 1) {
                $validator = Validator::make($request->all(), [
                    'lead_id'  => 'required',
                    'file'     => 'nullable|mimes:jpg,jpeg,png,pdf,xlsx,xls,docx,doc,csv,txt,ppt,pptx,html,xml,rtf,odt,ods,zip|max:500000',
                ]);

                if ($validator->fails()) {
                    return response()->json(['error' => $validator->errors()->all()]);
                }

                if(request('lead_id')){
                    $lead = Leads::where('hash_id',request('lead_id'))->first();
                    if(!$lead){
                        return response()->json(['error' => 'Lead not found.']);
                    }
                }

                DB::beginTransaction();
                $request = request()->all();
                $request['hash_id'] = getHashid();
                $request['lead_id'] = isset($lead)?$lead->id:NULL;
                $record = Documents::create($request);
                
                if (!$record) {
                    DB::rollBack();
                    return response()->json(['error' => 'Something went to wrong while saving record.']);
                }

                if(request('file')){
                    $file = new FileUploadController();
                    $lead_document = $file->upload(request(), 'file', 'lead_document');
                    $record->file_id = $lead_document;
                    $record->save();
                }

                DB::commit();
                setActivityLog('New Lead Document Added [ID: ' . $record->id . ']',json_encode($request),activityEnums('lead-documents'),$record->id,Auth::user()->id);
                
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
            $record  = Documents::where('hash_id', '=', $hash_id)->first();

            if (!$record) {
                return response()->json(['error' => "Record doesn't exist."]);
            }

            if (Auth::user()->can('delete-lead-documents') || Auth::user()->is_administator == 1) {
                $file = new FileUploadController();
                $uploadedFile = DB::table('files')->where('id',$record->file_id)->first();
                $folder_name = 'lead_document';
                $file->removeFromFolder($uploadedFile,$folder_name);
                $record->delete();
                setActivityLog('Lead Document Deleted [ID: ' . $record->id . ']',json_encode($request->all()),activityEnums('lead-documents'),$record->id,Auth::user()->id);
            } else {
                return response()->json(['error' => "You don't have permission for this part."]);
            }
            return response()->json(['message' => 'Record successfully deleted '], 200);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()]);
        }
    }

}