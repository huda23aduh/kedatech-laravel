<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Validator;

class RegisterController extends BaseController
{
    /**
     * Register api
     *
     * @return \Illuminate\Http\Response
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
            'c_password' => 'required|same:password',
            'user_type_id' => 'required|integer|between:1,2',
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $input = $request->all();
        $input['password'] = bcrypt($input['password']);
        $user = User::create($input);
        $passportScope = $user->user_type_id == 1  ? array("customer-scope") : array("staff-scope");
        $success['token'] = $user->createToken('MyApp', $passportScope)->accessToken;
        $success['role'] =  $user->user_type_id == 1  ? 'customer' : 'staff';


        return $this->sendResponse($success, 'User register successfully.');
    }

    /**
     * Login api
     *
     * @return \Illuminate\Http\Response
     */
    public function login(Request $request)
    {
        if(Auth::attempt(['email' => $request->email, 'password' => $request->password])){
            $user = Auth::user();
            $passportScope = $user->user_type_id == 1  ? array("customer-scope") : array("staff-scope");
            $success['token'] =  $user->createToken('MyApp', $passportScope)-> accessToken;
            $success['email'] =  $user->email;
            $success['role'] =  $user->user_type_id == 1  ? 'customer' : 'staff';

            return $this->sendResponse($success, 'User login successfully.');
        }
        else{
            return $this->sendError('email and password didnt match or email not found.', ['error'=>'Unauthorised']);
        }
    }

    public function check(Request $request)
    {
        if(Auth::guard('api')->user()){
            $user = Auth::guard('api')->user();
            $success['token'] =  $request->bearerToken();
            $success['email'] =  $user->email;
            $success['role'] =  $user->user_type_id == 1  ? 'customer' : 'staff';

            return $this->sendResponse($success, 'Authorized user.');
        }
        else{
            return $this->sendError('Unauthorised.', ['error'=>'Unauthorised']);
        }
    }

    public function logout(Request $request)
    {
        if(Auth::guard('api')->user()){
            Auth::guard('api')->user()->token()->revoke();
            return $this->sendResponse(null, 'Logout successfully.');
        }
        else{
            return $this->sendError('Unauthorised user or unknown token.', ['error'=>'Unauthorised']);
        }
    }
}
