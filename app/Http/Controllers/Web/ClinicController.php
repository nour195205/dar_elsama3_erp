<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\TestType;
use App\Models\Delegate;
use App\Support\ActivityLogger;
use App\Services\PatientService;
use App\Http\Requests\StoreDoctorRequest;
use App\Http\Requests\UpdateDoctorRequest;
use App\Http\Requests\StorePatientRequest;
use App\Http\Requests\UpdatePatientRequest;
use App\Http\Requests\StoreTestTypeRequest;
use App\Http\Requests\UpdateTestTypeRequest;
use App\Http\Requests\StoreDelegateRequest;
use App\Http\Requests\UpdateDelegateRequest;
use Illuminate\Support\Facades\DB;

class ClinicController extends Controller
{
    // ===================== DOCTORS =====================

    public function doctorsIndex()
    {
        $doctors = Doctor::paginate(15);
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

    public function doctorsStore(StoreDoctorRequest $request)
    {
        $doctor = Doctor::create([
            'name' => $request->name,
            'type' => $request->type,
            'address' => $request->address,
            'commission_type' => $request->commission_type,
            'commission_value' => $request->commission_value,
        ]);
        ActivityLogger::forModel('doctor.created', 'تمت إضافة طبيب: ' . $doctor->name, $doctor);

        return redirect()->route('doctors.index')->with('success', 'تم إضافة الطبيب بنجاح');
    }

    public function doctorsEdit($id)
    {
        $doctor = Doctor::findOrFail($id);
        return view('web.doctors.edit', [
            'title' => 'تعديل بيانات الطبيب',
            'subtitle' => 'تحديث بيانات ' . $doctor->name,
            'doctor' => $doctor
        ]);
    }

    public function doctorsUpdate(UpdateDoctorRequest $request, $id)
    {
        $doctor = Doctor::findOrFail($id);
        $doctor->update([
            'name' => $request->name,
            'type' => $request->type,
            'address' => $request->address,
            'commission_type' => $request->commission_type,
            'commission_value' => $request->commission_value,
        ]);
        ActivityLogger::forModel('doctor.updated', 'تم تحديث بيانات طبيب: ' . $doctor->name, $doctor);

        return redirect()->route('doctors.index')->with('success', 'تم تحديث بيانات الطبيب');
    }

    public function doctorsDestroy($id)
    {
        $doctor = Doctor::findOrFail($id);
        $label = $doctor->name;
        $doctor->delete();
        ActivityLogger::record('doctor.deleted', 'تم حذف طبيب: ' . $label, Doctor::class, (int) $id);

        return redirect()->route('doctors.index')->with('success', 'تم حذف الطبيب');
    }

    // ===================== PATIENTS =====================

    public function patientsIndex()
    {
        $patients = Patient::orderBy('created_at', 'desc')->paginate(15);
        return view('web.patients.index', [
            'title' => 'سجلات المرضى',
            'subtitle' => 'قاعدة بيانات المرضى والتاريخ الطبي',
            'patients' => $patients
        ]);
    }

    public function patientsCreate()
    {
        $internalDoctors = Doctor::where('type', 'Internal')->get();
        $externalDoctors = Doctor::where('type', 'External')->get();
        $testTypes = TestType::all();

        return view('web.patients.create', [
            'title' => 'تسجيل مريض جديد',
            'subtitle' => 'قم بإدخال بيانات المريض الشخصية والتفاصيل الطبية',
            'internalDoctors' => $internalDoctors,
            'externalDoctors' => $externalDoctors,
            'testTypes' => $testTypes
        ]);
    }

    public function patientsStore(StorePatientRequest $request)
    {
        $service = new PatientService();

        $patient = $service->createPatient($request->validated() + [
            'supplies_cost' => $request->input('supplies_cost', 0),
        ]);

        ActivityLogger::forModel('patient.created', 'تم تسجيل مريض: ' . $patient->name, $patient, [
            'phone' => $patient->phone,
            'test_price' => (string) $patient->test_price,
        ]);

        return redirect()->route('patients.index')->with('success', 'تم تسجيل المريض بنجاح');
    }

    public function patientsEdit($id)
    {
        $patient = Patient::findOrFail($id);
        $internalDoctors = Doctor::where('type', 'Internal')->get();
        $externalDoctors = Doctor::where('type', 'External')->get();
        $testTypes = TestType::all();

        return view('web.patients.edit', [
            'title' => 'تعديل بيانات المريض',
            'subtitle' => 'تحديث بيانات ' . $patient->name,
            'patient' => $patient,
            'internalDoctors' => $internalDoctors,
            'externalDoctors' => $externalDoctors,
            'testTypes' => $testTypes
        ]);
    }

    public function patientsUpdate(UpdatePatientRequest $request, $id)
    {
        $patient = Patient::findOrFail($id);
        $service = new PatientService();

        $patient = $service->updatePatient($patient, $request->validated());

        ActivityLogger::forModel('patient.updated', 'تم تحديث بيانات مريض: ' . $patient->name, $patient);

        return redirect()->route('patients.index')->with('success', 'تم تحديث بيانات المريض');
    }

    public function patientsDestroy($id)
    {
        $patient = Patient::findOrFail($id);
        $label = $patient->name;
        $pid = $patient->id;

        $service = new PatientService();
        $service->deletePatient($patient);

        ActivityLogger::record('patient.deleted', 'تم حذف مريض وسجلاته: ' . $label, Patient::class, $pid);

        return redirect()->route('patients.index')->with('success', 'تم حذف المريض وسجلاته المالية');
    }

    // ===================== TEST TYPES =====================

    public function testTypesIndex()
    {
        $testTypes = TestType::paginate(15);
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

    public function testTypesStore(StoreTestTypeRequest $request)
    {
        $testType = TestType::create([
            'name' => $request->name,
            'price' => $request->price,
        ]);
        ActivityLogger::forModel('test_type.created', 'تمت إضافة نوع فحص: ' . $testType->name, $testType);

        return redirect()->route('test-types.index')->with('success', 'تم إضافة الفحص بنجاح');
    }

    public function testTypesEdit($id)
    {
        $testType = TestType::findOrFail($id);
        return view('web.test_types.edit', [
            'title' => 'تعديل الفحص',
            'subtitle' => 'تحديث بيانات ' . $testType->name,
            'testType' => $testType
        ]);
    }

    public function testTypesUpdate(UpdateTestTypeRequest $request, $id)
    {
        $testType = TestType::findOrFail($id);
        $testType->update([
            'name' => $request->name,
            'price' => $request->price,
        ]);
        ActivityLogger::forModel('test_type.updated', 'تم تحديث نوع فحص: ' . $testType->name, $testType);

        return redirect()->route('test-types.index')->with('success', 'تم تحديث الفحص');
    }

    public function testTypesDestroy($id)
    {
        $testType = TestType::findOrFail($id);
        $label = $testType->name;
        $tid = $testType->id;
        $testType->delete();
        ActivityLogger::record('test_type.deleted', 'تم حذف نوع فحص: ' . $label, TestType::class, $tid);

        return redirect()->route('test-types.index')->with('success', 'تم حذف الفحص');
    }

    // ===================== DELEGATES =====================

    public function delegatesIndex()
    {
        $delegates = Delegate::paginate(15);
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

    public function delegatesStore(StoreDelegateRequest $request)
    {
        $delegate = Delegate::create([
            'name'    => $request->name,
            'company' => $request->company ?? null,
            'region'  => $request->region ?? null,
            'phone'   => $request->phone ?? null,
            'notes'   => $request->notes ?? null,
        ]);
        ActivityLogger::forModel('delegate.created', 'تمت إضافة مندوب: ' . $delegate->name, $delegate);

        return redirect()->route('delegates.index')->with('success', 'تم إضافة المندوب بنجاح');
    }

    public function delegatesDestroy($id)
    {
        $delegate = Delegate::findOrFail($id);
        $label = $delegate->name;
        $did = $delegate->id;
        $delegate->delete();
        ActivityLogger::record('delegate.deleted', 'تم حذف مندوب: ' . $label, Delegate::class, $did);

        return redirect()->route('delegates.index')->with('success', 'تم حذف المندوب');
    }
}
