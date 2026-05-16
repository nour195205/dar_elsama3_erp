<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

class AttendanceController extends Controller
{
    public function index()
    {
        $records = Attendance::with('user:id,name,hourly_rate')
            ->orderBy('date', 'desc')
            ->get();
            
        // Calculate work hours and salary dynamically
        $records = $records->map(function ($record) {
            $checkIn = \Carbon\Carbon::parse($record->check_in);
            $checkOut = $record->check_out ? \Carbon\Carbon::parse($record->check_out) : null;
            
            $hours = 0;
            $salary = 0;
            
            if ($checkOut) {
                $hours = $checkIn->diffInSeconds($checkOut) / 3600; // in hours
                $salary = $hours * ($record->user->hourly_rate ?? 0);
            }
            
            return [
                'id' => $record->id,
                'employee_name' => $record->user->name,
                'date' => $record->date,
                'check_in' => $record->check_in,
                'check_out' => $record->check_out,
                'status' => $record->status,
                'work_hours' => round($hours, 2),
                'earned_salary' => round($salary, 2)
            ];
        });
        
        return response()->json(['reports' => $records]);
    }

    public function attend(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'qr_content' => 'required|string',
            'device_id' => 'required|string',
        ]);

        $user = User::find($request->user_id);
        
        if (!$user) {
            return response()->json(['message' => 'User not found.'], 404);
        }

        // Verify Device Binding
        if (!$user->device_id || $user->device_id !== $request->device_id) {
            return response()->json(['message' => 'Device mismatch. This phone is not bound to this account.'], 403);
        }

        // Process Token Suffix (_in or _out)
        $rawToken = clone str($request->qr_content);
        $type = 'unknown';

        if (str_ends_with($request->qr_content, '_in')) {
            $type = 'in';
            $baseToken = str_replace('_in', '', $request->qr_content);
        } elseif (str_ends_with($request->qr_content, '_out')) {
            $type = 'out';
            $baseToken = str_replace('_out', '', $request->qr_content);
        } else {
            return response()->json(['message' => 'Invalid QR type format.'], 400);
        }

        // Verify the Dynamic QR Token against Cache
        $validToken = Cache::get('current_qr_token');
        if (!$validToken || $validToken !== $baseToken) {
            return response()->json(['message' => 'Expired or invalid QR code.'], 400);
        }

        $today = now()->toDateString();
        $currentTime = now()->toTimeString();

        // Find an open session today (checked in but not checked out)
        $openAttendance = Attendance::where('user_id', $user->id)
            ->where('date', $today)
            ->whereNull('check_out')
            ->first();

        if ($type === 'in') {
            if ($openAttendance) {
                return response()->json(['message' => 'لديك جلسة عمل مفتوحة، يرجى تسجيل الانصراف أولاً.'], 400);
            }

            Attendance::create([
                'user_id' => $user->id,
                'date' => $today,
                'check_in' => $currentTime,
                'status' => 'present'
            ]);
            return response()->json(['message' => 'تم تسجيل حضورك بنجاح.', 'status' => 'success']);
        } 
        
        if ($type === 'out') {
            if (!$openAttendance) {
                return response()->json(['message' => 'يجب تسجيل الحضور أولاً قبل الانصراف.'], 400);
            }

            $openAttendance->update([
                'check_out' => $currentTime
            ]);
            return response()->json(['message' => 'تم تسجيل انصرافك بنجاح.', 'status' => 'success']);
        }

        return response()->json(['message' => 'You have already checked in and out today.'], 400);
    }

    public function pairDevice(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'device_id' => 'required|string',
        ]);

        $user = User::find($request->user_id);

        // Admin can always override - no restriction on existing binding
        $user->update(['device_id' => $request->device_id]);

        return response()->json(['message' => 'تم ربط الجهاز بنجاح!']);
    }

    public function unpairDevice(User $user)
    {
        $user->update(['device_id' => null]);

        return response()->json(['message' => 'تم فك ربط الجهاز بنجاح!']);
    }

    public function update(Request $request, Attendance $attendance)
    {
        $request->validate([
            'check_in' => 'required|date_format:H:i:s',
            'check_out' => 'nullable|date_format:H:i:s|after:check_in',
        ]);

        $attendance->update([
            'check_in' => $request->check_in,
            'check_out' => $request->check_out,
        ]);

        return response()->json(['message' => 'تم تعديل السجل بنجاح.', 'attendance' => $attendance]);
    }

    public function destroy(Attendance $attendance)
    {
        $attendance->delete();
        return response()->json(['message' => 'تم حذف السجل بنجاح.']);
    }
}
