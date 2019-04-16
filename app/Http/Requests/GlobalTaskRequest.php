<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GlobalTaskRequest extends FormRequest
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
            'id' => 'nullable|exists:global_tasks,id',
            'projectCode' => 'required|unique:global_tasks,projectCode,'.$this->request->get('id').'|regex:/^([(IT)?(IT)?]{2})-[0-9]{4}-[0-9]{2}$/',
            'title'=>'required',
            'isActive' => 'required|in:active,inactive'
        ];
    }
}
