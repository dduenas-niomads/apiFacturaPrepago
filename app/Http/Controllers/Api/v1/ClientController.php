<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\User;
use App\Models\Client;
use Carbon\Carbon;

class ClientController extends Controller
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
            $clients = Client::join(User::TABLE_NAME, User::TABLE_NAME . '.id', '=',
                    Client::TABLE_NAME . '.users_id')
                ->select(User::TABLE_NAME . '.name as user_name',
                    User::TABLE_NAME . '.lastname as user_lastname',
                    User::TABLE_NAME . '.document_number as user_document_number',
                    Client::TABLE_NAME . '.id',
                    Client::TABLE_NAME . '.last_purchase',
                    Client::TABLE_NAME . '.total_purchases',
                    Client::TABLE_NAME . '.flag_active' )
                ->whereNull(Client::TABLE_NAME . '.deleted_at')
                ->where(Client::TABLE_NAME . '.bs_companies_id', $user->bs_companies_id);
            if (isset($params['search']) && !is_null($params['search'])) {
                $key = $params['search'];
                $clients = $clients->where(function($query) use ($key){
                    $query->where(User::TABLE_NAME . '.name', 'LIKE', '%' . $key . '%');
                    $query->orWhere(User::TABLE_NAME . '.lastname', 'LIKE', '%' . $key . '%');
                    $query->orWhere(User::TABLE_NAME . '.document_number', 'LIKE', '%' . $key . '%');
                });
            }
            if (isset($params['orderBy']) && !is_null($params['orderBy'])) {
                $clients = $clients->orderBy($params['orderBy'], $params['orderDir']);
            }
            $clients = $clients->paginate(env('ITEMS_PAGINATOR'));
            return response([
                "message" => "list of clients",
                "body" => $clients
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
            $clients = Client::join(User::TABLE_NAME, User::TABLE_NAME . '.id', '=',
                    Client::TABLE_NAME . '.users_id')
                ->select(User::TABLE_NAME . '.name as user_name',
                    User::TABLE_NAME . '.lastname as user_lastname',
                    User::TABLE_NAME . '.document_number as user_document_number',
                    Client::TABLE_NAME . '.id',
                    Client::TABLE_NAME . '.last_purchase',
                    Client::TABLE_NAME . '.total_purchases',
                    Client::TABLE_NAME . '.flag_active' )
                ->whereNull(Client::TABLE_NAME . '.deleted_at')
                ->where(Client::TABLE_NAME . '.bs_companies_id', $user->bs_companies_id)
                ->where(Client::TABLE_NAME . '.bs_warehouses_id', $user->bs_warehouses_id);
            if (isset($params['search']) && !is_null($params['search'])) {
                $key = $params['search'];
                $clients = $clients->where(function($query) use ($key){
                    $query->where(User::TABLE_NAME . '.name', 'LIKE', '%' . $key . '%');
                    $query->orWhere(User::TABLE_NAME . '.lastname', 'LIKE', '%' . $key . '%');
                    $query->orWhere(User::TABLE_NAME . '.document_number', 'LIKE', '%' . $key . '%');
                });
            }
            if (isset($params['orderBy']) && !is_null($params['orderBy'])) {
                $clients = $clients->orderBy($params['orderBy'], $params['orderDir']);
            }
            $clients = $clients->paginate(env('ITEMS_PAGINATOR'));
            return response([
                "message" => "list of clients",
                "body" => $clients
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
            $client = Client::create($params);
            if (!is_null($client)) {
                return response([
                    "message" => "Client created",
                    "body" => $client
                ], 200);
            } else {
                return response([
                    "message" => "Client not found",
                    "body" => $client
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
            $client = Client::where(Client::TABLE_NAME . '.bs_companies_id', $user->bs_companies_id)
                ->find($id);
            if (!is_null($client)) {
                return response([
                    "message" => "found client",
                    "body" => $client
                ], 200);
            } else {
                return response([
                    "message" => "Client not found",
                    "body" => $client
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
            $client = Client::find($user->bs_warehouses_id);
            if (!is_null($client)) {
                return response([
                    "message" => "found client",
                    "body" => $client
                ], 200);
            } else {
                return response([
                    "message" => "Client not found",
                    "body" => $client
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
            $client = Client::find($user->bs_warehouses_id);
            if (!is_null($client)) {
                $params = $request->all();
                $client->fill($params);
                $client->save();
                return response([
                    "message" => "Client updated",
                    "body" => $client
                ], 200);
            } else {
                return response([
                    "message" => "Client not found",
                    "body" => $client
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
            $client = Client::find($user->bs_warehouses_id);
            if (!is_null($client)) {
                $dateNow = Carbon::now();
                $client->flag_active = Client::STATE_ACTIVE;
                $client->deleted_at = $dateNow->toDateTimeString();;
                $client->save();
                return response([
                    "message" => "Client deleted",
                    "body" => $client
                ], 200);
            } else {
                return response([
                    "message" => "Client not found",
                    "body" => $client
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
