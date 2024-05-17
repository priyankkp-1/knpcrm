<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Hash;
use Validator;
use Illuminate\Validation\Rule;
use Auth;
use Str;
use DB;

class ActivityLogsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth'); 
    }

    public function getList(Request $request)
    {
        try{         
            if(Auth::user()->can('view-activity-logs') || Auth::user()->is_administator==1){

                $validator = Validator::make($request->all(), [
                    'rel_type' => Rule::in(['lead', 'customer', 'staff']),
                    'rel_id' => 'integer',
                ]);

                if($validator->fails()){
                    return response()->json(['error' => $validator->errors()->all()]);
                }
                $rel_type=request('rel_type')?request('rel_type'):'';
                $rel_id=request('rel_id')?request('rel_id'):'';
                
                $sort_by = $request->get('sortby');
                $sort_type = $request->get('sorttype');
                $query = $request->get('query');
                $query = str_replace(" ", "%", $query);
                $data = DB::table('activity_logs')->select('activity_logs.datetime','activity_logs.description','activity_logs.ipaddress','admins.first_name')->join("admins","admins.id","=","activity_logs.added_by")->where('activity_logs.id', 'like', '%'.$query.'%')->orWhere('activity_logs.ipaddress', 'like', '%'.$query.'%');

                if($sort_by && $sort_type){
                    $data=$data->orderBy($sort_by, $sort_type);
                }
                if($rel_id && $rel_type){
                    $data=$data->where('rel_id',$rel_id)->where('rel_type',$rel_type);
                }
                $data=$data->paginate();
                $success =  $data;

                return response()->json(['message' => 'Result.','data' => cleanObject($success)], 200);
            }else{
                return response()->json(['error' => "You don't have permission for this part."]);
            }
        } catch (\Exception $e) {

            return response()->json(['error' => $e->getMessage()]);
        }
    }



}




// $sort_by = $request->get('sortby');
//       $sort_type = $request->get('sorttype');
//             $query = $request->get('query');
//             $query = str_replace(" ", "%", $query);
//       $data = DB::table('post')
//                     ->where('id', 'like', '%'.$query.'%')
//                     ->orWhere('post_title', 'like', '%'.$query.'%')
//                     ->orWhere('post_description', 'like', '%'.$query.'%')
//                     ->orderBy($sort_by, $sort_type)
//                     ->paginate(5);



//$('#aa option[data-attr="30"]').attr("selected", "selected");

//composer require doctrine/dbal