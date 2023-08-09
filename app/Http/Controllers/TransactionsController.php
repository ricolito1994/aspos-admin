<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\TransactionDetail;

use App\Models\Product;
use App\Models\Pricelist;
use App\Models\Unit;
use App\Models\User;

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
        try {
            $req = $request->all();
            $transactionData = $req['transaction'];

            $transaction = Transaction::updateOrCreate([
               'transaction_code' => $transactionData['transaction_code'],
            ],$transactionData);

            if ($req['isCreate']) {
                foreach ($req['transactionDetails'] as $transactionDetail) {
                    // iterate through transaction details
                    $transaction->details()->create($transactionDetail);
                }
            }

            return response()->json(['res' => $transaction], 200);
        } catch (Exception $e) {
            return response()->json(['err'=>$e], 500);
        }
        
    }

    public function getTransactions ($companyId, $searchString, $transDateFrom, $transDateTo) 
    {
        // get many transactions
        try {
            $transactions = Transaction::where('company_id', $companyId)
                ->where('transaction_code', $searchString)
                ->whereBetween('created_date', [$transDateFrom, $transDateTo])
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
            $transaction = Transaction::with('details')->find($transactionId)->get();

            foreach ($transaction->details as $transactionDetail) {
                // get product, price/cost of each transaction detail
            }

            return response()->json(['res' => $transaction], 200);
        } catch (Exception $e) {
            return response()->json(['err'=>$e], 500);
        }
    }
}
