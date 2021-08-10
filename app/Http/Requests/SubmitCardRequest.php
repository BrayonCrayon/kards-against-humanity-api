<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SubmitCardRequest extends FormRequest
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
        $submitAmount = request()->get('submitAmount');
        return [
            'submitAmount' => 'required|integer',
            'whiteCardIds' => "exists:white_cards,id|array|size:{$submitAmount}",
        ];
    }

    public function messages()
    {
        return [
            'whiteCardIds' => [
                'exists' => 'White card Id does not exist',
                'size' => 'Not enough cards submitted'
            ],
            'submitAmount' => [
                'required' => 'Submit amount is required',
                'integer' => 'integer required'
            ]
        ];
    }
}
