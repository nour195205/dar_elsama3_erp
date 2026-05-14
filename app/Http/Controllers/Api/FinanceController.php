<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\Expense;
use App\Models\DoctorPayout;
use Illuminate\Http\Request;

class FinanceController extends Controller
{
    /**
     * Financial summary (Net Profit, totals) with optional date range.
     */
    public function summary(Request $request)
    {
        $query = Transaction::query();

        if ($request->has('from') && $request->has('to')) {
            $query->dateRange($request->from, $request->to);
        }

        $totalIncome   = (clone $query)->income()->sum('amount');
        $totalPayouts  = (clone $query)->payout()->sum('amount');
        $totalExpenses = (clone $query)->expense()->sum('amount');

        // Add manual expenses
        $manualExpenses = Expense::query();
        if ($request->has('from') && $request->has('to')) {
            $manualExpenses->whereBetween('date', [$request->from, $request->to]);
        }
        $totalManualExpenses = $manualExpenses->sum('amount');

        $grandExpenses = $totalExpenses + $totalManualExpenses;
        $netProfit     = $totalIncome - $totalPayouts - $grandExpenses;

        return response()->json([
            'data' => [
                'total_income'          => round($totalIncome, 2),
                'total_payouts'         => round($totalPayouts, 2),
                'total_auto_expenses'   => round($totalExpenses, 2),
                'total_manual_expenses' => round($totalManualExpenses, 2),
                'total_expenses'        => round($grandExpenses, 2),
                'net_profit'            => round($netProfit, 2),
            ],
        ]);
    }

    /**
     * List transactions with filters.
     */
    public function transactions(Request $request)
    {
        $query = Transaction::query();

        if ($request->has('type')) {
            $query->where('type', $request->type);
        }
        if ($request->has('category')) {
            $query->where('category', $request->category);
        }
        if ($request->has('from') && $request->has('to')) {
            $query->dateRange($request->from, $request->to);
        }
        if ($request->has('date')) {
            $query->whereDate('date', $request->date);
        }

        return response()->json([
            'data' => $query->orderBy('date', 'desc')->orderBy('id', 'desc')->get(),
        ]);
    }

    /**
     * Create a manual transaction.
     */
    public function storeTransaction(Request $request)
    {
        $data = $request->validate([
            'type'        => 'required|in:income,payout,expense',
            'category'    => 'required|string|max:100',
            'amount'      => 'required|numeric|min:0.01',
            'description' => 'nullable|string|max:1000',
            'date'        => 'required|date',
        ]);

        $transaction = Transaction::create($data);

        return response()->json(['data' => $transaction], 201);
    }

    /**
     * List expenses.
     */
    public function expenses(Request $request)
    {
        $query = Expense::query();

        if ($request->has('category')) {
            $query->where('category', $request->category);
        }
        if ($request->has('from') && $request->has('to')) {
            $query->whereBetween('date', [$request->from, $request->to]);
        }
        if ($request->has('date')) {
            $query->whereDate('date', $request->date);
        }

        return response()->json([
            'data' => $query->orderBy('date', 'desc')->get(),
        ]);
    }

    /**
     * Add a manual expense + auto-create a transaction record.
     */
    public function storeExpense(Request $request)
    {
        $data = $request->validate([
            'category'    => 'required|in:medical_supplies,operational,misc',
            'description' => 'required|string|max:1000',
            'amount'      => 'required|numeric|min:0.01',
            'date'        => 'required|date',
        ]);

        $expense = Expense::create($data);

        // Mirror as a transaction
        Transaction::create([
            'type'           => 'expense',
            'category'       => $data['category'],
            'amount'         => $data['amount'],
            'description'    => $data['description'],
            'reference_id'   => $expense->id,
            'reference_type' => Expense::class,
            'date'           => $data['date'],
        ]);

        return response()->json(['data' => $expense], 201);
    }

    /**
     * Update an expense.
     */
    public function updateExpense(Request $request, Expense $expense)
    {
        $data = $request->validate([
            'category'    => 'required|in:medical_supplies,operational,misc',
            'description' => 'required|string|max:1000',
            'amount'      => 'required|numeric|min:0.01',
            'date'        => 'required|date',
        ]);

        $expense->update($data);

        // Update the mirrored transaction
        Transaction::where('reference_id', $expense->id)
            ->where('reference_type', Expense::class)
            ->update([
                'category'    => $data['category'],
                'amount'      => $data['amount'],
                'description' => $data['description'],
                'date'        => $data['date'],
            ]);

        return response()->json(['data' => $expense]);
    }

