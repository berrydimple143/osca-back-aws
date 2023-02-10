<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'municipality' => 'required',
            'barangay' => 'required',
            'district_no' => 'required',
            'first_name' => 'required|string|max:191',
            'last_name' => 'required|string|max:191',
            'address' => 'required',
            'mobile' => 'required',
            'birth_place' => 'required',
            'gender' => 'required',
            'civil_status' => 'required',
            'blood_type' => 'required',
            'religion' => 'required',
            'education' => 'required',
            'employment_status' => 'required',
            'classification' => 'required',
            'contact_person' => 'required',
            'emergency_number' => 'required',
            'pension' => 'nullable',
            'gsis' => 'nullable',
            'sss' => 'nullable',
            'tin' => 'nullable',
            'philhealth' => 'nullable',
            'identification' => 'nullable|file',
            'middle_name' => 'nullable',
            'phone' => 'nullable',
            'email' => 'nullable|email|unique:users',
        ];
    }
}
