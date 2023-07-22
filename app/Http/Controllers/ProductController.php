<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Pricelist;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

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
        try {
            $arrPrices = [];
            $product = Product::create($data['product']);
            
            foreach ($data['prices'] as $price) {
                $arrPrices = [
                    "pricelist_name" => $price["pricelist_name"],
                    "is_default" => $price["is_default"],
                    "company_id" => $price["company_id"],
                    "branch_id" => $price["branch_id"],
                ];
                $priceList = $product->pricelist()->create($arrPrices);
                foreach ($price['unit'] as $unit) {
                    $unit['product_id'] = $product->id;
                    $priceList->unit()->create($unit);
                }
            }
            return response()->json($product, 200);
        } catch (Exception $e) {
            return response()->json(['err' => $e], 500);
        }
    }


    public function getProducts ($companyId) 
    {
        try {
            $products = Product::with('pricelist')->where('company_id', $companyId)->orderBy('id', 'DESC')->get();

            foreach ($products as $product) {
                $pricelist = $product['pricelist'];
                $ctr = 0;
                foreach ($pricelist as $price) {
                    $unit = Unit::find($price['id']);
                    $product['pricelist'][$ctr]['unit'][] = $unit;
                    $ctr++;
                }
            }
            return response()->json($products, 200);
        } catch (Exception $e) {
            return response()->json(['err' => $e], 500);
        }
    }

    public function getProduct ($product_id) 
    {
        try {
            $product = Product::with('pricelist')->where('id',$product_id)->first();

            #dd($product['pricelist']);
            $pricelist = $product['pricelist'];
            $ctr = 0;
            foreach ($pricelist as $price) {
                $unit = Unit::find($price['id']);
                $product['pricelist'][$ctr]['unit'][] = $unit;
                $ctr++;
            }
            
            return response()->json($product, 200);
        } catch (Exception $e) {
            return response()->json(['err' => $e], 500);
        }
    }

}
