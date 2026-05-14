<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Support\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class SettingsController extends Controller
{
    public function index()
    {
        $settings = [];
        $rows = DB::table('settings')->get();
        foreach ($rows as $row) {
            $settings[$row->key] = $row->value;
        }

        return view('web.settings.index', [
            'title' => 'إعدادات النظام',
            'subtitle' => 'تخصيص النظام، الشبكة، وإعدادات العيادة',
            'settings' => $settings
        ]);
    }

    public function updateClinic(Request $request)
    {
        $fields = ['clinic_name', 'clinic_phone', 'clinic_address'];
        foreach ($fields as $field) {
            DB::table('settings')->updateOrInsert(
                ['key' => $field],
                ['value' => $request->input($field, ''), 'updated_at' => now()]
            );
        }
        ActivityLogger::record('settings.clinic_updated', 'تم تحديث بيانات العيادة من الإعدادات');

        return redirect()->route('settings.index')->with('success', 'تم حفظ بيانات العيادة');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:4|confirmed',
        ]);

        $user = Auth::user();
        if (!$user || !Hash::check($request->current_password, $user->password)) {
            return back()->with('error', 'كلمة المرور الحالية غير صحيحة');
        }

        DB::table('users')->where('id', $user->id)->update([
            'password' => Hash::make($request->new_password),
            'updated_at' => now(),
        ]);

        ActivityLogger::record('settings.password_changed', 'تم تغيير كلمة مرور الحساب: ' . $user->email);

        return redirect()->route('settings.index')->with('success', 'تم تحديث كلمة المرور بنجاح');
    }

    public function loginForm()
    {
        return view('web.auth.login', [
            'title' => 'تسجيل الدخول',
            'subtitle' => 'سجل دخولك للوصول للوحة الإدارة'
        ]);
    }

    public function loginSubmit(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            ActivityLogger::record('auth.login', 'تسجيل دخول: ' . (Auth::user()->email ?? ''));
            return redirect()->intended(route('dashboard'))->with('success', 'تم تسجيل الدخول بنجاح');
        }

        return back()->with('error', 'بيانات الدخول غير صحيحة');
    }

    public function logout(Request $request)
    {
        if (Auth::check()) {
            ActivityLogger::record('auth.logout', 'تسجيل خروج: ' . (Auth::user()->email ?? ''));
        }
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('dashboard')->with('success', 'تم تسجيل الخروج');
    }
}
