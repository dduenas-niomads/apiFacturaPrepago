<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\MsSaleType;
use App\Models\MsSaleStatus;
use App\Models\Warehouse;
use App\Models\Sale;
use App\Models\SaleDetail;
use Carbon\Carbon;

class SaleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getListSuperAdmin(Request $request)
    {
        $user = Auth::user();
        if (!is_null($user)) {
            $params = $request->all();
            $sales = Sale::join(Warehouse::TABLE_NAME, Warehouse::TABLE_NAME . '.id', '=',
                    Sale::TABLE_NAME . '.bs_warehouses_id')
                ->join(MsSaleStatus::TABLE_NAME, MsSaleStatus::TABLE_NAME . '.id', '=',
                    Sale::TABLE_NAME . '.bs_ms_sales_status_id')
                ->join(MsSaleType::TABLE_NAME, MsSaleType::TABLE_NAME . '.code', '=',
                    Sale::TABLE_NAME . '.type_document')
                ->select(Sale::TABLE_NAME . '.*',
                    Warehouse::TABLE_NAME . '.commercial_name as warehouse_name',
                    MsSaleStatus::TABLE_NAME . '.name as status_name',
                    MsSaleType::TABLE_NAME . '.name as type_name')
                ->whereNull(Sale::TABLE_NAME . '.deleted_at')
                ->where(Sale::TABLE_NAME . '.bs_companies_id', $user->bs_companies_id);
                if (isset($params['search']) && !is_null($params['search'])) {
                    $key = $params['search'];
                    $sales = $sales->where(function($query) use ($key){
                        $query->where(Sale::TABLE_NAME . '.ticket', 'LIKE', '%' . $key . '%');
                        // $query->orWhere(Sale::TABLE_NAME . '.serie', 'LIKE', '%' . $key . '%');
                        // $query->orWhere(db::raw('CONCAT(' . Customer::TABLE_NAME . '.lastname, " ",' . Customer::TABLE_NAME . '.name' . ')'), 'LIKE', '%' . $key . '%');
                    });
                }
                if (isset($params['orderBy']) && !is_null($params['orderBy'])) {
                    $sales = $sales->orderBy($params['orderBy'], $params['orderDir']);
                }
            $sales = $sales->paginate(env('ITEMS_PAGINATOR'));
            return response([
                "message" => "list of sales",
                "body" => $sales
            ], 200);
        } else {
            return response([
                "message" => "forbidden",
                "body" => null
            ], 403);
        }
    }

    public function getListStoreAdmin(Request $request)
    {
        $user = Auth::user();
        if (!is_null($user)) {
            $params = $request->all();
            $sales = Sale::whereNull(Sale::TABLE_NAME . '.deleted_at')
                ->where(Sale::TABLE_NAME . '.bs_companies_id', $user->bs_companies_id)
                ->where(Sale::TABLE_NAME . '.bs_warehouses_id', $user->bs_warehouses_id);
                if (isset($params['search']) && !is_null($params['search'])) {
                    $key = $params['search'];
                    $sales = $sales->where(function($query) use ($key){
                        $query->where(Sale::TABLE_NAME . '.ticket', 'LIKE', '%' . $key . '%');
                        // $query->orWhere(Sale::TABLE_NAME . '.serie', 'LIKE', '%' . $key . '%');
                        // $query->orWhere(db::raw('CONCAT(' . Customer::TABLE_NAME . '.lastname, " ",' . Customer::TABLE_NAME . '.name' . ')'), 'LIKE', '%' . $key . '%');
                    });
                }
                if (isset($params['orderBy']) && !is_null($params['orderBy'])) {
                    $sales = $sales->orderBy($params['orderBy'], $params['orderDir']);
                }
            $sales = $sales->paginate(env('ITEMS_PAGINATOR'));
            return response([
                "message" => "list of sales",
                "body" => $sales
            ], 200);
        } else {
            return response([
                "message" => "forbidden",
                "body" => null
            ], 403);
        }
    }

    public function getListCustomer(Request $request)
    {
        $user = Auth::user();
        if (!is_null($user)) {
            $brandId = 0;
            if (isset($params['brand_url_friendly'])) {
                $brandId = Brand::getIdFromUrlFriendly($params['brand_url_friendly']);
            }
            $products = Product::with('brand')
                ->with('category')
                ->whereNull(Product::TABLE_NAME . '.deleted_at')
                ->where(Product::TABLE_NAME . '.bs_brands_id', $brandId)
                ->paginate(env('ITEMS_PAGINATOR'));
            return response([
                "message" => "list of products",
                "body" => $products
            ], 200);
        } else {
            return response([
                "message" => "forbidden",
                "body" => null
            ], 403);
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return null;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        if (!is_null($user)) {
            $params = $request->all();
            $params['bs_companies_id'] = $user->bs_companies_id;
            $params['bs_warehouses_id'] = $user->bs_warehouses_id;
            $product = Product::create($params);
            if (!is_null($product)) {
                return response([
                    "message" => "Producto creado correctamente",
                    "body" => $product
                ], 200);
            } else {
                return response([
                    "message" => "Producto no encontrado",
                    "body" => $product
                ], 404);
            }
        } else {
            return response([
                "message" => "Acceso prohibido",
                "body" => null
            ], 403);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $user = Auth::user();
        if (!is_null($user)) {
            $product = Product::where(Product::TABLE_NAME . '.bs_companies_id', $user->bs_companies_id)
                ->find($id);
            if (!is_null($product)) {
                return response([
                    "message" => "found product",
                    "body" => $product
                ], 200);
            } else {
                return response([
                    "message" => "Product not found",
                    "body" => $product
                ], 404);
            }
        } else {
            return response([
                "message" => "forbidden",
                "body" => null
            ], 403);
        }
    }

    /**
     * Display the specified resource by foreign id attribute.
     *
     * @return \Illuminate\Http\Response
     */
    public function showByUser()
    {
        $user = Auth::user();
        if (!is_null($user)) {
            $product = Product::find($user->bs_warehouses_id);
            if (!is_null($product)) {
                return response([
                    "message" => "found product",
                    "body" => $product
                ], 200);
            } else {
                return response([
                    "message" => "Product not found",
                    "body" => $product
                ], 404);
            }
        } else {
            return response([
                "message" => "forbidden",
                "body" => null
            ], 403);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        return null;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update($id, Request $request)
    {
        $user = Auth::user();
        if (!is_null($user)) {
            $product = Product::find($id);
            if (!is_null($product)) {
                $params = $request->all();
                if (isset($params['id'])) {
                    unset($params['id']);
                }
                $product->fill($params);
                $product->save();
                return response([
                    "message" => "Producto actualizado correctamente",
                    "body" => $product
                ], 200);
            } else {
                return response([
                    "message" => "Producto no encontrado",
                    "body" => $product
                ], 404);
            }
        } else {
            return response([
                "message" => "Acceso prohibido",
                "body" => null
            ], 403);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $user = Auth::user();
        if (!is_null($user)) {
            $product = Product::find($id);
            if (!is_null($product)) {
                $dateNow = Carbon::now();
                $product->flag_active = Product::STATE_INACTIVE;
                $product->deleted_at = $dateNow->toDateTimeString();;
                $product->save();
                return response([
                    "message" => "Producto eliminado correctamente",
                    "body" => $product
                ], 200);
            } else {
                return response([
                    "message" => "Producto no encontrado",
                    "body" => $product
                ], 404);
            }
        } else {
            return response([
                "message" => "Acceso prohibido",
                "body" => null
            ], 403);
        }
    }
}
