<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TestType;
use Illuminate\Http\Request;

class TestTypeController extends Controller
{
    public function index()
    {
        return response()->json(['data' => TestType::orderBy('name')->get()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'  => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
        ]);

        $testType = TestType::create($data);

        return response()->json(['data' => $testType], 201);
    }

    public function update(Request $request, TestType $testType)
    {
        $data = $request->validate([
            'name'  => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
        ]);

        $testType->update($data);

        return response()->json(['data' => $testType]);
    }

    public function destroy(TestType $testType)
    {
        $testType->delete();

        return response()->json(['message' => 'Test type deleted successfully.']);
    }
}
