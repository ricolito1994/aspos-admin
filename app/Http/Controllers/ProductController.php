<?php

namespace App\Http\Controllers;
use DB;
use App\Models\Product;
use App\Models\Pricelist;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    { 
        $user = session('user_object');
        return Inertia::render('Products', [
            "user" => $user,
            "company" => $user->company,
        ]); 
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $data = $request->all();
        $prod = $data['product'];
        
        try {
            DB::beginTransaction();
            $arrPrices = [];

            $validate = Validator::make($prod,
            [
                'product_code' => 'required|string|unique:products|max:255',
                'product_name' => 'required|string|unique:products|max:255',
            ],
            [
                'product_code.required' => 'Product code is required.',
                'product_name.required' => 'Product name is required.',
                'product_code.unique' => 'Product code already exists.',
                'product_name.unique' => 'Product name already exists.',
            ]);

            
            if ($validate->fails() && !isset($prod['id'])) {
                return response()->json(['err'=>$validate->errors()], 500);
            }
            
            $product = Product::updateOrCreate(
                [
                    'product_code' => $prod['product_code'], 
                ],
                [
                    'product_code' => $prod['product_code'],
                    'product_name' => $prod['product_name'],
                    'product_desc' => $prod['product_desc'],
                    'user_id' => $prod['user_id'],
                    'company_id' => $prod['company_id'],
                ]);
            
            
                 
            #$product->pricelist()->delete();
            #$prices = Pricelist::where('product_id', $product->id);
            #$prices->delete();
            $user = User::where('id', Auth::id())->first();

            // cascasde delete pricelist and unit
            $plist = $product->pricelist()->get();
           
            foreach ($plist as $p) {
                $pl = Pricelist::where('branch_id', $user->selected_branch)->find($p->id);
                if($pl) $pl->delete();
            }
            
            foreach ($data['prices'] as $price) {
                $arrPrices = [
                    "pricelist_name" => $price["pricelist_name"],
                    "is_default" => $price["is_default"],
                    "branch_id" => $price["branch_id"],
                ];
                
                $priceList = $product->pricelist()->create($arrPrices);

                foreach ($price['unit'] as $unit) {
                    $unit['product_id'] = $product->id;
                    $priceList->unit()->create($unit);
                }
            }

            /*$productTransactions = Product::find($product->id)
                ->with('transactions', function($query) use ($user) {
                    $query->with('transaction');
                    $query->where('branch_id', $user->selected_branch);
                    $query->orderBy('id', 'DESC');
                    $query->max('id');
                })
                ->get();*/

            $product['pricelist'] = Pricelist::where('product_id', $product->id)
                ->with('unit')
                ->get();
            
            //$product['transactions'] = $productTransactions;
            $product['remaining_balance'] = 0;
            $product['unit'] = '-';
            DB::commit();
            return response()->json($product, 200);
            
        } catch (Exception $e) {
            DB::rollback();
            return response()->json(['err' => $e], 500);
        }
    }


    public function getProducts ($companyId, $searchString) 
    {
        try {
            $user = User::where('id', Auth::id())->first();
            $products = Product::where('company_id', $companyId)->whereNull('deleted_at');
           
            if($searchString !== 'false') {
                $products = Product::where('product_code', 'LIKE', "%$searchString%")
                    ->orWhere('product_name', 'LIKE', "%$searchString%")
                    ->where('company_id', $companyId);
            }

            $products
                ->with('transactions', function($query) use ($user) {
                    $query->with('transaction', function ($q) {
                        $q->whereNull('is_pending_transaction');
                    });
                    $query->whereNull('is_pending_transaction');
                    $query->where('branch_id', $user->selected_branch);
                    $query->orderBy('id', 'DESC');
                    //$query->max('id');
                })
                ->with('pricelist', function($query) use ($user) {
                    $query->with('unit', function ($q) {
                        $q->whereNull('deleted_at');
                    });
                    $query->where('is_default', 1);
                    $query->where('branch_id', $user->selected_branch);
                });

            $productsRes = $products
                ->orderBy('id', 'DESC')
                ->paginate(10);
            
            foreach ($productsRes as $k => $p) {
                
                $remainingBalance = count($p['transactions']) > 0 ? $p['transactions'][0]['remaining_balance'] : 0;
                $unit_obj = []; 
                $unitRemainingBal = '-';

                if (count($p['transactions']) > 0) {
                    $unit = Unit::where([
                        ['heirarchy', $p['transactions'][0]['unit_id']],
                        ['branch_id', $user->selected_branch],
                    ])->first();
                    $unitRemainingBal = $unit->unit_name;
                    $unit_obj =  $unit;
                } else if (isset($p->pricelist[0])) {
                    $unit = Unit::where([
                        ['price_list_id', $p->pricelist[0]->id],
                        ['branch_id', $user->selected_branch],
                    ])
                    ->first();
                    $unitRemainingBal = $unit ? $unit->unit_name : '-';
                    $unit_obj = $unit ? $unit : [];
                } 
                $ppunit = (isset($unit_obj['price_per_unit']) ? $unit_obj['price_per_unit'] : '');
                $productsRes[$k]['remaining_balance'] = $remainingBalance;
                $productsRes[$k]['unit_name'] = $unitRemainingBal;
                $productsRes[$k]['unit_obj'] = $unit_obj;
                $productsRes[$k]['price'] = $ppunit.'/'.$unitRemainingBal;
            } 
            
            return response()->json($productsRes, 200);
        } catch (Exception $e) {
            return response()->json(['err' => $e], 500);
        }
    }

    public function getProduct ($product_id) 
    {
        try {
            $user = User::where('id', Auth::id())->first();
            
            $product = Product::with([
                'pricelist' => function ($query) use ($user) {
                    $query->where('branch_id', $user->selected_branch);
                    $query->whereNull('deleted_at');
                },
                'transactions' => function ($query) {
                    $query->whereNull('is_pending_transaction');
                    $query->orderBy('created_at');
                }
            ])
            ->whereNull('deleted_at')
            ->where('id',$product_id)->first();

            $pricelist = $product['pricelist'];
            $ctr = 0;
            foreach ($pricelist as $price) {
                $units = Unit::where('price_list_id', $price['id'])
                    ->whereNull('deleted_at')
                    ->orderBy('heirarchy', 'ASC')
                    ->get();
                $product['pricelist'][$ctr]['unit'] = $units;
                $ctr++;
            }
            return response()->json($product, 200);
        } catch (Exception $e) {
            return response()->json(['err' => $e], 500);
        }
    }

    public function delete($productId) 
    {
        try {
            $user = User::where('id', Auth::id())->first();
            $product = Product::find($productId)->with('transactions')->first();

            if (intval($user->designation) !== 1) {
                return response()->json(['error' => 'You are not allowed to delete a product.'], 500);
            }
            
            Product::find($productId)->delete();
            return response()->json(['message' => 'Product deleted successfully'], 200);
        } catch (Exception $e) {
            return response()->json(['error' => $e], 500);
        }
    }
    
}
