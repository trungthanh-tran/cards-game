<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CardCategory;
use App\Models\CardDenomination;
use Illuminate\Http\Request;

class CardController extends Controller
{
    public function categories()
    {
        $categories = CardCategory::where('is_active', true)
            ->with(['denominations' => function($q) {
                $q->where('is_active', true)
                  ->withCount('availableCards as stock');
            }])
            ->get();

        return response()->json($categories);
    }

    public function denominations($categoryId)
    {
        $denominations = CardDenomination::where('category_id', $categoryId)
            ->where('is_active', true)
            ->withCount('availableCards as stock')
            ->with('category')
            ->get();

        return response()->json($denominations);
    }
}