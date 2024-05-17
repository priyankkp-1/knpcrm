<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Products;
use App\Models\Taxes;
use App\Models\ItemGroup;
use Hash;
use Validator;
use Auth;
use Str;
use DB;

class ProductsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth'); 
    }

    public function getList(Request $request)
    {
        try{
            if(Auth::user()->can('view-products') || Auth::user()->is_administator==1){
                $sort_by = $request->get('sortby');
                $sort_type = $request->get('sorttype');
                $query = $request->get('query');
                $query = str_replace(" ", "%", $query);
                $res=Products::with('tax','tax2','itemgroup')->where('id', 'like', '%'.$query.'%')->orWhere('name', 'like', '%'.$query.'%')->orWhere('description', 'like', '%'.$query.'%')->orWhere('rate', 'like', '%'.$query.'%')->orWhere('unit', 'like', '%'.$query.'%');
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
            if(Auth::user()->can('edit-products') || Auth::user()->is_administator==1){
                $record=Products::where('hash_id',$hash_id)->get();
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
            if(Auth::user()->can('edit-products') || Auth::user()->is_administator==1){

                $hash_id=request('hash_id');
                $validator = Validator::make($request->all(), [
                    'hash_id' => 'required',
                    'name' => 'required|max:220|unique:products,name,'.$hash_id.',hash_id',
                    'rate' => 'required|numeric|between:0.00,99999999999.99',
                    'description' => 'max:1500',
                    'unit' => 'max:40',
                    'tax_id' => 'max:255',
                    'tax2_id' => 'max:255',
                    'itemgroup_id' => 'max:255'
                ]);

                if($validator->fails()){
                    return response()->json(['error' => $validator->errors()->all()]);
                }

                if(request('tax_id')){
                    $tax = Taxes::where('hash_id',request('tax_id'))->first();
                    if(!$tax){
                        return response()->json(['error' => 'Tax not found.']);
                    }
                }
                if(request('tax2_id')){
                    $tax2 = Taxes::where('hash_id',request('tax2_id'))->first();
                    if(!$tax2){
                        return response()->json(['error' => 'Tax2 not found.']);
                    }
                }
                if(request('itemgroup_id')){
                    $itemgroup = ItemGroup::where('hash_id',request('itemgroup_id'))->first();
                    if(!$itemgroup){
                        return response()->json(['error' => 'Item Group not found.']);
                    }
                }

                $record=Products::where('hash_id', '=', $hash_id)->first();
                if(!$record){
                    return response()->json(['error' => "Record doesn't exist."]);
                }
                $request=request()->except(['hash_id']);
                $request['tax_id'] = isset($tax)?$tax->id:NULL;
                $request['tax2_id'] = isset($tax2)?$tax2->id:NULL;
                $request['itemgroup_id'] = isset($itemgroup)?$itemgroup->id:NULL;
                Products::where('id', $record->id)->update($request);
                setActivityLog('Product Updated [ID: ' . $record->id . ', ' . request('name') . ']',json_encode($request),activityEnums('products'),$record->id,Auth::user()->id);
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
            if(Auth::user()->can('create-products') || Auth::user()->is_administator==1){
                $validator = Validator::make($request->all(), [
                    'name' => 'required|max:220|unique:products,name',
                    'rate' => 'required|numeric|between:0.01,99999999999.99',
                    'description' => 'max:1500',
                    'unit' => 'max:40',
                    'tax_id' => 'max:255',
                    'tax2_id' => 'max:255',
                    'itemgroup_id' => 'max:255'
                ]);

                if($validator->fails()){
                    return response()->json(['error' => $validator->errors()->all()]);
                }

                if(request('tax_id')){
                    $tax = Taxes::where('hash_id',request('tax_id'))->first();
                    if(!$tax){
                        return response()->json(['error' => 'Tax not found.']);
                    }
                }
                if(request('tax2_id')){
                    $tax2 = Taxes::where('hash_id',request('tax2_id'))->first();
                    if(!$tax2){
                        return response()->json(['error' => 'Tax2 not found.']);
                    }
                }
                if(request('itemgroup_id')){
                    $itemgroup = ItemGroup::where('hash_id',request('itemgroup_id'))->first();
                    if(!$itemgroup){
                        return response()->json(['error' => 'Item Group not found.']);
                    }
                }

                DB::beginTransaction();
                $request=request()->all();
                $request['hash_id'] = getHashid();
                $request['tax_id'] = isset($tax)?$tax->id:NULL;
                $request['tax2_id'] = isset($tax2)?$tax2->id:NULL;
                $request['itemgroup_id'] = isset($itemgroup)?$itemgroup->id:NULL;
                $record=Products::create($request);
                if(!$record){
                    DB::rollBack();
                    return response()->json(['error' => 'Something went to wrong while saving record.']);
                }
                DB::commit();
                setActivityLog('New Product Added [ID: ' . $record->id . ', ' . request('name') . ']',json_encode($request),activityEnums('products'),$record->id,Auth::user()->id);
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
            $record=Products::where('hash_id', '=', $hash_id)->first();
            if(!$record){
                return response()->json(['error' => "Record doesn't exist."]);
            }

            
            // if(AdminRoleRelation::where('role_id', $role->id)->exists()){
            //     return response()->json(['error' => "Record related with other row so, you can't delete it."]);
            // }
            if(Auth::user()->can('delete-products') || Auth::user()->is_administator==1){
                $record->delete();
                setActivityLog('Product Deleted [ID: ' . $record->id . ']',json_encode($request->all()),activityEnums('products'),$record->id,Auth::user()->id);
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
            if(Auth::user()->can('view-products') || Auth::user()->is_administator==1){
                $record=Products::with('tax','tax2','itemgroup')->get();
                return response()->json(['message' => 'Get records.','data' => cleanObject($record)], 200);
            }else{
                return response()->json(['error' => "You don't have permission for this part."]);
            }
        } catch (\Exception $e) {

            return response()->json(['error' => $e->getMessage()]);
        }
    }


}