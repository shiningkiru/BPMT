<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CompanyFormRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'id' => 'exists:companies,id',
            'name' => 'required|string',
            'email' => 'required|email|unique:companies,email,'.$this->request->get('id'),
            'mobileNumber' => 'required|phone_number:"Mobile Number"|unique:companies,mobileNumber,'.$this->request->get('id'),
            'logo' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'address' => 'required|string',
        ];
    }
}
