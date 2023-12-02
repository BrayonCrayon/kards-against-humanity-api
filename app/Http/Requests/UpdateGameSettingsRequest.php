<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateGameSettingsRequest extends FormRequest
{

    public function authorize() : bool
    {
        return $this->user()->can('update', $this->game);
    }

    public function rules() : array
    {
        return [
            'selection_timer' => 'sometimes|integer|max:300|min:60',
            'has_animations' => 'sometimes|boolean'
        ];
    }
}
