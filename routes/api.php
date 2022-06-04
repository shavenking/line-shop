<?php

use Illuminate\Support\Facades\Route;

Route::post('/google-form-webhook', function (\Illuminate\Http\Request $request) {
    $orderItems = [
        ['name' => '七里香', 'quantity' => $request->input(9), 'price' => 100],
        ['name' => '雞胗', 'quantity' => $request->input(10), 'price' => 100],
        ['name' => '雞翅', 'quantity' => $request->input(11), 'price' => 120],
        ['name' => '棒棒腿', 'quantity' => $request->input(12), 'price' => 100],
        ['name' => '雞腳', 'quantity' => $request->input(13), 'price' => 70],
        ['name' => '鵪鶉蛋', 'quantity' => $request->input(14), 'price' => 70],
        ['name' => '豆干', 'quantity' => $request->input(15), 'price' => 70],
        ['name' => '三杯米血', 'quantity' => $request->input(16), 'price' => 70],
    ];

    $data = [
        'purchaser_name'        => $request->input(3),
        'purchaser_phone'       => $request->input(4),
        'purchaser_postal_code' => $request->input(5),
        'purchaser_address'     => $request->input(6),
        'arrival_date'          => $request->input(7),
        'arrival_time'          => $request->input(8),
        'order_items'           => collect($orderItems)->where('quantity', '>', 0)->values()->all(),
    ];

    $data['order_item_total'] = collect($data['order_items'])->reduce(function ($total, $orderItem) {
        return $total + $orderItem['quantity'] * $orderItem['price'];
    });

    $data['giveaway_quantity'] = floor($data['order_item_total'] / 1000);
    $data['is_offshore_islands'] = \Illuminate\Support\Str::contains($data['purchaser_address'], [
        '澎湖', '金門', '馬祖', '綠島',
    ]);

    $data['shipping_fee'] = 0;

    if ($data['is_offshore_islands']) {
        // 離島
        switch (true) {
            case ($data['order_item_total'] <= 1000):
                $data['shipping_fee'] = 250;
                break;
            case ($data['order_item_total'] <= 2000):
                $data['shipping_fee'] = 340;
                break;
            case ($data['order_item_total'] <= 2999):
                $data['shipping_fee'] = 400;
                break;
        }
    } else {
        // 本島
        switch (true) {
            case ($data['order_item_total'] <= 1000):
                $data['shipping_fee'] = 160;
                break;
            case ($data['order_item_total'] <= 2000):
                $data['shipping_fee'] = 225;
                break;
            case ($data['order_item_total'] <= 2999):
                $data['shipping_fee'] = 290;
                break;
        }
    }

    $data['last_money_transfer_date'] = today()->addDays(3)->format('m/d');
    $data['total_amount'] = $data['order_item_total'] + $data['shipping_fee'];

    \App\Events\GoogleFormSubmitted::dispatch($data);

    return 'ok';
});
