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
            'projectCode' => 'required|unique:projects,projectCode,'.$this->request->get('id').'|regex:/^([(IPR)?(PR)?]{2,3})-[0-9]{3,4}-[0-9]{2}$/',
            'status' => 'required|in:received,pending,started,in-progress,on-hold,completed,cancelled,new',
            'company_id' => 'required|exists:companies,id',
            'customer_project_id' => 'required|exists:customers,id',
            'project_lead_id' => 'required|exists:users,id',
            'projectCategory' =>'required|in:internal,external',
            'projectType' =>'required|in:service,support',
            'startDate' =>'required',
            'endDate' =>  'required',
            'estimatedHours' =>  'required|time_format'
        ];
    }
}