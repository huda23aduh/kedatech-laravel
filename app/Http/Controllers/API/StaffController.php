<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use App\Models\User;
use DB;

class StaffController extends BaseController
{
    public function seeAllCustomerData(Request $request)
    {
        $success['customer-data'] = User::where('user_type_id', 1)->get();

        if ($request->has('view_type') && $request->input('view_type') == 'deleted') {
            $customerData =  User::onlyTrashed()->where('user_type_id', 1)->get();
        } else if ($request->has('view_type') && $request->input('view_type') == 'active') {
            $customerData =  User::where('user_type_id', 1)->get();
        } else {
            $customerData =  User::withTrashed()->where('user_type_id', 1)->get();
        }

        $success['customer-data'] = $customerData;

        return $this->sendResponse($success, 'data retrieved successfully.');

    }

    public function deleteCustomer(Request $request)
    {
        try {
            User::whereIn('id', $request->input('customerIds'))->where('user_type_id', 1)->update(['deleted_at' => now()]);

            return $this->sendResponse(null, 'data retrieved successfully.');

        } catch (\Exception $e) {
            if (env('APP_DEBUG') == TRUE) {
                return $e->getMessage();
            } else {
                return $this->sendError('something went wrong.', ['error'=>'deleteCustomer']);
            }
        }

    }
}
