<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterFormRequest extends FormRequest
{
    public function authorize()
    {
         return true;
    }

    public function rules()
    {
        return [
            'id' => 'exists:users,id',
            'firstName' => 'required|string',
            'lastName' => 'string',
            'email' => 'required|email|unique:users,email,'.$this->request->get('id'),
            'mobileNumber' => 'required|phone_number:"Mobile Number"|unique:users,mobileNumber,'.$this->request->get('id'),
            'password' => 'custom_password:'.$this->request->get('id').'|string|min:6|max:10',
            'dob' => 'required|date',
            'doj' => 'required|date',
            'salary' => 'required|numeric',
            'company_id' => 'required|exists:companies,id',
            'bloodGroup' => 'string',
            'address' => 'string',
            'isActive' => 'boolean',
            'profilePic' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'company_id' => 'required|exists:companies,id',
            'branch_id' => 'required|exists:branches,id',
            'dept_id' => 'required|department',
            'designation_id' => 'required|designation',
            'roles' => 'required|in:admin,management,hr,team-lead,project-lead,employee',
        ];
    }
}