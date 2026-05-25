<?php

namespace App\Services;

use App\Models\Doctor;
use App\Models\DoctorPayout;
use App\Models\Patient;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

/**
 * Service Layer موحد لعمليات المرضى.
 * يُستخدم من كلا الـ Web ClinicController و Api PatientController
 * لضمان اتساق البيانات المالية.
 */
class PatientService
{
    /**
     * تسجيل مريض جديد مع حساب العمولات وإنشاء السجلات المالية تلقائياً.
     *
     * @param array $data بيانات المريض الأساسية
     * @return Patient
     */
    public function createPatient(array $data): Patient
    {
        return DB::transaction(function () use ($data) {
            $internalDoctor = !empty($data['internal_doctor_id'])
                ? Doctor::find($data['internal_doctor_id'])
                : null;

            $externalDoctor = !empty($data['referring_doctor_id'])
                ? Doctor::find($data['referring_doctor_id'])
                : null;

            $testPrice    = (float) ($data['test_price'] ?? 0);
            $suppliesCost = (float) ($data['supplies_cost'] ?? 0);

            // حساب العمولات تلقائياً من بيانات الطبيب
            $commInternal = $this->calculateCommission($internalDoctor, $testPrice);
            $commExternal = $this->calculateCommission($externalDoctor, $testPrice);

            // إنشاء سجل المريض
            $patient = Patient::create([
                'name'                => $data['name'],
                'phone'               => $data['phone'] ?? null,
                'age'                 => $data['age'] ?? null,
                'address'             => $data['address'] ?? null,
                'visit_type'          => $data['visit_type'] ?? null,
                'date'                => $data['date'] ?? now()->toDateString(),
                'referring_doctor_id' => $data['referring_doctor_id'] ?? null,
                'internal_doctor_id'  => $data['internal_doctor_id'] ?? null,
                'test_type_id'        => $data['test_type_id'] ?? null,
                'test_price'          => $testPrice,
                'supplies_cost'       => $suppliesCost,
                'commission_internal' => $commInternal,
                'commission_external' => $commExternal,
            ]);

            // إنشاء السجلات المالية
            $this->createFinancialRecords($patient, $internalDoctor, $externalDoctor);

            return $patient;
        });
    }

    /**
     * تحديث بيانات المريض مع إعادة حساب السجلات المالية.
     *
     * @param Patient $patient المريض الحالي
     * @param array   $data    البيانات الجديدة
     * @return Patient
     */
    public function updatePatient(Patient $patient, array $data): Patient
    {
        return DB::transaction(function () use ($patient, $data) {
            // حذف السجلات المالية القديمة
            $this->deleteFinancialRecords($patient);

            $internalDoctor = !empty($data['internal_doctor_id'])
                ? Doctor::find($data['internal_doctor_id'])
                : null;

            $externalDoctor = !empty($data['referring_doctor_id'])
                ? Doctor::find($data['referring_doctor_id'])
                : null;

            $testPrice    = (float) ($data['test_price'] ?? $patient->test_price);
            $suppliesCost = (float) ($data['supplies_cost'] ?? $patient->supplies_cost);

            // إعادة حساب العمولات
            $commInternal = $this->calculateCommission($internalDoctor, $testPrice);
            $commExternal = $this->calculateCommission($externalDoctor, $testPrice);

            // تحديث بيانات المريض
            $patient->update([
                'name'                => $data['name'] ?? $patient->name,
                'phone'               => $data['phone'] ?? $patient->phone,
                'age'                 => $data['age'] ?? $patient->age,
                'address'             => $data['address'] ?? $patient->address,
                'visit_type'          => $data['visit_type'] ?? $patient->visit_type,
                'date'                => $data['date'] ?? $patient->date,
                'referring_doctor_id' => $data['referring_doctor_id'] ?? null,
                'internal_doctor_id'  => $data['internal_doctor_id'] ?? null,
                'test_type_id'        => $data['test_type_id'] ?? $patient->test_type_id,
                'test_price'          => $testPrice,
                'supplies_cost'       => $suppliesCost,
                'commission_internal' => $commInternal,
                'commission_external' => $commExternal,
            ]);

            $patient->refresh();

            // إعادة إنشاء السجلات المالية بالبيانات المحدثة
            $this->createFinancialRecords($patient, $internalDoctor, $externalDoctor);

            return $patient;
        });
    }

