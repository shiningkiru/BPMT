<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DocumentManagerFormRequest extends FormRequest
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
            'id' => 'exists:document_managers,id',
            'title' => 'required|string',
            'fileUrl' => 'mimes:jpeg,png,jpg,gif,svg,ppt,pdf,doc,docx,xls,xlsx,zip,tar|max:2048',
            'project_id' => 'required|exists:projects,id',//'nullable|required_without_all:doc_task_id,milestone_id,doc_sprint_id',
            'milestone_id' => 'exists:milestones,id',
            'doc_sprint_id' => 'exists:sprints,id',
            'doc_task_id' => 'exists:tasks,id',
            'relatedTo' => 'required|in:project,milestone,sprint,task',
        ];
    }
}