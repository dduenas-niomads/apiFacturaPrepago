<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\Order;
use App\Models\Company;
use App\Models\OrderEcommerce;
use App\Models\MsOrderStatus;
use Carbon\Carbon;
use App\Notifications\SendEINotification;

class OrderEcommerceController extends Controller
{
    public function getListSuperAdminLocal(Request $request)
    {
        $companyId = 7;
        $apiResponse = [ "sync" => 0 ];
        $response = ["data" => []];
        if (!is_null($companyId)) {
            // shopify access
            $ecommerceCredentials = self::getEcommerceCredentials($companyId);
            if (!is_null($ecommerceCredentials->ecommerce_api_key) &&
                !is_null($ecommerceCredentials->ecommerce_password) &&
                !is_null($ecommerceCredentials->ecommerce_shared_secret) &&
                !is_null($ecommerceCredentials->ecommerce_store)) {
                    $credential = new \Slince\Shopify\PrivateAppCredential(
                        $ecommerceCredentials->ecommerce_api_key,
                        $ecommerceCredentials->ecommerce_password,
                        $ecommerceCredentials->ecommerce_shared_secret);
                    $client = new \Slince\Shopify\Client($credential, 
                        $ecommerceCredentials->ecommerce_store . '.myshopify.com', 
                        [ 
                            // 'metaCacheDir' => '/app/storage/app/public/cache/tmp' // Metadata cache dir, required 
                            'metaCacheDir' => './tmp' // Metadata cache dir, required 
                        ]
                    );
                    $orders = $client->getOrderManager()->findAll([
                        "status" => "any"
                    ]);
                    foreach ($orders as $key => $value) {
                        $order = [
                            "email" => $value->getEmail(),
                            "createdAt" => $value->getCreatedAt(),
                            "totalPrice" => $value->getTotalPrice(),
                            "subtotalPrice" => $value->getSubtotalPrice(),
                            "totalDiscounts" => $value->getTotalDiscounts(),
                            "totalLineItemsPrice" => $value->getTotalLineItemsPrice(),
                            "currency" => $value->getCurrency(),
                            "gateway" => $value->getGateway(),
                            "orderNumber" => $value->getOrderNumber(),
                            "confirmed" => $value->isConfirmed(),
                            "financialStatus" => $value->getFinancialStatus(),
                            "lineItems" => [],
                            "shippingLines" => [],
                            "billingAddress" => [],
                        ];
                        if (!is_null($value->getBillingAddress())) {
                            $order['billingAddress'] = [
                                "name" => $value->getBillingAddress()->getName(),
                                "address1" => $value->getBillingAddress()->getAddress1(),
                                "address2" => $value->getBillingAddress()->getAddress2(),
                                "city" => $value->getBillingAddress()->getCity(),
                                "country" => $value->getBillingAddress()->getCountry(),
                                "province" => $value->getBillingAddress()->getProvince(),
                                "zip" => $value->getBillingAddress()->getZip(),
                                "phone" => $value->getBillingAddress()->getPhone(),
                                "provinceCode" => $value->getBillingAddress()->getProvinceCode(),
                                "countryCode" => $value->getBillingAddress()->getCountryCode()
                            ];
                        
                        }
                        foreach ($value->getLineItems() as $keyLineItem => $lineItem) {
                            array_push($order['lineItems'], [
                                "name" => $lineItem->getName(),
                                "vendor" => $lineItem->getVendor(),
                                "quantity" => $lineItem->getQuantity(),
                                "price" => $lineItem->getPrice(),
                            ]);
                        }
                        foreach ($value->getShippingLines() as $keyShippingLine => $shippingLine) {
                            array_push($order['shippingLines'], [
                                "code" => $shippingLine->getCode(),
                                "price" => $shippingLine->getPrice(),
                                "source" => $shippingLine->getSource(),
                            ]);
                        }
                        array_push($response['data'], $order);
                    }
            }
        }
        return $response;
    }

