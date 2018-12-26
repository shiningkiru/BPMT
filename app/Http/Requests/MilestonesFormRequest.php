<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MilestonesFormRequest extends FormRequest
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
            'title' => 'required|string|unique:milestones,title,'.$this->request->get('id').',id,project_milestone_id,'.$this->request->get('project_id'),
            'status' => 'required|in:created,assigned,onhold,inprogress,completed,cancelled,failed',
            'startDate' =>'required|before_or_equal:endDate',
            'endDate' =>  'required|after_or_equal:startDate',
            'estimatedHours' =>  'required|time_format',
            'project_id' => 'required|exists:projects,id|milestone_number:'.$this->get("id"),
        ];
    }
}