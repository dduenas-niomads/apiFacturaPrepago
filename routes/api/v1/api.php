<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Users
Route::prefix('/user')->group(function() {
    Route::post('/login', 'Api\v1\LoginController@login');
    Route::middleware('auth:api')->delete('/logout', 'Api\v1\LoginController@logout');
    Route::middleware('auth:api')->delete('/logout-all', 'Api\v1\LoginController@logoutAll');
    Route::middleware('auth:api')->get('/', 'Api\v1\UserController@show');
    Route::middleware('auth:api')->patch('/update', 'Api\v1\UserController@update');
});

// Licenses/Plans
Route::prefix('/licenses')->group(function() {
    Route::middleware('auth:api')->get('/', 'Api\v1\LicenseController@index');
    Route::middleware('auth:api')->get('/{id}', 'Api\v1\LicenseController@show');
    Route::middleware('auth:api')->get('/by-type/{type}', 'Api\v1\LicenseController@showByType');
});

// Purchases
Route::prefix('/purchases')->group(function() {
    Route::middleware('auth:api')->get('/next-number', 'Api\v1\PurchaseController@generatePurchaseNumber');
    Route::middleware('auth:api')->post('/', 'Api\v1\PurchaseController@createPurchase');
});

// Companies
Route::prefix('/companies')->group(function() {
    Route::middleware('auth:api')->get('/', 'Api\v1\CompanyController@showByUser');
    Route::middleware('auth:api')->get('/categories', 'Api\v1\CompanyController@getListCompanyCategories');
    Route::middleware('auth:api')->patch('/', 'Api\v1\CompanyController@update');
});

// Warehouses - SUPER ADMIN
Route::prefix('/super-admin/warehouses')->group(function() {
    Route::middleware('auth:api')->get('/', 'Api\v1\WarehouseController@getListSuperAdmin');
    Route::middleware('auth:api')->get('/by-user', 'Api\v1\WarehouseController@showByUser');
    // Crud
    Route::middleware('auth:api')->get('/{id}', 'Api\v1\WarehouseController@show');
    Route::middleware('auth:api')->post('/', 'Api\v1\WarehouseController@store');
    Route::middleware('auth:api')->patch('/{id}', 'Api\v1\WarehouseController@update');
    Route::middleware('auth:api')->delete('/{id}', 'Api\v1\WarehouseController@destroy');
});
// Warehouses - STORE ADMIN
Route::prefix('/store-admin/warehouses')->group(function() {
    Route::middleware('auth:api')->get('/', 'Api\v1\WarehouseController@getListStoreAdmin');
    Route::middleware('auth:api')->get('/by-user', 'Api\v1\WarehouseController@showByUser');
    // Crud
    Route::middleware('auth:api')->get('/{id}', 'Api\v1\WarehouseController@show');
    Route::middleware('auth:api')->patch('/{id}', 'Api\v1\WarehouseController@update');
});
// Brands - STORE ADMIN
Route::prefix('/store-admin/brands')->group(function() {
    Route::middleware('auth:api')->get('/', 'Api\v1\BrandController@getListStoreAdmin');
    // Route::middleware('auth:api')->get('/by-user', 'Api\v1\BrandController@showByUser');
    // // Crud
    // Route::middleware('auth:api')->get('/{id}', 'Api\v1\BrandController@show');
    // Route::middleware('auth:api')->patch('/{id}', 'Api\v1\BrandController@update');
});

// Products - SUPER ADMIN
Route::prefix('/super-admin/products')->group(function() {
    Route::middleware('auth:api')->get('/', 'Api\v1\ProductController@getListSuperAdmin');
    // Crud
    Route::middleware('auth:api')->get('/{id}', 'Api\v1\ProductController@show');
    Route::middleware('auth:api')->post('/', 'Api\v1\ProductController@store');
    Route::middleware('auth:api')->patch('/{id}', 'Api\v1\ProductController@update');
    Route::middleware('auth:api')->delete('/{id}', 'Api\v1\ProductController@destroy');
});
// Products - STORE ADMIN
Route::prefix('/store-admin/products')->group(function() {
    Route::middleware('auth:api')->get('/', 'Api\v1\ProductController@getListStoreAdmin');
    // Crud
    Route::middleware('auth:api')->get('/{id}', 'Api\v1\ProductController@show');
    Route::middleware('auth:api')->post('/', 'Api\v1\ProductController@store');
    Route::middleware('auth:api')->patch('/{id}', 'Api\v1\ProductController@update');
    Route::middleware('auth:api')->delete('/{id}', 'Api\v1\ProductController@destroy');
});

// Sales - SUPER ADMIN
Route::prefix('/super-admin/sales')->group(function() {
    Route::middleware('auth:api')->get('/', 'Api\v1\SaleController@getListSuperAdmin');
    // Crud
    Route::middleware('auth:api')->get('/{id}', 'Api\v1\SaleController@show');
    Route::middleware('auth:api')->post('/', 'Api\v1\SaleController@store');
    Route::middleware('auth:api')->patch('/{id}', 'Api\v1\SaleController@update');
    Route::middleware('auth:api')->delete('/{id}', 'Api\v1\SaleController@destroy');
});
// Sales - STORE ADMIN
Route::prefix('/store-admin/sales')->group(function() {
    Route::middleware('auth:api')->get('/', 'Api\v1\SaleController@getListStoreAdmin');
    // Crud
    Route::middleware('auth:api')->get('/{id}', 'Api\v1\SaleController@show');
    Route::middleware('auth:api')->post('/', 'Api\v1\SaleController@store');
    Route::middleware('auth:api')->patch('/{id}', 'Api\v1\SaleController@update');
    Route::middleware('auth:api')->delete('/{id}', 'Api\v1\SaleController@destroy');
});

// Clients - SUPER ADMIN
Route::prefix('/super-admin/clients')->group(function() {
    Route::middleware('auth:api')->get('/', 'Api\v1\ClientController@getListSuperAdmin');
});
// Clients - STORE ADMIN
Route::prefix('/store-admin/clients')->group(function() {
    Route::middleware('auth:api')->get('/', 'Api\v1\ClientController@getListStoreAdmin');
});

// Orders - SUPER ADMIN
Route::prefix('/super-admin/orders')->group(function() {
    Route::middleware('auth:api')->get('/', 'Api\v1\OrderController@getListSuperAdmin');
});
// Orders - SUPER ADMIN
Route::prefix('/super-admin/orders-ecommerce')->group(function() {
    Route::middleware('auth:api')->get('/', 'Api\v1\OrderEcommerceController@getListSuperAdmin');
    Route::middleware('auth:api')->get('/rdc', 'Api\v1\OrderEcommerceController@getListRdc');
});
// Orders - STORE ADMIN
Route::prefix('/store-admin/orders')->group(function() {
    Route::middleware('auth:api')->get('/', 'Api\v1\OrderController@getListStoreAdmin');
});