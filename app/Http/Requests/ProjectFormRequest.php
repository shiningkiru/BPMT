<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProjectFormRequest extends FormRequest
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
            // 'id' => 'exists:projects,id',
            'projectName' => 'string|unique:projects,projectName,'.$this->request->get('id'),
            'projectCode' => 'unique:projects,projectCode,'.$this->request->get('id'),
            'status' => 'required|in:received,pending,started,in-progress,in-hold,completed,cancelled,new',
            'company_id' => 'required|exists:companies,id',
            'client_project_id' => 'required|exists:clients,id',
            'project_lead_id' => 'required|exists:users,id',
            'projectCategory' =>'required',
            'startDate' =>'required',
            'endDate' =>  'required',
            'estimatedHours' =>  'required|time_format'
        ];
    }
}