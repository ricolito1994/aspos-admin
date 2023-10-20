<?php

namespace App\Http\Controllers;

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


    public function create (Request $request) 
    {
        $data = $request->all();
        $cust = $data['customer'];
  
        try {
            DB::beginTransaction();
            $user = User::where('id', Auth::id())->first();
            $validate = Validator::make($prod,
            [
                'customer_name' => 'required|string|unique:products|max:255',
                'customer_code' => 'required|string|unique:products|max:255',
            ],
            [
                'customer_code.unique' => 'Customer code already exists.',
            ]);
            
            $product = Product::updateOrCreate(
                [
                    'customer_code' => $prod['customer_code'], 
                ],
                [
                    'customer_code' => $cust['customer_code'],
                    'customer_name' => $cust['customer_name'],
                    'pwd_no' => $cust['pwd_no'],
                    'address' => $cust['address'],
                    'company_id' => $cust['company_id'],
                    'created_by' => $user->id,
                ]);
           
            DB::commit();
            return response()->json($product, 200);
            
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
                    ->where('company_id', $companyId)
                    ->where('customer_type', $customerType);
            }
            
            $customerRes = $customers->orderBy('id', 'DESC')->get();
            return response()->json($customerRes, 200);
        } catch (Exception $e) {
            return response()->json(['err' => $e], 500);
        }
    }
}
