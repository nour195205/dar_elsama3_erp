<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:4|confirmed',
            'phone' => 'nullable|string|max:20',
            'role' => 'required|in:employee,manager,admin',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            if ($this->input('role') === 'admin' && $this->user()?->role !== 'admin') {
                $v->errors()->add('role', 'فقط مسؤول النظام يمكنه إنشاء حساب مسؤول جديد.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'name.required' => 'الاسم مطلوب',
            'email.required' => 'البريد الإلكتروني مطلوب',
            'email.unique' => 'البريد الإلكتروني موجود بالفعل',
            'password.required' => 'كلمة المرور مطلوبة',
            'password.min' => 'كلمة المرور يجب أن تكون 4 أحرف على الأقل',
            'role.required' => 'الدور الوظيفي مطلوب',
        ];
    }
}
