<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TaskMemberRequest extends FormRequest
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
            'estimatedHour' => 'required|numeric|between:0,999.99',
            'task_id' => 'required|exists:tasks,id|unique:task_members,task_identification,'.$this->request->get('id').',id,member_identification,'.$this->request->get('member_id'),
            'member_id' => 'required|exists:users,id|unique:task_members,member_identification,'.$this->request->get('id').',id,task_identification,'.$this->request->get('task_id'),
            // 'task_id' => 'required|exists:tasks,id',
            // 'member_id' => 'required|exists:users,id',
        ];
    }
}
