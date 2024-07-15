<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\TransactionDetail;

use App\Models\Product;
use App\Models\Pricelist;
use App\Models\Unit;
use App\Models\User;
use App\Models\PendingTransaction;

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
        try {
            DB::beginTransaction();
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
                    $transaction->itemDetails()->create($transactionDetail);
                }
            } else {
                $transaction = Transaction::updateOrCreate([
                    'transaction_code' => $transactionData['transaction_code'],
                ], $transactionData);
            }
            if (isset($transactionData['ref_transaction_id_'])) {
                Transaction::updateOrCreate([
                    'id' => $transactionData['ref_transaction_id_'],
                ], [
                    'is_done_pending_transaction' => true,
                    'ref_transaction_id' => $transaction->id
                ]);
                
                TransactionDetail::updateOrCreate(
                    ['transaction_id' => $transactionData['ref_transaction_id_']],
                    ['is_done_pending_transaction' => true]
                );
                
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
        $searchType = 'ALL',
    ) {
        // get many transactions
        try {
            $conds =  [
                ['company_id', $companyId],
                ['branch_id', $branchId]
            ];
            if (isset($userId) && !is_numeric($userId)) {
                $searchType = $userId;
            }

            switch ($searchType) {
                case "SALE":
                    $conds[] = ['item_transaction_type', 'SALE'];
                    $conds[] = ['is_pending_transaction', null];
                    break;
                case "REFUND":
                    $conds[] = ['item_transaction_type', '-'];
                    break;
                case "DELIVERY":
                    $conds[] = ['item_transaction_type', 'DELIVERY'];
                    break;
                case "PENDING":
                    $conds[] = ['is_pending_transaction', 1];
                    $conds[] = ['is_done_pending_transaction', null];
                    break;
                case "ALL":
                    //$conds[] = ['item_transaction_type', "LIKE", "%%"];
                    $conds[] = ['is_pending_transaction', null];
                    break;
                default:
                    $conds[] = ['is_pending_transaction', null];
                    break;
            }

            if ($searchString != 'false') $conds[] = ['transaction_code', 'LIKE', "%$searchString%"];
            if (isset($userId) && is_numeric($userId)) {
                $conds[] = ['user_id', $userId];
            }
            //var_dump($conds);
            if ($transDateFrom && $transDateTo) {
                $transactions = Transaction::where ($conds)
                    ->whereBetween('transaction_date', [$transDateFrom, $transDateTo])
                    //->whereNull('is_pending_transaction')
                    ->with('customer')
                    ->with('createdBy')
                    ->with('requestedBy')
                    ->with('itemDetails')
                    ->orderBy('id', 'DESC')
                    ->paginate(10);
            } else {
                   
                $transactions = Transaction::where ($conds)
                    //->whereNull('is_pending_transaction')
                    ->with('customer')
                    ->with('createdBy')
                    ->with('requestedBy')
                    ->with('itemDetails')
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
                ->with('itemDetails', function ($qt) {
                    $qt->with('unit');
                    $qt->with('product', function ($q) use ($qt) {
                        $q->with('unit');
                        $q->latestTransaction();
                        $q->latestPendingTransaction();
                    });
                })
                ->with('refTransaction', function ($q) {
                    $q->with('itemDetails', function ($q) {
                        $q->with('unit');
                    });
                })
                ->orderBy('id', 'ASC')
                ->paginate(10);
                if (count($transactions) > 0) {
                    for ($h = 0; $h < count($transactions); $h++) {
                        for ($i = 0 ; $i < count($transactions[$h]->itemDetails); $i++) {
                            $transactionDetail = $transactions[$h]->itemDetails[$i];
                           
                            $rembal = TransactionDetail::where(function($q) use ($transactionDetail){
                                $product_id=$transactionDetail->product_id;
                                if(!isset($product_id)) {
                                  return;
                                }
                                $q->whereRaw("id = (SELECT max(id) from transaction_details where product_id=$product_id and is_pending_transaction IS NULL)");
                                $q->where("product_id", $product_id);
                            })
                            ->first();
                            $transactionDetail['latest_rem_bal_qty'] = $rembal->remaining_balance;
                            $transactionDetail['latest_rem_bal_unit'] = $rembal->unit_id;
                            $transactions[$h]->itemDetails[$i] = $transactionDetail;
                        }
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
                ->whereNull('is_pending_transaction')
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
                ->whereNull('is_pending_transaction')
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
                ->whereNull('is_pending_transaction')
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

    public function deleteTransaction ($transactionID) 
    {
        try {
            //$user = User::where('id', Auth::id())->first();
            $transaction = Transaction::find($transactionID);
            if (!isset($transaction->is_pending_transaction))
                return response()->json(['error' => 'Must be a pending transaction.'], 500);

            $transaction->itemDetails()->delete();
            $transaction->delete();
            return response()->json(['message' => 'Transaction deleted successfully'], 200);
        } catch (Exception $e) {
            return response()->json(['error' => $e], 500);
        }
    }
}
