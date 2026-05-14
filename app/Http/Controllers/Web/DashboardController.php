<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'doctors' => DB::table('doctors')->count(),
            'patients' => DB::table('patients')->count(),
            'test_types' => DB::table('test_types')->count(),
            'income' => DB::table('transactions')->where('type', 'income')->sum('amount'),
        ];

        $recentPatients = DB::table('patients')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        $recentTransactions = DB::table('transactions')
            ->orderBy('date', 'desc')
            ->take(5)
            ->get();

        return view('web.dashboard', compact('stats', 'recentPatients', 'recentTransactions'));
    }
}
