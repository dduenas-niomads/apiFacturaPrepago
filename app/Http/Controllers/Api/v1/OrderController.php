<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\Order;
use App\Models\MsOrderStatus;
use Carbon\Carbon;

class OrderController extends Controller
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
            $orders = Order::join(MsOrderStatus::TABLE_NAME, MsOrderStatus::TABLE_NAME . '.id', '=',
                    Order::TABLE_NAME . '.bs_ms_order_status_id')
                ->select(MsOrderStatus::TABLE_NAME . '.code as status_code',
                    MsOrderStatus::TABLE_NAME . '.class as status_class',
                    MsOrderStatus::TABLE_NAME . '.name as status_name',
                    Order::TABLE_NAME . '.id',
                    Order::TABLE_NAME . '.correlative',
                    Order::TABLE_NAME . '.reference',
                    Order::TABLE_NAME . '.created_at',
                    Order::TABLE_NAME . '.total')
                ->whereNull(Order::TABLE_NAME . '.deleted_at')
                ->where(Order::TABLE_NAME . '.bs_companies_id', $user->bs_companies_id);
            if (isset($params['search']) && !is_null($params['search'])) {
                $key = $params['search'];
                $orders = $orders->where(function($query) use ($key){
                    $query->where(Order::TABLE_NAME . '.correlative', 'LIKE', '%' . $key . '%');
                    $query->orWhere(Order::TABLE_NAME . '.reference', 'LIKE', '%' . $key . '%');
                    $query->orWhere(MsOrderStatus::TABLE_NAME . '.status_code', 'LIKE', '%' . $key . '%');
                    $query->orWhere(MsOrderStatus::TABLE_NAME . '.status_name', 'LIKE', '%' . $key . '%');
                });
            }
            if (isset($params['orderBy']) && !is_null($params['orderBy'])) {
                $orders = $orders->orderBy($params['orderBy'], $params['orderDir']);
            }
            $orders = $orders->paginate(env('ITEMS_PAGINATOR'));
            return response([
                "message" => "list of orders",
                "body" => $orders
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
            $orders = Order::join(MsOrderStatus::TABLE_NAME, MsOrderStatus::TABLE_NAME . '.id', '=',
                    Order::TABLE_NAME . '.bs_ms_order_status_id')
                ->select(MsOrderStatus::TABLE_NAME . '.code as status_code',
                    MsOrderStatus::TABLE_NAME . '.name as status_name',
                    Order::TABLE_NAME . '.id',
                    Order::TABLE_NAME . '.correlative',
                    Order::TABLE_NAME . '.reference',
                    Order::TABLE_NAME . '.created_at',
                    Order::TABLE_NAME . '.total')
                ->whereNull(Order::TABLE_NAME . '.deleted_at')
                ->where(Order::TABLE_NAME . '.bs_companies_id', $user->bs_companies_id)
                ->where(Order::TABLE_NAME . '.bs_warehouses_id', $user->bs_warehouses_id);
            if (isset($params['search']) && !is_null($params['search'])) {
                $key = $params['search'];
                $orders = $orders->where(function($query) use ($key){
                    $query->where(Order::TABLE_NAME . '.correlative', 'LIKE', '%' . $key . '%');
                    $query->orWhere(Order::TABLE_NAME . '.reference', 'LIKE', '%' . $key . '%');
                    $query->orWhere(MsOrderStatus::TABLE_NAME . '.status_code', 'LIKE', '%' . $key . '%');
                    $query->orWhere(MsOrderStatus::TABLE_NAME . '.status_name', 'LIKE', '%' . $key . '%');
                });
            }
            if (isset($params['orderBy']) && !is_null($params['orderBy'])) {
                $orders = $orders->orderBy($params['orderBy'], $params['orderDir']);
            }
            $orders = $orders->paginate(env('ITEMS_PAGINATOR'));
            return response([
                "message" => "list of orders",
                "body" => $orders
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
            $order = Order::create($params);
            if (!is_null($order)) {
                return response([
                    "message" => "Order created",
                    "body" => $order
                ], 200);
            } else {
                return response([
                    "message" => "Order not found",
                    "body" => $order
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
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $user = Auth::user();
        if (!is_null($user)) {
            $order = Order::where(Order::TABLE_NAME . '.bs_companies_id', $user->bs_companies_id)
                ->find($id);
            if (!is_null($order)) {
                return response([
                    "message" => "found order",
                    "body" => $order
                ], 200);
            } else {
                return response([
                    "message" => "Order not found",
                    "body" => $order
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
            $order = Order::find($user->bs_warehouses_id);
            if (!is_null($order)) {
                return response([
                    "message" => "found order",
                    "body" => $order
                ], 200);
            } else {
                return response([
                    "message" => "Order not found",
                    "body" => $order
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
    public function update(Request $request)
    {
        $user = Auth::user();
        if (!is_null($user)) {
            $order = Order::find($user->bs_warehouses_id);
            if (!is_null($order)) {
                $params = $request->all();
                $order->fill($params);
                $order->save();
                return response([
                    "message" => "Order updated",
                    "body" => $order
                ], 200);
            } else {
                return response([
                    "message" => "Order not found",
                    "body" => $order
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
     * Remove the specified resource from storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy()
    {
        $user = Auth::user();
        if (!is_null($user)) {
            $order = Order::find($user->bs_warehouses_id);
            if (!is_null($order)) {
                $dateNow = Carbon::now();
                $order->flag_active = Order::STATE_ACTIVE;
                $order->deleted_at = $dateNow->toDateTimeString();;
                $order->save();
                return response([
                    "message" => "Order deleted",
                    "body" => $order
                ], 200);
            } else {
                return response([
                    "message" => "Order not found",
                    "body" => $order
                ], 404);
            }
        } else {
            return response([
                "message" => "forbidden",
                "body" => null
            ], 403);
        }
    }
}
