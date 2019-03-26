<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CustomerMeetingRequest extends FormRequest
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
            'id' => 'nullable|exists:customer_meetings,id',
            'customer_id' => 'required|exists:customers,id',
            'dateFor' => 'required|date',
            'status' => 'required|in:open,close',
            'details' => 'required'
        ];
    }
}
