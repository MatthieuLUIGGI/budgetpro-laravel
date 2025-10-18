<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BudgetController extends Controller
{
    public function index(Request $request)
    {
        $year = (int)($request->query('year') ?? date('Y'));
        $month = (int)($request->query('month') ?? date('n'));
        $items = Budget::where('user_id', Auth::id())
            ->where('year', $year)
            ->where('month', $month)
            ->orderBy('category')
            ->get();

        // Calcul consommé par catégorie
        $spent = Transaction::where('user_id', Auth::id())
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->where('amount', '<', 0)
            ->selectRaw('category, SUM(ABS(amount)) as total')
            ->groupBy('category')
            ->pluck('total', 'category');

        return response()->json([
            'year' => $year,
            'month' => $month,
            'budgets' => $items,
            'spent' => $spent,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'category' => 'required|string|max:255',
            'year' => 'required|integer|min:2000|max:2100',
            'month' => 'required|integer|min:1|max:12',
            'amount' => 'required|numeric|min:0'
        ]);

        $budget = Budget::updateOrCreate([
            'user_id' => Auth::id(),
            'category' => $data['category'],
            'year' => $data['year'],
            'month' => $data['month'],
        ], [
            'amount' => $data['amount']
        ]);

        return response()->json($budget, 201);
    }
}
