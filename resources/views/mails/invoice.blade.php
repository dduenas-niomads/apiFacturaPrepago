<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Meh Perú</title>
    
    <style>
    .invoice-box {
        max-width: 800px;
        margin: auto;
        padding: 30px;
        border: 1px solid #eee;
        box-shadow: 0 0 10px rgba(0, 0, 0, .15);
        font-size: 16px;
        line-height: 24px;
        font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;
        color: #555;
    }
    
    .invoice-box table {
        width: 100%;
        line-height: inherit;
        text-align: left;
    }
    
    .invoice-box table td {
        padding: 5px;
        vertical-align: top;
    }
    
    .invoice-box table tr td:nth-child(2) {
        text-align: right;
    }
    
    .invoice-box table tr.top table td {
        padding-bottom: 20px;
    }
    
    .invoice-box table tr.top table td.title {
        font-size: 45px;
        line-height: 45px;
        color: #333;
    }
    
    .invoice-box table tr.information table td {
        padding-bottom: 40px;
    }
    
    .invoice-box table tr.heading td {
        backgnumber_format: #eee;
        border-bottom: 1px solid #ddd;
        font-weight: bold;
    }
    
    .invoice-box table tr.details td {
        padding-bottom: 20px;
    }
    
    .invoice-box table tr.item td{
        border-bottom: 1px solid #eee;
    }
    
    .invoice-box table tr.item.last td {
        border-bottom: none;
    }
    
    .invoice-box table tr.total td:nth-child(2) {
        border-top: 2px solid #eee;
        font-weight: bold;
    }
    
    @media only screen and (max-width: 600px) {
        .invoice-box table tr.top table td {
            width: 100%;
            display: block;
            text-align: center;
        }
        
        .invoice-box table tr.information table td {
            width: 100%;
            display: block;
            text-align: center;
        }
    }
    
    /** RTL **/
    .rtl {
        direction: rtl;
        font-family: Tahoma, 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;
    }
    
    .rtl table {
        text-align: right;
    }
    
    .rtl table tr td:nth-child(2) {
        text-align: left;
    }
    </style>
</head>

<body>
    <div class="invoice-box">
        <table cellpadding="0" cellspacing="0">
            <tr class="top">
                <td colspan="2">
                    <table>
                        <tr>
                            <td class="title">
                                <img src="https://cdn.shopify.com/s/files/1/0025/5308/6019/files/MEH_1_300x300.png" style="width:100%; max-width:300px;">
                            </td>
                            
                            <td>
                                Boleta de venta #: B002-{{ str_pad($order->id, 8, "0", STR_PAD_LEFT) }} <br>
                                Fecha: {{ $order->created_at }}<br>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            
            <tr class="information">
                <td colspan="2">
                    <table>
                        <tr>
                            <td>
                                Av. Dos de Mayo 1675<br>
                                San Isidro<br>
                                +51 952 928 928
                            </td>
                            
                            <td>
                                @if (!is_null($order->billing_address))
                                    {{ isset($order->billing_address['name']) ? $order->billing_address['name'] : "" }}<br>
                                    {{ isset($order->billing_address['phone']) ? $order->billing_address['phone'] : "" }}<br>
                                @endif 
                                {{ $order->email }}
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            
            <tr class="heading">
                <td colspan="2">
                    Método de pago
                </td>
                
                <td>
                    Total
                </td>
            </tr>
            
            <tr class="details">
                <td colspan="2">
                    {{ $order->gateway }} (Número de órden: {{ $order->order_number }})
                </td>
                
                <td>
                    {{ $order->currency }} {{ number_format($order->total_price, 2) }}  
                </td>
            </tr>
            
            <tr class="heading">
                <td>
                    Detalle
                </td>
                
                <td>
                    Cantidad
                </td>
                
                <td align="right">
                    Precio
                </td>
            </tr>

            @foreach ( $order->line_items as $lineItem)
                <tr class="item">
                    <td>
                        {{ $lineItem['name'] }}
                    </td>
                    
                    <td>
                        {{ $lineItem['quantity'] }}
                    </td>

                    <td align="right">
                        {{ $order->currency }} {{ number_format($lineItem['price'],2) }}
                    </td>
                </tr>
            @endforeach
            @php
                $shippingCost = 0;
            @endphp
            @foreach ( $order->shipping_lines as $shippingLine)
                <tr class="item">
                    <td>
                        {{ $shippingLine['code'] }}
                    </td>
                    
                    <td>
                        1
                    </td>

                    <td align="right">
                        {{ $order->currency }}  {{ number_format($shippingLine['price'],2) }}
                    </td>
                </tr>
                @php
                    $shippingCost = $shippingCost + $shippingLine['price'];
                @endphp
            @endforeach

            <tr class="item_last">
                <td align="right" colspan="3">
                   Total productos: {{ $order->currency }} {{ number_format($order->total_line_items_price, 2) }}
                </td>
            </tr>
            <tr class="item_last">
                <td align="right" colspan="3">
                   Descuentos: {{ $order->currency }} {{ number_format($order->total_discounts, 2) }}
                </td>
            </tr>
            <tr class="item_last">
                <td align="right" colspan="3">
                   Subtotal: {{ $order->currency }} {{ number_format($order->subtotal_price, 2) }}
                </td>
            </tr>
            <tr class="item_last">
                <td align="right" colspan="3">
                   Envíos: {{ $order->currency }} {{ number_format($shippingCost, 2) }}
                </td>
            </tr>
            
            <tr class="total">
                <td></td>
                <td colspan="2">
                   Total: {{ $order->currency }} {{ number_format($order->total_price, 2) }}
                </td>
            </tr>
        </table>
    </div>
</body>
</html>