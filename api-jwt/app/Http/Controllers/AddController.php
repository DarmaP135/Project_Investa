<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Order;

class AddController extends Controller
{


    public function Order(Request $request)
{
    // Validasi input
    $validator = Validator::make($request->all(), [
        'item_name' => 'required|string',
        'quantity' => 'required|integer|min:1',
        'price' => 'required|numeric|min:0',
    ]);

    if ($validator->fails()) {
        return response()->json(['error' => $validator->errors()], 400);
    }

    // Ambil user yang sedang login
    $user = auth()->user();

    // Buat pesanan baru
    $order = new Order();
    $order->user_id = $user->id;
    $order->item_name = $request->input('item_name');
    $order->quantity = $request->input('quantity');
    $order->price = $request->input('price');
    $order->save();

    return response()->json(['success' => 'Order created successfully'], 200);
}

}
