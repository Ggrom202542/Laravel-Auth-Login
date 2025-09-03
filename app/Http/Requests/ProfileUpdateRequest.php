<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // อนุญาตให้ผู้ใช้ที่ล็อกอินแล้วแก้ไขโปรไฟล์ได้
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $userId = $this->user()->id ?? null;

        return [
            // ข้อมูลพื้นฐาน
            'prefix' => 'nullable|string|max:10',
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($userId)
            ],
            'phone' => 'nullable|string|max:20|regex:/^[\d\-\(\)\+\s]+$/',
            
            // ข้อมูลส่วนตัว
            'bio' => 'nullable|string|max:1000',
            'date_of_birth' => 'nullable|date|before:today|after:1900-01-01',
            'gender' => 'nullable|in:male,female,other,prefer_not_to_say',
            
            // ข้อมูลที่อยู่
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
            
            // การตั้งค่า
            'theme' => 'nullable|in:light,dark',
            'language' => 'nullable|in:th,en',
            'email_notifications' => 'nullable|boolean',
            'sms_notifications' => 'nullable|boolean',
            'push_notifications' => 'nullable|boolean',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'first_name.required' => 'กรุณากรอกชื่อ',
            'first_name.string' => 'ชื่อต้องเป็นตัวอักษร',
            'first_name.max' => 'ชื่อต้องไม่เกิน 100 ตัวอักษร',
            
            'last_name.required' => 'กรุณากรอกนามสกุล',
            'last_name.string' => 'นามสกุลต้องเป็นตัวอักษร',
            'last_name.max' => 'นามสกุลต้องไม่เกิน 100 ตัวอักษร',
            
            'email.required' => 'กรุณากรอกอีเมล',
            'email.email' => 'รูปแบบอีเมลไม่ถูกต้อง',
            'email.unique' => 'อีเมลนี้มีผู้ใช้งานแล้ว',
            'email.max' => 'อีเมลต้องไม่เกิน 255 ตัวอักษร',
            
            'phone.regex' => 'รูปแบบเบอร์โทรศัพท์ไม่ถูกต้อง',
            'phone.max' => 'เบอร์โทรศัพท์ต้องไม่เกิน 20 ตัวอักษร',
            
            'bio.max' => 'เนื้อหาแนะนำตัวต้องไม่เกิน 1,000 ตัวอักษร',
            
            'date_of_birth.date' => 'วันเกิดต้องเป็นรูปแบบวันที่ที่ถูกต้อง',
            'date_of_birth.before' => 'วันเกิดต้องเป็นวันที่ในอดีต',
            'date_of_birth.after' => 'วันเกิดต้องหลังปี ค.ศ. 1900',
            
            'gender.in' => 'กรุณาเลือกเพศที่ถูกต้อง',
            
            'address.max' => 'ที่อยู่ต้องไม่เกิน 500 ตัวอักษร',
            'city.max' => 'ชื่อเมืองต้องไม่เกิน 100 ตัวอักษร',
            'state.max' => 'ชื่อจังหวัด/รัฐต้องไม่เกิน 100 ตัวอักษร',
            'postal_code.max' => 'รหัสไปรษณีย์ต้องไม่เกิน 20 ตัวอักษร',
            'country.max' => 'ชื่อประเทศต้องไม่เกิน 100 ตัวอักษร',
            
            'theme.in' => 'กรุณาเลือกธีมที่ถูกต้อง (light หรือ dark)',
            'language.in' => 'กรุณาเลือกภาษาที่ถูกต้อง (th หรือ en)',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'first_name' => 'ชื่อ',
            'last_name' => 'นามสกุล',
            'email' => 'อีเมล',
            'phone' => 'เบอร์โทรศัพท์',
            'bio' => 'เนื้อหาแนะนำตัว',
            'date_of_birth' => 'วันเกิด',
            'gender' => 'เพศ',
            'address' => 'ที่อยู่',
            'city' => 'เมือง',
            'state' => 'จังหวัด/รัฐ',
            'postal_code' => 'รหัสไปรษณีย์',
            'country' => 'ประเทศ',
            'theme' => 'ธีม',
            'language' => 'ภาษา',
            'email_notifications' => 'การแจ้งเตือนทางอีเมล',
            'sms_notifications' => 'การแจ้งเตือนทาง SMS',
            'push_notifications' => 'การแจ้งเตือนแบบ Push',
        ];
    }
}
