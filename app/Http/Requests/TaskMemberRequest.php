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
            'takenHour' => 'required|numeric|between:0,999.99',
            'task_id' => 'required|exists:tasks,id',
            'member_id' => 'required|exists:users,id',
        ];
    }
}
