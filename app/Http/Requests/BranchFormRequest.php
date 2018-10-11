<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BranchFormRequest extends FormRequest
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
            'id' => 'exists:branches,id',
            'branchName' => 'required|string',
            'branchCode' => 'required|string|unique:branches,id,'.$this->request->get('id'),
            'address' => 'required|string',
            'company_id' => 'required|exists:companies,id',
        ];
    }
}