    /**
     * Delete an expense.
     */
    public function destroyExpense(Expense $expense)
    {
        Transaction::where('reference_id', $expense->id)
            ->where('reference_type', Expense::class)
            ->delete();

        $expense->delete();

        return response()->json(['message' => 'Expense deleted successfully.']);
    }

    /**
     * List doctor payouts.
     */
    public function doctorPayouts(Request $request)
    {
        $query = DoctorPayout::with(['doctor:id,name,type', 'patient:id,name']);

        if ($request->has('doctor_id')) {
            $query->where('doctor_id', $request->doctor_id);
        }
        if ($request->has('is_paid')) {
            $query->where('is_paid', $request->boolean('is_paid'));
        }
        if ($request->has('doctor_type')) {
            $query->where('doctor_type', $request->doctor_type);
        }
        if ($request->has('from') && $request->has('to')) {
            $query->whereBetween('date', [$request->from, $request->to]);
        }

        return response()->json([
            'data' => $query->orderBy('date', 'desc')->get(),
        ]);
    }

    /**
     * Mark payouts as paid.
     */
    public function markPayoutsPaid(Request $request)
    {
        $request->validate([
            'ids'   => 'required|array|min:1',
            'ids.*' => 'exists:doctor_payouts,id',
        ]);

        DoctorPayout::whereIn('id', $request->ids)->update([
            'is_paid' => true,
            'paid_at' => now(),
        ]);

        return response()->json(['message' => 'Payouts marked as paid.']);
    }

    /**
     * Full financial report with breakdowns.
     */
    public function report(Request $request)
    {
        $from = $request->get('from', now()->startOfMonth()->toDateString());
        $to   = $request->get('to', now()->toDateString());

        $transactions = Transaction::dateRange($from, $to)->get();

        // Income breakdown by category
        $incomeBreakdown = $transactions->where('type', 'income')
            ->groupBy('category')
            ->map(fn($items) => round($items->sum('amount'), 2));

        // Expense breakdown by category
        $expenseBreakdown = $transactions->where('type', 'expense')
            ->groupBy('category')
            ->map(fn($items) => round($items->sum('amount'), 2));

        // Payout breakdown
        $payoutBreakdown = $transactions->where('type', 'payout')
            ->groupBy('category')
            ->map(fn($items) => round($items->sum('amount'), 2));

        // Manual expenses
        $manualExpenses = Expense::whereBetween('date', [$from, $to])->get();
        $manualBreakdown = $manualExpenses->groupBy('category')
            ->map(fn($items) => round($items->sum('amount'), 2));

        $totalIncome   = $transactions->where('type', 'income')->sum('amount');
        $totalPayouts  = $transactions->where('type', 'payout')->sum('amount');
        $totalExpenses = $transactions->where('type', 'expense')->sum('amount') + $manualExpenses->sum('amount');
        $netProfit     = $totalIncome - $totalPayouts - $totalExpenses;

        // Doctor payout summary
        $doctorPayouts = DoctorPayout::with('doctor:id,name,type')
            ->whereBetween('date', [$from, $to])
            ->get()
            ->groupBy('doctor_id')
            ->map(function ($payouts) {
                $doctor = $payouts->first()->doctor;
                return [
                    'doctor_name' => $doctor->name ?? 'Unknown',
                    'doctor_type' => $doctor->type ?? 'Unknown',
                    'total'       => round($payouts->sum('amount'), 2),
                    'paid'        => round($payouts->where('is_paid', true)->sum('amount'), 2),
                    'unpaid'      => round($payouts->where('is_paid', false)->sum('amount'), 2),
                ];
            })
            ->values();

        return response()->json([
            'data' => [
                'period'            => ['from' => $from, 'to' => $to],
                'total_income'      => round($totalIncome, 2),
                'total_payouts'     => round($totalPayouts, 2),
                'total_expenses'    => round($totalExpenses, 2),
                'net_profit'        => round($netProfit, 2),
                'income_breakdown'  => (object) $incomeBreakdown->toArray(),
                'expense_breakdown' => (object) $expenseBreakdown->toArray(),
                'payout_breakdown'  => (object) $payoutBreakdown->toArray(),
                'manual_expenses'   => (object) $manualBreakdown->toArray(),
                'doctor_payouts'    => $doctorPayouts->toArray(),
            ],
        ]);
    }
}

