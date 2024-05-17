<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\WebToLead;
use App\Models\Source;
use App\Models\Status;
use App\Models\Admin;
use App\Models\Templates;
use App\Models\WebToLeadStaffNotifyRel;
use Hash;
use Validator;
use Auth;
use Str;
use DB;

class WebToLeadController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth'); 
    }

    public function getList(Request $request)
    {
        try{
            if (Auth::user()->can('view-web-to-lead') || Auth::user()->is_administator == 1) {
                $sort_by   = $request->get('sortby');
                $sort_type = $request->get('sorttype');
                $query     = $request->get('query');
                $query     = str_replace(" ", "%", $query);

                $res = WebToLead::where('id', 'like', '%'.$query.'%')->orWhere('status_id', 'like', '%'.$query.'%')->orWhere('responsible', 'like', '%'.$query.'%');
                
                if ($sort_by && $sort_type) {
                    $res = $res->orderBy($sort_by, $sort_type);
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
            if (Auth::user()->can('edit-web-to-lead') || Auth::user()->is_administator == 1) {

                $record = WebToLead::where('hash_id',$hash_id)->get();
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
            if(Auth::user()->can('edit-web-to-lead') || Auth::user()->is_administator == 1){

                $hash_id = request('hash_id');

                $validator = Validator::make($request->all(), [
                    'form_name'                      => 'required|max:240',
                    'form_data'                      => 'required|json',
                    'status_id'                      => 'required|max:255',
                    'source_id'                      => 'required|max:255',
                    'submit_button'                  => 'required|max:255',
                    'responsible'                    => 'required|max:255',
                    'message_after_success'          => 'required|max:1500',
                    'thank_you_page_link'            => 'required|url|max:1500',
                    'allow_duplicate_lead_for_entry' => 'required|boolean',
                    'mark_as_public'                 => 'required|boolean',
                    'notify_when_lead_import'        => 'required|boolean',
                    'staff_members'                  => 'json',
                    'template'                       => 'required|in:template,template2,template3'
                ]);

                $validator->setAttributeNames([
                    'status_id' => 'status',
                    'source_id' => 'source',
                 
                ]);

                if ($validator->fails()) {
                    return response()->json(['error' => $validator->errors()->all()]);
                }
                
                $source_find = Source::where('hash_id',request('source_id'))->first();
                $status_find = Status::where('hash_id',request('status_id'))->where('type','lead')->first();
                $admin_find = Admin::where('hash_id',request('responsible'))->first();
                $template_find = Templates::where('body',request('template'))->first();

                if (!$source_find) {
                    return response()->json(['error' => 'Source not found.']);
                }

                if (!$status_find) {
                    return response()->json(['error' => 'Status not found.']);
                }

                if (!$admin_find) {
                    return response()->json(['error' => 'Responsible admin not found.']);
                }

                if (!$template_find) {
                    return response()->json(['error' => 'Template not found.']);
                }

                $record = WebToLead::where('hash_id', '=', $hash_id)->first();

                if (!$record) {
                    return response()->json(['error' => "Record doesn't exist."]);
                }
                $request = request()->except(['hash_id','staff_members','template']);
                $request['status_id'] = isset($status_find)?$status_find->id:NULL;
                $request['source_id'] = isset($source_find)?$source_find->id:NULL;
                $request['responsible'] = isset($admin_find)?$admin_find->id:NULL;
                $request['template_id'] = isset($template_find)?$template_find->id:NULL;
                WebToLead::where('id', $record->id)->update($request);

                $staff_members = request('staff_members')?json_decode(request('staff_members')):'';
                if ($staff_members) {
                    $find = Admin::select('id')->whereIn('hash_id',$staff_members)->pluck('id');
                    if ($find) {
                        $record->webToLeadStaffNotifyRels()->sync($find);
                    } else {
                        return response()->json(['error' => 'Something went to wrong while saving record for staff member.']);
                    }
                }

                setActivityLog('Web To Lead Updated [ID: ' . $record->id . ', ' . request('name') . ']',json_encode($request),activityEnums('web_to_lead'),$record->id,Auth::user()->id);
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
            if (Auth::user()->can('create-web-to-lead') || Auth::user()->is_administator == 1) {
                $validator = Validator::make($request->all(), [
                    'form_name'                      => 'required|max:240',
                    'form_data'                      => 'required|json',
                    'status_id'                      => 'required|max:255',
                    'source_id'                      => 'required|max:255',
                    'submit_button'                  => 'required|max:255',
                    'responsible'                    => 'required|max:255',
                    'message_after_success'          => 'required|max:1500',
                    'thank_you_page_link'            => 'required|url|max:1500',
                    'allow_duplicate_lead_for_entry' => 'required|boolean',
                    'mark_as_public'                 => 'required|boolean',
                    'notify_when_lead_import'        => 'required|boolean',
                    'staff_members'                  => 'json',
                    'template'                       => 'required|in:template,template2,template3'
                ]);

                $validator->setAttributeNames([
                    'status_id' => 'status',
                    'source_id' => 'source',
                 
                ]);

                if ($validator->fails()) {
                    return response()->json(['error' => $validator->errors()->all()]);
                }
                
                $source_find = Source::where('hash_id',request('source_id'))->first();
                $status_find = Status::where('hash_id',request('status_id'))->where('type','lead')->first();
                $admin_find = Admin::where('hash_id',request('responsible'))->first();
                $template_find = Templates::where('body',request('template'))->first();

                if (!$source_find) {
                    return response()->json(['error' => 'Source not found.']);
                }

                if (!$status_find) {
                    return response()->json(['error' => 'Status not found.']);
                }

                if (!$admin_find) {
                    return response()->json(['error' => 'Responsible admin not found.']);
                }

                if (!$template_find) {
                    return response()->json(['error' => 'Template not found.']);
                }

                DB::beginTransaction();
                $request = request()->all();
                $request['hash_id'] = getHashid();
                $request['status_id'] = isset($status_find)?$status_find->id:NULL;
                $request['source_id'] = isset($source_find)?$source_find->id:NULL;
                $request['responsible'] = isset($admin_find)?$admin_find->id:NULL;
                $request['template_id'] = isset($template_find)?$template_find->id:NULL;
                $record = WebToLead::create($request);
                
                if (!$record) {
                    DB::rollBack();
                    return response()->json(['error' => 'Something went to wrong while saving record.']);
                }

                $staff_members = request('staff_members')?json_decode(request('staff_members')):'';
                if ($staff_members) {
                    $find = Admin::select('id')->whereIn('hash_id',$staff_members)->pluck('id');
                    if ($find) {
                        $record->webToLeadStaffNotifyRels()->sync($find);
                    } else {
                        return response()->json(['error' => 'Something went to wrong while saving record for staff member.']);
                    }
                }

                DB::commit();
                setActivityLog('New Web To Lead Added [ID: ' . $record->id . ']',json_encode($request),activityEnums('web_to_lead'),$record->id,Auth::user()->id);
                
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
            $record  = WebToLead::where('hash_id', '=', $hash_id)->first();

            if (!$record) {
                return response()->json(['error' => "Record doesn't exist."]);
            }

            if (WebToLeadStaffNotifyRel::where('web_to_lead_id', $record->id )->exists()) {
                return response()->json(['error' => "Record related with web to lead staff notify rel so, you can't delete it."]);
            }

            if (Auth::user()->can('delete-web-to-lead') || Auth::user()->is_administator == 1) {
                $record->delete();
                setActivityLog('Web To Leads Deleted [ID: ' . $record->id . ']',json_encode($request->all()),activityEnums('web_to_lead'),$record->id,Auth::user()->id);
            } else {
                return response()->json(['error' => "You don't have permission for this part."]);
            }
            return response()->json(['message' => 'Record successfully deleted '.$record->name.'.'], 200);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()]);
        }
    }


}