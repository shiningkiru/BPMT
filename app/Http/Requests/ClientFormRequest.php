<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ClientFormRequest extends FormRequest
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
            'id' => 'exists:clients,id',
            'name' => 'required|string',
            'mobileNumber' => 'required|phone_number:"Mobile Number"|unique:clients,mobileNumber,'.$this->request->get('id').',id,client_company_id,'.$this->request->get('company_id'),
            'secondaryMobileNumber' => 'phone_number:"Mobile Number"',
            'email' => 'required|email|unique:clients,email,'.$this->request->get('id').',id,client_company_id,'.$this->request->get('company_id'),
            'secondaryEmail' => 'email',
            'profilePic' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'address' => 'required|string',
            'status' => 'required|in:active,inactive',
            'company_id' => 'required|exists:companies,id',
        ];
    }
}
