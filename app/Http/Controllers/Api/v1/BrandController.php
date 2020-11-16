<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\Brand;
use App\Models\Company;
use Carbon\Carbon;

class BrandController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getListStoreAdmin(Request $request)
    {
        $user = Auth::user();
        if (!is_null($user)) {
            $params = $request->all();
            $brands = Brand::whereNull(Brand::TABLE_NAME . '.deleted_at')
                ->where(Brand::TABLE_NAME . '.bs_companies_id', $user->bs_companies_id);
            if (isset($params['search']) && !is_null($params['search'])) {
                $key = $params['search'];
                $brands = $brands->where(function($query) use ($key){
                    $query->where(Brand::TABLE_NAME . '.name', 'LIKE', '%' . $key . '%');
                    $query->orWhere(Brand::TABLE_NAME . '.description', 'LIKE', '%' . $key . '%');
                });
            }
            // if (isset($params['orderBy']) && !is_null($params['orderBy'])) {
            //     $brands = $brands->orderBy($params['orderBy'], $params['orderDir']);
            // }
            $brands = $brands->paginate(env('ITEMS_PAGINATOR'));
            return response([
                "message" => "list of warehouses",
                "body" => $brands
            ], 200);
        } else {
            return response([
                "message" => "forbidden",
                "body" => null
            ], 403);
        }
    }
}
