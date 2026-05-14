<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ClinicController extends Controller
{
    // ===================== DOCTORS =====================

    public function doctorsIndex()
    {
        $doctors = DB::table('doctors')->get();
        return view('web.doctors.index', [
            'title' => 'إدارة الأطباء',
            'subtitle' => 'قائمة بالأطباء الداخليين والمحولين للمركز',
            'doctors' => $doctors
        ]);
    }

    public function doctorsCreate()
    {
        return view('web.doctors.create', [
            'title' => 'إضافة طبيب جديد',
            'subtitle' => 'قم بإدخال بيانات الطبيب الجديد ونسبة العمولة'
        ]);
    }

    public function doctorsStore(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:Internal,External',
            'commission_value' => 'nullable|numeric|min:0',
        ]);

        DB::table('doctors')->insert([
            'name' => $request->name,
            'type' => $request->type,
            'address' => $request->address,
            'commission_type' => $request->commission_type ?? 'Percentage',
            'commission_value' => $request->commission_value ?? 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        return redirect()->route('doctors.index')->with('success', 'تم إضافة الطبيب بنجاح');
    }

    public function doctorsEdit($id)
    {
        $doctor = DB::table('doctors')->find($id);
        abort_unless($doctor, 404);
        return view('web.doctors.edit', [
            'title' => 'تعديل بيانات الطبيب',
            'subtitle' => 'تحديث بيانات ' . $doctor->name,
            'doctor' => $doctor
        ]);
    }

    public function doctorsUpdate(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:Internal,External',
            'commission_value' => 'nullable|numeric|min:0',
        ]);

        DB::table('doctors')->where('id', $id)->update([
            'name' => $request->name,
            'type' => $request->type,
            'address' => $request->address,
            'commission_type' => $request->commission_type ?? 'Percentage',
            'commission_value' => $request->commission_value ?? 0,
            'updated_at' => now(),
        ]);
        return redirect()->route('doctors.index')->with('success', 'تم تحديث بيانات الطبيب');
    }

    public function doctorsDestroy($id)
    {
        DB::table('doctors')->where('id', $id)->delete();
        return redirect()->route('doctors.index')->with('success', 'تم حذف الطبيب');
    }

    // ===================== PATIENTS =====================

    public function patientsIndex()
    {
        $patients = DB::table('patients')->orderBy('created_at', 'desc')->get();
        return view('web.patients.index', [
            'title' => 'سجلات المرضى',
            'subtitle' => 'قاعدة بيانات المرضى والتاريخ الطبي',
            'patients' => $patients
        ]);
    }

    public function patientsCreate()
    {
        $internalDoctors = DB::table('doctors')->where('type', 'Internal')->get();
        $externalDoctors = DB::table('doctors')->where('type', 'External')->get();
        $testTypes = DB::table('test_types')->get();

        return view('web.patients.create', [
            'title' => 'تسجيل مريض جديد',
            'subtitle' => 'قم بإدخال بيانات المريض الشخصية والتفاصيل الطبية',
            'internalDoctors' => $internalDoctors,
            'externalDoctors' => $externalDoctors,
            'testTypes' => $testTypes
        ]);
    }

    public function patientsStore(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string',
            'age' => 'required|integer|min:0',
            'test_price' => 'nullable|numeric|min:0',
        ]);

        DB::transaction(function () use ($request) {
            $internalDoctor = $request->internal_doctor_id ? DB::table('doctors')->find($request->internal_doctor_id) : null;
            $externalDoctor = $request->referring_doctor_id ? DB::table('doctors')->find($request->referring_doctor_id) : null;

            $testPrice = $request->test_price ?? 0;

            $commInternal = 0;
            if ($internalDoctor) {
                $commInternal = $internalDoctor->commission_type == 'Percentage'
                    ? ($testPrice * ($internalDoctor->commission_value / 100))
                    : $internalDoctor->commission_value;
            }

            $commExternal = 0;
            if ($externalDoctor) {
                $commExternal = $externalDoctor->commission_type == 'Percentage'
                    ? ($testPrice * ($externalDoctor->commission_value / 100))
                    : $externalDoctor->commission_value;
            }

            $patientId = DB::table('patients')->insertGetId([
                'name' => $request->name,
                'phone' => $request->phone,
                'age' => $request->age,
                'address' => $request->address,
                'visit_type' => $request->visit_type,
                'date' => $request->date ?? now()->toDateString(),
                'referring_doctor_id' => $request->referring_doctor_id,
                'internal_doctor_id' => $request->internal_doctor_id,
                'test_type_id' => $request->test_type_id,
                'test_price' => $testPrice,
                'commission_internal' => $commInternal,
                'commission_external' => $commExternal,
                'supplies_cost' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            if ($testPrice > 0) {
                DB::table('transactions')->insert([
                    'type' => 'income',
                    'category' => 'test_revenue',
                    'amount' => $testPrice,
                    'description' => "إيراد فحص: {$request->name}",
                    'reference_id' => $patientId,
                    'reference_type' => \App\Models\Patient::class,
                    'date' => $request->date ?? now()->toDateString(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            if ($internalDoctor && $commInternal > 0) {
                DB::table('doctor_payouts')->insert([
                    'doctor_id' => $internalDoctor->id,
                    'doctor_type' => 'internal',
                    'patient_id' => $patientId,
                    'amount' => $commInternal,
                    'calculation_basis' => $internalDoctor->commission_type,
                    'calculation_value' => $internalDoctor->commission_value,
                    'date' => $request->date ?? now()->toDateString(),
                    'is_paid' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                DB::table('transactions')->insert([
                    'type' => 'payout',
                    'category' => 'doctor_commission',
                    'amount' => $commInternal,
                    'description' => "عمولة طبيب داخلي - {$request->name}",
                    'reference_id' => $patientId,
                    'reference_type' => \App\Models\Patient::class,
                    'date' => $request->date ?? now()->toDateString(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            if ($externalDoctor && $commExternal > 0) {
                DB::table('doctor_payouts')->insert([
                    'doctor_id' => $externalDoctor->id,
                    'doctor_type' => 'external',
                    'patient_id' => $patientId,
                    'amount' => $commExternal,
                    'calculation_basis' => $externalDoctor->commission_type,
                    'calculation_value' => $externalDoctor->commission_value,
                    'date' => $request->date ?? now()->toDateString(),
                    'is_paid' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                DB::table('transactions')->insert([
                    'type' => 'payout',
                    'category' => 'doctor_commission',
                    'amount' => $commExternal,
                    'description' => "عمولة طبيب محول - {$request->name}",
                    'reference_id' => $patientId,
                    'reference_type' => \App\Models\Patient::class,
                    'date' => $request->date ?? now()->toDateString(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });

        return redirect()->route('patients.index')->with('success', 'تم تسجيل المريض بنجاح');
    }

    public function patientsEdit($id)
    {
        $patient = DB::table('patients')->find($id);
        abort_unless($patient, 404);
        $internalDoctors = DB::table('doctors')->where('type', 'Internal')->get();
        $externalDoctors = DB::table('doctors')->where('type', 'External')->get();
        $testTypes = DB::table('test_types')->get();

        return view('web.patients.edit', [
            'title' => 'تعديل بيانات المريض',
            'subtitle' => 'تحديث بيانات ' . $patient->name,
            'patient' => $patient,
            'internalDoctors' => $internalDoctors,
            'externalDoctors' => $externalDoctors,
            'testTypes' => $testTypes
        ]);
    }

    public function patientsUpdate(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string',
            'age' => 'required|integer|min:0',
        ]);

        DB::table('patients')->where('id', $id)->update([
            'name' => $request->name,
            'phone' => $request->phone,
            'age' => $request->age,
            'address' => $request->address,
            'visit_type' => $request->visit_type,
            'updated_at' => now(),
        ]);
        return redirect()->route('patients.index')->with('success', 'تم تحديث بيانات المريض');
    }

    public function patientsDestroy($id)
    {
        DB::table('transactions')
            ->where('reference_id', $id)
            ->where('reference_type', \App\Models\Patient::class)
            ->delete();
        DB::table('doctor_payouts')->where('patient_id', $id)->delete();
        DB::table('patients')->where('id', $id)->delete();
        return redirect()->route('patients.index')->with('success', 'تم حذف المريض وسجلاته المالية');
    }

    // ===================== TEST TYPES =====================

    public function testTypesIndex()
    {
        $testTypes = DB::table('test_types')->get();
        return view('web.test_types.index', [
            'title' => 'أنواع الفحوصات',
            'subtitle' => 'قائمة الفحوصات الطبية وأسعارها',
            'testTypes' => $testTypes
        ]);
    }

    public function testTypesCreate()
    {
        return view('web.test_types.create', [
            'title' => 'إضافة فحص جديد',
            'subtitle' => 'قم بإدخال بيانات الفحص وتكلفته'
        ]);
    }

    public function testTypesStore(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
        ]);

        DB::table('test_types')->insert([
            'name' => $request->name,
            'price' => $request->price,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        return redirect()->route('test-types.index')->with('success', 'تم إضافة الفحص بنجاح');
    }

    public function testTypesEdit($id)
    {
        $testType = DB::table('test_types')->find($id);
        abort_unless($testType, 404);
        return view('web.test_types.edit', [
            'title' => 'تعديل الفحص',
            'subtitle' => 'تحديث بيانات ' . $testType->name,
            'testType' => $testType
        ]);
    }

    public function testTypesUpdate(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
        ]);

        DB::table('test_types')->where('id', $id)->update([
            'name' => $request->name,
            'price' => $request->price,
            'updated_at' => now(),
        ]);
        return redirect()->route('test-types.index')->with('success', 'تم تحديث الفحص');
    }

    public function testTypesDestroy($id)
    {
        DB::table('test_types')->where('id', $id)->delete();
        return redirect()->route('test-types.index')->with('success', 'تم حذف الفحص');
    }

    // ===================== DELEGATES =====================

    public function delegatesIndex()
    {
        $delegates = DB::table('delegates')->get();
        return view('web.delegates.index', [
            'title' => 'إدارة المناديب',
            'subtitle' => 'قائمة المناديب والشركات التابعين لها',
            'delegates' => $delegates
        ]);
    }

    public function delegatesCreate()
    {
        return view('web.delegates.create', [
            'title' => 'إضافة مندوب جديد',
            'subtitle' => 'إدخال بيانات المندوب للتواصل وتتبع الزيارات'
        ]);
    }

    public function delegatesStore(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        DB::table('delegates')->insert([
            'name' => $request->name,
            'company' => $request->company,
            'phone' => $request->phone,
            'notes' => $request->notes,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        return redirect()->route('delegates.index')->with('success', 'تم إضافة المندوب بنجاح');
    }

    public function delegatesDestroy($id)
    {
        DB::table('delegates')->where('id', $id)->delete();
        return redirect()->route('delegates.index')->with('success', 'تم حذف المندوب');
    }
}
