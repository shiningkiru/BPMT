<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GlobalTaskWorkTrackRequest extends FormRequest
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
            'task_id' => 'required|exists:global_tasks,id',
            'user_id' => 'required|exists:users,id',
            'entryDate' => 'required|date',
            'takenHours' => 'required|time_format'
        ];
    }
}
