<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use App\Models\Transaction;
use App\Models\DoctorPayout;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PatientController extends Controller
{
    public function index(Request $request)
    {
        $query = Patient::with(['referringDoctor:id,name', 'internalDoctor:id,name', 'testType:id,name,price']);

        // Optional date filter
        if ($request->has('date')) {
            $query->whereDate('date', $request->date);
        }

        if ($request->has('from') && $request->has('to')) {
            $query->whereBetween('date', [$request->from, $request->to]);
        }

        return response()->json(['data' => $query->orderBy('date', 'desc')->get()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'                => 'required|string|max:255',
            'age'                 => 'required|integer|min:0',
            'phone'               => 'required|string|max:20',
            'address'             => 'nullable|string|max:500',
            'visit_type'          => 'nullable|in:Initial,Follow-up',
            'date'                => 'required|date',
            'referring_doctor_id' => 'nullable|exists:doctors,id',
            'internal_doctor_id'  => 'nullable|exists:doctors,id',
            'test_type_id'        => 'nullable|exists:test_types,id',
            'test_price'          => 'required|numeric|min:0',
            'supplies_cost'       => 'required|numeric|min:0',
            'commission_external' => 'required|numeric|min:0',
            'commission_internal' => 'required|numeric|min:0',
        ]);

        $patient = DB::transaction(function () use ($data) {
            $patient = Patient::create($data);

            // --- Auto-generate financial records ---

            // 1. Income transaction from test price
            if ($patient->test_price > 0) {
                Transaction::create([
                    'type'           => 'income',
                    'category'       => 'test_revenue',
                    'amount'         => $patient->test_price,
                    'description'    => "Patient: {$patient->name}",
                    'reference_id'   => $patient->id,
                    'reference_type' => Patient::class,
                    'date'           => $patient->date,
                ]);
            }

            // 2. External doctor payout
            if ($patient->referring_doctor_id && $patient->commission_external > 0) {
                DoctorPayout::create([
                    'doctor_id'         => $patient->referring_doctor_id,
                    'doctor_type'       => 'external',
                    'patient_id'        => $patient->id,
                    'amount'            => $patient->commission_external,
                    'calculation_basis' => $patient->referringDoctor->commission_type ?? 'Flat',
                    'calculation_value' => $patient->referringDoctor->commission_value ?? 0,
                    'date'              => $patient->date,
                ]);

                Transaction::create([
                    'type'           => 'payout',
                    'category'       => 'doctor_commission',
                    'amount'         => $patient->commission_external,
                    'description'    => "External commission - {$patient->name}",
                    'reference_id'   => $patient->id,
                    'reference_type' => Patient::class,
                    'date'           => $patient->date,
                ]);
            }

            // 3. Internal doctor payout
            if ($patient->internal_doctor_id && $patient->commission_internal > 0) {
                DoctorPayout::create([
                    'doctor_id'         => $patient->internal_doctor_id,
                    'doctor_type'       => 'internal',
                    'patient_id'        => $patient->id,
                    'amount'            => $patient->commission_internal,
                    'calculation_basis' => $patient->internalDoctor->commission_type ?? 'Flat',
                    'calculation_value' => $patient->internalDoctor->commission_value ?? 0,
                    'date'              => $patient->date,
                ]);

                Transaction::create([
                    'type'           => 'payout',
                    'category'       => 'doctor_commission',
                    'amount'         => $patient->commission_internal,
                    'description'    => "Internal commission - {$patient->name}",
                    'reference_id'   => $patient->id,
                    'reference_type' => Patient::class,
                    'date'           => $patient->date,
                ]);
            }

            // 4. Supplies cost as expense transaction
            if ($patient->supplies_cost > 0) {
                Transaction::create([
                    'type'           => 'expense',
                    'category'       => 'medical_supplies',
                    'amount'         => $patient->supplies_cost,
                    'description'    => "Supplies for: {$patient->name}",
                    'reference_id'   => $patient->id,
                    'reference_type' => Patient::class,
                    'date'           => $patient->date,
                ]);
            }

            return $patient;
        });

        $patient->load(['referringDoctor:id,name', 'internalDoctor:id,name', 'testType:id,name,price']);

        return response()->json(['data' => $patient], 201);
    }

    public function update(Request $request, Patient $patient)
    {
        $data = $request->validate([
            'name'                => 'required|string|max:255',
            'age'                 => 'required|integer|min:0',
            'phone'               => 'required|string|max:20',
            'address'             => 'nullable|string|max:500',
            'visit_type'          => 'nullable|in:Initial,Follow-up',
            'date'                => 'required|date',
            'referring_doctor_id' => 'nullable|exists:doctors,id',
            'internal_doctor_id'  => 'nullable|exists:doctors,id',
            'test_type_id'        => 'nullable|exists:test_types,id',
            'test_price'          => 'required|numeric|min:0',
            'supplies_cost'       => 'required|numeric|min:0',
            'commission_external' => 'required|numeric|min:0',
            'commission_internal' => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($patient, $data) {
            // Remove old financial records for this patient
            Transaction::where('reference_id', $patient->id)
                ->where('reference_type', Patient::class)
                ->delete();

            DoctorPayout::where('patient_id', $patient->id)->delete();

            // Update patient
            $patient->update($data);
            $patient->refresh();

            // Re-create financial records with updated data
            if ($patient->test_price > 0) {
                Transaction::create([
                    'type'           => 'income',
                    'category'       => 'test_revenue',
                    'amount'         => $patient->test_price,
                    'description'    => "Patient: {$patient->name}",
                    'reference_id'   => $patient->id,
                    'reference_type' => Patient::class,
                    'date'           => $patient->date,
                ]);
            }

            if ($patient->referring_doctor_id && $patient->commission_external > 0) {
                DoctorPayout::create([
                    'doctor_id'         => $patient->referring_doctor_id,
                    'doctor_type'       => 'external',
                    'patient_id'        => $patient->id,
                    'amount'            => $patient->commission_external,
                    'calculation_basis' => $patient->referringDoctor->commission_type ?? 'Flat',
                    'calculation_value' => $patient->referringDoctor->commission_value ?? 0,
                    'date'              => $patient->date,
                ]);

                Transaction::create([
                    'type'           => 'payout',
                    'category'       => 'doctor_commission',
                    'amount'         => $patient->commission_external,
                    'description'    => "External commission - {$patient->name}",
                    'reference_id'   => $patient->id,
                    'reference_type' => Patient::class,
                    'date'           => $patient->date,
                ]);
            }

            if ($patient->internal_doctor_id && $patient->commission_internal > 0) {
                DoctorPayout::create([
                    'doctor_id'         => $patient->internal_doctor_id,
                    'doctor_type'       => 'internal',
                    'patient_id'        => $patient->id,
                    'amount'            => $patient->commission_internal,
                    'calculation_basis' => $patient->internalDoctor->commission_type ?? 'Flat',
                    'calculation_value' => $patient->internalDoctor->commission_value ?? 0,
                    'date'              => $patient->date,
                ]);

                Transaction::create([
                    'type'           => 'payout',
                    'category'       => 'doctor_commission',
                    'amount'         => $patient->commission_internal,
                    'description'    => "Internal commission - {$patient->name}",
                    'reference_id'   => $patient->id,
                    'reference_type' => Patient::class,
                    'date'           => $patient->date,
                ]);
            }

            if ($patient->supplies_cost > 0) {
                Transaction::create([
                    'type'           => 'expense',
                    'category'       => 'medical_supplies',
                    'amount'         => $patient->supplies_cost,
                    'description'    => "Supplies for: {$patient->name}",
                    'reference_id'   => $patient->id,
                    'reference_type' => Patient::class,
                    'date'           => $patient->date,
                ]);
            }
        });

        $patient->load(['referringDoctor:id,name', 'internalDoctor:id,name', 'testType:id,name,price']);

        return response()->json(['data' => $patient]);
    }

    public function destroy(Patient $patient)
    {
        DB::transaction(function () use ($patient) {
            Transaction::where('reference_id', $patient->id)
                ->where('reference_type', Patient::class)
                ->delete();

            DoctorPayout::where('patient_id', $patient->id)->delete();

            $patient->delete();
        });

        return response()->json(['message' => 'Patient deleted successfully.']);
    }
}
