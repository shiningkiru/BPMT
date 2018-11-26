<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AccessPrevilegesRequest extends FormRequest
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
            'access_previlages.*.id' => 'required|exists:access_previleges,id',
            'access_previlages.*.access_previlage' => 'required|in:read-only,editable,full-access,denied',
        ];
    }
}
