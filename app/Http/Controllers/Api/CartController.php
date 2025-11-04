<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CardDenomination;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class CartController extends Controller
{
    public function add(Request $request)
    {
        $request->validate([
            'denomination_id' => 'required|exists:card_denominations,id',
            'quantity' => 'required|integer|min:1|max:50'
        ]);

        $denomination = CardDenomination::with('category')
            ->withCount('availableCards as stock')
            ->findOrFail($request->denomination_id);

        if ($denomination->stock < $request->quantity) {
            return response()->json([
                'message' => 'Số lượng thẻ không đủ'
            ], 400);
        }

        $userId = $request->user()->id;
        $cart = Cache::get("cart_$userId", []);
        
        $itemKey = "item_{$request->denomination_id}";
        
        if (isset($cart[$itemKey])) {
            $cart[$itemKey]['quantity'] += $request->quantity;
        } else {
            $cart[$itemKey] = [
                'denomination_id' => $denomination->id,
                'category_name' => $denomination->category->name,
                'value' => $denomination->value,
                'price' => $denomination->price,
                'quantity' => $request->quantity
            ];
        }

        Cache::put("cart_$userId", $cart, now()->addDays(7));

        return response()->json([
            'message' => 'Đã thêm vào giỏ hàng',
            'cart' => $this->calculateCart($cart)
        ]);
    }

    public function view(Request $request)
    {
        $userId = $request->user()->id;
        $cart = Cache::get("cart_$userId", []);
        
        return response()->json($this->calculateCart($cart));
    }

    public function update(Request $request)
    {
        $request->validate([
            'denomination_id' => 'required|exists:card_denominations,id',
            'quantity' => 'required|integer|min:0|max:50'
        ]);

        $userId = $request->user()->id;
        $cart = Cache::get("cart_$userId", []);
        $itemKey = "item_{$request->denomination_id}";

        if ($request->quantity == 0) {
            unset($cart[$itemKey]);
        } else {
            $denomination = CardDenomination::withCount('availableCards as stock')
                ->findOrFail($request->denomination_id);

            if ($denomination->stock < $request->quantity) {
                return response()->json(['message' => 'Số lượng không đủ'], 400);
            }

            if (isset($cart[$itemKey])) {
                $cart[$itemKey]['quantity'] = $request->quantity;
            }
        }

        Cache::put("cart_$userId", $cart, now()->addDays(7));

        return response()->json($this->calculateCart($cart));
    }

    public function remove(Request $request, $denominationId)
    {
        $userId = $request->user()->id;
        $cart = Cache::get("cart_$userId", []);
        $itemKey = "item_$denominationId";

        unset($cart[$itemKey]);
        Cache::put("cart_$userId", $cart, now()->addDays(7));

        return response()->json($this->calculateCart($cart));
    }

    public function clear(Request $request)
    {
        $userId = $request->user()->id;
        Cache::forget("cart_$userId");
        
        return response()->json(['message' => 'Đã xóa giỏ hàng']);
    }

    private function calculateCart($cart)
    {
        $items = array_values($cart);
        $total = array_reduce($items, function($sum, $item) {
            return $sum + ($item['price'] * $item['quantity']);
        }, 0);

        return [
            'items' => $items,
            'total' => $total,
            'count' => count($items)
        ];
    }
}