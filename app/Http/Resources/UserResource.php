<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * API Resource لعرض بيانات المستخدم بشكل آمن.
 * يمنع كشف حقول حساسة مثل device_id, email_verified_at, etc.
 */
class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'email'       => $this->email,
            'phone'       => $this->phone,
            'role'        => $this->role,
            'is_active'   => $this->is_active,
            'hourly_rate' => $this->hourly_rate,
            'created_at'  => $this->created_at,
        ];
    }
}
