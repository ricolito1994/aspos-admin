<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\TransactionDetail;

use App\Models\Product;
use App\Models\Pricelist;
use App\Models\Unit;
use App\Models\User;

use DB;

use Illuminate\Http\Request;
use Inertia\Inertia;

class TransactionsController extends Controller
{
    // inject dependencies, unit converter service maybe
    public function __construct()
    {

    }

    public function index () 
    {
        $user = session('user_object');
        return Inertia::render('Transactions', [
            "user" => $user,
            "company" => $user->company,
        ]); 
    }

    public function createTransaction (Request $request) 
    {
        // create | update transactions
        DB::beginTransaction();

        try {
            $req = $request->all();
            
            $transactionData = $req['transaction'];
            $transactionDetails = $req['transactionDetails'];

            $omitsToTransaction = ['units','product'];
            foreach ($transactionDetails as $j => $td) {
                foreach ($omitsToTransaction as $k => $omcm) {
                    if(isset($transactionDetails[$j][$omitsToTransaction[$k]]) )
                        unset($transactionDetails[$j][$omitsToTransaction[$k]]);
                }
            }

            $transaction = Transaction::updateOrCreate([
               'transaction_code' => $transactionData['transaction_code'],
            ], $transactionData);

            if ($req['isCreate']) {
                foreach ($transactionDetails as $transactionDetail) {
                    // iterate through transaction details
                    $transaction->details()->create($transactionDetail);
                }
            }
            DB::commit();
            return response()->json(['res' => $transaction], 200);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['err'=>$e], 500);
        }
        
    }

    public function getTransactions ($companyId, $branchId, $searchString, $transDateFrom, $transDateTo) 
    {
        // get many transactions
        try {
            $conds =  [
                ['company_id', $companyId],
                ['branch_id', $branchId],
                ['transaction_code', 'LIKE', "%$searchString%"]
            ];
                
            if($searchString == "false") unset($conds[2]);
               
            $transactions = Transaction::where ($conds)
                    ->whereBetween('transaction_date', [$transDateFrom, $transDateTo])
                    ->get();
            
            return response()->json(['res' => $transactions], 200);

        } catch (Exception $e) {
            return response()->json(['err'=>$e], 500);
        }
    }

    public function getTransaction ($transactionId) 
    {
        // get single transaction
        try {
            $transaction = Transaction::where('id', $transactionId)->with('itemDetails')->first();
            $ctr = 0;
            foreach ($transaction->itemDetails as $transactionDetail) {
                // get product, price/cost of each transaction detail
                $transaction->itemDetails[$ctr]['pp'] = ($transactionDetail->product()->with('unit')->first());
                $ctr ++;
            }

            return response()->json(['res' => $transaction], 200);
        } catch (Exception $e) {
            return response()->json(['err'=>$e], 500);  
        }
    }
}
