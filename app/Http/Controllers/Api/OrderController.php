<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Card;
use App\Models\CardDenomination;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function checkout(Request $request)
    {
        $request->validate([
            'payment_method' => 'required|in:wallet'
        ]);

        $userId = $request->user()->id;
        $cart = Cache::get("cart_$userId", []);

        if (empty($cart)) {
            return response()->json(['message' => 'Giỏ hàng trống'], 400);
        }

        DB::beginTransaction();
        try {
            $totalAmount = 0;
            $orderItems = [];

            // Kiểm tra tồn kho và tính tổng tiền
            foreach ($cart as $item) {
                $denomination = CardDenomination::withCount('availableCards as stock')
                    ->lockForUpdate()
                    ->findOrFail($item['denomination_id']);

                if ($denomination->stock < $item['quantity']) {
                    throw new \Exception("Thẻ {$denomination->category->name} mệnh giá {$denomination->value} không đủ số lượng");
                }

                $totalAmount += $item['price'] * $item['quantity'];
                $orderItems[] = [
                    'denomination' => $denomination,
                    'quantity' => $item['quantity'],
                    'price' => $item['price']
                ];
            }

            // Kiểm tra số dư
            $wallet = $request->user()->wallet;
            if ($wallet->balance < $totalAmount) {
                throw new \Exception('Số dư trong ví không đủ');
            }

            // Tạo đơn hàng
            $order = Order::create([
                'user_id' => $userId,
                'total_amount' => $totalAmount,
                'payment_method' => 'wallet',
                'status' => 'pending'
            ]);

            // Gán thẻ cho đơn hàng
            foreach ($orderItems as $item) {
                $cards = Card::where('denomination_id', $item['denomination']->id)
                    ->where('status', 'available')
                    ->where('expiry_date', '>', now())
                    ->limit($item['quantity'])
                    ->lockForUpdate()
                    ->get();

                foreach ($cards as $card) {
                    $order->items()->create([
                        'card_id' => $card->id,
                        'denomination_id' => $item['denomination']->id,
                        'price' => $item['price']
                    ]);

                    $card->update(['status' => 'sold']);
                }
            }

            // Trừ tiền trong ví
            $wallet->withdraw($totalAmount, "Thanh toán đơn hàng #{$order->order_number}");

            // Cập nhật trạng thái đơn hàng
            $order->update(['status' => 'completed']);

            // Xóa giỏ hàng
            Cache::forget("cart_$userId");

            DB::commit();

            return response()->json([
                'message' => 'Đặt hàng thành công',
                'order' => $order->load('items.card', 'items.denomination.category')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Đặt hàng thất bại: ' . $e->getMessage()
            ], 400);
        }
    }

    public function index(Request $request)
    {
        $orders = $request->user()
            ->orders()
            ->with(['items.card', 'items.denomination.category'])
            ->latest()
            ->paginate(10);

        return response()->json($orders);
    }

    public function show(Request $request, $id)
    {
        $order = $request->user()
            ->orders()
            ->with(['items.card', 'items.denomination.category'])
            ->findOrFail($id);

        return response()->json($order);
    }
}