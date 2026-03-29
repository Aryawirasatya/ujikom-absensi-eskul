<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreStudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'        => 'required|string|max:255',
            'nisn'        => [
                'required',
                'digits_between:5,15',
                'unique:users,nisn'
            ],
            'gender' => 'required|in:L,P',
            'grade'       => 'required|in:7,8,9',
            'class_label' => 'nullable|string|max:10',
            'photo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',

        ];
    }

}
