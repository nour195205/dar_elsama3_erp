<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use App\Services\PatientService;
use Illuminate\Http\Request;

class PatientController extends Controller
{
    public function __construct(
        private readonly PatientService $patientService,
    ) {}

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
            'supplies_cost'       => 'nullable|numeric|min:0',
        ]);

        $patient = $this->patientService->createPatient($data);

        $patient->load(['referringDoctor:id,name', 'internalDoctor:id,name', 'testType:id,name,price']);

        return response()->json(['data' => $patient], 201);
    }

    public function show(Patient $patient)
    {
        $patient->load(['referringDoctor:id,name', 'internalDoctor:id,name', 'testType:id,name,price']);
        return response()->json(['data' => $patient]);
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
            'supplies_cost'       => 'nullable|numeric|min:0',
        ]);

        $patient = $this->patientService->updatePatient($patient, $data);

        $patient->load(['referringDoctor:id,name', 'internalDoctor:id,name', 'testType:id,name,price']);

        return response()->json(['data' => $patient]);
    }

    public function destroy(Patient $patient)
    {
        $this->patientService->deletePatient($patient);

        return response()->json(['message' => 'Patient deleted successfully.']);
    }
}
