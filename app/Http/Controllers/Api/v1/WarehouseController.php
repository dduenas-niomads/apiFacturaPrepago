<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\Warehouse;
use App\Models\Company;
use Carbon\Carbon;

class WarehouseController extends Controller
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
            $warehouses = Warehouse::whereNull(Warehouse::TABLE_NAME . '.deleted_at')
                ->where(Warehouse::TABLE_NAME . '.bs_companies_id', $user->bs_companies_id);
            if (isset($params['search']) && !is_null($params['search'])) {
                $key = $params['search'];
                $warehouses = $warehouses->where(function($query) use ($key){
                    $query->where(Warehouse::TABLE_NAME . '.commercial_name', 'LIKE', '%' . $key . '%');
                    $query->orWhere(Warehouse::TABLE_NAME . '.document_number', 'LIKE', '%' . $key . '%');
                    $query->orWhere(Warehouse::TABLE_NAME . '.show_address', 'LIKE', '%' . $key . '%');
                    $query->orWhere(Warehouse::TABLE_NAME . '.phone', 'LIKE', '%' . $key . '%');
                });
            }
            if (isset($params['orderBy']) && !is_null($params['orderBy'])) {
                $warehouses = $warehouses->orderBy($params['orderBy'], $params['orderDir']);
            }
            $warehouses = $warehouses->paginate(env('ITEMS_PAGINATOR'));
            return response([
                "message" => "list of warehouses",
                "body" => $warehouses
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
            $warehouses = Warehouse::whereNull(Warehouse::TABLE_NAME . '.deleted_at')
                ->where(Warehouse::TABLE_NAME . '.id', $user->bs_warehouses_id);
            if (isset($params['search']) && !is_null($params['search'])) {
                $key = $params['search'];
                $warehouses = $warehouses->where(function($query) use ($key){
                    $query->where(Warehouse::TABLE_NAME . '.commercial_name', 'LIKE', '%' . $key . '%');
                    $query->orWhere(Warehouse::TABLE_NAME . '.document_number', 'LIKE', '%' . $key . '%');
                    $query->orWhere(Warehouse::TABLE_NAME . '.show_address', 'LIKE', '%' . $key . '%');
                    $query->orWhere(Warehouse::TABLE_NAME . '.phone', 'LIKE', '%' . $key . '%');
                });
            }
            if (isset($params['orderBy']) && !is_null($params['orderBy'])) {
                $warehouses = $warehouses->orderBy($params['orderBy'], $params['orderDir']);
            }
            $warehouses = $warehouses->paginate(env('ITEMS_PAGINATOR'));
            return response([
                "message" => "list of warehouses",
                "body" => $warehouses
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
            $warehouseTotal = Warehouse::whereNull(Warehouse::TABLE_NAME . '.deleted_at')
                ->where(Warehouse::TABLE_NAME . '.bs_companies_id', $user->bs_companies_id)
                ->count();
            $warehouseQty = Company::select(Company::TABLE_NAME . '.stores')
                ->find($user->bs_companies_id);
            if ($warehouseTotal >= $warehouseQty->stores) {
                return response([
                    "message" => "No cuenta con tiendas disponibles. Aumente su plan para más beneficios.",
                    "body" => null
                ], 400);
            }
            $params = $request->all();
            $params['bs_companies_id'] = $user->bs_companies_id;
            $warehouse = Warehouse::create($params);
            if (!is_null($warehouse)) {
                return response([
                    "message" => "Tienda creada correctamente",
                    "body" => $warehouse
                ], 200);
            } else {
                return response([
                    "message" => "No se pudo crear la tienda",
                    "body" => $warehouse
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
            $warehouse = Warehouse::where(Warehouse::TABLE_NAME . '.bs_companies_id', $user->bs_companies_id)
                ->find($id);
            if (!is_null($warehouse)) {
                return response([
                    "message" => "found warehouse",
                    "body" => $warehouse
                ], 200);
            } else {
                return response([
                    "message" => "Warehouse not found",
                    "body" => $warehouse
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
            $warehouse = Warehouse::find($user->bs_warehouses_id);
            if (!is_null($warehouse)) {
                return response([
                    "message" => "found warehouse",
                    "body" => $warehouse
                ], 200);
            } else {
                return response([
                    "message" => "Warehouse not found",
                    "body" => $warehouse
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
    public function update($id = null, Request $request)
    {
        $user = Auth::user();
        if (!is_null($user)) {
            if (is_null($id)) {
                $id = $user->bs_warehouses_id;
            }
            $warehouse = Warehouse::find($id);
            if (!is_null($warehouse)) {
                $params = $request->all();
                if (isset($params['id'])) {
                    unset($params['id']);
                }
                $warehouse->fill($params);
                $warehouse->save();
                return response([
                    "message" => "Tienda actualizada correctamente",
                    "body" => $warehouse
                ], 200);
            } else {
                return response([
                    "message" => "No se pudo actualizar la tienda",
                    "body" => $warehouse
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
    public function destroy($id = null)
    {
        $user = Auth::user();
        if (!is_null($user)) {
            if (is_null($id)) {
                $id = $user->bs_warehouses_id;
            }
            $warehouse = Warehouse::find($id);
            if (!is_null($warehouse)) {
                $dateNow = Carbon::now();
                $warehouse->flag_active = Warehouse::STATE_INACTIVE;
                $warehouse->deleted_at = $dateNow->toDateTimeString();;
                $warehouse->save();
                return response([
                    "message" => "Tienda eliminada correctamente",
                    "body" => $warehouse
                ], 200);
            } else {
                return response([
                    "message" => "No se pudo eliminar la tienda",
                    "body" => $warehouse
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
