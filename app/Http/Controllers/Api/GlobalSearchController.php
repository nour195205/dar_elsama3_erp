<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Delegate;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\TestType;
use App\Models\User;
use Illuminate\Http\Request;

class GlobalSearchController extends Controller
{
    public function __invoke(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        if (mb_strlen($q) < 2) {
            return response()->json([
                'patients' => [],
                'doctors' => [],
                'delegates' => [],
                'test_types' => [],
                'employees' => [],
            ]);
        }

        $term = '%' . addcslashes($q, '%_\\') . '%';

        $patients = Patient::query()
            ->where(function ($w) use ($term) {
                $w->where('name', 'like', $term)
                    ->orWhere('phone', 'like', $term);
            })
            ->orderByDesc('created_at')
            ->limit(60)
            ->get()
            ->unique(function (Patient $p) {
                $phone = $p->phone !== null && $p->phone !== ''
                    ? mb_strtolower(trim((string) $p->phone))
                    : null;

                return $phone ?: 'id-' . $p->id;
            })
            ->take(15)
            ->values()
            ->map(function (Patient $p) {
                return [
                    'id' => $p->id,
                    'name' => $p->name,
                    'phone' => $p->phone,
                    'age' => $p->age,
                    'address' => $p->address,
                    'label' => $p->name . ' — ' . $p->phone,
                    'url' => route('patients.edit', $p->id),
                ];
            });

        $doctors = Doctor::query()
            ->where(function ($w) use ($term) {
                $w->where('name', 'like', $term)
                    ->orWhere('address', 'like', $term);
            })
            ->orderBy('name')
            ->limit(12)
            ->get()
            ->map(fn (Doctor $d) => [
                'id' => $d->id,
                'name' => $d->name,
                'type' => $d->type,
                'label' => $d->name . ($d->type ? ' (' . ($d->type === 'Internal' ? 'داخلي' : 'خارجي') . ')' : ''),
                'url' => route('doctors.edit', $d->id),
            ]);

        $delegates = Delegate::query()
            ->where(function ($w) use ($term) {
                $w->where('name', 'like', $term)
                    ->orWhere('phone', 'like', $term)
                    ->orWhere('company', 'like', $term)
                    ->orWhere('region', 'like', $term);
            })
            ->orderBy('name')
            ->limit(12)
            ->get()
            ->map(fn (Delegate $d) => [
                'id' => $d->id,
                'name' => $d->name,
                'phone' => $d->phone,
                'label' => $d->name . ($d->phone ? ' — ' . $d->phone : ''),
                'url' => route('delegates.index'),
            ]);

        $testTypes = TestType::query()
            ->where('name', 'like', $term)
            ->orderBy('name')
            ->limit(12)
            ->get()
            ->map(fn (TestType $t) => [
                'id' => $t->id,
                'name' => $t->name,
                'label' => $t->name,
                'url' => route('test-types.edit', $t->id),
            ]);

        $employees = User::query()
            ->where('role', '!=', 'admin')
            ->where(function ($w) use ($term) {
                $w->where('name', 'like', $term)
                    ->orWhere('email', 'like', $term)
                    ->orWhere('phone', 'like', $term);
            })
            ->orderBy('name')
            ->limit(12)
            ->get()
            ->map(fn (User $u) => [
                'id' => $u->id,
                'name' => $u->name,
                'label' => $u->name . ($u->email ? ' — ' . $u->email : ''),
                'url' => route('employees.edit', $u->id),
            ]);

        return response()->json([
            'patients' => $patients,
            'doctors' => $doctors,
            'delegates' => $delegates,
            'test_types' => $testTypes,
            'employees' => $employees,
        ]);
    }
}
