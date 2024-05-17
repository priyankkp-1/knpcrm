<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Paymentmodes;
use Hash;
use Validator;
use Auth;
use Str;
use DB;

class PaymentModeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth'); 
    }

    public function getList(Request $request)
    {
        try{
            if(Auth::user()->can('view-payment-modes') || Auth::user()->is_administator==1){
                $sort_by = $request->get('sortby');
                $sort_type = $request->get('sorttype');
                $query = $request->get('query');
                $query = str_replace(" ", "%", $query);
                $res=Paymentmodes::where('id', 'like', '%'.$query.'%')->orWhere('name', 'like', '%'.$query.'%');
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
            if(Auth::user()->can('edit-payment-modes') || Auth::user()->is_administator==1){
                $record=Paymentmodes::where('hash_id',$hash_id)->get();
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
            if(Auth::user()->can('edit-payment-modes') || Auth::user()->is_administator==1){

                $hash_id=request('hash_id');
                $validator = Validator::make($request->all(), [
                    'hash_id' => 'required',
                    'name' => 'required|max:100|unique:paymentmodes,name,'.$hash_id.',hash_id',
                    'description' => 'required',
                    'show_on_pdf' => 'required|boolean',
                    'selected_by_default' => 'required|boolean',
                    'use_only' => 'required|in:INVOICES,EXPENCES',
                    'isactive' => 'required|boolean'
                ]);

                if($validator->fails()){
                    return response()->json(['error' => $validator->errors()->all()]);
                }

                $record=Paymentmodes::where('hash_id', '=', $hash_id)->first();
                if(!$record){
                    return response()->json(['error' => "Record doesn't exist."]);
                }
                $request=request()->except(['hash_id']);
                Paymentmodes::where('id', $record->id)->update($request);
                setActivityLog('Payment Mode Updated [ID: ' . $record->id . ', ' . request('name') . ']',json_encode($request),activityEnums('payment-modes'),$record->id,Auth::user()->id);
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
            if(Auth::user()->can('create-payment-modes') || Auth::user()->is_administator==1){
                $validator = Validator::make($request->all(), [
                    'name' => 'required|max:100|unique:paymentmodes,name',
                    'description' => 'required',
                    'show_on_pdf' => 'required|boolean',
                    'selected_by_default' => 'required|boolean',
                    'use_only' => 'required|in:INVOICES,EXPENCES',
                    'isactive' => 'required|boolean'
                ]);

                if($validator->fails()){
                    return response()->json(['error' => $validator->errors()->all()]);
                }

                DB::beginTransaction();
                $request=request()->all();
                $request['hash_id'] = getHashid();
                $record=Paymentmodes::create($request);
                if(!$record){
                    DB::rollBack();
                    return response()->json(['error' => 'Something went to wrong while saving record.']);
                }
                DB::commit();
                setActivityLog('New Payment Mode Added [ID: ' . $record->id . ', ' . request('name') . ']',json_encode($request),activityEnums('payment-modes'),$record->id,Auth::user()->id);
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
            $record=Paymentmodes::where('hash_id', '=', $hash_id)->first();
            if(!$record){
                return response()->json(['error' => "Record doesn't exist."]);
            }
            // if(AdminRoleRelation::where('role_id', $role->id)->exists()){
            //     return response()->json(['error' => "Record related with other row so, you can't delete it."]);
            // }
            if(Auth::user()->can('delete-payment-modes') || Auth::user()->is_administator==1){
                $record->delete();
                setActivityLog('Payment Mode Deleted [ID: ' . $record->id . ']',json_encode($request->all()),activityEnums('payment-modes'),$record->id,Auth::user()->id);
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
            if(Auth::user()->can('view-payment-modes') || Auth::user()->is_administator==1){
                $record=Paymentmodes::get();
                return response()->json(['message' => 'Get records.','data' => cleanObject($record)], 200);
            }else{
                return response()->json(['error' => "You don't have permission for this part."]);
            }
        } catch (\Exception $e) {

            return response()->json(['error' => $e->getMessage()]);
        }
    }


}