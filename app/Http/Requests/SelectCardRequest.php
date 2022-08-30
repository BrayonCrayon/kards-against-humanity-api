<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SelectCardRequest extends FormRequest
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
            'whiteCardIds' => "exists:white_cards,id|array|size:" . $this->game->blackCardPick,
        ];
    }

}
