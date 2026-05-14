<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDoctorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'type' => 'required|in:Internal,External',
            'address' => 'nullable|string|max:500',
            'commission_type' => 'required|in:Percentage,Fixed',
            'commission_value' => 'required|numeric|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'اسم الطبيب مطلوب',
            'type.required' => 'نوع الطبيب مطلوب',
            'commission_value.required' => 'قيمة العمولة مطلوبة',
        ];
    }
}
