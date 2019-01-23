<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CustomerFormRequest extends FormRequest
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
            'id' => 'exists:customers,id',
            'company' => 'required|regex:/^[a-zA-Z0-9_ \-]*$/',
            'streetNo' => 'required|regex:/^[a-zA-Z0-9_ \-]*$/',
            'postCode' => 'required|numeric',
            'city' => 'required|regex:/^[a-zA-Z0-9_ \-]*$/',
            'country' => 'required|regex:/^[a-zA-Z0-9_ \-]*$/',
            'officeTel' => 'numeric',
            'branch' => 'required|regex:/^[a-zA-Z0-9_ \-]*$/',
            'homepage' => 'regex:/^[a-zA-Z0-9_ \-]*$/',
            'email' => 'email',//|unique:customers,email,'.$this->request->get('id').',id,customer_company_id,'.$this->request->get('company_id'),
            'details' => 'regex:/^[a-zA-Z0-9_ \-]*$/',
            'status' => 'required|in:active,inactive',
            'company_id' => 'required|exists:companies,id',
        ];
    }
}
