<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TransactionController extends Controller
{
    public function index()
    {
        $transactions = Transaction::where('user_id', Auth::id())
            ->orderBy('date', 'desc')
            ->get();
            
        return response()->json($transactions);
    }

    public function store(Request $request)
    {
        $request->validate([
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'type' => 'required|in:income,expense',
            'category' => 'required|string|max:255',
            'date' => 'required|date',
            'is_recurring' => 'nullable|boolean',
            'recurrence_day' => 'nullable|integer|min:1|max:31'
        ]);

        $transaction = Transaction::create([
            'user_id' => Auth::id(),
            'description' => $request->description,
            'amount' => $request->type === 'income' ? $request->amount : -$request->amount,
            'type' => $request->type,
            'category' => $request->category,
            'date' => $request->date,
            'is_recurring' => (bool)$request->is_recurring,
            'recurrence_day' => $request->is_recurring ? ($request->recurrence_day ?? (int)\Illuminate\Support\Carbon::parse($request->date)->format('d')) : null
        ]);

        return response()->json($transaction, 201);
    }

    public function destroy($id)
    {
        $transaction = Transaction::where('user_id', Auth::id())->findOrFail($id);
        $transaction->delete();

        return response()->json(['message' => 'Transaction supprimée']);
    }

    public function dashboard()
    {
        $transactions = Transaction::where('user_id', Auth::id())->get();
        
        $totalIncome = $transactions->where('amount', '>', 0)->sum('amount');
        $totalExpenses = abs($transactions->where('amount', '<', 0)->sum('amount'));
        $totalBalance = $totalIncome - $totalExpenses;

        return response()->json([
            'totalIncome' => $totalIncome,
            'totalExpenses' => $totalExpenses,
            'totalBalance' => $totalBalance
        ]);
    }
}