<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'device_id' => 'nullable|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        // Admins bypass device check — they can login from any device (desktop/laptop)
        if ($user->role !== 'admin') {
            if (!$user->device_id) {
                $user->device_id = $request->device_id;
                $user->save();
            } else if ($request->device_id && $user->device_id !== $request->device_id) {
                return response()->json([
                    'message' => 'Device mismatch. You cannot login from this device.'
                ], 403);
            }
        }

        return response()->json([
            'token' => $user->createToken('mobile_app')->plainTextToken,
            'user'  => $user
        ]);
    }

    public function hasAdmins()
    {
        return response()->json(['hasAdmins' => User::where('role', 'admin')->exists()]);
    }

    public function generateAuthQr()
    {
        $token = Str::random(40);
        // Store as 'pending' for up to 60 seconds
        Cache::put('admin_auth_' . $token, ['status' => 'pending'], 60);
        
        return response()->json(['token' => $token]);
    }

    public function authorizeAuthQr(Request $request)
    {
        $request->validate([
            'qr_token' => 'required|string'
        ]);

        $user = $request->user();

        if ($user->role !== 'admin') {
            return response()->json(['message' => 'Only admins can authorize this session.'], 403);
        }

        $sessionData = Cache::get('admin_auth_' . $request->qr_token);

        if (!$sessionData) {
            return response()->json(['message' => 'Expired or invalid QR code.'], 400);
        }

        // Generate a new API token for the desktop session
        $desktopToken = $user->createToken('desktop_admin_session')->plainTextToken;

        // Automatically log them in as "Check_in" if they don't have an open session today
        $today = now()->toDateString();
        $currentTime = now()->toTimeString();
        $openAttendance = \App\Models\Attendance::where('user_id', $user->id)
            ->where('date', $today)
            ->whereNull('check_out')
            ->first();

        if (!$openAttendance) {
            \App\Models\Attendance::create([
                'user_id' => $user->id,
                'date' => $today,
                'check_in' => $currentTime,
                'status' => 'present'
            ]);
        }

        // Mark the session as authorized and store the API token
        \Illuminate\Support\Facades\Cache::put('admin_auth_' . $request->qr_token, [
            'status' => 'authorized',
            'api_token' => $desktopToken,
            'user' => $user
        ], 60);

        return response()->json(['message' => 'Desktop authorized successfully!']);
    }

    public function pollAuthQr($token)
    {
        $sessionData = Cache::get('admin_auth_' . $token);

        if (!$sessionData) {
            return response()->json(['status' => 'expired'], 400);
        }

        if ($sessionData['status'] === 'authorized') {
            // Clear the cache to prevent replay
            Cache::forget('admin_auth_' . $token);
            return response()->json([
                'status' => 'authorized',
                'token' => $sessionData['api_token'],
                'user' => $sessionData['user']
            ]);
        }

        return response()->json(['status' => 'pending']);
    }
}
