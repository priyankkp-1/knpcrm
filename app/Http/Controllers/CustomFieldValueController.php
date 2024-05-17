<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CustomFields;
use App\Models\CustomFieldValue;
use App\Models\Customers;
use App\Models\Leads;
use Hash;
use Validator;
use Auth;
use Str;
use DB;

class CustomFieldValueController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth'); 
    }

    public function create(Request $request)
    {
        try{
            $validator = Validator::make($request->all(), [
                'field_to'      => 'required|in:lead,customer',
                'value'         => 'required|array',
                'field_id'      => 'max:255',
                'rel_column_id' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()->all()]);
            }

            if (request('field_id')) {
                $field = CustomFields::where('hash_id',request('field_id'))->first();
                if (!$field) {
                    return response()->json(['error' => 'Custom Field not found.']);
                }
            }

            if (request('rel_column_id')) {
                if (request('field_to') == 'lead') {
                    $lc_field = Leads::where('hash_id',request('rel_column_id'))->first();
                } else {
                    $lc_field = Customers::where('hash_id',request('rel_column_id'))->first();
                }

                if (!$lc_field) {
                    return response()->json(['error' => 'Customer or Lead not found.']);
                }
            }


            DB::beginTransaction();
            $request = request()->all();
            $request['hash_id'] = getHashid();
            $request['field_id'] = isset($field)?$field->id:NULL;
            $request['rel_column_id'] = isset($lc_field)?$lc_field->id:NULL;
            $record = CustomFieldValue::create($request);

            if (!$record) {
                DB::rollBack();
                return response()->json(['error' => 'Something went to wrong while saving record.']);
            }

            DB::commit();
            setActivityLog('New Custom Field Value Added [ID: ' . $record->id . ']',json_encode($request),activityEnums('custom-field-value'),$record->id,Auth::user()->id);
            
            return response()->json(['message' => 'Record successfully created '], 200);

           

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()]);
        }
    }

    public static function customFieldLeadCustomerRel($type,$type_id,$value) 
    {
        $newvalue = [];
        CustomFieldValue::where('rel_column_id', $type_id)->where('field_to', $type)->delete();

        $value_array = $value?json_decode($value):[];

        if ($value_array) {

            $key_val_array = arrayByToKeys($value_array,'id','val');
            $array_key = array_keys($key_val_array);
            $array_val = array_values($key_val_array);

            $customFieldRec = CustomFields::whereIn('hash_id',$array_key)->where('field_to','=',$type)->pluck('id','hash_id');
           
            if ($customFieldRec) {
                foreach ($customFieldRec as $key => $c_value) {
                    if ($key_val_array && $customFieldRec && $key_val_array[$key] && $customFieldRec[$key] ) {
                        $hash_id = getHashid();
                        $newvalue [] = [
                            'hash_id'       => $hash_id,
                            'field_to'      => $type,
                            'value'         => $key_val_array[$key],
                            'rel_column_id' => $type_id,
                            'deleted_at'    => NULL,
                            'field_id'      => $customFieldRec[$key],
                        ];
                    }
                }

                DB::table('custom_field_value')->upsert(
                    $newvalue, 
                    ['field_to', 'rel_column_id','field_id'], 
                    ['value','deleted_at']
                );
                return 1;
            }else {
                return 0;
            }
          
        }else{
            return 0;
        }
    }

    public function getRecords(Request $request)
    {
        try{
            $record = CustomFieldValue::with('customField:id,name')->select('field_id','value','id')
                ->where('rel_column_id',request('rel_column_id'))
                ->where('field_to',request('field_to'))
                ->get();

                $customFieldData = $record->map(function ($record) {
                    return [
                        'id' =>$record->id,
                        'custom_field_id'    => $record->customField->id,
                        'name'  => $record->customField->name,
                        'value' => $record->value,
                    ];
                })->toArray();


            return response()->json(['message' => 'Get records.','data' => cleanObject($customFieldData)], 200);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()]);
        }
    }

}