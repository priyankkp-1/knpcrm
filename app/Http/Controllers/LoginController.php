<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Admin;
use Hash;
use Validator;
use Auth;
use Log;

class LoginController extends Controller
{

    public function userGetlist(Request $request)
    {
        try {
            $sort_by = $request->get('sortby');
            $sort_type = $request->get('sorttype');
            $query = $request->get('query');
            $query = str_replace(" ", "%", $query);
            $res=User::where('id', 'like', '%'.$query.'%')->orWhere('name', 'like', '%'.$query.'%');
            if($sort_by && $sort_type){
                $res=$res->orderBy($sort_by, $sort_type);
            }
            $res = $res->paginate();
            $success =  $res;

            return response()->json(['message' => 'Result.','data' => cleanObject($success)], 200);
        } catch (\Exception $e) {

            return response()->json(['error' => $e->getMessage()]);
        }
    }

    public function userLogin(Request $request)
    {
        try{
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
                'password' => 'required',
            ]);

            if($validator->fails()){
                return response()->json(['error' => $validator->errors()->all()]);
            }

            if(auth()->guard('user')->attempt(['email' => request('email'), 'password' => request('password')])){

                config(['auth.guards.api.provider' => 'user']);
                
                $user = User::select('users.*')->find(auth()->guard('user')->user()->id);
                $success =  $user;
                $success['token'] =  $user->createToken('MyApp',['user'])->accessToken; 

                return response()->json(['message' => 'Result.','data' => cleanObject($success)], 200);
            }else{ 
                return response()->json(['error' => 'Email and Password are Wrong.'], 200);
            }
        } catch (\Exception $e) {

            return response()->json(['error' => $e->getMessage()]);
        }
    }

    public function userLogout(Request $request)
    {
        try{
            $request->user()->token()->revoke();
            return response()->json([
                'message' => 'Successfully logged out'
            ],200);
        } catch (\Exception $e) {

            return response()->json(['error' => $e->getMessage()]);
        }
    }

    public function adminLogin(Request $request)
    {
        try{

            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
                'password' => 'required',
            ]);

            if($validator->fails()){
                return response()->json(['error' => $validator->errors()->all()]);
            }

            if(auth()->guard('admin')->attempt(['email' => request('email'), 'password' => request('password') ])){

                config(['auth.guards.api.provider' => 'admin']);

                if (!auth()->guard('admin')->user()->is_active) {
                    // Login failed - not active user
                    Log::info("--- ADMIN --- User '" . $request->input('email') . "' is not active!");
                    auth()->guard('admin')->user()->tokens->each(function($token, $key) {
                        $token->delete();
                    });
                    auth()->guard('admin')->logout();
                    $desc='Inactive User Tried to Login [Email: '.request('email').']';
                    setActivityLog($desc,json_encode($request->all()),activityEnums('staff'));
                    return response()->json(['error' => 'user is not active.'], 200);
                }
                $admin = Admin::select('admins.id','admins.hash_id','admins.first_name','admins.last_name','admins.email','admins.hash_id','admins.is_administator','admins.is_active')->find(auth()->guard('admin')->user()->id);
                $success =  $admin;
                $success['token'] =  $admin->createToken('MyApp',['admin'])->accessToken; 

                return response()->json(['message' => 'Result.','data' => cleanObject($success)], 200);
            }else{ 
                $desc='Non Existing User Tried to Login / Failed Login Attempt [Email: '.request('email').']';
                setActivityLog($desc,json_encode($request->all()),activityEnums('staff'));
                return response()->json(['error' => 'Email and Password are Wrong.'], 200);
            }
        } catch (\Exception $e) {

            return response()->json(['error' => $e->getMessage()]);
        }
    }

    public function adminLogout(Request $request)
    {
        try{
            $request->user()->token()->revoke();
            return response()->json([
                'message' => 'Successfully logged out'
            ],200);
        } catch (\Exception $e) {

            return response()->json(['error' => $e->getMessage()]);
        }
    }
}