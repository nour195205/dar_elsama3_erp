<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AttendanceController extends Controller
{
    public function index()
    {
        $attendance = DB::table('attendances')
            ->join('users', 'attendances.user_id', '=', 'users.id')
            ->where('users.is_active', true)
            ->select('attendances.*', 'users.name as employee_name')
            ->orderBy('attendances.date', 'desc')
            ->take(50)
            ->get();
            
        return view('web.attendance.index', [
            'title' => 'سجلات الحضور والانصراف',
            'subtitle' => 'متابعة أوقات حضور الموظفين والغياب اليومي',
            'attendance' => $attendance
        ]);
    }

    public function scanner()
    {
        $activeEmployees = DB::table('users')
            ->where('is_active', true)
            ->where('role', '!=', 'admin')
            ->orderBy('name')
            ->select('id', 'name', 'device_id')
            ->get();
            
        return view('web.attendance.scanner', [
            'title' => 'ماسح الحضور والربط',
            'subtitle' => 'شاشة الحضور والانصراف الذكية وربط الأجهزة الجديدة',
            'employees' => $activeEmployees
        ]);
    }
}

