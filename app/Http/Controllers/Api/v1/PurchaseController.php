<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\Purchase;
use App\Models\LicensePrUser;
use App\Models\License;
use Carbon\Carbon;

class PurchaseController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return null;
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
    public function createPurchase(Request $request)
    {
        $user = Auth::user();
        if (!is_null($user)) {
            $result = null;
            $params = $request->all();
            $license = License::select('id', 'months')->where('type', $params['licenseType'])->first();
            if (!is_null($license)) {
                $dateStart = Carbon::now();
                $dateEnd = Carbon::now()->addMonths($license->months);
                $licensePrUser = new LicensePrUser();
                $licensePrUser->users_id = $user->id;
                $licensePrUser->licenses_id = $license->id;
                $licensePrUser->date_start = $dateStart->toDateString();
                $licensePrUser->date_end = $dateEnd->toDateString();
                if (isset($params['failed']) && (boolean)$params['failed']) {
                    $licensePrUser->status = LicensePrUser::STATUS_CANCELLED;
                    $licensePrUser->flag_active = LicensePrUser::STATE_INACTIVE;
                } else { 
                    $licensePrUser->status = LicensePrUser::STATUS_AVAILABLE;
                }
                $licensePrUser->save();
                if ($licensePrUser) {
                    $number = 0;
                    $dateNow = Carbon::now();
                    if (isset($params['purchaseNumber'])) {
                        $number = $params['purchaseNumber'];
                    } else {
                        // find purchase number
                    }
                    $purchase = new Purchase();
                    $purchase->license_pr_user_id = $licensePrUser->id;
                    $purchase->number = $number;
                    $purchase->invoice_details = json_decode($params['visaResponse']);
                    $purchase->receptor_details = $user;	
                    $purchase->sended_at = $dateNow->toDateTimeString();
                    if ($params['failed']) {
                        $purchase->status = Purchase::STATUS_CANCELLED;
                        $purchase->flag_active = Purchase::STATE_INACTIVE;
                    } else { 
                        $purchase->status = Purchase::STATUS_FINISHED;
                        $this->cleanPreviusLicenses($user->id, $licensePrUser->id);
                    }
                    $purchase->save();
                    if (!is_null($purchase)) {    
                        return response([
                            "message" => "first purchase number",
                            "body" => Purchase::START_NUMBER
                        ], 201);
                    }
                }
            }

            if (is_null($result)) {
                return response([
                    "message" => "bad request",
                    "body" => null
                ], 400);
            }
        } else {
            return response([
                "message" => "forbidden",
                "body" => null
            ], 403);
        }
    }

    private function cleanPreviusLicenses($userId, $licensePrUserId)
    {
        LicensePrUser::whereNull('deleted_at')
            ->where('users_id', $userId)
            ->where('status', LicensePrUser::STATUS_AVAILABLE)
            ->where('id', '!=', $licensePrUserId)
            ->update([
                'status' => LicensePrUser::STATUS_UNAVAILABLE
            ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return null;
    }

    /**
     * Display the specified resource by type attribute.
     *
     * @param  string  $type
     * @return \Illuminate\Http\Response
     */
    public function generatePurchaseNumber()
    {
        $user = Auth::user();
        if (!is_null($user)) {
            $purchase = Purchase::select('number')
                ->orderBy('id', 'DESC')->first();
            if (!is_null($purchase)) {
                return response([
                    "message" => "found purchase number",
                    "body" => $purchase->number + 1
                ], 200);
            } else {
                return response([
                    "message" => "first purchase number",
                    "body" => Purchase::START_NUMBER
                ], 201);
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
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        return null;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        return null;
    }
}
