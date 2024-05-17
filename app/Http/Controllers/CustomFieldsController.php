<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CustomFields;
use App\Models\CustomFieldValue;
use Hash;
use Validator;
use Auth;
use Str;
use DB;

class CustomFieldsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth'); 
    }

    public function getList(Request $request)
    {
        try{
            if (Auth::user()->can('view-custom-fields') || Auth::user()->is_administator == 1) {
                $sort_by   = $request->get('sortby');
                $sort_type = $request->get('sorttype');
                $query     = $request->get('query');
                $query     = str_replace(" ", "%", $query);

                $res = CustomFields::where('id', 'like', '%'.$query.'%')->orWhere('name', 'like', '%'.$query.'%')->orWhere('field_to', 'like', '%'.$query.'%');
                
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
            if (Auth::user()->can('edit-custom-fields') || Auth::user()->is_administator == 1) {

                $record = CustomFields::where('hash_id',$hash_id)->get();
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
            if(Auth::user()->can('edit-custom-fields') || Auth::user()->is_administator == 1){

                $hash_id = request('hash_id');
                $validator = Validator::make($request->all(), [
                    'hash_id'      => 'required',
                    'field_to'     => 'required|in:lead,customer',
                    'name'         => 'required|max:240',
                    'required'     => 'required|boolean',
                    'type'         => 'required|in:input,hidden,number,textarea,select,multiselect,checkbox,date_picker,date_picker_time,colorpicker,link,radio',
                    'field_order'   => 'required|integer|max:3',
                    'active'        => 'required|boolean',
                    'show_on_table' => 'required|boolean',
                    'bs_column'     => 'required|integer|min:1|max:12',
                ]);

                if ($validator->fails()) {
                    return response()->json(['error' => $validator->errors()->all()]);
                }

                $find = CustomFields::where('name',request('name'))->where('field_to',request('field_to'))->where('hash_id', '!=', $hash_id)->first();

                if ($find) {
                    return response()->json(['error' => 'Name already exist with same field_to so, please use another name and field_to combination.']);
                }

                $record = CustomFields::where('hash_id', '=', $hash_id)->first();

                if (!$record) {
                    return response()->json(['error' => "Record doesn't exist."]);
                }

                $request = request()->except(['hash_id']);

                if (request('type') == 'multiselect' || request('type') == 'radio' || request('type') == 'select' || request('type') == 'checkbox') {
                    $validator = Validator::make($request, [
                        'options'     => 'required|regex:/.*,.*$/',
                    ]);
    
                    if ($validator->fails()) {
                        return response()->json(['error' => $validator->errors()->all()]);
                    }
                } else {
                    $request['options'] = NULL;
                }

          
                $request['slug'] = Str::slug(request('name'));
                CustomFields::where('id', $record->id)->update($request);

                setActivityLog('Custom Fields Updated [ID: ' . $record->id . ', ' . request('name') . ']',json_encode($request),activityEnums('custom_fields'),$record->id,Auth::user()->id);
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
            if (Auth::user()->can('create-custom-fields') || Auth::user()->is_administator == 1) {
                $validator = Validator::make($request->all(), [
                    'field_to'     => 'required|in:lead,customer',
                    'name'         => 'required|max:240',
                    'required'     => 'required|boolean',
                    'type'          => 'required|in:input,hidden,number,textarea,select,multiselect,checkbox,date_picker,date_picker_time,colorpicker,link,radio',
                    'field_order'   => 'required|integer|max:3',
                    'active'        => 'required|boolean',
                    'show_on_table' => 'required|boolean',
                    'bs_column'     => 'required|integer|min:1|max:12',
                ]);

                if ($validator->fails()) {
                    return response()->json(['error' => $validator->errors()->all()]);
                }

                $find = CustomFields::where('name',request('name'))->where('field_to',request('field_to'))->first();

                if ($find) {
                    return response()->json(['error' => 'Name already exist with same field-to so, please use another name and field-to combination.']);
                }

                DB::beginTransaction();
                $request = request()->all();

                if (request('type') == 'multiselect' || request('type') == 'radio' || request('type') == 'select' || request('type') == 'checkbox') {
                    $validator = Validator::make($request, [
                        'options'     => 'required|regex:/.*,.*$/',
                    ]);
    
                    if ($validator->fails()) {
                        return response()->json(['error' => $validator->errors()->all()]);
                    }
                } else {
                    $request['options'] = NULL;
                }

                $request['hash_id'] = getHashid();
                $request['slug'] = Str::slug(request('name'));

       
                $record = CustomFields::create($request);
                
                if (!$record) {
                    DB::rollBack();
                    return response()->json(['error' => 'Something went to wrong while saving record.']);
                }

                DB::commit();
                setActivityLog('New Custom Field Added [ID: ' . $record->id . ', ' . request('name') . ']',json_encode($request),activityEnums('custom_fields'),$record->id,Auth::user()->id);
                
                return response()->json(['message' => 'Record successfully created '.request('name').'.'], 200);

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
            $record  = CustomFields::where('hash_id', '=', $hash_id)->first();

            if (!$record) {
                return response()->json(['error' => "Record doesn't exist."]);
            }

            if (CustomFieldValue::where('field_id', $record->id )->exists()) {
                return response()->json(['error' => "Record related with custom field value so, you can't delete it."]);
            }

            if (Auth::user()->can('delete-custom-fields') || Auth::user()->is_administator == 1) {
                $record->delete();
                setActivityLog('Custom Fields Deleted [ID: ' . $record->id . ']',json_encode($request->all()),activityEnums('custom_fields'),$record->id,Auth::user()->id);
            } else {
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
            $field_to   = $request->get('field_to');
            if (Auth::user()->can('view-custom-fields') || Auth::user()->is_administator == 1) {
                if ($field_to) {
                    $record = CustomFields::where('field_to', $field_to)->where('active',1)->get();
                }else {
                    $record = [];
                }
                return response()->json(['message' => 'Get records.','data' => cleanObject($record)], 200);
            } else {
                return response()->json(['error' => "You don't have permission for this part."]);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()]);
        }
    }

}