    public function getListSuperAdmin(Request $request)
    {
        $user = Auth::user();
        if (!is_null($user)) {
            $params = $request->all();
            $orders = OrderEcommerce::whereNull(OrderEcommerce::TABLE_NAME . '.deleted_at')
                ->where(OrderEcommerce::TABLE_NAME . '.bs_companies_id', $user->bs_companies_id);
            if (isset($params['limit']) && (int)$params['limit'] === 0) {
                $orders = $orders->where(OrderEcommerce::TABLE_NAME . '.financial_status', "paid")
                    ->whereNotNull(OrderEcommerce::TABLE_NAME . '.ruc');
            }
            if (isset($params['search']) && !is_null($params['search'])) {
                $key = $params['search'];
                $orders = $orders->where(function($query) use ($key){
                    $query->where(OrderEcommerce::TABLE_NAME . '.order_number', 'LIKE', '%' . $key . '%');
                    $query->orWhere(OrderEcommerce::TABLE_NAME . '.email', 'LIKE', '%' . $key . '%');
                    $query->orWhere(OrderEcommerce::TABLE_NAME . '.gateway', 'LIKE', '%' . $key . '%');
                });
            }
            if (isset($params['document'])) {
                $orders = $orders->where(OrderEcommerce::TABLE_NAME . '.ruc', $params['document']);
            }
            if (isset($params['period'])) {
                $orders = $orders->where(OrderEcommerce::TABLE_NAME . '.created_at', 'LIKE' , '%' . $params['period'] . '%');
            }
            if (isset($params['orderNumber']) && strlen($params['orderNumber']) > 1) {
                $orders = $orders->where(OrderEcommerce::TABLE_NAME . '.order_number', 'LIKE' , '%' . (int)$params['orderNumber'] . '%');
            }
            // if (isset($params['orderBy']) && !is_null($params['orderBy'])) {
            //     $orders = $orders->orderBy($params['orderBy'], $params['orderDir']);
            // }
            $orders = $orders->orderBy('order_number', 'DESC');
            $ordersTotal = 0;
            $ordersSubtotal = 0;
            if (isset($params['limit']) && (int)$params['limit'] === 0) {
                $orders = $orders->get();
                foreach ($orders as $key => $value) {
                    if ((int)$value->confirmed === 1 
                        && $value->financial_status === "paid") {
                        $ordersTotal = $ordersTotal + $value->total_price;
                        $ordersSubtotal = $ordersSubtotal + $value->subtotal_price;
                    }
                }
            } else {
                $orders = $orders->paginate(10);
            }
            return response([
                "message" => "list of orders",
                "body" => $orders,
                "ordersTotal" => $ordersTotal,
                "ordersSubtotal" => $ordersSubtotal
            ], 200);
        } else {
            return response([
                "message" => "forbidden",
                "body" => null
            ], 403);
        }
    }

    public function getListRdc(Request $request)
    {
        $user = Auth::user();
        if (!is_null($user)) {
            $params = $request->all();
            $orders = OrderEcommerce::whereNull(OrderEcommerce::TABLE_NAME . '.deleted_at')
                ->where(OrderEcommerce::TABLE_NAME . '.bs_companies_id', $user->bs_companies_id)
                ->where(OrderEcommerce::TABLE_NAME . '.financial_status', "paid")
                ->whereNotNull(OrderEcommerce::TABLE_NAME . '.ruc');
            if (isset($params['document'])) {
                $orders = $orders->where(OrderEcommerce::TABLE_NAME . '.ruc', $params['document']);
            }
            if (isset($params['period'])) {
                $orders = $orders->where(OrderEcommerce::TABLE_NAME . '.created_at', 'LIKE' , '%' . $params['period'] . '%');
            }
            if (!isset($params['offset'])) {
                $params['offset'] = 0;
            }
            if (!isset($params['limit'])) {
                $params['limit'] = 1;
            }
            $orders = $orders->orderBy('correlative', 'ASC')
                ->offset($params['offset'])->limit($params['limit'])
                ->get();
            $orders_ = [];
            $count = 1;
                foreach ($orders as $key => $value) {
                    if ((int)$value->confirmed === 1 
                        && $value->financial_status === "paid") {
                            $documentNumber = "88888888";
                            if ($value->total_price > 700) {
                                $documentNumber = "74122648";
                            }
                            array_push($orders_, [
                                "ITEM" => $count,
                                "TIPO_COMPROBANTE" => "03",
                                "NRO_COMPROBANTE" => $value->serie . "-" . $value->correlative,
                                "NRO_DOCUMENTO" => $documentNumber,
                                "TIPO_DOCUMENTO" => "1",
                                "NRO_COMPROBANTE_REF" => "0",
                                "TIPO_COMPROBANTE_REF" => "0",
                                "STATUS" => "1",
                                "COD_MONEDA" => "PEN",
                                "TOTAL" => (float)round($value->total_price, 2),
                                "GRAVADA" => (float)round($value->total_price/1.18, 2),
                                "EXONERADO" => "0",
                                "INAFECTO" => "0",
                                "EXPORTACION" => "0",
                                "GRATUITAS" => "0",
                                "MONTO_CARGO_X_ASIG" => "0",
                                "CARGO_X_ASIGNACION" => "0",
                                "ISC" => "0",
                                "IGV" => (float)round($value->total_price - ($value->total_price/1.18), 2),
                                "OTROS" => "0"
                            ]);
                            $count++;
                    }
                }
            return response([
                "message" => "RC",
                "offset" => $params['offset'],
                "limit" => $params['limit'],
                "total" => count($orders_),
                "body" => $orders_,
            ], 200);
        } else {
            return response([
                "message" => "forbidden",
                "body" => null
            ], 403);
        }
    }

