<?php

namespace App\Http\Controllers;
use DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\User;

class CustomerController extends Controller
{
    //
    public function index () 
    {
        // render customer page
    }


    public function save (Request $request) 
    {
        $data = $request->all();
        $cust = $data;

        try {
            DB::beginTransaction();
            $user = User::where('id', Auth::id())->first();
            $validate = Validator::make($cust,
            [
                'customer_name' => 'required|string|max:255',
                'customer_code' => 'required|string|max:255',
            ],
            [
                'customer_code.required' => 'Customer code required.',
                'customer_name.required' => 'Customer name required.',
            ]);

            if ($validate->fails()) {
                return response()->json([
                    'err' => $validate->errors()
                ], 500);
            }

            $customer = Customer::updateOrCreate(
                ['customer_code' => $cust['customer_code']], $cust);
           
            DB::commit();
            return response()->json($customer, 200);
            
        } catch (Exception $e) {
            DB::rollback();
            return response()->json(['err' => $e], 500);
        }
    }

    public function get (
        $companyId = 1,
        $customerType = 2,
        $searchString = 'false',
        ) 
    {
        try {
            $user = User::where('id', Auth::id())->first();
            $customers = Customer::where('company_id', $companyId)
                ->where('customer_type', $customerType);

            if($searchString !== 'false') {
                $customers = 
                    Customer::where(function($q) use ($searchString) {
                        $q->orWhere('customer_code', 'LIKE', "%$searchString%");
                        $q->orWhere('customer_name', 'LIKE', "%$searchString%");
                    })
                    ->where('company_id', $companyId);
                    //->where('customer_type', $customerType);
            }
            
            $customerRes = $customers->orderBy('id', 'DESC')->get();
            return response()->json($customerRes, 200);
        } catch (Exception $e) {
            return response()->json(['err' => $e], 500);
        }
    }
}
