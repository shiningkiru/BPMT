<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TaskFormRequest extends FormRequest
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
            'id' => 'nullable|exists:tasks,id',
            'taskName' => 'required|string|unique:tasks,taskName,'.$this->request->get('id').',id,sprint_id,'.$this->request->get('sprint_id'),
            'status' => 'required|in:created,assigned,onhold,inprogress,completed,cancelled,failed',
            'priority' => 'required|in:critical,high,medium,low',
            'estimatedHours' => 'required|time_format',
            'takenHours' => 'time_format',
            'sprint_id' => 'required|exists:sprints,id',
            'startDate' =>'required|date|before_or_equal:endDate',
            'endDate' =>  'required|date|after_or_equal:startDate',
            'uatRelease' =>  'nullable|date',
            'prodRelease' =>  'nullable|date',
        ];
    }
}