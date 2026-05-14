<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePatientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'age' => 'required|integer|min:0|max:150',
            'address' => 'nullable|string|max:500',
            'visit_type' => 'nullable|string',
            'date' => 'nullable|date',
            'referring_doctor_id' => 'nullable|exists:doctors,id',
            'internal_doctor_id' => 'nullable|exists:doctors,id',
            'test_type_id' => 'nullable|exists:test_types,id',
            'test_price' => 'nullable|numeric|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'اسم المريض مطلوب',
            'phone.required' => 'رقم الهاتف مطلوب',
            'age.required' => 'العمر مطلوب',
            'age.integer' => 'العمر يجب أن يكون رقماً صحيحاً',
        ];
    }
}
