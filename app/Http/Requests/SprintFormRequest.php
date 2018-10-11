<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SprintFormRequest extends FormRequest
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
            'id' => 'exists:sprints,id',
            'sprintTitle' => 'required|string|unique:sprints,sprintTitle,'.$this->request->get('id').',id,milestone_id,'.$this->request->get('milestone_id'),
            'status' => 'required|in:created,assigned,onhold,inprogress,completed,cancelled,failed',
            'priority' => 'required|in:critical,high,medium,low',
            'milestone_id' => 'required|exists:milestones,id',
        ];
    }
}