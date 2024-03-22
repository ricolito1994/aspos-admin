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
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

use Illuminate\Support\Facades\Validator;

class TransactionsController extends Controller
{

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

            $validate = Validator::make($req['transaction'],
            [
                'transaction_code' => 'required|string|unique:transactions|max:255',
            ],
            [
                'transaction_code.unique' => 'Transaction code already exists.',
                'transaction_code.required' => 'Transaction code is required.',
            ]);

            if ($validate->fails()) {
                return response()->json(['err'=>$validate->errors()], 500);
            }
            
            $transactionData = $req['transaction'];
            $transactionDetails = $req['transactionDetails'];

            $omitsToTransaction = [
                'units',
                'product',
                'indx',
                'old_qty',
                'latest_rem_bal_unit_name',
                "latest_rem_bal_qty",
                "latest_rem_bal_unit",
            ];
            foreach ($transactionDetails as $j => $td) {
                foreach ($omitsToTransaction as $k => $omcm) {
                    if(isset($transactionDetails[$j][$omitsToTransaction[$k]]) )
                        unset($transactionDetails[$j][$omitsToTransaction[$k]]);
                }
            }
            
            if ($req['isCreate']) {
                $transaction = Transaction::create($transactionData);
                
                foreach ($transactionDetails as $transactionDetail) {
                    // iterate through transaction details
                    $transaction->itemDetails()->create($transactionDetail);
                }
            } else {
                $transaction = Transaction::updateOrCreate([
                    'transaction_code' => $transactionData['transaction_code'],
                ], $transactionData);
            }
            DB::commit();
            return response()->json(['res' => $transaction], 200);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['err'=>$e], 500);
        }
        
    }

    public function getTransactions (
        $companyId = 1, 
        $branchId = 1, 
        $searchString = 'false', 
        $transDateFrom = null, 
        $transDateTo = null,
        $userId = null,
    ) {
        // get many transactions
        try {
            $conds =  [
                ['company_id', $companyId],
                ['branch_id', $branchId]
            ];

            if ($searchString != 'false') $conds[] = ['transaction_code', 'LIKE', "%$searchString%"];
            if ($userId) $conds[] = ['user_id', $userId];
               
            $transactions = Transaction::where ($conds)
                    ->with('customer')
                    ->with('createdBy')
                    ->with('requestedBy')
                    ->orderBy('id', 'DESC')
                    ->paginate(10);
            
            if ($transDateFrom && $transDateTo) {
                $transactions = Transaction::where ($conds)
                    ->whereBetween('transaction_date', [$transDateFrom, $transDateTo])
                    ->with('customer')
                    ->with('createdBy')
                    ->with('requestedBy')
                    ->orderBy('id', 'DESC')
                    ->paginate(10);
            }
            
            return response()->json(['res' => $transactions], 200);

        } catch (Exception $e) {
            return response()->json(['err'=>$e], 500);
        }
    }


    public function searchTransaction (
        $searchString = false,
        $companyId = 1, 
        $branchId = 1, 
        $userId = null,
    ) 
    {
        // get many transactions
        try {
           
            
            $conds =  [
                ['company_id', $companyId],
                ['branch_id', $branchId],
                ['transaction_code', 'LIKE', "%$searchString%"]
            ];

            if ($userId) 
                $conds[] = ['user_id', $userId];
                
            if($searchString == "false") unset($conds[2]);
               
            $transactions = Transaction::where ($conds)
                ->with('customer')
                ->with('createdBy')
                ->with('requestedBy')
                ->with('itemDetails', function ($q) {
                    $q->with('unit');
                    $q->with('product', function ($q){
                        $q->with('unit');
                    });
                })
                ->with('refTransaction', function ($q) {
                    $q->with('itemDetails', function ($q) {
                        $q->with('unit');
                    });
                })
                ->orderBy('id', 'ASC')
                ->get();
                if (count($transactions) > 0) {
                    for ($i = 0 ; $i < count($transactions[0]->itemDetails); $i++) {
                        $transactionDetail = $transactions[0]->itemDetails[$i];

                        $rembal = TransactionDetail::where(function($q) use ($transactionDetail){
                            $product_id=$transactionDetail->product_id;
                            $q->whereRaw("id = (SELECT max(id) from transaction_details where product_id=$product_id)");
                            $q->where("product_id", $product_id);
                        })
                        ->first();
                        $transactionDetail['latest_rem_bal_qty'] = $rembal->remaining_balance;
                        $transactionDetail['latest_rem_bal_unit'] = $rembal->unit_id;
                        $transactions[0]->itemDetails[$i] = $transactionDetail;
                    }
                }
            return response()->json(['res' => $transactions], 200);

        } catch (Exception $e) {
            return response()->json(['err'=>$e], 500);
        }
    }

    public function getTransaction (
        $transactionId, 
        $userId = null
    ) {
        // get single transaction
        try {
            $conds = [
                ['id', $transactionId]
            ];

            if ($userId) 
                $conds[] = ['user_id', $userId];

            $transaction = Transaction::where($conds)
                ->with('itemDetails')
                ->with('customer')
                ->with('createdBy')
                ->with('requestedBy')
                ->with('refTransaction', function ($q) {
                    $q->with('itemDetails');
                })
                ->first();
            $ctr = 0;
            foreach ($transaction->itemDetails as $transactionDetail) {
                // get product, price/cost of each transaction detail
                $transaction->itemDetails[$ctr]['pp'] = 
                    $transactionDetail
                        ->product()
                        ->with('unit')
                        ->first();
                $ctr ++;
            }

            return response()->json(['res' => $transaction], 200);
        } catch (Exception $e) {
            return response()->json(['err'=>$e], 500);  
        }
    }

    public function getStartingBalance(
        $date, 
        $userId = null
    ) {
        try {

            $user = User::where('id', Auth::id())->first();

            $conditions = [
                ['transaction_date', $date],
                ['branch_id' , $user->selected_branch],
                ['company_id', $user->company_id],
            ];

            if ($userId) 
                $conditions[] = ['user_id', $userId];

            $transaction = Transaction::where($conditions)
                ->orderBy('id', 'asc')
                ->first();
            
            return response()->json(['res' => $transaction], 200);
        } catch (Exception $e) {
            return response()->json(['err'=>$e], 500); 
        }
    }

    public function getCurrentBalance(
        $date, 
        $userId = null
    ) {
        try {
            $user = User::where('id', Auth::id())->first();

            $conditions = [
                ['transaction_date', $date],
                ['branch_id' , $user->selected_branch],
                ['company_id', $user->company_id],
            ];

            if ($userId) 
                $conditions[] = ['user_id', $userId];
            
            $transaction = Transaction::where($conditions)
                ->whereNotNull('remaining_balance')
                ->orderBy('id', 'desc')
                ->first();
            return response()->json(['res' => $transaction], 200);
        } catch (Exception $e) {
            return response()->json(['err'=>$e], 500); 
        }
    }


    public function getTotalSales(
        $date, 
        $userId = null
    ) {
        try {
            $user = User::where('id', Auth::id())->first();
            $totalPrice = 0;
            $totalCost = 0;
            $totalSale = 0;
            $conditions = [
                ['transaction_date', $date],
                ['transaction_type', 'ITEM_TRANSACTION'],
                ['item_transaction_type', 'SALE'],
                ['branch_id' , $user->selected_branch],
                ['company_id', $user->company_id],
            ];

            if ($userId) 
                $conditions[] = ['user_id', $userId];

            $transaction = Transaction::where($conditions)
                ->orderBy('id', 'desc')
                ->get();
            foreach($transaction as $t) {
                $totalPrice += $t['total_price'];
                $totalCost += $t['total_cost'];
            }
            //dd($transaction);
            $totalSale = $totalPrice - $totalCost;
            
            return response()->json(['res' => 
                [
                    'transactions' => $transaction,
                    'total_sale' => $totalSale,
                    'total_price' => $totalPrice,
                    'total_cost' => $totalCost,
                ]
            ], 200);
        } catch (Exception $e) {
            return response()->json(['err'=>$e], 500); 
        }
    }

    public function getTotalExpenses(
        $date, 
        $userId = null
    ) {
        try {
            $user = User::where('id', Auth::id())->first();
            $totalExpenses = 0;
            $conditions = [
                ['transaction_date', $date],
                ['transaction_type', 'CASH_WITHRAWAL'],
                ['is_expense', 1],
                ['branch_id' , $user->selected_branch],
                ['company_id', $user->company_id],
            ];

            if ($userId) 
                $conditions[] = ['user_id', $userId];

            $transaction = Transaction::where($conditions)
                ->orderBy('id', 'desc')
                ->get();

            foreach($transaction as $t) {
                $totalExpenses += $t['amt_released'];
            }
            
            return response()->json(['res' => 
                [
                    'transactions' => $transaction,
                    'total_expenses' => $totalExpenses,
                ]
            ], 200);
        } catch (Exception $e) {
            return response()->json(['err'=>$e], 500); 
        }
    }
}
