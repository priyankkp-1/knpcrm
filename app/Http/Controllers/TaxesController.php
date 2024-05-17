<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Taxes;
use App\Models\CompanySettings;
use App\Models\Products;
use Hash;
use Validator;
use Auth;
use Str;
use DB;

class TaxesController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth'); 
    }

    public function getList(Request $request)
    {
        try{
            if(Auth::user()->can('view-taxes') || Auth::user()->is_administator==1){
                $sort_by = $request->get('sortby');
                $sort_type = $request->get('sorttype');
                $query = $request->get('query');
                $query = str_replace(" ", "%", $query);
                $res=Taxes::where('id', 'like', '%'.$query.'%')->orWhere('name', 'like', '%'.$query.'%');
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
            if(Auth::user()->can('edit-taxes') || Auth::user()->is_administator==1){
                $record=Taxes::where('hash_id',$hash_id)->get();
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
            if(Auth::user()->can('edit-taxes') || Auth::user()->is_administator==1){

                $hash_id=request('hash_id');
                $validator = Validator::make($request->all(), [
                    'hash_id' => 'required',
                    'name' => 'required|max:100|unique:taxes,name,'.$hash_id.',hash_id',
                    'taxrate' => 'required|numeric|between:0.01,9999999999.99',
                    'tax_type' => 'required|boolean'
                ]);

                if($validator->fails()){
                    return response()->json(['error' => $validator->errors()->all()]);
                }

                if(request('tax_type') == 1){
                    if(request('taxrate') > 100){
                        return response()->json(['error' => 'Taxrate more than 100 not allow.']);
                    }
                }

                $record=Taxes::where('hash_id', '=', $hash_id)->first();
                if(!$record){
                    return response()->json(['error' => "Record doesn't exist."]);
                }
                $request=request()->except(['hash_id']);
                Taxes::where('id', $record->id)->update($request);
                setActivityLog('Tax Updated [ID: ' . $record->id . ', ' . request('name') . ']',json_encode($request),activityEnums('taxes'),$record->id,Auth::user()->id);
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
            if(Auth::user()->can('create-taxes') || Auth::user()->is_administator==1){
                $validator = Validator::make($request->all(), [
                    'name' => 'required|max:100|unique:taxes,name',
                    'taxrate' => 'required|numeric|between:0.01,9999999999.99',
                    'tax_type' => 'required|boolean'
                ]);

                if($validator->fails()){
                    return response()->json(['error' => $validator->errors()->all()]);
                }

                if(request('tax_type') == 1){
                    if(request('taxrate') > 100){
                        return response()->json(['error' => 'Taxrate more than 100 not allow.']);
                    }
                }

                DB::beginTransaction();
                $request=request()->all();
                $request['hash_id'] = getHashid();
                $record=Taxes::create($request);
                if(!$record){
                    DB::rollBack();
                    return response()->json(['error' => 'Something went to wrong while saving record.']);
                }
                DB::commit();
                setActivityLog('New Tax Added [ID: ' . $record->id . ', ' . request('name') . ']',json_encode($request),activityEnums('taxes'),$record->id,Auth::user()->id);
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
            $record=Taxes::where('hash_id', '=', $hash_id)->first();
            if(!$record){
                return response()->json(['error' => "Record doesn't exist."]);
            }

            $defaulttax=CompanySettings::select('meta_value')->where('meta_key','=','default_tax')->first();
            if($defaulttax){
                if($defaulttax->meta_value == $hash_id){
                    return response()->json(['error' => "Record related with default tax so, you can't delete it."]);
                }
            }
            if(Products::where('tax_id', $record->id)->orWhere('tax2_id', $record->id)->exists()){
                return response()->json(['error' => "Record related with product so, you can't delete it."]);
            }
            if(Auth::user()->can('delete-taxes') || Auth::user()->is_administator==1){
                $record->delete();
                setActivityLog('Tax Deleted [ID: ' . $record->id . ']',json_encode($request->all()),activityEnums('taxes'),$record->id,Auth::user()->id);
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
            if(Auth::user()->can('view-taxes') || Auth::user()->is_administator==1){
                $record=Taxes::get();
                return response()->json(['message' => 'Get records.','data' => cleanObject($record)], 200);
            }else{
                return response()->json(['error' => "You don't have permission for this part."]);
            }
        } catch (\Exception $e) {

            return response()->json(['error' => $e->getMessage()]);
        }
    }


}