<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\RecurringTransactionService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TransactionController extends Controller
{
    public function index()
    {
        $q = request();
        $builder = Transaction::query()->where('user_id', Auth::id());

        // Filters
        if ($search = $q->query('search')) {
            $builder->where(function($sub) use ($search) {
                $sub->where('description', 'like', "%$search%")
                    ->orWhere('category', 'like', "%$search%");
            });
        }

        if ($type = $q->query('type')) {
            $builder->where('type', $type);
        }
        if ($category = $q->query('category')) {
            $builder->where('category', $category);
        }
        if ($year = $q->query('year')) {
            $builder->whereYear('date', (int)$year);
        }
        if ($month = $q->query('month')) {
            $builder->whereMonth('date', (int)$month);
        }
        if ($min = $q->query('min_amount')) {
            $builder->where('amount', '>=', (float)$min);
        }
        if ($max = $q->query('max_amount')) {
            $builder->where('amount', '<=', (float)$max);
        }
        if ($recurring = $q->query('recurring')) {
            if ($recurring === 'template') {
                $builder->where('is_recurring', true)->whereNull('parent_id');
            } elseif ($recurring === 'occurrence') {
                $builder->whereNotNull('parent_id');
            }
        }

        $builder->orderBy('date', 'desc')->orderBy('id', 'desc');

        // Pagination if requested
        $perPage = (int) $q->query('per_page', 0);
        if ($perPage > 0) {
            $paginator = $builder->paginate($perPage)->appends($q->query());
            return response()->json($paginator);
        }

        return response()->json($builder->get());
    }

    public function store(Request $request, RecurringTransactionService $recurringService)
    {
        $request->validate([
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'type' => 'required|in:income,expense',
            'category' => 'required|string|max:255',
            'date' => 'required|date',
            'is_recurring' => 'sometimes|boolean',
            'recurrence_frequency' => 'nullable|in:daily,weekly,monthly,yearly',
            'recurrence_interval' => 'nullable|integer|min:1',
            'recurrence_day' => 'nullable|integer|min:1|max:31',
            'recurrence_end_date' => 'nullable|date|after_or_equal:date'
        ]);

        $data = [
            'user_id' => Auth::id(),
            'description' => $request->description,
            'amount' => $request->type === 'income' ? $request->amount : -$request->amount,
            'type' => $request->type,
            'category' => $request->category,
            'date' => $request->date
        ];

        // Handle recurrence setup on base transaction (template)
        if ($request->boolean('is_recurring')) {
            $data['is_recurring'] = true;
            $data['recurrence_frequency'] = $request->recurrence_frequency;
            $data['recurrence_interval'] = $request->recurrence_interval ?? 1;
            // For monthly/yearly, default recurrence_day to day of transaction if not provided
            if (in_array($request->recurrence_frequency, ['monthly', 'yearly'])) {
                $data['recurrence_day'] = $request->recurrence_day ?? (int) date('j', strtotime($request->date));
            } else {
                $data['recurrence_day'] = $request->recurrence_day; // optional
            }
            $data['recurrence_end_date'] = $request->recurrence_end_date;
            // Anchor last_generated_date to the base transaction date so generation starts from it
            $data['last_generated_date'] = $request->date;
        } else {
            $data['is_recurring'] = false;
        }

        $transaction = Transaction::create($data);

        // Si récurrent, générer immédiatement toutes les occurrences dues jusqu'à aujourd'hui
        if ($transaction->is_recurring && $transaction->parent_id === null) {
            $recurringService->generateDueOccurrences($transaction);
        }

        return response()->json($transaction, 201);
    }

    public function destroy($id)
    {
        $transaction = Transaction::where('user_id', Auth::id())->findOrFail($id);
        $transaction->delete();

        return response()->json(['message' => 'Transaction supprimée']);
    }

    public function update(Request $request, $id, RecurringTransactionService $recurringService)
    {
        $transaction = Transaction::where('user_id', Auth::id())->findOrFail($id);

        $request->validate([
            'description' => 'sometimes|required|string|max:255',
            'amount' => 'sometimes|required|numeric|min:0',
            'type' => 'sometimes|required|in:income,expense',
            'category' => 'sometimes|required|string|max:255',
            'date' => 'sometimes|required|date',
            // Champs de récurrence (uniquement pour les modèles)
            'is_recurring' => 'sometimes|boolean',
            'recurrence_frequency' => 'nullable|in:daily,weekly,monthly,yearly',
            'recurrence_interval' => 'nullable|integer|min:1',
            'recurrence_day' => 'nullable|integer|min:1|max:31',
            'recurrence_end_date' => 'nullable|date|after_or_equal:date'
        ]);

        $data = [];
        if ($request->has('description')) $data['description'] = $request->description;
        if ($request->has('amount')) $data['amount'] = ($request->type ?? $transaction->type) === 'income' ? $request->amount : -$request->amount;
        if ($request->has('type')) $data['type'] = $request->type;
        if ($request->has('category')) $data['category'] = $request->category;
        if ($request->has('date')) $data['date'] = $request->date;

        // Mise à jour champs simples
        if (!empty($data)) {
            $transaction->update($data);
        }

        // Si c'est un modèle (parent_id null), accepter la mise à jour des champs de récurrence
        $isBase = $transaction->parent_id === null;
        if ($isBase && ($request->has('is_recurring') || $request->hasAny(['recurrence_frequency','recurrence_interval','recurrence_day','recurrence_end_date']))) {
            if ($request->has('is_recurring')) {
                $transaction->is_recurring = $request->boolean('is_recurring');
                if ($transaction->is_recurring && !$transaction->last_generated_date) {
                    // ancrer au besoin
                    $transaction->last_generated_date = $transaction->date;
                }
            }
            if ($request->filled('recurrence_frequency')) {
                $transaction->recurrence_frequency = $request->recurrence_frequency;
            }
            if ($request->filled('recurrence_interval')) {
                $transaction->recurrence_interval = (int)$request->recurrence_interval;
            }
            if (in_array($transaction->recurrence_frequency, ['monthly','yearly'])) {
                $transaction->recurrence_day = $request->recurrence_day ?? (int) date('j', strtotime($transaction->date));
            } else {
                if ($request->has('recurrence_day')) {
                    $transaction->recurrence_day = $request->recurrence_day; // accepté mais non utilisé
                }
            }
            if ($request->filled('recurrence_end_date')) {
                $transaction->recurrence_end_date = $request->recurrence_end_date;
            }
            $transaction->save();

            // Générer immédiatement les occurrences dues si activé
            if ($transaction->is_recurring) {
                $recurringService->generateDueOccurrences($transaction);
            }
        }

        return response()->json($transaction->fresh());
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

    public function export()
    {
        // Reutilise les filtres d'index
        $request = request();
        $request->merge(['per_page' => 0]);
        // Construire le même builder que index()
        $builder = Transaction::query()->where('user_id', Auth::id());

        if ($search = $request->query('search')) {
            $builder->where(function($sub) use ($search) {
                $sub->where('description', 'like', "%$search%")
                    ->orWhere('category', 'like', "%$search%");
            });
        }
        if ($type = $request->query('type')) { $builder->where('type', $type); }
        if ($category = $request->query('category')) { $builder->where('category', $category); }
        if ($year = $request->query('year')) { $builder->whereYear('date', (int)$year); }
        if ($month = $request->query('month')) { $builder->whereMonth('date', (int)$month); }
        if ($min = $request->query('min_amount')) { $builder->where('amount', '>=', (float)$min); }
        if ($max = $request->query('max_amount')) { $builder->where('amount', '<=', (float)$max); }
        if ($recurring = $request->query('recurring')) {
            if ($recurring === 'template') {
                $builder->where('is_recurring', true)->whereNull('parent_id');
            } elseif ($recurring === 'occurrence') {
                $builder->whereNotNull('parent_id');
            }
        }

        $builder->orderBy('date','desc')->orderBy('id','desc');
        $rows = $builder->get();

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="transactions.csv"'
        ];

        $callback = function() use ($rows) {
            $out = fopen('php://output', 'w');
            // BOM UTF-8
            fwrite($out, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($out, ['ID','Description','Montant','Type','Catégorie','Date','Récurrente','ParentID']);
            foreach ($rows as $t) {
                fputcsv($out, [
                    $t->id,
                    $t->description,
                    $t->amount,
                    $t->type,
                    $t->category,
                    $t->date?->format('Y-m-d'),
                    $t->is_recurring ? '1' : '0',
                    $t->parent_id,
                ]);
            }
            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function importPreview(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:2048'
        ]);

        $file = $request->file('file')->getRealPath();
        $handle = fopen($file, 'r');
        if (!$handle) return response()->json(['message' => 'Fichier illisible'], 422);

        // Skip BOM
        $bom = fgets($handle, 4);
        if ($bom !== false && !str_starts_with($bom, "ID,")) {
            // not accurate but continue by rewinding
            rewind($handle);
        }

        $header = fgetcsv($handle);
        if (!$header) return response()->json(['message' => 'Entête CSV manquante'], 422);

        $expected = ['ID','Description','Montant','Type','Catégorie','Date'];
        // Soft mapping by name
        $map = [];
        foreach ($expected as $col) {
            $idx = array_search($col, $header);
            if ($idx === false) return response()->json(['message' => "Colonne manquante: $col"], 422);
            $map[$col] = $idx;
        }

        $preview = [];
        $line = 1;
        while (($row = fgetcsv($handle)) !== false && count($preview) < 50) {
            $line++;
            $amount = (float) ($row[$map['Montant']] ?? 0);
            $type = ($row[$map['Type']] ?? 'income');
            $preview[] = [
                'description' => $row[$map['Description']] ?? '',
                'amount' => $amount,
                'type' => in_array($type, ['income','expense']) ? $type : ($amount >= 0 ? 'income' : 'expense'),
                'category' => $row[$map['Catégorie']] ?? 'Autre',
                'date' => $row[$map['Date']] ?? date('Y-m-d'),
            ];
        }
        fclose($handle);

        return response()->json(['preview' => $preview, 'count' => count($preview)]);
    }

    public function importCommit(Request $request)
    {
        $request->validate([
            'rows' => 'required|array|min:1',
            'rows.*.description' => 'required|string|max:255',
            'rows.*.amount' => 'required|numeric',
            'rows.*.type' => 'required|in:income,expense',
            'rows.*.category' => 'required|string|max:255',
            'rows.*.date' => 'required|date'
        ]);

        $created = [];
        foreach ($request->rows as $r) {
            $created[] = Transaction::create([
                'user_id' => Auth::id(),
                'description' => $r['description'],
                'amount' => $r['type'] === 'income' ? $r['amount'] : -abs($r['amount']),
                'type' => $r['type'],
                'category' => $r['category'],
                'date' => $r['date'],
            ]);
        }

        return response()->json(['created' => count($created)]);
    }
}