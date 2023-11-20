<?php
namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\TransactionDetail;

use App\Models\Product;
use App\Models\User;

use DB;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    //
    public function index () 
    {
    }

    public function getUser ($userId) 
    {
        try {
            $conditions = [
                ['id', $userId],
            ];
               
            $transactions = User::where ($conds)->first();
            
            return response()->json(['res' => $transactions], 200);

        } catch (Exception $e) {
            return response()->json(['err'=>$e], 500);
        }
    } 

    public function getUsers ($searchString = null)
    {
        try {
            $user = User::where('id', Auth::id())->first();

            $conditions = [
                ['company_id', $user->company_id],
                ['branch_id', $user->selected_branch],
            ];

            if ($searchString) $conditions[] = ['name','like',"%$searchString%"];
               
            $users = User::where ($conditions)->get();
            
            return response()->json(['res' => $users], 200);

        } catch (Exception $e) {
            return response()->json(['err'=>$e], 500);
        }
    } 

    public function save (Request $request) 
    {
        DB::beginTransaction();

        try {
            
            $req = $request->all();
            
            $validate = Validator::make($req,
            [
                'username' => 'required|string|unique:users,username|max:255',
                'email' => 'required|string|email|unique:users,email|max:255',
                'name' => 'required|string|max:255',
            ],
            [
                'username.unique' => 'Username already exists.',
                'username.required' => 'Username is required.',
                'email.unique' => 'Email already exists.',
                'email.required' => 'Email is required.',
                'email.email' => 'Invalid email.',
                'name.required' => 'Name is required.',
            ]);

            if ($validate->fails()) {
                return response()->json(['err'=>$validate->errors()], 500);
            }
            
            if (isset($req['id'])) {
                $user = User::updateOrCreate([
                    'id' => $req['id'],
                ], $req);
            } else {
                $user = User::create($req);
            }
            
            DB::commit();
            return response()->json(['res' => $user], 200);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['err' => $e], 500);
        }
    } 

}
