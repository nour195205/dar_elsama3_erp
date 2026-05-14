<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DelegateVisit;
use Illuminate\Http\Request;

class DelegateVisitController extends Controller
{
    public function index()
    {
        $visits = DelegateVisit::with(['delegate:id,name', 'doctor:id,name'])
            ->orderBy('date', 'desc')
            ->get();

        return response()->json(['data' => $visits]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'delegate_id' => 'required|exists:delegates,id',
            'doctor_id'   => 'required|exists:doctors,id',
            'region'      => 'required|string|max:255',
            'day'         => 'required|string|max:50',
            'date'        => 'required|date',
        ]);

        $visit = DelegateVisit::create($data);
        $visit->load(['delegate:id,name', 'doctor:id,name']);

        return response()->json(['data' => $visit], 201);
    }

    public function update(Request $request, DelegateVisit $delegateVisit)
    {
        $data = $request->validate([
            'delegate_id' => 'required|exists:delegates,id',
            'doctor_id'   => 'required|exists:doctors,id',
            'region'      => 'required|string|max:255',
            'day'         => 'required|string|max:50',
            'date'        => 'required|date',
        ]);

        $delegateVisit->update($data);
        $delegateVisit->load(['delegate:id,name', 'doctor:id,name']);

        return response()->json(['data' => $delegateVisit]);
    }

    public function destroy(DelegateVisit $delegateVisit)
    {
        $delegateVisit->delete();

        return response()->json(['message' => 'Delegate visit deleted successfully.']);
    }
}
