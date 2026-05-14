<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $this->route('id'),
            'password' => 'nullable|string|min:4|confirmed',
            'phone' => 'nullable|string|max:20',
            'role' => 'required|in:employee,manager,admin',
            'is_active' => 'sometimes|boolean',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            if ($this->input('role') === 'admin' && $this->user()?->role !== 'admin') {
                $v->errors()->add('role', 'فقط مسؤول النظام يمكنه تعيين دور مسؤول.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'name.required' => 'الاسم مطلوب',
            'email.required' => 'البريد الإلكتروني مطلوب',
            'email.unique' => 'البريد الإلكتروني موجود بالفعل',
            'role.required' => 'الدور الوظيفي مطلوب',
        ];
    }
}
