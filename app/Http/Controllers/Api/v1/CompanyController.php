<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\Company;
use App\Models\MsCompanyCategory;
use Carbon\Carbon;

class CompanyController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = Auth::user();
        if (!is_null($user)) {
            $companies = Company::whereNull('deleted_at')->get();
            return response([
                "message" => "list of companies",
                "body" => $companies
            ], 200);
        } else {
            return response([
                "message" => "forbidden",
                "body" => null
            ], 403);
        }
    }

    public function getListCompanyCategories()
    {
        $user = Auth::user();
        if (!is_null($user)) {
            $companyCategories = MsCompanyCategory::whereNull('deleted_at')->get();
            return response([
                "message" => "list of company categories",
                "body" => $companyCategories
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
            $company = Company::create($params);
            if (!is_null($company)) {
                return response([
                    "message" => "company created",
                    "body" => $company
                ], 200);
            } else {
                return response([
                    "message" => "company not found",
                    "body" => $company
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
            $company = Company::find($id);
            if (!is_null($company)) {
                return response([
                    "message" => "found company",
                    "body" => $company
                ], 200);
            } else {
                return response([
                    "message" => "company not found",
                    "body" => $company
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
            $company = Company::with('category')->find($user->bs_companies_id);
            if (!is_null($company)) {
                $company->country_name = Company::findCountryName($company->country);
                $company->currency_name = Company::findCurrencyName($company->currency);
                $company->currencies = Company::getCurrencies();
                $company->countries = Company::getCountries();
                return response([
                    "message" => "found company",
                    "body" => $company
                ], 200);
            } else {
                return response([
                    "message" => "company not found",
                    "body" => $company
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
            $company = Company::find($user->bs_companies_id);
            if (!is_null($company)) {
                $params = $request->all();
                if (isset($params['null_validation']) && $params['null_validation']) {
                    $params = array_filter($params);
                }
                $company->fill($params);
                $company->save();
                return response([
                    "message" => "company updated",
                    "body" => $company
                ], 200);
            } else {
                return response([
                    "message" => "company not found",
                    "body" => $company
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
            $company = Company::find($user->bs_companies_id);
            if (!is_null($company)) {
                $dateNow = Carbon::now();
                $company->flag_active = Company::STATE_ACTIVE;
                $company->deleted_at = $dateNow->toDateTimeString();;
                $company->save();
                return response([
                    "message" => "company deleted",
                    "body" => $company
                ], 200);
            } else {
                return response([
                    "message" => "company not found",
                    "body" => $company
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
