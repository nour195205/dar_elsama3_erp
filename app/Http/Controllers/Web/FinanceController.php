<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\DoctorPayout;
use App\Support\ActivityLogger;
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

        $fromDate = \Illuminate\Support\Carbon::parse($from);
        $toDate = \Illuminate\Support\Carbon::parse($to);
        $diffInDays = $fromDate->diffInDays($toDate);

        // جلب المرضى والزيارات إذا كانت الفترة شهراً أو أقل (31 يوماً)
        $patients = [];
        if ($diffInDays <= 31) {
            $patients = DB::table('patients')
                ->leftJoin('doctors as int_doc', 'patients.internal_doctor_id', '=', 'int_doc.id')
                ->leftJoin('doctors as ext_doc', 'patients.referring_doctor_id', '=', 'ext_doc.id')
                ->leftJoin('test_types', 'patients.test_type_id', '=', 'test_types.id')
                ->whereBetween('patients.date', [$from, $to])
                ->select(
                    'patients.*',
                    'int_doc.name as internal_doctor_name',
                    'ext_doc.name as external_doctor_name',
                    'test_types.name as test_type_name'
                )
                ->orderBy('patients.date', 'asc')
                ->get();
        }

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

        // حساب التدفق النقدي اليومي للرسم البياني
        $dailyCashflow = DB::table('transactions')
            ->select('date', 
                DB::raw("SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) as income"),
                DB::raw("SUM(CASE WHEN type = 'payout' THEN amount ELSE 0 END) + SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) as expense")
            )
            ->whereBetween('date', [$from, $to])
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();

        $report = [
            'total_income' => round($totalIncome, 2),
            'total_payouts' => round($totalPayouts, 2),
            'total_expenses' => round($totalExpenses, 2),
            'net_profit' => round($totalIncome - $totalPayouts - $totalExpenses, 2),
            'income_breakdown' => $incomeBreakdown,
            'expense_breakdown' => $expenseBreakdown,
            'doctor_payouts' => $doctorPayouts,
            'daily_cashflow' => $dailyCashflow,
        ];

        return view('web.finance.reports', [
            'title' => 'التقارير المالية',
            'subtitle' => "تقرير مفصل من $from إلى $to",
            'report' => $report,
            'from' => $from,
            'to' => $to,
            'patients' => $patients,
            'diffInDays' => $diffInDays
        ]);
    }

    public function exportCsv(Request $request)
    {
        $from = $request->get('from', now()->startOfMonth()->toDateString());
        $to = $request->get('to', now()->toDateString());

        // 1. جلب المرضى والزيارات في الفترة
        $patients = DB::table('patients')
            ->leftJoin('doctors as int_doc', 'patients.internal_doctor_id', '=', 'int_doc.id')
            ->leftJoin('doctors as ext_doc', 'patients.referring_doctor_id', '=', 'ext_doc.id')
            ->leftJoin('test_types', 'patients.test_type_id', '=', 'test_types.id')
            ->whereBetween('patients.date', [$from, $to])
            ->select(
                'patients.*',
                'int_doc.name as internal_doctor_name',
                'ext_doc.name as external_doctor_name',
                'test_types.name as test_type_name'
            )
            ->orderBy('patients.date', 'asc')
            ->get();

        // 2. جلب عمولات الأطباء وتفصيل مستحقاتهم
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

        // 3. جلب إجماليات المعاملات المالية للتقرير
        $transactions = DB::table('transactions')
            ->whereBetween('date', [$from, $to])->get();

        $totalIncome = $transactions->where('type', 'income')->sum('amount');
        $totalExpenses = $transactions->where('type', 'expense')->sum('amount');
        
        $internalPayouts = $doctorPayouts->filter(fn($d) => strtolower($d['doctor_type']) === 'internal');
        $externalPayouts = $doctorPayouts->filter(fn($d) => strtolower($d['doctor_type']) === 'external');
        
        $internalPayoutsSum = $internalPayouts->sum('total');
        $externalPayoutsSum = $externalPayouts->sum('total');
        $totalDoctorPayouts = $internalPayoutsSum + $externalPayoutsSum;
        $netProfit = $totalIncome - $totalDoctorPayouts - $totalExpenses;

        $internalDoctorNames = $internalPayouts->pluck('doctor_name')->unique()->toArray();
        $externalDoctorNames = $externalPayouts->pluck('doctor_name')->unique()->toArray();

        $headers = [
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=simplified_report_{$from}_to_{$to}.csv",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function() use($from, $to, $patients, $internalPayoutsSum, $externalPayoutsSum, $internalDoctorNames, $externalDoctorNames, $totalIncome, $totalExpenses, $netProfit) {
            $file = fopen('php://output', 'w');
            // UTF-8 BOM لضمان فتح اللغة العربية بنجاح في إكسل
            fwrite($file, "\xEF\xBB\xBF");

            // --- القسم الأول: ترويسة التقرير والملخص المالي العام ---
            fputcsv($file, ['تقرير الحسابات المالية لمركز دار السماء']);
            fputcsv($file, ["من تاريخ: {$from}", "إلى تاريخ: {$to}"]);
            fputcsv($file, []); // سطر فارغ

            fputcsv($file, ['البيان المالي', 'المبلغ (ج.م)']);
            fputcsv($file, ['إجمالي الإيرادات (الكشوفات والفحوصات)', number_format($totalIncome, 2, '.', '')]);
            fputcsv($file, ['إجمالي مصروفات المستلزمات والتشغيل', number_format($totalExpenses, 2, '.', '')]);
            fputcsv($file, ['إجمالي عمولات الأطباء الداخليين (طاقم المركز)', number_format($internalPayoutsSum, 2, '.', '')]);
            fputcsv($file, ['إجمالي عمولات الأطباء الخارجيين (المحولين)', number_format($externalPayoutsSum, 2, '.', '')]);
            fputcsv($file, ['صافي الربح الفعلي للمركز', number_format($netProfit, 2, '.', '')]);
            
            fputcsv($file, []); // سطرين فارغين للفصل
            fputcsv($file, []);

            // --- القسم الثاني: كشف وحالات المرضى والزيارات في الفترة ---
            fputcsv($file, ['كشف وحالات المرضى والزيارات خلال الفترة']);
            fputcsv($file, ['اسم المريض', 'تاريخ الزيارة', 'السن', 'الموبايل', 'نوع الفحص/الأشعة', 'سعر الفحص', 'طبيب المركز (الداخلي)', 'الطبيب المحول (الخارجي)']);
            
            if (count($patients) > 0) {
                foreach ($patients as $p) {
                    fputcsv($file, [
                        $p->name,
                        $p->date,
                        $p->age,
                        $p->phone !== '0' && $p->phone !== '' ? $p->phone : '—',
                        $p->test_type_name ?? '—',
                        number_format($p->test_price, 2, '.', ''),
                        $p->internal_doctor_name ?? '—',
                        $p->external_doctor_name ?? '—'
                    ]);
                }
            } else {
                fputcsv($file, ['لا توجد حالات مسجلة في هذه الفترة']);
            }

            fputcsv($file, []); // سطرين فارغين للفصل
            fputcsv($file, []);

            // --- القسم الثالث: كشف وعمولات الأطباء الداخليين والخارجيين ومين هما ---
            fputcsv($file, ['تفصيل العمولات ومستحقات الأطباء بالتفصيل']);
            
            fputcsv($file, ['إجمالي عمولات أطباء المركز (الداخليين)', number_format($internalPayoutsSum, 2, '.', '')]);
            fputcsv($file, ['أسماء الأطباء الداخليين المستحقين', count($internalDoctorNames) > 0 ? implode('، ', $internalDoctorNames) : 'لا يوجد']);
            fputcsv($file, []); // سطر فارغ
            
            fputcsv($file, ['إجمالي عمولات الأطباء المحولين (الخارجيين)', number_format($externalPayoutsSum, 2, '.', '')]);
            fputcsv($file, ['أسماء الأطباء الخارجيين المستحقين', count($externalDoctorNames) > 0 ? implode('، ', $externalDoctorNames) : 'لا يوجد']);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
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
        ActivityLogger::record(
            'finance.transaction_created',
            'معاملة يدوية (' . ($request->type === 'income' ? 'إيراد' : 'مصروف') . '): ' . $request->description . ' — ' . $request->amount . ' ج.م'
        );

        return redirect()->route('finance.index')->with('success', 'تم إضافة المعاملة بنجاح');
    }

    public function doctorPayouts(Request $request)
    {
        $query = DoctorPayout::with('doctor', 'patient')
            ->orderBy('date', 'desc');
            
        $filter = $request->get('filter', 'all');
        if ($filter == 'paid') {
            $query->where('is_paid', true);
        } elseif ($filter == 'unpaid') {
            $query->where('is_paid', false);
        }

        $payouts = $query->get();

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
            $ids = $request->payout_ids;
            DoctorPayout::whereIn('id', $ids)
                ->update([
                    'is_paid' => true,
                    'paid_at' => now()
                ]);
            ActivityLogger::record(
                'finance.payouts_marked_paid',
                'تم تأكيد دفع عمولات أطباء (عدد: ' . count($ids) . ')',
                null,
                null,
                ['payout_ids' => array_map('intval', $ids)]
            );
        }
        return redirect()->route('finance.doctor_payouts')->with('success', 'تم تأكيد دفع العمولات المحددة');
    }

    public function markPayoutIndividual(Request $request, $id)
    {
        $payout = DoctorPayout::findOrFail($id);
        $payout->update([
            'is_paid' => true,
            'paid_at' => now()
        ]);
        ActivityLogger::forModel(
            'finance.payout_marked_paid',
            'تم تأكيد دفع عمولة طبيب للسجل #' . $payout->id,
            $payout
        );

        return redirect()->route('finance.doctor_payouts')->with('success', 'تم تأكيد دفع العمولة');
    }
}
