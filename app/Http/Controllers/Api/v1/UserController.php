<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\User;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($message = "found user")
    {
        return response([
            "message" => $message,
            "body" => $this->showByIdFunction(Auth::user()->id)
        ]);
    }

    public function showById($id)
    {
        return response($this->showByIdFunction($id));
    }

    private function showByIdFunction($id)
    {
        return User::with('activeLicense')
            ->with('company:id')
            ->with('warehouse:id,commercial_name,type_document,document_number,phone,email,show_address')
            ->with('role:id,name,description,code,actions')
            ->find($id);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        // 
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $user = Auth::user();
        $params = $request->all();
        if (isset($params['null_validation']) && $params['null_validation']) {
            $params = array_filter($params);
        }
        if (isset($params['password']) && !is_null($params['password'])) {
            $params['password'] = Hash::make($params['password']);
        }
        $user->fill($params);
        $user->save();
        return $this->show("updated user");
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
