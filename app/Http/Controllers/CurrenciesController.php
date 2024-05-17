<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Currencies;
use Hash;
use Validator;
use Auth;
use Str;
use DB;

class CurrenciesController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth'); 
    }

    public function getList(Request $request)
    {
        try{
            if(Auth::user()->can('view-currencies') || Auth::user()->is_administator==1){
                $sort_by = $request->get('sortby');
                $sort_type = $request->get('sorttype');
                $query = $request->get('query');
                $query = str_replace(" ", "%", $query);
                $res=Currencies::where('id', 'like', '%'.$query.'%')->orWhere('name', 'like', '%'.$query.'%');
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
            if(Auth::user()->can('edit-currencies') || Auth::user()->is_administator==1){
                $record=Currencies::where('hash_id',$hash_id)->get();
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
            if(Auth::user()->can('edit-currencies') || Auth::user()->is_administator==1){

                $hash_id=request('hash_id');
                $validator = Validator::make($request->all(), [
                    'hash_id' => 'required',
                    'name' => 'required|max:100|unique:currencies,name,'.$hash_id.',hash_id',
                    'symbol' => 'required|max:5',
                    'decimal_separator' => 'required|max:3',
                    'thousand_separator' => 'required|max:3',
                    'placement' => 'required|in:BEFORE,AFTER',
                    'isdefault' => 'required|boolean'
                ]);

                if($validator->fails()){
                    return response()->json(['error' => $validator->errors()->all()]);
                }

                if(request('isdefault')==1){
                    $getone=Currencies::where('hash_id', '!=', $hash_id)->where('isdefault',1)->first();
                    if($getone){
                        return response()->json(['error' => "Already one currency default on set so, you can't add more."]);
                    }
                }

                $record=Currencies::where('hash_id', '=', $hash_id)->first();
                if(!$record){
                    return response()->json(['error' => "Record doesn't exist."]);
                }
                $request=request()->except(['hash_id']);
                Currencies::where('id', $record->id)->update($request);
                setActivityLog('Currency Updated [ID: ' . $record->id . ', ' . request('name') . ']',json_encode($request),activityEnums('currencies'),$record->id,Auth::user()->id);
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
            if(Auth::user()->can('create-currencies') || Auth::user()->is_administator==1){
                $validator = Validator::make($request->all(), [
                    'name' => 'required|max:100|unique:currencies,name',
                    'symbol' => 'required|max:5',
                    'decimal_separator' => 'required|max:3',
                    'thousand_separator' => 'required|max:3',
                    'placement' => 'required|in:BEFORE,AFTER',
                    'isdefault' => 'required|boolean'
                ]);

                if($validator->fails()){
                    return response()->json(['error' => $validator->errors()->all()]);
                }

                if(request('isdefault')==1){
                    $getone=Currencies::where('isdefault',1)->first();
                    if($getone){
                        return response()->json(['error' => "Already one currency default on set so, you can't add more."]);
                    }
                }

                DB::beginTransaction();
                $request=request()->all();
                $request['hash_id'] = getHashid();
                $record=Currencies::create($request);
                if(!$record){
                    DB::rollBack();
                    return response()->json(['error' => 'Something went to wrong while saving record.']);
                }
                DB::commit();
                setActivityLog('New currency Added [ID: ' . $record->id . ', ' . request('name') . ']',json_encode($request),activityEnums('currencies'),$record->id,Auth::user()->id);
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
            $record=Currencies::where('hash_id', '=', $hash_id)->first();
            if(!$record){
                return response()->json(['error' => "Record doesn't exist."]);
            }
            // if(AdminRoleRelation::where('role_id', $role->id)->exists()){
            //     return response()->json(['error' => "Record related with other row so, you can't delete it."]);
            // }
            if(Auth::user()->can('delete-currencies') || Auth::user()->is_administator==1){
                $record->delete();
                setActivityLog('Currency Deleted [ID: ' . $record->id . ']',json_encode($request->all()),activityEnums('currencies'),$record->id,Auth::user()->id);
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
            if(Auth::user()->can('view-currencies') || Auth::user()->is_administator==1){
                $record=Currencies::get();
                return response()->json(['message' => 'Get records.','data' => cleanObject($record)], 200);
            }else{
                return response()->json(['error' => "You don't have permission for this part."]);
            }
        } catch (\Exception $e) {

            return response()->json(['error' => $e->getMessage()]);
        }
    }

    public function exchangeRate(Request $request)
    {
        try{
            if(Auth::user()->can('edit-exchange-currencies') || Auth::user()->is_administator==1){
                $validator = Validator::make($request->all(), [
                    'to_currency_id' => 'required',
                    'exchange_rate' => 'required|between:0.01,99999.99'
                ]);

                if($validator->fails()){
                    return response()->json(['error' => $validator->errors()->all()]);
                }

                
                $base_currency=Currencies::where('isdefault',1)->first();
                if(!$base_currency){
                    return response()->json(['error' => "Please set first default currency."]);
                }
                $baseid=$base_currency->id;


                $to_currency=Currencies::where('isdefault',0)->where('hash_id',request('to_currency_id'))->first();
                if(!$to_currency){
                    return response()->json(['error' => "Invalid second currency or it's self default."]);
                }
                $toid=$to_currency->id;

                DB::table('exchange_rate')->upsert(
                    [
                        ['base_currency_id' => $baseid, 'to_currency_id' => $toid, 'exchange_rate' => request('exchange_rate')]
                    ], 
                    ['base_currency_id', 'to_currency_id', 'exchange_rate'], 
                    ['exchange_rate']
                );

                setActivityLog('Exchange currency rate Updated [ID: ' . $toid . ', ' .$base_currency->name.' -> '. $to_currency->name . ' Rate  '.request('exchange_rate').']',json_encode($request),activityEnums('currencies'),$toid,Auth::user()->id);
                return response()->json(['message' => 'Exchange Rate successfully updated '.$to_currency->name.'.'], 200);
            }else{
                return response()->json(['error' => "You don't have permission for this part."]);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()]);
        }
    }


    public function exchangerateList()
    {
        try{
            if(Auth::user()->can('view-exchange-currencies') || Auth::user()->is_administator==1){
                $res = DB::table('exchange_rate')->select('c1.name as from_currecy','c2.name as to_currecy','c1.hash_id as from_hash_id','c2.hash_id as to_hash_id','exchange_rate.exchange_rate')->join('currencies as c1', 'c1.id', '=', 'exchange_rate.base_currency_id')->join('currencies as c2', 'c2.id', '=', 'exchange_rate.to_currency_id');
                if(request('to_hash_id')){
                    $res=$res->where('c2.hash_id',request('to_hash_id'));
                }
                $res=$res->get();
                return response()->json(['message' => 'Get list.','data' => cleanObject($res)], 200);
            }else{
                return response()->json(['error' => "You don't have permission for this part."]);
            }
        } catch (\Exception $e) {

            return response()->json(['error' => $e->getMessage()]);
        }
    }



}