<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use Illuminate\Http\Request;

class DoctorController extends Controller
{
    public function index()
    {
        return response()->json(['data' => Doctor::orderBy('name')->get()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'             => 'required|string|max:255',
            'type'             => 'required|in:Internal,External',
            'address'          => 'nullable|string|max:500',
            'commission_type'  => 'required|in:Flat,Percentage',
            'commission_value' => 'required|numeric|min:0',
        ]);

        $doctor = Doctor::create($data);

        return response()->json(['data' => $doctor], 201);
    }

    public function show(Doctor $doctor)
    {
        return response()->json(['data' => $doctor]);
    }

    public function update(Request $request, Doctor $doctor)
    {
        $data = $request->validate([
            'name'             => 'required|string|max:255',
            'type'             => 'required|in:Internal,External',
            'address'          => 'nullable|string|max:500',
            'commission_type'  => 'required|in:Flat,Percentage',
            'commission_value' => 'required|numeric|min:0',
        ]);

        $doctor->update($data);

        return response()->json(['data' => $doctor]);
    }

    public function destroy(Doctor $doctor)
    {
        $doctor->delete();

        return response()->json(['message' => 'Doctor deleted successfully.']);
    }
}