    public static function createOrderEcommerce($params = [], $sendEmail = true)
    {
        $orderEcommerce = new OrderEcommerce();
        $orderEcommerce->bs_companies_id = isset($params['bs_companies_id']) ? $params['bs_companies_id'] : null;
        $orderEcommerce->bs_documents_id = isset($params['bs_documents_id']) ? $params['bs_documents_id'] : null;
        $orderEcommerce->email = isset($params['email']) ? $params['email'] : null;
        $orderEcommerce->total_price = isset($params['totalPrice']) ? $params['totalPrice'] : null;
        $orderEcommerce->subtotal_price = isset($params['subtotalPrice']) ? $params['subtotalPrice'] : null;
        $orderEcommerce->total_discounts = isset($params['totalDiscounts']) ? $params['totalDiscounts'] : null;
        $orderEcommerce->total_line_items_price = isset($params['totalLineItemsPrice']) ? $params['totalLineItemsPrice'] : null;
        $orderEcommerce->currency = isset($params['currency']) ? $params['currency'] : null;
        $orderEcommerce->gateway = isset($params['gateway']) ? $params['gateway'] : null;
        $orderEcommerce->order_number = isset($params['orderNumber']) ? $params['orderNumber'] : null;
        $orderEcommerce->confirmed = isset($params['confirmed']) ? $params['confirmed'] : null;
        $orderEcommerce->financial_status = isset($params['financialStatus']) ? $params['financialStatus'] : null;
        $orderEcommerce->line_items = isset($params['lineItems']) ? $params['lineItems'] : null;
        $orderEcommerce->shipping_lines = isset($params['shippingLines']) ? $params['shippingLines'] : null;
        $orderEcommerce->billing_address = isset($params['billingAddress']) ? $params['billingAddress'] : null;
        $orderEcommerce->created_at = isset($params['created_at']) ? $params['created_at'] : null;
        $orderEcommerce->save();

        if ($orderEcommerce->confirmed 
            && $orderEcommerce->financial_status === "paid"
             && $sendEmail) {
            self::sendEmail($orderEcommerce);
        }

        return $orderEcommerce;
    }

    public static function searchOrderEcommerce($params = [], $sendEmail = true)
    {
        $response = false;

        $orderEcommerce = OrderEcommerce::whereNull("deleted_at")
            ->where(OrderEcommerce::TABLE_NAME . ".bs_companies_id", $params['bs_companies_id'])
            ->where(OrderEcommerce::TABLE_NAME . ".order_number", (int)$params['orderNumber'])
            ->first();
    
        if (is_null($orderEcommerce)) {
            self::createOrderEcommerce($params, $sendEmail);
            $response = true;
        } else {
            if ($orderEcommerce->financial_status !== $params['financialStatus']) {
                $orderEcommerce->financial_status = $params['financialStatus'];
                $orderEcommerce->save();
            }

            if (!$orderEcommerce->flag_ei_send 
                && isset($params['financialStatus'])
                && $params['financialStatus'] === "paid") {
                    if ($sendEmail) {
                        self::sendEmail($orderEcommerce);
                    }
            }
        }
        
        return $response;
    }