    /**
     * حذف مريض مع جميع سجلاته المالية.
     */
    public function deletePatient(Patient $patient): void
    {
        DB::transaction(function () use ($patient) {
            $this->deleteFinancialRecords($patient);
            $patient->delete();
        });
    }

    /**
     * حساب العمولة بناءً على نوع العمولة (نسبة مئوية أو مبلغ ثابت).
     */
    private function calculateCommission(?Doctor $doctor, float $testPrice): float
    {
        if (!$doctor) {
            return 0;
        }

        if ($doctor->commission_type === 'Percentage') {
            return $testPrice * ($doctor->commission_value / 100);
        }

        return (float) $doctor->commission_value;
    }

    /**
     * إنشاء جميع السجلات المالية المرتبطة بمريض (Transactions + Payouts).
     */
    private function createFinancialRecords(Patient $patient, ?Doctor $internalDoctor, ?Doctor $externalDoctor): void
    {
        // 1. إيراد الفحص
        if ($patient->test_price > 0) {
            Transaction::create([
                'type'           => 'income',
                'category'       => 'test_revenue',
                'amount'         => $patient->test_price,
                'description'    => "إيراد فحص: {$patient->name}",
                'reference_id'   => $patient->id,
                'reference_type' => Patient::class,
                'date'           => $patient->date,
            ]);
        }

        // 2. عمولة الطبيب الداخلي
        if ($internalDoctor && $patient->commission_internal > 0) {
            DoctorPayout::create([
                'doctor_id'         => $internalDoctor->id,
                'doctor_type'       => 'internal',
                'patient_id'        => $patient->id,
                'amount'            => $patient->commission_internal,
                'calculation_basis' => $internalDoctor->commission_type,
                'calculation_value' => $internalDoctor->commission_value,
                'date'              => $patient->date,
                'is_paid'           => false,
            ]);

            Transaction::create([
                'type'           => 'payout',
                'category'       => 'doctor_commission',
                'amount'         => $patient->commission_internal,
                'description'    => "عمولة طبيب داخلي - {$patient->name}",
                'reference_id'   => $patient->id,
                'reference_type' => Patient::class,
                'date'           => $patient->date,
            ]);
        }

        // 3. عمولة الطبيب المحول (الخارجي)
        if ($externalDoctor && $patient->commission_external > 0) {
            DoctorPayout::create([
                'doctor_id'         => $externalDoctor->id,
                'doctor_type'       => 'external',
                'patient_id'        => $patient->id,
                'amount'            => $patient->commission_external,
                'calculation_basis' => $externalDoctor->commission_type,
                'calculation_value' => $externalDoctor->commission_value,
                'date'              => $patient->date,
                'is_paid'           => false,
            ]);

            Transaction::create([
                'type'           => 'payout',
                'category'       => 'doctor_commission',
                'amount'         => $patient->commission_external,
                'description'    => "عمولة طبيب محول - {$patient->name}",
                'reference_id'   => $patient->id,
                'reference_type' => Patient::class,
                'date'           => $patient->date,
            ]);
        }

        // 4. مصروف المستلزمات الطبية
        if ($patient->supplies_cost > 0) {
            Transaction::create([
                'type'           => 'expense',
                'category'       => 'medical_supplies',
                'amount'         => $patient->supplies_cost,
                'description'    => "مستلزمات طبية: {$patient->name}",
                'reference_id'   => $patient->id,
                'reference_type' => Patient::class,
                'date'           => $patient->date,
            ]);
        }
    }

    /**
     * حذف جميع السجلات المالية المرتبطة بمريض.
     */
    private function deleteFinancialRecords(Patient $patient): void
    {
        Transaction::where('reference_id', $patient->id)
            ->where('reference_type', Patient::class)
            ->delete();

        DoctorPayout::where('patient_id', $patient->id)->delete();
    }
}
