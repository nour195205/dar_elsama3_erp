<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FinanceController extends Controller
{
    public function index()
    {
        $transactions = DB::table('transactions')->orderBy('date', 'desc')->take(50)->get();
        $summary = [
            'total_income' => DB::table('transactions')->where('type', 'income')->sum('amount'),
            'total_expenses' => DB::table('transactions')->where('type', 'expense')->sum('amount'),
            'total_payouts' => DB::table('transactions')->where('type', 'payout')->sum('amount'),
        ];

        return view('web.finance.index', [
            'title' => 'الإدارة المالية',
            'subtitle' => 'تتبع الإيرادات، المصروفات، ومستحقات الأطباء',
            'transactions' => $transactions,
            'summary' => $summary
        ]);
    }

    public function reports(Request $request)
    {
        $from = $request->get('from', now()->startOfMonth()->toDateString());
        $to = $request->get('to', now()->toDateString());

        $transactions = DB::table('transactions')
            ->whereBetween('date', [$from, $to])->get();

        $totalIncome = $transactions->where('type', 'income')->sum('amount');
        $totalPayouts = $transactions->where('type', 'payout')->sum('amount');
        $totalExpenses = $transactions->where('type', 'expense')->sum('amount');

        $incomeBreakdown = $transactions->where('type', 'income')
            ->groupBy('category')
            ->map(fn($items) => round($items->sum('amount'), 2));

        $expenseBreakdown = $transactions->where('type', 'expense')
            ->groupBy('category')
            ->map(fn($items) => round($items->sum('amount'), 2));

        $doctorPayouts = DB::table('doctor_payouts')
            ->join('doctors', 'doctor_payouts.doctor_id', '=', 'doctors.id')
            ->whereBetween('doctor_payouts.date', [$from, $to])
            ->select('doctor_payouts.*', 'doctors.name as doctor_name', 'doctors.type as doctor_type_val')
            ->get()
            ->groupBy('doctor_id')
            ->map(function ($payouts) {
                $first = $payouts->first();
                return [
                    'doctor_name' => $first->doctor_name,
                    'doctor_type' => $first->doctor_type_val ?? $first->doctor_type,
                    'total' => round($payouts->sum('amount'), 2),
                    'paid' => round($payouts->where('is_paid', true)->sum('amount'), 2),
                    'unpaid' => round($payouts->where('is_paid', false)->sum('amount'), 2),
                ];
            })->values();

        $report = [
            'total_income' => round($totalIncome, 2),
            'total_payouts' => round($totalPayouts, 2),
            'total_expenses' => round($totalExpenses, 2),
            'net_profit' => round($totalIncome - $totalPayouts - $totalExpenses, 2),
            'income_breakdown' => $incomeBreakdown,
            'expense_breakdown' => $expenseBreakdown,
            'doctor_payouts' => $doctorPayouts,
        ];

        return view('web.finance.reports', [
            'title' => 'التقارير المالية',
            'subtitle' => "تقرير مفصل من $from إلى $to",
            'report' => $report,
            'from' => $from,
            'to' => $to
        ]);
    }

    public function storeTransaction(Request $request)
    {
        $request->validate([
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'type' => 'required|in:income,expense',
        ]);

        DB::table('transactions')->insert([
            'description' => $request->description,
            'amount' => $request->amount,
            'type' => $request->type,
            'category' => $request->type == 'income' ? 'manual_income' : 'manual_expense',
            'date' => $request->date ?? now()->toDateString(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        return redirect()->route('finance.index')->with('success', 'تم إضافة المعاملة بنجاح');
    }

    public function doctorPayouts(Request $request)
    {
        $query = DB::table('doctor_payouts')
            ->join('doctors', 'doctor_payouts.doctor_id', '=', 'doctors.id')
            ->leftJoin('patients', 'doctor_payouts.patient_id', '=', 'patients.id')
            ->select('doctor_payouts.*', 'doctors.name as doctor_name', 'patients.name as patient_name');
            
        $filter = $request->get('filter', 'all');
        if ($filter == 'paid') {
            $query->where('doctor_payouts.is_paid', true);
        } elseif ($filter == 'unpaid') {
            $query->where('doctor_payouts.is_paid', false);
        }

        $payouts = $query->orderBy('doctor_payouts.date', 'desc')->get();

        return view('web.finance.doctor_payouts', [
            'title' => 'مستحقات الأطباء',
            'subtitle' => 'حساب وتأكيد دفع عمولات الأطباء',
            'payouts' => $payouts,
            'filter' => $filter
        ]);
    }

    public function markPayoutsPaid(Request $request)
    {
        if ($request->has('payout_ids') && is_array($request->payout_ids)) {
            DB::table('doctor_payouts')
                ->whereIn('id', $request->payout_ids)
                ->update([
                    'is_paid' => true,
                    'paid_at' => now()
                ]);
        }
        return redirect()->route('finance.doctor_payouts')->with('success', 'تم تأكيد دفع العمولات المحددة');
    }
}
