<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ToDoRequest extends FormRequest
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
            'id' => 'nullable|exists:todos,id',
            'dateFor' => 'required|date',
            'details' => 'required',
            'status' => 'required|in:open,close',
            'relatedTo' => 'required|in:customer,project,general',
            'linkId' => 'todo_link_id:'.$this->get('relatedTo'),
            'to_do_resp_user' => 'required|exists:users,id'
        ];
    }
}