    public static function sendEmail($orderEcommerce, $financialStatus = null)
    {
        try {
            // $orderEcommerce->notify(new SendEINotification($orderEcommerce));
            $orderEcommerce->email_sended_at = date("Y-m-d H:i:s");
            $orderEcommerce->flag_ei_send = 1;
            $orderEcommerce->save();
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public static function syncOrderEcommerce(Request $request, $companyId = null)
    {
        $apiResponse = [ "sync" => 0 ];
        $response = ["data" => []];
        $params = $request->all();
        if (!is_null($companyId)) {
            // shopify access
            $ecommerceCredentials = self::getEcommerceCredentials($companyId);
            if (!is_null($ecommerceCredentials->ecommerce_api_key) &&
                !is_null($ecommerceCredentials->ecommerce_password) &&
                !is_null($ecommerceCredentials->ecommerce_shared_secret) &&
                !is_null($ecommerceCredentials->ecommerce_store)) {
                    $credential = new \Slince\Shopify\PrivateAppCredential(
                        $ecommerceCredentials->ecommerce_api_key,
                        $ecommerceCredentials->ecommerce_password,
                        $ecommerceCredentials->ecommerce_shared_secret);
                    $client = new \Slince\Shopify\Client($credential, 
                        $ecommerceCredentials->ecommerce_store . '.myshopify.com', 
                        [ 
                            'metaCacheDir' => env('META_CACHE_DIR')
                            // CLOUD '/app/storage/app/public/cache/tmp'
                            // LOCAL './tmp'
                        ]
                    );
                    $orders = $client->getOrderManager()->findAll([
                        "status" => "any",
                        "created_at_min" => $params["startDate"] . " 00:00:00",
                        "created_at_max" => $params["endDate"] . " 21:59:59",
                        "limit" => 250
                    ]);
                    foreach ($orders as $key => $value) {
                        $order = [
                            "email" => $value->getEmail(),
                            "createdAt" => $value->getCreatedAt(),
                            "totalPrice" => $value->getTotalPrice(),
                            "subtotalPrice" => $value->getSubtotalPrice(),
                            "totalDiscounts" => $value->getTotalDiscounts(),
                            "totalLineItemsPrice" => $value->getTotalLineItemsPrice(),
                            "currency" => $value->getCurrency(),
                            "gateway" => $value->getGateway(),
                            "orderNumber" => $value->getOrderNumber(),
                            "confirmed" => $value->isConfirmed(),
                            "financialStatus" => $value->getFinancialStatus(),
                            "lineItems" => [],
                            "shippingLines" => [],
                            "billingAddress" => [],
                        ];
                        if (!is_null($value->getBillingAddress())) {
                            $order['billingAddress'] = [
                                "name" => $value->getBillingAddress()->getName(),
                                "address1" => $value->getBillingAddress()->getAddress1(),
                                "address2" => $value->getBillingAddress()->getAddress2(),
                                "city" => $value->getBillingAddress()->getCity(),
                                "country" => $value->getBillingAddress()->getCountry(),
                                "province" => $value->getBillingAddress()->getProvince(),
                                "zip" => $value->getBillingAddress()->getZip(),
                                "phone" => $value->getBillingAddress()->getPhone(),
                                "provinceCode" => $value->getBillingAddress()->getProvinceCode(),
                                "countryCode" => $value->getBillingAddress()->getCountryCode()
                            ];
                        }
                        foreach ($value->getLineItems() as $keyLineItem => $lineItem) {
                            array_push($order['lineItems'], [
                                "name" => $lineItem->getName(),
                                "vendor" => $lineItem->getVendor(),
                                "quantity" => $lineItem->getQuantity(),
                                "price" => $lineItem->getPrice(),
                            ]);
                        }
                        foreach ($value->getShippingLines() as $keyShippingLine => $shippingLine) {
                            array_push($order['shippingLines'], [
                                "code" => $shippingLine->getCode(),
                                "price" => $shippingLine->getPrice(),
                                "source" => $shippingLine->getSource(),
                            ]);
                        }
                        array_push($response['data'], $order);
                    }
                    if (isset($params['printOrders']) && (boolean)$params['printOrders']) {
                        dd(json_encode($response));
                    }
            }
        }
        // return $response;
        if (!empty($response)) {
            foreach ($response['data'] as $key => $value) {
                $value['bs_companies_id'] = $companyId;
                $value['created_at'] = $value['createdAt'];
                $sendEmail = true;
                if (isset($params['sendEmail'])) {
                    $sendEmail = (boolean)$params['sendEmail'];
                }
                if (self::searchOrderEcommerce($value, $sendEmail)) {
                    $apiResponse['sync']++;
                }
            }
        }
        return $apiResponse;
    }

    public static function getEcommerceCredentials($companyId)
    {
        return (object)[  
            "ecommerce_api_key" => "c529ebe68597513c7108f7caa390d064",
            "ecommerce_password" => "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",
            "ecommerce_shared_secret" => "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",
            "ecommerce_store" => "pruebasrebmeh",
        ];
        // return Company::find($companyId);
    }
}
