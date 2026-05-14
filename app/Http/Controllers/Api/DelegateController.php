<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Delegate;
use Illuminate\Http\Request;

class DelegateController extends Controller
{
    public function index()
    {
        return response()->json(['data' => Delegate::orderBy('name')->get()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'   => 'required|string|max:255',
            'region' => 'nullable|string|max:255',
        ]);

        $delegate = Delegate::create($data);

        return response()->json(['data' => $delegate], 201);
    }

    public function update(Request $request, Delegate $delegate)
    {
        $data = $request->validate([
            'name'   => 'required|string|max:255',
            'region' => 'nullable|string|max:255',
        ]);

        $delegate->update($data);

        return response()->json(['data' => $delegate]);
    }

    public function destroy(Delegate $delegate)
    {
        $delegate->delete();

        return response()->json(['message' => 'Delegate deleted successfully.']);
    }
}
