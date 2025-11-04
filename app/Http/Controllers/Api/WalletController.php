<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class WalletController extends Controller
{
    public function balance(Request $request)
    {
        $wallet = $request->user()->wallet;
        return response()->json([
            'balance' => $wallet->balance
        ]);
    }

    public function transactions(Request $request)
    {
        $transactions = $request->user()->wallet
            ->transactions()
            ->latest()
            ->paginate(20);

        return response()->json($transactions);
    }

    public function createDepositQR(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:10000'
        ]);

        $referenceCode = 'DEP' . strtoupper(Str::random(10));
        
        // Tạo QR code (giả lập - thực tế cần tích hợp với cổng thanh toán)
        $qrData = [
            'reference_code' => $referenceCode,
            'amount' => $request->amount,
            'bank_account' => '1234567890',
            'bank_name' => 'MB Bank',
            'account_holder' => 'NGUYEN VAN A',
            'content' => $referenceCode
        ];

        return response()->json([
            'qr_data' => $qrData,
            'qr_url' => "https://img.vietqr.io/image/970422-1234567890-compact.png?amount={$request->amount}&addInfo={$referenceCode}",
            'reference_code' => $referenceCode
        ]);
    }

    public function confirmDeposit(Request $request)
    {
        $request->validate([
            'reference_code' => 'required|string',
            'amount' => 'required|numeric|min:10000'
        ]);

        // Trong thực tế, cần verify với webhook từ ngân hàng
        // Đây là demo nên tự động confirm
        $wallet = $request->user()->wallet;
        $wallet->deposit(
            $request->amount, 
            'Nạp tiền vào ví', 
            $request->reference_code
        );

        return response()->json([
            'message' => 'Nạp tiền thành công',
            'balance' => $wallet->balance
        ]);
    }
}