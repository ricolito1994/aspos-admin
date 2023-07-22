<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Response;
use App\Models\User;
use App\Models\Branch;


class TopnavController extends Controller
{
    public function getBranches ( ) 
    {
        try {
            $branches = [];
            $user_owner = User::where('is_owner', true)->find(Auth::id());

            if($user_owner) {
                $branches['branches'] = $user_owner->company()
                                ->with('branches')
                                ->first()
                                ->branches;
                $branches['selected_branch'] = $user_owner->selectedBranch()->first();
            
            } else {
                $branches['branches'][] = User::find(Auth::id())->branch()->first();
                $branches['selected_branch'] = $branches['branches'][0];
            }
        
            return response()->json($branches, 200);

        } catch (Exception $e) {
            return response()->json(['err' => $e], 500);
        }
    }

    /** 
     * for owners only
     * get
     * @param Integer 
     * 
     */
    public function changeBranch (Request $request, $branchId) 
    {
        try {
            $user = User::where('id',Auth::id());
            $user->update(['selected_branch' => $branchId]);
            
            return response()->json(['selected_branch'=> 
                User::find(Auth::id())
                    ->selectedBranch()
                    ->first()
                ]);

        } catch (Exception $e) {
            return response()->json(['err' => $e], 500);
        }
    }
}
