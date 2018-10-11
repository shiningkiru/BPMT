<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProjectTeamFormRequest extends FormRequest
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
            'id' => 'exists:project_teams,id',
            'team_user_id' => 'required|exists:users,id|unique:project_teams,team_user_id,'.$this->request->get('id').',id,team_project_id,'.$this->request->get('team_project_id'),
            'team_project_id' => 'required|exists:projects,id|unique:project_teams,team_project_id,'.$this->request->get('id').',id,team_user_id,'.$this->request->get('team_user_id'),
            'status' => 'required|in:active,inactive',
        ];
    }
}